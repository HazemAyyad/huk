<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use App\Models\User;
use App\Services\Religious\AppAdminAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AssociationAccess
{
    public function __construct(private AppAdminAccess $access) {}

    public function require(AppAccount $account, string $module, string $action = 'view'): object
    {
        $grant = $this->workspace($account);
        abort_unless($this->allows($grant, $module, $action), 403, 'association_permission_denied');

        return $grant;
    }

    public function workspace(AppAccount $account): object
    {
        abort_unless(config('association.enabled') && $account->association_enabled && $account->user_id, 403, 'association_access_denied');
        $grant = $this->access->require($account, 'association.enter');
        abort_unless(json_decode($grant->locales, true) === [], 403, 'association_global_locale_required');
        abort_unless($this->global($grant) || count($this->categories($grant)) > 0, 403, 'association_scope_required');
        abort_unless(Schema::hasTable('association_operations'), 503, 'association_migration_required');
        abort_if(DB::table('association_account_policies')->where('account_id', $account->id)->where('app_enabled', false)->exists(), 403, 'application_access_disabled');

        return $grant;
    }

    public function allows(object $grant, string $module, string $action = 'view'): bool
    {
        $definition = AssociationModules::definition($module);
        if (! in_array($action, $definition['actions'], true) || ! in_array("association.$module.$action", $grant->effective_permissions, true)) {
            return false;
        }
        if (in_array($module, ['staff', 'sessions', 'settings', 'audit', 'usage', 'support-categories', 'governorates', 'cities', 'areas', 'payments', 'paid-salaries'], true)) {
            if (! $this->global($grant)) {
                return false;
            }
        }
        if (($module === 'forms' && in_array($action, ['create', 'update', 'delete'], true)) ||
            ($action === 'delete' && in_array($module, ['events', 'beneficiaries', 'chat', 'staff'], true)) || in_array($action, ['purge', 'reactivate', 'archive'], true)) {
            return (bool) $grant->is_superadmin;
        }

        return true;
    }

    public function can(AppAccount $account, string $module, string $action = 'view'): bool
    {
        try {
            $this->require($account, $module, $action);

            return true;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            if (in_array($exception->getStatusCode(), [403, 404, 503], true)) {
                return false;
            }
            throw $exception;
        }
    }

    public function global(object $grant): bool
    {
        return (bool) ($grant->is_superadmin || ($grant->association_global ?? false));
    }

    public function categories(object $grant): array
    {
        $ids = json_decode($grant->association_categories ?? '[]', true);

        return is_array($ids) ? array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0))) : [];
    }

    public function beneficiaryQuery(object $grant, bool $includeDeleted = false)
    {
        $query = DB::table('telegram_users');
        if (! $includeDeleted) { $query->whereNull('telegram_users.deleted_at')->whereNull('telegram_users.deleted_by')
            ->whereNotIn('telegram_users.id', DB::table('association_deleted_records')->where('module', 'beneficiaries')->select('record_id')); }
        if (! $this->global($grant)) {
            $ids = $this->categories($grant);
            $query->where(function ($query) use ($ids): void {
                $query->whereIn('telegram_users.category_id', $ids);
                if (Schema::hasTable('user_categories')) {
                    $query->orWhereIn('telegram_users.id', DB::table('user_categories')->whereIn('category_id', $ids)->select('telegram_user_id'));
                }
            });
        }

        return $query;
    }

    public function beneficiary(object $grant, int $id): object
    {
        return $this->beneficiaryQuery($grant)->where('telegram_users.id', $id)->first() ?? abort(404);
    }

    public function staff(object $grant): User
    {
        return User::findOrFail($grant->staff_id);
    }
}
