<?php

namespace App\Console\Commands;

use App\Models\AppAccount;
use App\Models\Permission;
use App\Models\ReligiousAccount;
use App\Models\User;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppLocalization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class InitializeHaqApp extends Command
{
    protected $signature = 'haq-app:initialize {--user= : Existing association user ID} {--religious= : Existing religious account ID} {--association=1 : Allow association area} {--permissions= : Comma-separated app permission keys} {--locales= : Comma-separated locale scope; empty means all}';

    protected $description = 'Initialize language drafts and explicitly link existing identities; never matches emails or resets role tables';

    public function handle(AppLocalization $catalog, AppAdminAccess $access): int
    {
        $permissions = array_values(array_filter(explode(',', (string) $this->option('permissions'))));
        $locales = array_values(array_filter(explode(',', (string) $this->option('locales'))));
        if (array_diff($permissions, array_keys(AppAdminAccess::PERMISSIONS)) || array_diff($locales, array_column($catalog->languages(), 'locale')) || ! in_array($this->option('association'), ['0', '1'], true)) {
            $this->error('Invalid permissions, locale scope or association flag.');

            return self::FAILURE;
        }
        $user = $this->option('user') ? User::findOrFail($this->option('user')) : null;
        $religious = $this->option('religious') ? ReligiousAccount::findOrFail($this->option('religious')) : null;
        if (($permissions && ! $user) || ($user && ! $religious && $this->option('association') === '0')) {
            $this->error('A staff identity is required for admin grants; at least one account area must remain available.');

            return self::FAILURE;
        }
        if ($user && $religious) {
            $first = AppAccount::where('user_id', $user->id)->first();
            $second = AppAccount::where('religious_account_id', $religious->id)->first();
            if (($first && $second && $first->id !== $second->id) || ($first?->religious_account_id && $first->religious_account_id !== $religious->id) || ($second?->user_id && $second->user_id !== $user->id)) {
                $this->error('Conflicting existing identity links. No automatic merge; review accounts and their active sessions first.');

                return self::FAILURE;
            }
        }
        DB::transaction(function () use ($catalog, $user, $religious, $permissions, $locales, $access): void {
            $catalog->initialize();
            foreach (AppAdminAccess::PERMISSIONS as $name) {
                Permission::firstOrCreate(['name' => $name], ['display_name' => $name]);
            }
            if (! $user && ! $religious) {
                return;
            }
            $query = AppAccount::query();
            $query->where(function ($query) use ($user, $religious): void {
                if ($user) {
                    $query->where('user_id', $user->id);
                }
                if ($religious) {
                    $query->orWhere('religious_account_id', $religious->id);
                }
            });
            $account = $query->first() ?? new AppAccount;
            $account->user_id = $user?->id ?? $account->user_id;
            $account->religious_account_id = $religious?->id ?? $account->religious_account_id;
            $account->association_enabled = (bool) ($account->user_id && $this->option('association') === '1');
            $account->religious_enabled = $account->religious_account_id !== null;
            $account->active = true;
            $account->save();
            if ($permissions) {
                // The employee must separately hold matching Laratrust permissions.
                DB::table('app_admin_memberships')->updateOrInsert(['app_account_id' => $account->id], [
                    'active' => true, 'permissions' => json_encode($permissions), 'locales' => json_encode($locales),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $access->audit(null, 'identity.configured_by_console', (string) $account->id, [
                'user_id' => $account->user_id, 'religious_account_id' => $account->religious_account_id,
                'areas' => $account->areas(), 'permissions' => $permissions, 'locales' => $locales,
            ]);
            $this->info('App account ID: '.$account->id);
        });
        $this->info('35 language records initialized. Seed translations remain drafts. Existing roles/permissions and content were preserved.');

        return self::SUCCESS;
    }
}
