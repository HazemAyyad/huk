<?php

namespace App\Jobs;

use App\Models\AppAccount;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\QuranSourceArchive;
use App\Services\Religious\QuranTranslationStager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ImportQuranSource implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public int $tries = 1;

    public function __construct(public readonly int $importId) {}

    public function handle(QuranTranslationStager $stager, QuranSourceArchive $archive, AppAdminAccess $access): void
    {
        if (! DB::table('app_source_imports')->where('id', $this->importId)->where('status', 'queued')->update(['status' => 'running', 'updated_at' => now()])) {
            return;
        }
        try {
            $import = DB::table('app_source_imports')->where('id', $this->importId)->first();
            $owner = AppAccount::findOrFail($import->account_id);
            $access->require($owner, 'content.edit', $import->locale);
            $result = $stager->stage($import->locale, $import->provider_key);
            $json = file_get_contents($result['path']);
            $checked = $archive->validate($json, $import->locale, $import->provider_key, $result['sha256']);
            // Revocation while downloading cannot turn a background job into a write bypass.
            $access->require($owner->fresh(), 'content.edit', $import->locale);
            DB::transaction(function () use ($import, $result, $json, $checked, $access, $owner): void {
                DB::table('app_source_imports')->where('id', $this->importId)->where('status', 'running')->update([
                    'status' => 'ready', 'artifact' => $json, 'sha256' => $result['sha256'],
                    'summary' => json_encode(['title' => $checked['source']['title'], 'version' => $checked['source']['version'],
                        'direction' => $checked['source']['direction'], 'surahs' => $checked['surah_count'],
                        'items' => $checked['item_count'], 'bytes' => strlen($json),
                        'mapping_review_required' => $checked['mapping_review_required']], JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
                $access->audit($owner->id, 'source.imported', (string) $import->id, ['locale' => $import->locale, 'sha256' => $result['sha256']]);
            });
        } catch (Throwable) {
            $this->failed();
        }
    }

    public function failed(?Throwable $exception = null): void
    {
        DB::table('app_source_imports')->where('id', $this->importId)->whereIn('status', ['queued', 'running'])->update([
            'status' => 'failed', 'error_code' => 'source_import_failed', 'updated_at' => now(),
        ]);
    }
}
