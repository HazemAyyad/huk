<?php

namespace Database\Seeders;

use App\Models\AppAccount;
use App\Models\Permission;
use App\Models\ReligiousAccount;
use App\Models\Role;
use App\Models\User;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppLocalization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/** Explicit bootstrap only. Never called by DatabaseSeeder or a web request. */
final class HaqAppAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) config('haq_app.bootstrap_admin.email'));
        if ($email === '' && $this->command?->input->isInteractive()) {
            $email = (string) $this->command->ask('Email for the new Haq administrator');
        }
        $email = mb_strtolower(trim($email));
        Validator::make(['email' => $email], ['email' => 'required|email|max:255'])->validate();
        $existing = User::where('email', $email)->first();
        if ($existing) {
            $account = AppAccount::where('user_id', $existing->id)->first();
            if ($account && DB::table('app_admin_audit')->where('action', 'bootstrap.created')->where('subject', (string) $account->id)->exists()) {
                $this->command?->info('This bootstrap administrator already exists. Password, grants and account state were preserved.');

                return;
            }
            throw new RuntimeException('Email already belongs to an existing association identity. Use the explicit linking procedure; no account was promoted.');
        }
        if (ReligiousAccount::where('email', $email)->exists()) {
            throw new RuntimeException('Email already belongs to an existing religious identity. Use the explicit linking procedure; no account was merged.');
        }
        $password = (string) config('haq_app.bootstrap_admin.password');
        if ($password === '' && $this->command?->input->isInteractive()) {
            $password = (string) $this->command->secret('New administrator password (14–128 characters)');
            $confirmation = (string) $this->command->secret('Repeat password');
            if (! hash_equals($password, $confirmation)) {
                throw new RuntimeException('Password confirmation did not match.');
            }
        }
        if (mb_strlen($password) < 14 || mb_strlen($password) > 128) {
            throw new RuntimeException('A private 14–128 character password is required. No default administrator password exists.');
        }
        $name = trim((string) config('haq_app.bootstrap_admin.name'));
        Validator::make(['name' => $name], ['name' => 'required|string|max:100'])->validate();
        $id = DB::transaction(function () use ($email, $password, $name) {
            app(AppLocalization::class)->initialize();
            $role = Role::firstOrCreate(['name' => 'haq-content-admin'], ['display_name' => 'مدير محتوى حق']);
            $permissionIds = [];
            foreach (AppAdminAccess::PERMISSIONS as $permission) {
                $permissionIds[] = Permission::firstOrCreate(['name' => $permission], ['display_name' => $permission])->id;
            }
            $role->permissions()->syncWithoutDetaching($permissionIds);
            $user = User::create(['name' => $name, 'email' => $email, 'password' => $password]);
            $user->addRole($role);
            $religious = ReligiousAccount::create(['name' => $name, 'email' => $email, 'password' => $password]);
            // Both identities were created explicitly by this operation. Equal emails elsewhere never auto-link.
            $account = AppAccount::create(['user_id' => $user->id, 'religious_account_id' => $religious->id,
                'association_enabled' => true, 'religious_enabled' => true, 'active' => true]);
            DB::table('app_admin_memberships')->insert(['app_account_id' => $account->id, 'active' => true,
                'permissions' => json_encode(array_keys(AppAdminAccess::PERMISSIONS)), 'locales' => '[]', 'created_at' => now(), 'updated_at' => now()]);
            app(AppAdminAccess::class)->audit($account->id, 'bootstrap.created', (string) $account->id,
                ['user_id' => $user->id, 'religious_account_id' => $religious->id]);

            return $account->id;
        });
        $this->command?->info('Haq administrator created. App account ID: '.$id.'. Sign in with the email and password you supplied.');
    }
}
