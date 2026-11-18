<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use App\Models\TelegramUser;
use App\Models\User;

final class AssociationPortal
{
    public function person(AppAccount $account): TelegramUser
    {
        abort_unless(config('association.enabled') && $account->active && $account->association_enabled && $account->user_id, 403, 'association_access_denied');
        $staff = User::findOrFail($account->user_id);
        abort_if($staff->deleted_by || $staff->getAttribute('deleted_at'), 403);
        $person = TelegramUser::whereKey($staff->telegram_user_id)->whereNull('deleted_by')->firstOrFail();
        abort_if(\Illuminate\Support\Facades\DB::table('association_deleted_records')->where('module', 'beneficiaries')->where('record_id', (string) $person->id)->exists(), 404);

        return $person;
    }
}
