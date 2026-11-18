<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use App\Models\User;
use App\Services\Religious\AppAdminAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class AssociationStaff
{
    public function __construct(private AssociationOperations $operations, private AssociationAccess $access, private AssociationRecords $records) {}

    public function details(User $staff): array
    {
        $roles = $staff->roles()->orderBy('name')->pluck('name')->all();
        $account = AppAccount::where('user_id', $staff->id)->first();
        $membership = $account ? DB::table('app_admin_memberships')->where('app_account_id', $account->id)->first() : null;
        $policy = $account ? DB::table('association_account_policies')->where('account_id', $account->id)->first() : null;

        return ['roles' => $roles, 'permissions' => $staff->permissions()->orderBy('name')->pluck('name')->all(),
            'app_permissions' => $membership ? json_decode($membership->permissions, true) : [],
            'association_global' => (bool) ($membership->association_global ?? false),
            'association_categories' => $membership ? json_decode($membership->association_categories ?? '[]', true) : [],
            'app_enabled' => (bool) ($account?->association_enabled && $account?->active && ($policy->app_enabled ?? true)),
            'admin_enabled' => (bool) ($membership->active ?? false), 'device_limit' => $policy->device_limit ?? null];
    }

    public function fields(): array
    {
        $fields = [];
        foreach (['name' => ['الاسم', 'text', true], 'email' => ['البريد الإلكتروني', 'text', true], 'password' => ['كلمة المرور', 'password', false],
            'title' => ['المسمى الوظيفي', 'text', false], 'basic_salary' => ['الراتب الأساسي', 'number', false],
            'roles' => ['الأدوار', 'multiselect', false], 'permissions' => ['صلاحيات النظام', 'multiselect', false],
            'app_permissions' => ['صلاحيات التطبيق', 'multiselect', false], 'association_global' => ['جميع فئات الجمعية', 'bool', false],
            'association_categories' => ['الفئات المفوضة', 'lookup_many', false], 'app_enabled' => ['دخول التطبيق', 'bool', false],
            'admin_enabled' => ['دخول لوحة الإدارة', 'bool', false], 'device_limit' => ['عدد الأجهزة (فارغ بلا حد)', 'number', false]] as $key => [$label, $type, $required]) {
            $field = ['key' => $key, 'label' => $label, 'label_en' => \Illuminate\Support\Str::headline($key), 'type' => $type, 'required' => $required, 'nullable' => ! $required];
            if ($key === 'roles' || $key === 'permissions') { $field['options'] = DB::table($key)->orderBy('name')->pluck('name')->all(); }
            if ($key === 'app_permissions') { $field['options'] = array_keys(AppAdminAccess::PERMISSIONS); }
            if ($key === 'association_categories') { $field['lookup'] = 'categories'; }
            $fields[] = $field;
        }

        return $fields;
    }

    public function save(AppAccount $actor, object $grant, array $input, ?int $id): array
    {
        return $this->operations->run($actor, 'staff.'.($id ? 'update' : 'create'), $input, function () use ($actor, $grant, $input, $id): array {
            $staff = $id ? User::whereKey($id)->lockForUpdate()->firstOrFail() : new User;
            $before = $id ? $this->records->revisionData('staff', $staff) : null;
            if ($before) { $this->operations->expect($before, $input); }
            $data = Validator::make($input['record'], [
                'name' => ($id ? 'sometimes|' : 'required|').'string|max:255',
                'email' => [($id ? 'sometimes' : 'required'), 'email', 'max:255', Rule::unique('users')->ignore($id)],
                'password' => ($id ? 'sometimes|nullable|' : 'required|').'string|min:8|max:255',
                'title' => 'sometimes|nullable|string|max:255', 'basic_salary' => 'sometimes|numeric|min:0|max:999999999',
                'roles' => 'sometimes|array', 'roles.*' => 'string|exists:roles,name',
                'permissions' => 'sometimes|array', 'permissions.*' => 'string|exists:permissions,name',
                'app_permissions' => 'sometimes|array', 'app_permissions.*' => [Rule::in(array_keys(AppAdminAccess::PERMISSIONS))],
                'association_global' => 'sometimes|boolean', 'association_categories' => 'sometimes|array',
                'association_categories.*' => 'integer|exists:categories,id', 'app_enabled' => 'sometimes|boolean',
                'admin_enabled' => 'sometimes|boolean', 'device_limit' => 'sometimes|nullable|integer|min:1|max:1000',
            ])->validate();
            abort_if(array_diff(array_keys($input['record']), array_column($this->fields(), 'key')), 422, 'unsupported_staff_fields');
            $sensitive = array_intersect(array_keys($data), ['roles', 'permissions', 'app_permissions', 'association_global', 'association_categories', 'app_enabled', 'admin_enabled', 'device_limit']);
            abort_if($sensitive && ! $this->access->allows($grant, 'staff', 'permissions'), 403, 'staff_permission_management_required');
            abort_if($id === (int) $actor->user_id && $sensitive, 403, 'cannot_edit_own_grant');
            $superTarget = $id && $staff->hasRole('superadmin');
            abort_if(! $grant->is_superadmin && ($superTarget || isset($data['roles']) || isset($data['permissions'])), 403, 'role_changes_require_superadmin');
            if (isset($data['app_permissions'])) {
                abort_if(array_diff($data['app_permissions'], $grant->effective_permissions), 403, 'cannot_delegate_permission');
            }
            if (! $this->access->global($grant)) {
                abort_if(($data['association_global'] ?? false) || array_diff($data['association_categories'] ?? [], $this->access->categories($grant)), 403, 'cannot_delegate_scope');
            }
            $attributes = array_intersect_key($data, array_flip(['name', 'email', 'password', 'title', 'basic_salary']));
            if (empty($attributes['password'])) { unset($attributes['password']); }
            $staff->forceFill($attributes)->save();
            if (isset($data['roles'])) { $staff->syncRoles($data['roles']); }
            if (isset($data['permissions'])) { $staff->syncPermissions($data['permissions']); }
            if ($sensitive) {
                $account = AppAccount::firstOrCreate(['user_id' => $staff->id]);
                if (array_key_exists('app_enabled', $data)) { $account->forceFill(['association_enabled' => $data['app_enabled']])->save(); }
                $policy = ['updated_at' => now()];
                if (array_key_exists('device_limit', $data)) { $policy['device_limit'] = $data['device_limit']; }
                if (array_key_exists('app_enabled', $data)) { $policy['app_enabled'] = $data['app_enabled']; }
                DB::table('association_account_policies')->updateOrInsert(['account_id' => $account->id], $policy);
                $membership = DB::table('app_admin_memberships')->where('app_account_id', $account->id)->first();
                $values = ['updated_at' => now()];
                foreach (['app_permissions' => 'permissions', 'association_categories' => 'association_categories'] as $key => $column) {
                    if (isset($data[$key])) { $values[$column] = json_encode(array_values(array_unique($data[$key]))); }
                }
                foreach (['admin_enabled' => 'active', 'association_global' => 'association_global'] as $key => $column) {
                    if (array_key_exists($key, $data)) { $values[$column] = $data[$key]; }
                }
                if (! $membership) { $values += ['active' => false, 'permissions' => '[]', 'locales' => '[]', 'created_at' => now()]; }
                DB::table('app_admin_memberships')->updateOrInsert(['app_account_id' => $account->id], $values);
                // Delegation still requires the underlying Laratrust permission.
                if (isset($data['app_permissions'])) {
                    $ids = [];
                    foreach ($data['app_permissions'] as $permission) {
                        $ids[] = \App\Models\Permission::firstOrCreate(['name' => AppAdminAccess::PERMISSIONS[$permission]])->id;
                    }
                    $staff->permissions()->syncWithoutDetaching($ids);
                }
            }
            $this->operations->audit($actor, $grant, 'staff', $staff->id, $id ? 'update' : 'create', $before, $this->records->revisionData('staff', $staff->fresh()));

            return ['record' => app(AssociationEntities::class)->describe('staff', $staff->fresh())];
        });
    }
}
