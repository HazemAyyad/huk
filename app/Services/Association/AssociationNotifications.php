<?php

namespace App\Services\Association;

use Illuminate\Support\Facades\DB;

final class AssociationNotifications
{
    /** Persist an inbox entry and outbox rows in the caller's transaction. */
    public function system(array $accounts, ?int $staff, string $title, string $body, array $link): ?int
    {
        $accounts = DB::table('app_accounts')->where('active', true)->whereIn('id', array_unique($accounts))->pluck('id')->all();
        if (! $accounts) {
            return null;
        }
        $id = DB::table('association_notifications')->insertGetId([
            'created_by' => $staff, 'title' => $title, 'body' => $body, 'link' => json_encode($link, JSON_THROW_ON_ERROR),
            'audience' => json_encode(['accounts' => $accounts], JSON_THROW_ON_ERROR), 'status' => 'queued',
            'requires_confirmation' => false, 'reminder_count' => 0, 'revision' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ($accounts as $account) {
            DB::table('association_notification_recipients')->insert([
                'notification_id' => $id, 'account_id' => $account, 'status' => 'pending',
                'next_attempt_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $id;
    }

    public function beneficiary(int $beneficiary, int $staff, string $title, string $body, array $link): ?int
    {
        $accounts = DB::table('app_accounts')->join('users', 'users.id', '=', 'app_accounts.user_id')
            ->where('app_accounts.association_enabled', true)->where('app_accounts.active', true)
            ->where('users.telegram_user_id', $beneficiary)->pluck('app_accounts.id')->all();

        return $this->system($accounts, $staff, $title, $body, $link);
    }
}
