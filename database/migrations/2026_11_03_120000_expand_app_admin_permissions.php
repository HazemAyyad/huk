<?php

use App\Models\Permission;
use App\Models\User;
use App\Services\Religious\AppAdminAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('app_admin_memberships')) {
            return;
        }
        foreach (AppAdminAccess::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
        // Only existing global superadmin memberships receive the new app scope.
        // Ordinary editors retain their exact grants and require explicit delegation.
        foreach (DB::table('app_admin_memberships')->get() as $grant) {
            if (json_decode($grant->locales, true) !== []) {
                continue;
            }
            $staff = DB::table('app_accounts')->where('id', $grant->app_account_id)->value('user_id');
            $super = DB::table('role_user')->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', $staff)->where('role_user.user_type', (new User)->getMorphClass())->where('roles.name', 'superadmin')->exists();
            if ($staff && $super) {
                DB::table('app_admin_memberships')->where('id', $grant->id)->update([
                    'permissions' => json_encode(array_values(array_unique([...json_decode($grant->permissions, true), ...array_keys(AppAdminAccess::PERMISSIONS)]))),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Permission names may be delegated to staff; never delete unrelated grants on rollback.
    }
};
