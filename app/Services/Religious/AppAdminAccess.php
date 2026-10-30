<?php

namespace App\Services\Religious;

use App\Models\AppAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class AppAdminAccess
{
    // Existing Laratrust roles/direct permissions remain the owner of authorization.
    public const PERMISSIONS = [
        'admin.enter' => 'read_app_admin',
        'translations.edit' => 'update_app_translations',
        'content.edit' => 'update_app_content',
        'review' => 'review_app_content',
        'publish' => 'publish_app_content',
        'languages.manage' => 'update_app_languages',
        'team.manage' => 'update_app_team',
    ];

    public function grant(AppAccount $account): ?object
    {
        if (! config('haq_app.enabled')) {
            return null;
        }
        if (! $account->fresh()?->active || ! $account->user_id) {
            return null;
        }
        $grant = DB::table('app_admin_memberships')->where('app_account_id', $account->id)->where('active', true)->first();
        if (! $grant) {
            return null;
        }
        $staff = User::find($account->user_id);
        if (! $staff || $staff->getAttribute('deleted_at') || $staff->getAttribute('deleted_by') || $staff->getAttribute('must_change_password')) {
            return null;
        }
        // Do not cache this check: removing a role/permission revokes the next request.
        $roleIds = DB::table('role_user')->where('user_id', $staff->id)->where('user_type', $staff->getMorphClass())->pluck('role_id');
        $super = DB::table('roles')->whereIn('id', $roleIds)->where('name', 'superadmin')->exists();
        $effective = DB::table('permissions')->where(function ($query) use ($staff, $roleIds) {
            $query->whereIn('id', DB::table('permission_user')->where('user_id', $staff->id)->where('user_type', $staff->getMorphClass())->select('permission_id'))
                ->orWhereIn('id', DB::table('permission_role')->whereIn('role_id', $roleIds)->select('permission_id'));
        })->pluck('name')->all();
        $granted = json_decode($grant->permissions, true);
        $grant->effective_permissions = array_values(array_filter(array_keys(self::PERMISSIONS),
            fn ($key) => in_array($key, $granted, true) && ($super || in_array(self::PERMISSIONS[$key], $effective, true))));
        if (! in_array('admin.enter', $grant->effective_permissions, true)) {
            return null;
        }

        return $grant;
    }

    public function session(AppAccount $account): array
    {
        $grant = $this->grant($account);

        return ['admin_access' => $grant !== null, 'permissions' => $grant?->effective_permissions ?? [],
            'locales' => $grant ? json_decode($grant->locales, true) : []];
    }

    public function require(AppAccount $account, ?string $permission = null, ?string $locale = null): object
    {
        $grant = $this->grant($account);
        abort_unless($grant, 403, 'admin_access_denied');
        $permissions = $grant->effective_permissions;
        abort_if($permission && ! in_array($permission, $permissions, true), 403, 'permission_denied');
        $locales = json_decode($grant->locales, true);
        abort_if($locale && $locales && ! in_array($locale, $locales, true), 403, 'locale_denied');

        return $grant;
    }

    public function audit(?int $account, string $action, string $subject, array $details = []): void
    {
        DB::table('app_admin_audit')->insert(['account_id' => $account, 'action' => $action, 'subject' => $subject,
            'details' => json_encode($details, JSON_THROW_ON_ERROR), 'created_at' => now()]);
    }
}
