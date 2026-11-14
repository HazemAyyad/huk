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
            'show_translation_source_names' => (bool) ($row?->show_translation_source_names ?? false),
            'show_translation_footnotes' => (bool) ($row?->show_translation_footnotes ?? false),
            'revision' => (int) ($row?->revision ?? 0)];
    }

    public function update(array $settings, int $revision, int $account): array
    {
        abort_unless(Schema::hasTable('app_content_settings'), 503, 'app_settings_not_initialized');

        foreach (array_keys($settings) as $column) {
            abort_unless(Schema::hasColumn('app_content_settings', $column), 503, 'app_settings_not_initialized');
        }

        return DB::transaction(function () use ($settings, $revision, $account): array {
            DB::table('app_content_settings')->insertOrIgnore(['id' => 1, 'show_translation_source_links' => false,
                'revision' => 0, 'created_at' => now(), 'updated_at' => now()]);
            $row = DB::table('app_content_settings')->where('id', 1)->lockForUpdate()->first();
            abort_unless((int) $row->revision === $revision, 409, 'settings_revision_conflict');
            DB::table('app_content_settings')->where('id', 1)->update([...$settings,
                'revision' => $revision + 1, 'updated_by' => $account, 'updated_at' => now()]);
            app(AppAdminAccess::class)->audit($account, 'app.settings.updated', 'source_links', [
                ...$settings, 'revision' => $revision + 1,
            ]);

            return $this->current();
        });
    }
}
