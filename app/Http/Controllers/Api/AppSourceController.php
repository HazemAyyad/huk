<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ImportQuranSource;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppLocalization;
use App\Services\Religious\QuranSourceArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AppSourceController extends Controller
{
    public function __construct(private AppAdminAccess $access, private QuranSourceArchive $archive) {}

    public function index(Request $request, string $locale)
    {
        $this->access->require($request->user(), null, $locale);
        $language = app(AppLocalization::class)->language($locale);
        $index = json_decode(file_get_contents(resource_path('haq-app/sources/quranenc-index-20261030.json')), true, 512, JSON_THROW_ON_ERROR);
        $catalog = array_values(array_filter($index['translations'], fn ($source) => $source['language_iso_code'] === QuranSourceArchive::providerLocale($locale)));
        $imports = DB::table('app_source_imports')->where('locale', $locale)->orderByDesc('id')->paginate(20);
        $imports->getCollection()->transform(function ($row) {
            return ['id' => $row->id, 'provider_key' => $row->provider_key, 'status' => $row->status,
                'revision' => $row->revision, 'summary' => $row->summary ? json_decode($row->summary, true) : null,
                'error_code' => $row->error_code, 'created_at' => $row->created_at];
        });

        return response()->json(['data' => ['catalog' => $catalog, 'catalog_checked_at' => '2026-10-30',
            'imports' => $imports, 'selected_id' => $language->source_import_id, 'language_revision' => $language->revision]])->header('Cache-Control', 'no-store');
    }

    public function create(Request $request, string $locale)
    {
        $this->access->require($request->user(), 'content.edit', $locale);
        app(AppLocalization::class)->language($locale);
        $data = $request->validate(['request_id' => 'required|uuid', 'provider_key' => 'required|string|regex:/^[a-z0-9_]{1,80}$/D']);
        $index = json_decode(file_get_contents(resource_path('haq-app/sources/quranenc-index-20261030.json')), true, 512, JSON_THROW_ON_ERROR);
        $source = collect($index['translations'])->firstWhere('key', $data['provider_key']);
        abort_unless($source && $locale !== 'ar' && $source['language_iso_code'] === QuranSourceArchive::providerLocale($locale), 422, 'source_locale_mismatch');
        $connection = config('haq_app.import_connection');
        abort_unless(in_array(config('queue.connections.'.$connection.'.driver'), ['database', 'redis', 'sqs', 'beanstalkd'], true), 503, 'source_worker_required');
        $account = $request->user()->id;
        $created = false;
        $id = DB::transaction(function () use ($data, $locale, $account, &$created) {
            DB::table('app_languages')->where('locale', $locale)->lockForUpdate()->first();
            $existing = DB::table('app_source_imports')->where('account_id', $account)->where('request_id', $data['request_id'])->first();
            if ($existing) {
                abort_unless($existing->locale === $locale && $existing->provider_key === $data['provider_key'], 409, 'request_id_reused');

                return $existing->id;
            }
            $running = DB::table('app_source_imports')->where('locale', $locale)->where('provider_key', $data['provider_key'])
                ->whereIn('status', ['queued', 'running'])->first();
            if ($running) {
                return $running->id;
            }
            $created = true;
            $id = DB::table('app_source_imports')->insertGetId(['account_id' => $account, 'request_id' => $data['request_id'],
                'locale' => $locale, 'provider_key' => $data['provider_key'], 'status' => 'queued', 'created_at' => now(), 'updated_at' => now()]);
            $this->access->audit($account, 'source.queued', (string) $id, ['locale' => $locale, 'provider_key' => $data['provider_key']]);

            return $id;
        });
        if ($created) {
            try {
                ImportQuranSource::dispatch($id)->onConnection($connection)->onQueue('haq-imports');
            } catch (\Throwable) {
                DB::table('app_source_imports')->where('id', $id)->update(['status' => 'failed', 'error_code' => 'source_queue_unavailable', 'updated_at' => now()]);
            }
        }

        return response()->json(['data' => ['id' => $id, 'status' => DB::table('app_source_imports')->where('id', $id)->value('status')]], 202);
    }

    private function item(string $locale, int $id): object
    {
        return DB::table('app_source_imports')->where('locale', $locale)->where('id', $id)->first() ?? abort(404);
    }

    public function preview(Request $request, string $locale, int $id)
    {
        $this->access->require($request->user(), null, $locale);
        $input = $request->validate(['surah' => 'nullable|integer|min:1|max:114']);
        $row = $this->item($locale, $id);
        abort_unless(in_array($row->status, ['ready', 'reviewed'], true), 409, 'source_not_ready');
        $verified = $this->archive->validate($row->artifact, $locale, $row->provider_key, $row->sha256);
        $artifact = json_decode($row->artifact, true, 512, JSON_THROW_ON_ERROR);
        $surahs = array_map('intval', array_keys($artifact['surahs']));
        $surah = $input['surah'] ?? $surahs[0];
        abort_unless(in_array($surah, $surahs, true), 404);

        return response()->json(['data' => ['id' => $row->id, 'status' => $row->status, 'revision' => $row->revision,
            'source' => $verified['source'], 'attribution' => $artifact['attribution'], 'policy_url' => $artifact['policy_url'],
            'surahs' => $surahs, 'surah' => $surah, 'mapping_review_required' => $verified['mapping_review_required'],
            'rows' => array_values(array_filter($verified['verses'], fn ($verse) => (int) $verse['sura'] === $surah))]])->header('Cache-Control', 'no-store');
    }

    public function review(Request $request, string $locale, int $id)
    {
        $this->access->require($request->user(), 'review', $locale);
        $input = $request->validate(['revision' => 'required|integer|min:1', 'mapping_confirmed' => 'required|boolean']);
        DB::transaction(function () use ($request, $locale, $id, $input): void {
            $row = DB::table('app_source_imports')->where('locale', $locale)->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            abort_unless(in_array($row->status, ['ready', 'reviewed'], true) && $row->revision === $input['revision'], 409, 'source_revision_conflict');
            $checked = $this->archive->validate($row->artifact, $locale, $row->provider_key, $row->sha256);
            abort_if($checked['mapping_review_required'] && ! $input['mapping_confirmed'], 422, 'source_mapping_review_required');
            DB::table('app_source_imports')->where('id', $id)->update(['status' => 'reviewed', 'revision' => $row->revision + 1,
                'reviewer_id' => $request->user()->id, 'updated_at' => now()]);
            $this->access->audit($request->user()->id, 'source.reviewed', (string) $id, ['locale' => $locale, 'sha256' => $row->sha256]);
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
            }
            DB::table('app_languages')->where('locale', $locale)->update(['source_import_id' => $input['source_id'],
                'revision' => $language->revision + 1, 'updated_at' => now()]);
            $this->access->audit($request->user()->id, 'source.selected', $locale, ['source_id' => $input['source_id']]);
        });

        return response()->json(['data' => ['selected' => true]]);
    }
}
