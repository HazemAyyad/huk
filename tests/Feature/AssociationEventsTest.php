<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationEventsTest extends AssociationTestCase
{
    private function invitation(): array
    {
        $person = $this->beneficiary($this->category());
        $event = DB::table('events')->insertGetId(['name' => 'Distribution', 'event_date' => now()->toDateString(), 'event_time' => '00:00:00']);
        $invitation = DB::table('message_logs')->insertGetId(['telegram_user_id' => $person, 'event_id' => $event,
            'message' => 'Invitation', 'message_type' => 'invitation', 'delivery_status' => 'manual']);

        return [$person, $event, $invitation];
    }

    private function confirm(string $mode, int $invitation, array $extra = [])
    {
        return $this->postJson('/api/v2/admin/association/confirm/'.$mode,
            ['operation_id' => (string) Str::uuid(), 'invitation_id' => $invitation, ...$extra]);
    }

    public function test_delivery_requires_attendance_and_counts_only_once_across_distinct_operations(): void
    {
        [$person, , $invitation] = $this->invitation();
        [$account, $staff] = $this->actor();
        $this->withToken($this->token($account));
        $this->confirm('delivery', $invitation)->assertUnprocessable();
        $this->assertSame(0, DB::table('telegram_users')->value('benefit_count'));
        $this->confirm('attendance', $invitation)->assertOk()->assertJsonPath('data.already_applied', false);
        $this->confirm('delivery', $invitation)->assertOk()->assertJsonPath('data.delivered', true);
        $this->confirm('delivery', $invitation)->assertOk()->assertJsonPath('data.already_applied', true);
        $this->assertSame(1, DB::table('telegram_users')->where('id', $person)->value('benefit_count'));
        $this->assertSame(1, DB::table('message_logs')->where('message_type', 'received')->count());
        $this->assertSame($staff->id, DB::table('message_logs')->where('message_type', 'received')->value('sent_by'));
    }

    public function test_override_requires_a_reason_and_its_own_permission(): void
    {
        [, , $invitation] = $this->invitation();
        [$account] = $this->actor(['admin.enter', 'association.enter', 'association.deliveries.confirm'], 'delivery');
        $this->withToken($this->token($account));
        $this->confirm('delivery', $invitation, ['override' => true, 'reason' => 'Emergency'])->assertForbidden();
        [$super] = $this->actor();
        $this->withToken($this->token($super));
        $this->confirm('delivery', $invitation, ['override' => true])->assertUnprocessable();
        $this->confirm('delivery', $invitation, ['override' => true, 'reason' => 'Emergency'])->assertOk();
        $this->assertSame('Emergency', DB::table('association_audit')->where('module', 'deliveries')->value('reason'));
    }

    public function test_every_failed_scan_is_recorded_and_hidden_people_are_not_disclosed(): void
    {
        $allowed = $this->category('Allowed');
        $this->beneficiary($this->category('Other'));
        [$account] = $this->actor(['admin.enter', 'association.enter', 'association.attendance.scan'], 'scan', false, [$allowed]);
        $this->withToken($this->token($account));
        $this->postJson('/api/v2/admin/association/scan/attendance', ['code' => 'invalid'])->assertOk()->assertJsonPath('data.result', 'invalid');
        $this->postJson('/api/v2/admin/association/scan/attendance', ['code' => '123456789'])->assertOk()->assertJsonPath('data.result', 'not_found')->assertJsonMissingPath('data.beneficiary');
        $this->assertSame(2, DB::table('association_scan_history')->count());
    }

    public function test_a_signed_offline_confirmation_can_sync_after_expiry_but_not_after_archiving(): void
    {
        [$person, $event, $invitation] = $this->invitation();
        [$account] = $this->actor();
        $this->withToken($this->token($account));
        $scan = $this->postJson('/api/v2/admin/association/scan/attendance', ['code' => '123456789'])->assertOk()->json('data');
        $this->assertSame($person, $scan['beneficiary']['id']);
        $time = now()->toIso8601String();
        $permit = $scan['events'][0]['offline_permit'];
        $this->travel(2)->days();
        $this->withToken($this->token($account));
        $this->confirm('attendance', $invitation, ['occurred_at' => $time, 'offline_permit' => $permit])->assertOk();
        $this->confirm('delivery', $invitation)->assertConflict();
        DB::table('events')->where('id', $event)->update(['is_archived' => true]);
        $this->confirm('delivery', $invitation, ['occurred_at' => $time, 'offline_permit' => $permit])->assertConflict();
        $this->travelBack();
    }

    public function test_withdrawn_invitation_and_changed_identity_are_rejected(): void
    {
        [$person, , $invitation] = $this->invitation();
        [$account] = $this->actor();
        $this->withToken($this->token($account));
        $scan = $this->postJson('/api/v2/admin/association/scan/attendance', ['code' => '123456789'])->assertOk()->json('data');
        DB::table('telegram_users')->where('id', $person)->update(['full_name' => 'Changed']);
        $this->confirm('attendance', $invitation, ['identity_hash' => $scan['beneficiary']['identity_hash']])->assertConflict();
        DB::table('association_invitation_states')->insert(['message_log_id' => $invitation, 'withdrawn_at' => now()]);
        $this->confirm('attendance', $invitation)->assertConflict();
        $this->assertNull(DB::table('message_logs')->where('id', $invitation)->value('attended_at'));
    }
}
