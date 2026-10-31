<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ImportHadithSource;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppLocalization;
use App\Services\Religious\HadithSourceArchive;
use App\Services\Religious\ReligiousCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AppHadithSourceController extends Controller
{
    public function __construct(private AppAdminAccess $access, private HadithSourceArchive $archive) {}

    public function index(Request $request, string $locale)
    {
        $this->access->require($request->user(), null, $locale);
        $language = app(AppLocalization::class)->language($locale);
        $available = true;
        try {
            $this->archive->language($locale);
        } catch (\RuntimeException) {
            $available = false;
        }
        $imports = DB::table('app_hadith_imports')->where('locale', $locale)->orderByDesc('id')->paginate(20);
        $imports->getCollection()->transform(fn ($row) => ['id' => $row->id, 'status' => $row->status, 'revision' => $row->revision,
            'summary' => $row->summary ? json_decode($row->summary, true) : null, 'error_code' => $row->error_code, 'created_at' => $row->created_at]);
        $items = array_values(array_map(fn ($item) => array_intersect_key($item, array_flip(['id', 'title', 'text', 'source', 'sourceUrl'])),
            array_filter(app(ReligiousCatalog::class)->dataset()['items'], fn ($item) => $item['type'] === 'hadith')));

        return response()->json(['data' => ['available' => $available, 'provider' => 'HadeethEnc.com', 'source_locale' => HadithSourceArchive::providerLocale($locale), 'catalog_checked_at' => '2026-10-30',
            'items' => $items, 'imports' => $imports, 'selected_id' => $language->hadith_import_id, 'language_revision' => $language->revision]])->header('Cache-Control', 'no-store');
    }

    public function create(Request $request, string $locale)
    {
        $this->access->require($request->user(), 'content.edit', $locale);
        app(AppLocalization::class)->language($locale);
        $input = $request->validate(['request_id' => 'required|uuid', 'mapping' => 'required|array|min:1|max:100',
            'mapping.*' => 'required|string|regex:/^[1-9][0-9]{0,9}$/D']);
        try {
            $this->archive->language($locale);
            $this->archive->references($input['mapping']);
        } catch (\RuntimeException $e) {
            abort(422, $e->getMessage());
        }
        ksort($input['mapping']);
        $connection = config('haq_app.import_connection');
        abort_unless(in_array(config('queue.connections.'.$connection.'.driver'), ['database', 'redis', 'sqs', 'beanstalkd'], true), 503, 'source_worker_required');
        $actor = $request->user()->id;
        $created = false;
        $id = DB::transaction(function () use ($locale, $input, $actor, &$created) {
            DB::table('app_languages')->where('locale', $locale)->lockForUpdate()->first();
            $existing = DB::table('app_hadith_imports')->where('account_id', $actor)->where('request_id', $input['request_id'])->first();
            if ($existing) {
                abort_unless($existing->locale === $locale && json_decode($existing->requested_mapping, true) === $input['mapping'], 409, 'request_id_reused');

                return $existing->id;
            }
            foreach (DB::table('app_hadith_imports')->where('locale', $locale)->whereIn('status', ['queued', 'running'])->get() as $running) {
                if (json_decode($running->requested_mapping, true) === $input['mapping']) {
                    return $running->id;
                }
            }
            $created = true;
            $id = DB::table('app_hadith_imports')->insertGetId(['account_id' => $actor, 'request_id' => $input['request_id'],
                'locale' => $locale, 'requested_mapping' => json_encode($input['mapping'], JSON_THROW_ON_ERROR), 'status' => 'queued',
                'created_at' => now(), 'updated_at' => now()]);
            $this->access->audit($actor, 'hadith.queued', (string) $id, ['locale' => $locale, 'items' => count($input['mapping'])]);

            return $id;
        });
        if ($created) {
            try {
                ImportHadithSource::dispatch($id)->onConnection($connection)->onQueue('haq-imports');
            } catch (\Throwable) {
                DB::table('app_hadith_imports')->where('id', $id)->update(['status' => 'failed', 'error_code' => 'source_queue_unavailable', 'updated_at' => now()]);
            }
        }

        return response()->json(['data' => ['id' => $id, 'status' => DB::table('app_hadith_imports')->where('id', $id)->value('status')]], 202);
    }

    private function item(string $locale, int $id): object
    {
        return DB::table('app_hadith_imports')->where('locale', $locale)->where('id', $id)->first() ?? abort(404);
    }

    public function preview(Request $request, string $locale, int $id)
    {
        $this->access->require($request->user(), null, $locale);
        $input = $request->validate(['content_id' => 'nullable|string|max:100']);
        $row = $this->item($locale, $id);
        abort_unless(in_array($row->status, ['ready', 'reviewed'], true), 409, 'source_not_ready');
        $checked = $this->archive->validate($row->artifact, $locale, $row->sha256);
        $contentId = $input['content_id'] ?? array_key_first($checked['references']);
        abort_unless(isset($checked['references'][$contentId]), 404);
        $reference = $checked['references'][$contentId];
        $original = app(ReligiousCatalog::class)->item($contentId);

        return response()->json(['data' => ['id' => $id, 'status' => $row->status, 'revision' => $row->revision,
            'source' => $checked['source'], 'content_ids' => array_keys($checked['references']), 'content_id' => $contentId,
            'direction' => $checked['direction'], 'attribution' => $checked['attribution'],
            'original' => array_intersect_key($original, array_flip(['id', 'title', 'text', 'source', 'sourceUrl'])),
            'record' => $checked['records'][$reference['source_id']], 'mapping_review_required' => $checked['mapping_review_required']]])->header('Cache-Control', 'no-store');
    }

    public function review(Request $request, string $locale, int $id)
    {
        $this->access->require($request->user(), 'review', $locale);
        $input = $request->validate(['revision' => 'required|integer|min:1', 'references_confirmed' => 'required|array|min:1|max:100',
            'references_confirmed.*' => 'required|string|distinct|max:100', 'mapping_confirmed' => 'required|boolean']);
        DB::transaction(function () use ($request, $locale, $id, $input): void {
            $row = DB::table('app_hadith_imports')->where('locale', $locale)->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            abort_unless(in_array($row->status, ['ready', 'reviewed'], true) && $row->revision === $input['revision'], 409, 'source_revision_conflict');
            $checked = $this->archive->validate($row->artifact, $locale, $row->sha256);
            $confirmed = $input['references_confirmed'];
            $expected = array_keys($checked['references']);
            sort($confirmed);
            sort($expected);
            abort_unless($confirmed === $expected, 422, 'hadith_references_review_required');
            abort_if($checked['mapping_review_required'] && ! $input['mapping_confirmed'], 422, 'source_mapping_review_required');
            DB::table('app_hadith_imports')->where('id', $id)->update(['status' => 'reviewed', 'revision' => $row->revision + 1,
                'reviewer_id' => $request->user()->id, 'updated_at' => now()]);
            $this->access->audit($request->user()->id, 'hadith.reviewed', (string) $id, ['locale' => $locale, 'sha256' => $row->sha256, 'references' => $confirmed]);
        });

        return response()->json(['data' => ['reviewed' => true]]);
    }

    public function select(Request $request, string $locale)
    {
        $this->access->require($request->user(), 'publish', $locale);
        $input = $request->validate(['source_id' => 'present|nullable|integer|min:1', 'language_revision' => 'required|integer|min:1']);
        DB::transaction(function () use ($request, $locale, $input): void {
            $language = DB::table('app_languages')->where('locale', $locale)->lockForUpdate()->first() ?? abort(404);
            abort_unless($language->revision === $input['language_revision'], 409, 'revision_conflict');
            if ($input['source_id'] !== null) {
                $source = $this->item($locale, $input['source_id']);
                abort_unless($source->status === 'reviewed', 422, 'source_review_required');
                $this->archive->validate($source->artifact, $locale, $source->sha256);
            }
            DB::table('app_languages')->where('locale', $locale)->update(['hadith_import_id' => $input['source_id'],
                'revision' => $language->revision + 1, 'updated_at' => now()]);
            $this->access->audit($request->user()->id, 'hadith.selected', $locale, ['source_id' => $input['source_id']]);
        });

        return response()->json(['data' => ['selected' => true]]);
    }
}
