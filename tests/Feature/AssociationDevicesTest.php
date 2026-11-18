<?php

namespace Tests\Feature;

use App\Models\AppAccount;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationDevicesTest extends AssociationTestCase
{
    private function owner(string $identity): User
    {
        $person = $this->beneficiary($this->category($identity), $identity);
        return User::create(['name' => 'Owner', 'email' => $identity.'@example.test', 'password' => 'owner-password', 'telegram_user_id' => $person]);
    }

    private function login(User $user, string $device, ?string $fcm = null)
    {
        $this->app['auth']->forgetGuards();
        return $this->postJson('/api/v2/account/login', ['provider' => 'association', 'email' => $user->email, 'password' => 'owner-password',
            'device' => ['installation_id' => $device, 'platform' => 'android', 'label' => 'Test phone', ...$fcm ? ['fcm_token' => $fcm] : []]]);
    }

    public function test_beneficiary_device_limit_and_staff_unlink_revoke_parent_and_admin_sessions(): void
    {
        config(['association.enforce_devices' => true]);
        $user = $this->owner('111111111');
        $installation = (string) Str::uuid();
        $first = $this->login($user, $installation)->assertOk()->json('data');
        $this->login($user, (string) Str::uuid())->assertConflict();
        $second = $this->login($user, $installation)->assertOk()->json('data');
        $this->withToken($first['token'])->getJson('/api/v2/account/session')->assertOk();
        [$admin] = $this->actor();
        $device = DB::table('association_devices')->where('account_id', $first['account']['id'])->value('id');
        $this->withToken($this->token($admin))->postJson('/api/v2/admin/association/devices/'.$device.'/revoke', ['operation_id' => (string) Str::uuid(), 'account_id' => $first['account']['id'], 'reason' => 'نقل إلى هاتف جديد'])->assertOk();
        $this->withToken($second['token'])->getJson('/api/v2/account/session')->assertUnauthorized();
        $this->login($user, $installation)->assertForbidden();
        $this->login($user, (string) Str::uuid())->assertOk();
        $this->assertSame(2, DB::table('association_devices')->count());
        $this->assertSame(1, DB::table('association_devices')->whereNull('revoked_at')->count());
    }

    public function test_registration_tokens_are_encrypted_and_move_to_only_the_current_account(): void
    {
        config(['association.enforce_devices' => true]);
        $first = $this->owner('111111111');
        $second = $this->owner('222222222');
        $fcm = str_repeat('private-registration-', 4);
        $this->login($first, (string) Str::uuid(), $fcm)->assertOk();
        $row = DB::table('association_devices')->first();
        $this->assertNotSame($fcm, $row->fcm_token);
        $this->assertSame($fcm, Crypt::decryptString($row->fcm_token));
        $this->login($second, (string) Str::uuid(), $fcm)->assertOk();
        $this->assertSame(1, DB::table('association_devices')->whereNotNull('fcm_token')->count());
        $this->assertNull(DB::table('association_devices')->where('id', $row->id)->value('fcm_token'));
    }

    public function test_disabled_app_policy_denies_login_and_missing_device_cannot_bypass_a_limited_account(): void
    {
        config(['association.enforce_devices' => true]);
        $user = $this->owner('111111111');
        $this->postJson('/api/v2/account/login', ['provider' => 'association', 'email' => $user->email, 'password' => 'owner-password'])->assertUnprocessable();
        $account = AppAccount::where('user_id', $user->id)->firstOrFail();
        DB::table('association_account_policies')->insert(['account_id' => $account->id, 'app_enabled' => false]);
        $this->login($user, (string) Str::uuid())->assertForbidden();
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }
}
