<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppLocalization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AppTranslationExchangeController extends Controller
{
    public function __construct(private AppLocalization $catalog, private AppAdminAccess $access) {}

    public function export(Request $request, string $locale)
    {
        $this->access->require($request->user(), null, $locale);
        $input = $request->validate(['kind' => 'required|in:ui,content', 'page' => 'nullable|integer|min:1|max:10000']);
        $this->catalog->language($locale);
        $page = (int) ($input['page'] ?? 1);
        $records = $this->catalog->records($locale, $input['kind'], '', $page);

        return response()->json(['data' => ['format' => 'haq.translation.exchange', 'schema' => 1,
            'locale' => $locale, 'kind' => $input['kind'], 'page' => $page, 'total' => $records['total'],
            'per_page' => $records['per_page'], 'records' => array_map(fn ($row) => [
                'key' => $row['key'], 'revision' => $row['revision'], 'value' => $row['value'],
                'original' => $row['original'], 'suggestion' => $row['suggestion'] ?? null,
            ], $records['items'])]])->header('Cache-Control', 'no-store');
    }

    public function import(Request $request, string $locale)
    {
        return $this->process($request, $locale, false);
    }

    public function review(Request $request, string $locale)
    {
        return $this->process($request, $locale, true);
    }

    private function process(Request $request, string $locale, bool $review)
    {
        abort_if(strlen($request->getContent()) > 5 * 1024 * 1024, 413);
        $this->access->require($request->user(), null, $locale);
        $input = $request->validate([
            'dry_run' => 'sometimes|boolean', 'package' => 'required|array',
            'package.format' => 'required|in:haq.translation.exchange', 'package.schema' => 'required|integer|in:1',
            'package.locale' => 'required|string', 'package.records' => 'required|array|min:1|max:500',
            'package.records.*.key' => 'required|string|max:190|distinct',
            'package.records.*.revision' => 'required|integer|min:0', 'package.records.*.value' => 'required',
        ]);
        if ($input['package']['locale'] !== $locale) {
            throw ValidationException::withMessages(['package.locale' => 'translation_locale_mismatch']);
        }
        $this->catalog->language($locale);
        $rows = $input['package']['records'];
        // Validate every item and permission before any mutation. Metadata from an export
        // is advisory; neither imported review status nor source text grants publication.
        foreach ($rows as $row) {
            $this->access->require($request->user(), $review ? 'review' : (str_starts_with($row['key'], 'ui/') ? 'translations.edit' : 'content.edit'), $locale);
            $this->catalog->validateValue($row['key'], $row['value']);
        }
        $dry = $input['dry_run'] ?? true;
        $result = DB::transaction(function () use ($rows, $locale, $dry, $request, $review) {
            DB::table('app_languages')->where('locale', $locale)->lockForUpdate()->first();
            $conflicts = [];
            foreach ($rows as $row) {
                $current = DB::table('app_translation_drafts')->where('locale', $locale)->where('resource_key', $row['key'])->first();
                if (($current?->revision ?? 0) !== $row['revision'] || ($review && (! $current || json_decode($current->value, true) != $row['value']))) {
                    $conflicts[] = $row['key'];
                }
                if ($locale === 'ar' && str_starts_with($row['key'], 'content/')) {
                    $original = $this->catalog->resource($row['key']);
                    $item = app(\App\Services\Religious\ReligiousCatalog::class)->item(substr($row['key'], 8));
                    if (($item['type'] ?? '') === 'quran' && $row['value']['text'] !== $original['original']['text']) {
                        throw ValidationException::withMessages(['package.records' => 'quran_original_is_immutable']);
                    }
                }
            }
            if ($conflicts) {
                return ['conflicts' => $conflicts];
            }
            if (! $dry) {
                foreach ($rows as $row) {
                    if ($review) {
                        $this->catalog->review($locale, $row['key'], $row['revision'], $request->user()->id);
                    } else {
                        $this->catalog->save($locale, $row['key'], $row['value'], $row['revision'], $request->user()->id);
                    }
                }
                $this->access->audit($request->user()->id, $review ? 'translation.batch_reviewed' : 'translation.batch_imported', $locale, ['count' => count($rows)]);
            }

            return ['dry_run' => $dry, 'count' => count($rows), 'status' => $dry ? 'valid' : ($review ? 'reviewed' : 'draft')];
        });

        return response()->json(['data' => $result], isset($result['conflicts']) ? 409 : 200)->header('Cache-Control', 'no-store');
    }
}
