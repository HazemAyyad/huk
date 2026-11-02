<?php

namespace App\Services\Religious;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AppContentSettings
{
    public function current(): array
    {
        // Old deployments remain readable until the additive migration runs.
        $row = Schema::hasTable('app_content_settings') ? DB::table('app_content_settings')->where('id', 1)->first() : null;

        return ['show_translation_source_links' => (bool) ($row?->show_translation_source_links ?? false),
            'revision' => (int) ($row?->revision ?? 0)];
    }

    public function update(bool $visible, int $revision, int $account): array
    {
        abort_unless(Schema::hasTable('app_content_settings'), 503, 'app_settings_not_initialized');

        return DB::transaction(function () use ($visible, $revision, $account): array {
            DB::table('app_content_settings')->insertOrIgnore(['id' => 1, 'show_translation_source_links' => false,
                'revision' => 0, 'created_at' => now(), 'updated_at' => now()]);
            $row = DB::table('app_content_settings')->where('id', 1)->lockForUpdate()->first();
            abort_unless((int) $row->revision === $revision, 409, 'settings_revision_conflict');
            DB::table('app_content_settings')->where('id', 1)->update(['show_translation_source_links' => $visible,
                'revision' => $revision + 1, 'updated_by' => $account, 'updated_at' => now()]);
            app(AppAdminAccess::class)->audit($account, 'app.settings.updated', 'source_links', [
                'show_translation_source_links' => $visible, 'revision' => $revision + 1,
            ]);

            return $this->current();
        });
    }
}
