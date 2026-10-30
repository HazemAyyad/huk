<?php

namespace App\Services\Religious;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AppLocalization
{
    public function languages(): array
    {
        return json_decode(file_get_contents(resource_path('haq-app/languages.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function keys(): array
    {
        return json_decode(file_get_contents(resource_path('haq-app/ui.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function initialize(): void
    {
        DB::transaction(function (): void {
            foreach ($this->languages() as $language) {
                DB::table('app_languages')->insertOrIgnore(['locale' => $language['locale'], 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach ($this->keys() as $key => $values) {
                foreach ($values as $locale => $value) {
                    DB::table('app_translation_drafts')->insertOrIgnore(['locale' => $locale, 'resource_key' => 'ui/'.$key,
                        'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        'status' => 'draft', 'revision' => 1, 'editor_id' => 0, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        });
    }

    public function language(string $locale): object
    {
        abort_unless(in_array($locale, array_column($this->languages(), 'locale'), true), 404);

        return DB::table('app_languages')->where('locale', $locale)->first() ?? abort(404, 'language_not_initialized');
    }

    public function summary(): array
    {
        $states = DB::table('app_languages')->get()->keyBy('locale');

        return array_map(function ($language) use ($states) {
            $state = $states[$language['locale']] ?? null;
            $reviewed = DB::table('app_translation_drafts')->where('locale', $language['locale'])->where('resource_key', 'like', 'ui/%')->where('status', 'reviewed')->count();

            return [...$language, 'enabled' => (bool) ($state?->enabled ?? false), 'revision' => $state?->revision ?? 0,
                'release_id' => $state?->release_id, 'reviewed' => $reviewed, 'total' => count($this->keys())];
        }, $this->languages());
    }

    public function resource(string $key): array
    {
        if (str_starts_with($key, 'ui/')) {
            $entry = $this->keys()[substr($key, 3)] ?? null;
            abort_unless($entry, 404);

            return ['key' => $key, 'original' => $entry['ar'], 'suggestion' => $entry['en'] ?? null, 'kind' => 'ui'];
        }
        if (str_starts_with($key, 'content/')) {
            $item = app(ReligiousCatalog::class)->item(substr($key, 8));
            abort_unless($item, 404);

            return ['key' => $key, 'original' => array_intersect_key($item, array_flip(['title', 'text', 'summary', 'source', 'sourceUrl'])), 'kind' => 'content'];
        }
        abort(404);
    }

    public function records(string $locale, string $kind, string $search, int $page): array
    {
        $this->language($locale);
        $resources = $kind === 'content'
            ? array_map(fn ($item) => 'content/'.$item['id'], app(ReligiousCatalog::class)->dataset()['items'])
            : array_map(fn ($key) => 'ui/'.$key, array_keys($this->keys()));
        $resources = array_values(array_filter($resources, fn ($key) => $search === '' || str_contains(mb_strtolower($key.' '.json_encode($this->resource($key)['original'], JSON_UNESCAPED_UNICODE)), mb_strtolower($search))));
        $selected = array_slice($resources, ($page - 1) * 30, 30);
        $drafts = DB::table('app_translation_drafts')->where('locale', $locale)->whereIn('resource_key', $selected)->get()->keyBy('resource_key');

        return ['items' => array_map(function ($key) use ($drafts) {
            $draft = $drafts[$key] ?? null;

            return [...$this->resource($key), 'value' => $draft ? json_decode($draft->value, true) : null,
                'revision' => $draft?->revision ?? 0, 'status' => $draft?->status ?? 'missing'];
        }, $selected), 'page' => $page, 'total' => count($resources), 'per_page' => 30];
    }

    public function validateValue(string $key, mixed $value): void
    {
        $resource = $this->resource($key);
        if ($resource['kind'] === 'ui') {
            Validator::make(['value' => $value], ['value' => 'required|string|max:3000'])->validate();
            preg_match_all('/\{([a-zA-Z][a-zA-Z0-9_]*)\}/', $resource['original'], $expected);
            preg_match_all('/\{([a-zA-Z][a-zA-Z0-9_]*)\}/', $value, $actual);
            sort($expected[1]);
            sort($actual[1]);
            $stripped = preg_replace('/\{[a-zA-Z][a-zA-Z0-9_]*\}/', '', $value);
            if ($expected[1] !== $actual[1] || strpbrk($stripped, '{}') !== false || preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F]/u', $value)) {
                throw ValidationException::withMessages(['value' => 'invalid_translation_placeholders']);
            }
        } else {
            Validator::make(['value' => $value], [
                'value' => 'required|array:title,text,summary,source,sourceUrl',
                'value.title' => 'required|string|max:300', 'value.text' => 'required|string|max:150000',
                'value.summary' => 'nullable|string|max:2000', 'value.source' => 'required|string|max:1500',
                'value.sourceUrl' => 'required|url:https|max:2000',
            ])->validate();
        }
    }

    public function save(string $locale, string $key, mixed $value, int $revision, int $editor): array
    {
        $this->validateValue($key, $value);
        if ($locale === 'ar' && str_starts_with($key, 'content/')) {
            $original = app(ReligiousCatalog::class)->item(substr($key, 8));
            if (($original['type'] ?? null) === 'quran' && $value['text'] !== $original['text']) {
                throw ValidationException::withMessages(['value.text' => 'quran_original_is_immutable']);
            }
        }

        return DB::transaction(function () use ($locale, $key, $value, $revision, $editor) {
            // Serializes publication and editing of the same locale on MySQL.
            $language = DB::table('app_languages')->where('locale', $locale)->lockForUpdate()->first();
            abort_unless($language, 404);
            $previous = DB::table('app_translation_drafts')->where('locale', $locale)->where('resource_key', $key)->first();
            abort_unless(($previous?->revision ?? 0) === $revision, 409, 'revision_conflict');
            $data = ['value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'revision' => $revision + 1,
                'status' => 'draft', 'editor_id' => $editor, 'reviewer_id' => null, 'updated_at' => now()];
            DB::table('app_translation_drafts')->updateOrInsert(['locale' => $locale, 'resource_key' => $key], [...$data, 'created_at' => $previous?->created_at ?? now()]);
            app(AppAdminAccess::class)->audit($editor, 'translation.saved', $locale.':'.$key, ['revision' => $revision + 1]);

            return ['revision' => $revision + 1, 'status' => 'draft'];
        });
    }

    public function review(string $locale, string $key, int $revision, int $reviewer): void
    {
        DB::transaction(function () use ($locale, $key, $revision, $reviewer): void {
            DB::table('app_languages')->where('locale', $locale)->lockForUpdate()->first();
            $row = DB::table('app_translation_drafts')->where('locale', $locale)->where('resource_key', $key)->first();
            abort_unless($row && $row->revision === $revision, 409, 'revision_conflict');
            $this->validateValue($key, json_decode($row->value, true));
            DB::table('app_translation_drafts')->where('id', $row->id)->update(['status' => 'reviewed', 'reviewer_id' => $reviewer, 'updated_at' => now()]);
            app(AppAdminAccess::class)->audit($reviewer, 'translation.reviewed', $locale.':'.$key, ['revision' => $revision]);
        });
    }

    public function publish(string $locale, int $expectedRevision, int $publisher, ?int $restore = null): array
    {
        return DB::transaction(function () use ($locale, $expectedRevision, $publisher, $restore) {
            $language = DB::table('app_languages')->where('locale', $locale)->lockForUpdate()->first();
            abort_unless($language && $language->revision === $expectedRevision, 409, 'revision_conflict');
            $strings = [];
            $content = [];
            $source = null;
            if ($restore !== null) {
                $old = DB::table('app_content_releases')->where('locale', $locale)->where('id', $restore)->first() ?? abort(404);
                $snapshot = json_decode($old->payload, true, 512, JSON_THROW_ON_ERROR);
                $strings = $snapshot['strings'];
                $content = $snapshot['content'];
                $source = $snapshot['quran_source'] ?? null;
            } else {
                foreach (DB::table('app_translation_drafts')->where('locale', $locale)->where('status', 'reviewed')->get() as $row) {
                    if (str_starts_with($row->resource_key, 'ui/')) {
                        $strings[substr($row->resource_key, 3)] = json_decode($row->value, true);
                    } else {
                        $content[substr($row->resource_key, 8)] = json_decode($row->value, true);
                    }
                }
                if ($language->source_import_id ?? null) {
                    $selected = DB::table('app_source_imports')->where('id', $language->source_import_id)->where('locale', $locale)->where('status', 'reviewed')->first();
                    abort_unless($selected, 422, 'source_review_required');
                    app(QuranSourceArchive::class)->validate($selected->artifact, $locale, $selected->provider_key, $selected->sha256);
                    $source = ['artifact' => $selected->artifact, 'sha256' => $selected->sha256];
                }
            }
            foreach (array_keys($this->keys()) as $key) {
                if (! isset($strings[$key])) {
                    throw ValidationException::withMessages(['locale' => 'required_keys_not_reviewed']);
                }
                $this->validateValue('ui/'.$key, $strings[$key]);
            }
            if ($source !== null) {
                $metadata = json_decode($source['artifact'], true, 512, JSON_THROW_ON_ERROR);
                app(QuranSourceArchive::class)->validate($source['artifact'], $locale, $metadata['source']['key'], $source['sha256']);
            }
            $version = $expectedRevision + 1;
            $json = json_encode(['schema' => 1, 'locale' => $locale, 'version' => $version,
                'strings' => (object) $strings, 'content' => (object) $content, 'quran_source' => $source], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            abort_if(strlen($json) > config('haq_app.max_package_bytes'), 422, 'package_too_large');
            $secret = base64_decode(config('haq_app.signing_key'), true);
            abort_unless(function_exists('sodium_crypto_sign_detached') && is_string($secret) && strlen($secret) === SODIUM_CRYPTO_SIGN_SECRETKEYBYTES, 503, 'signing_key_not_configured');
            $signature = base64_encode(sodium_crypto_sign_detached($json, $secret));
            $id = DB::table('app_content_releases')->insertGetId(['locale' => $locale, 'payload' => $json, 'sha256' => hash('sha256', $json),
                'key_id' => config('haq_app.signing_key_id'), 'signature' => $signature, 'publisher_id' => $publisher, 'created_at' => now()]);
            DB::table('app_languages')->where('locale', $locale)->update(['enabled' => true, 'revision' => $version, 'release_id' => $id, 'updated_at' => now()]);
            app(AppAdminAccess::class)->audit($publisher, $restore ? 'release.restored' : 'release.published', $locale, ['release_id' => $id, 'restored_from' => $restore]);

            return ['release_id' => $id, 'revision' => $version];
        });
    }
}
