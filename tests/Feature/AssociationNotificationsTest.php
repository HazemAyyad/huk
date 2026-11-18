<?php

namespace Tests\Feature;

use App\Models\AppAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationNotificationsTest extends AssociationTestCase
{
    private function owner(int $category, string $identity): array
    {
        $person = $this->beneficiary($category, $identity);
        $user = User::create(['name' => 'Owner', 'email' => Str::uuid().'@example.test', 'password' => 'owner-password', 'telegram_user_id' => $person]);

        return [AppAccount::create(['user_id' => $user->id, 'association_enabled' => true]), $person];
    }

    private function draft(array $filter): array
    {
        return $this->postJson('/api/v2/admin/association/records/notifications', ['operation_id' => (string) Str::uuid(), 'record' => [
            'title' => 'إشعار موجه', 'body' => 'تفاصيل الخدمة', 'audience' => ['kind' => 'beneficiaries', 'filter' => $filter],
            'requires_confirmation' => true, 'first_reminder_minutes' => 10, 'reminder_interval_minutes' => 60, 'reminder_count' => 2,
        ]])->assertOk()->json('data.record');
    }

    public function test_preview_excludes_flagged_people_and_replaying_send_does_not_duplicate_inbox_rows(): void
    {
        $category = $this->category();
        [$recipient] = $this->owner($category, '111111111');
        [, $blocked] = $this->owner($category, '222222222');
        [, $noted] = $this->owner($category, '333333333');
        DB::table('telegram_users')->where('id', $blocked)->update(['is_blocked' => true]);
        DB::table('telegram_user_notes')->insert(['telegram_user_id' => $noted, 'note' => 'مراجعة الموظف']);
        [$admin] = $this->actor();
        $this->withToken($this->token($admin));
        $draft = $this->draft(['category_ids' => [$category]]);
        $preview = $this->postJson('/api/v2/admin/association/notifications/'.$draft['id'].'/preview')->assertOk()->assertJsonPath('data.recipient_count', 1)->assertJsonPath('data.flagged.count', 2)->json('data');
        $input = ['action' => 'send', 'operation_id' => (string) Str::uuid(), 'expected_revision' => $draft['_revision'], 'preview_id' => $preview['preview_id'], 'flagged_ack_ids' => []];
        $this->postJson('/api/v2/admin/association/records/notifications/'.$draft['id'].'/action', $input)->assertOk();
        $this->postJson('/api/v2/admin/association/records/notifications/'.$draft['id'].'/action', $input)->assertOk();
        $this->assertSame(1, DB::table('association_notification_recipients')->count());
        $this->assertSame($recipient->id, DB::table('association_notification_recipients')->value('account_id'));
        $this->withToken($this->personal($recipient))->getJson('/api/v2/association/inbox')->assertOk()->assertJsonCount(1, 'data.items');
    }

    public function test_each_flagged_recipient_requires_explicit_acknowledgement_and_stale_audiences_are_rejected(): void
    {
        $category = $this->category();
        [$recipient, $person] = $this->owner($category, '111111111');
        DB::table('telegram_users')->where('id', $person)->update(['is_blocked' => true]);
        [$admin] = $this->actor();
        $this->withToken($this->token($admin));
        $draft = $this->draft(['category_ids' => [$category], 'include_flagged_ids' => [$person]]);
        $preview = $this->postJson('/api/v2/admin/association/notifications/'.$draft['id'].'/preview')->assertOk()->json('data');
        $input = ['action' => 'send', 'expected_revision' => $draft['_revision'], 'preview_id' => $preview['preview_id'], 'flagged_ack_ids' => []];
        $this->postJson('/api/v2/admin/association/records/notifications/'.$draft['id'].'/action', [...$input, 'operation_id' => (string) Str::uuid()])->assertUnprocessable();
        DB::table('app_accounts')->where('id', $recipient->id)->update(['active' => false]);
        $this->postJson('/api/v2/admin/association/records/notifications/'.$draft['id'].'/action', [...$input, 'flagged_ack_ids' => [$person], 'operation_id' => (string) Str::uuid()])->assertConflict();
        $this->assertSame(0, DB::table('association_notification_recipients')->count());
    }

    public function test_receipt_confirmation_is_account_owned_and_stops_reminders_without_claiming_provider_delivery(): void
    {
        $category = $this->category();
        [$recipient] = $this->owner($category, '111111111');
        [$other] = $this->owner($category, '222222222');
        [$admin] = $this->actor();
        $adminToken = $this->token($admin);
        $this->withToken($adminToken);
        $draft = $this->draft([]);
        // Target one app account using the audience's account selector.
        $record = $draft;
        $record['audience'] = ['kind' => 'beneficiaries', 'filter' => [], 'account_ids' => [$recipient->id]];
        $draft = $this->putJson('/api/v2/admin/association/records/notifications/'.$draft['id'], ['operation_id' => (string) Str::uuid(), 'expected_revision' => $draft['_revision'], 'record' => $record])->assertOk()->json('data.record');
        $preview = $this->postJson('/api/v2/admin/association/notifications/'.$draft['id'].'/preview')->assertOk()->json('data');
        $this->postJson('/api/v2/admin/association/records/notifications/'.$draft['id'].'/action', ['action' => 'send', 'operation_id' => (string) Str::uuid(), 'expected_revision' => $draft['_revision'], 'preview_id' => $preview['preview_id'], 'flagged_ack_ids' => []])->assertOk();
        $recipientId = DB::table('association_notification_recipients')->value('id');
        DB::table('association_push_attempts')->insert(['recipient_id' => $recipientId, 'device_id' => 99, 'reminder' => 1, 'status' => 'pending']);
        $input = ['operation_id' => (string) Str::uuid(), 'action' => 'confirmed'];
        $this->withToken($this->personal($other))->postJson('/api/v2/association/inbox/'.$draft['id'].'/acknowledge', $input)->assertNotFound();
        $this->withToken($this->personal($recipient))->postJson('/api/v2/association/inbox/'.$draft['id'].'/acknowledge', $input)->assertOk();
        $this->assertNull(DB::table('association_notification_recipients')->value('sent_at'));
        $this->assertNotNull(DB::table('association_notification_recipients')->value('delivered_at'));
        $this->assertSame('cancelled', DB::table('association_push_attempts')->value('status'));
        $this->withToken($adminToken)->getJson('/api/v2/admin/association/records/notifications/'.$draft['id'])->assertOk()->assertJsonPath('data.stats.provider_accepted', 0)->assertJsonPath('data.stats.device_delivered', 1)->assertJsonPath('data.stats.confirmed', 1);
    }
}
