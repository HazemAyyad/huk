<?php

namespace App\Services\Religious;

use App\Models\ReligiousAccount;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class ReligiousSubscriptions
{
    public const TABLES = ['religious_billing_accounts', 'religious_subscriptions', 'religious_assistant_budgets', 'religious_assistant_requests', 'religious_billing_events'];

    public function schemaReady(): bool
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    public function actor(ReligiousAccount $account): int
    {
        abort_unless($this->schemaReady(), 503, 'خدمة المساعد والاشتراك غير مهيأة حاليًا');
        abort_unless(DB::table('religious_accounts')->where('id', $account->id)->exists(), 401);

        return (int) $account->id;
    }

    public function storeToken(ReligiousAccount $account): string
    {
        $owner = $this->actor($account);

        return DB::transaction(function () use ($owner) {
            abort_unless(DB::table('religious_accounts')->where('id', $owner)->lockForUpdate()->first(), 401);
            DB::table('religious_billing_accounts')->insertOrIgnore(['account_id' => $owner, 'store_token' => (string) Str::uuid()]);

            return DB::table('religious_billing_accounts')->where('account_id', $owner)->value('store_token');
        });
    }

    public function verify(ReligiousAccount $account, string $platform, string $proof): array
    {
        $owner = $this->actor($account);
        $hash = hash('sha256', $platform.':'.$proof);
        $old = DB::table('religious_subscriptions')->where('purchase_hash', $hash)->first();
        abort_if($old && (int) $old->account_id !== $owner, 403, 'الشراء مرتبط بحساب آخر');
        abort_if($old && $old->state === 'REPLACED', 422, 'الشراء استُبدل باشتراك أحدث');
        $result = app(StoreReceipts::class)->verify($platform, $proof, $this->storeToken($account));
        DB::transaction(function () use ($owner, $hash, $platform, $proof, $result): void {
            abort_unless(DB::table('religious_accounts')->where('id', $owner)->lockForUpdate()->first(), 401);
            $existing = DB::table('religious_subscriptions')->where('purchase_hash', $hash)->lockForUpdate()->first();
            abort_if($existing && (int) $existing->account_id !== $owner, 403);
            if ($result['original_hash']) {
                abort_if(DB::table('religious_subscriptions')->where('original_hash', $result['original_hash'])->where('account_id', '!=', $owner)->exists(), 403);
            }
            if ($result['linked_hash']) {
                DB::table('religious_subscriptions')->where('purchase_hash', $result['linked_hash'])->update(['active' => false, 'state' => 'REPLACED', 'updated_at' => now()]);
            }
            DB::table('religious_subscriptions')->updateOrInsert(['purchase_hash' => $hash], ['account_id' => $owner, 'platform' => $platform, 'product' => $result['product'], 'proof' => Crypt::encryptString($proof),
                'original_hash' => $result['original_hash'], 'state' => $result['state'], 'active' => $result['active'], 'expires_at' => $result['expires_at'], 'verified_at' => now(), 'updated_at' => now(), 'created_at' => $existing?->created_at ?? now()]);
        }, 3);

        return ['active' => $result['active'], 'expires_at' => $result['expires_at']->toIso8601String(), 'state' => $result['state'], 'platform' => $platform];
    }

    public function status(ReligiousAccount $account): array
    {
        $owner = $this->actor($account);
        $result = ['active' => false, 'expires_at' => null, 'state' => 'NONE', 'platform' => null];
        // Every protected use refreshes with the store; a local active flag never grants model access.
        foreach (DB::table('religious_subscriptions')->where('account_id', $owner)->where('state', '!=', 'REPLACED')->orderByDesc('expires_at')->limit(10)->get() as $row) {
            if (! app(StoreReceipts::class)->ready($row->platform)) {
                continue;
            }
            $checked = $this->verify($account, $row->platform, Crypt::decryptString($row->proof));
            if ($checked['active']) {
                return $checked;
            }
            $result = $checked;
        }

        return $result;
    }
}
