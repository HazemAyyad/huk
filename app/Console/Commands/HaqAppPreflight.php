<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

final class HaqAppPreflight extends Command
{
    protected $signature = 'haq-app:preflight {--json : Machine-readable results without credentials}';

    protected $description = 'Read-only check of app administration migration, signing and import-worker prerequisites';

    public function handle(): int
    {
        $required = [
            'users' => ['id', 'name', 'email', 'password'],
            'religious_accounts' => ['id', 'name', 'email', 'password'],
            'personal_access_tokens' => ['id', 'tokenable_id', 'tokenable_type', 'token', 'abilities', 'expires_at'],
            'roles' => ['id', 'name'], 'permissions' => ['id', 'name'],
            'role_user' => ['user_id', 'role_id', 'user_type'],
            'permission_user' => ['user_id', 'permission_id', 'user_type'], 'permission_role' => ['role_id', 'permission_id'],
            'app_accounts' => ['id', 'user_id', 'religious_account_id', 'association_enabled', 'religious_enabled'],
            'app_admin_memberships' => ['app_account_id', 'permissions', 'locales', 'active'],
            'app_languages' => ['locale', 'revision', 'release_id', 'source_import_id'],
            'app_translation_drafts' => ['locale', 'resource_key', 'value', 'revision', 'status'],
            'app_content_releases' => ['payload', 'sha256', 'signature'], 'app_admin_audit' => ['action', 'subject'],
            'app_source_imports' => ['locale', 'status', 'artifact', 'sha256'],
        ];
        $queue = config('haq_app.import_connection');
        if (config('queue.connections.'.$queue.'.driver') === 'database') {
            $required[config('queue.connections.'.$queue.'.table', 'jobs')] = ['queue', 'payload', 'available_at'];
        }
        $missing = [];
        try {
            foreach ($required as $table => $columns) {
                if (! Schema::hasTable($table)) {
                    $missing[] = $table;
                } else {
                    foreach ($columns as $column) {
                        if (! Schema::hasColumn($table, $column)) {
                            $missing[] = $table.'.'.$column;
                        }
                    }
                }
            }
        } catch (\Throwable) {
            $missing[] = 'database_unavailable';
        }
        $secret = base64_decode((string) config('haq_app.signing_key'), true);
        $signing = function_exists('sodium_crypto_sign_detached') && is_string($secret) && strlen($secret) === 64;
        $result = ['schema_ready' => ! $missing, 'missing' => $missing, 'enabled' => (bool) config('haq_app.enabled'),
            'signing_ready' => $signing, 'import_connection' => $queue, 'worker_liveness' => 'not_checked'];
        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            $this->line('Schema: '.($missing ? 'missing prerequisites: '.implode(', ', $missing) : 'ready'));
            $this->line('App feature: '.($result['enabled'] ? 'enabled' : 'disabled'));
            $this->line('Publication signing: '.($signing ? 'configured' : 'not configured'));
            $this->line('Import connection: '.$queue.'; worker process liveness requires an actual queued import test.');
        }

        return $missing ? self::FAILURE : self::SUCCESS;
    }
}
