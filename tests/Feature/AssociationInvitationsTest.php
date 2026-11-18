<?php

namespace Tests\Feature;

use App\Models\AppAccount;
use App\Models\Event;
use App\Models\MessageLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationInvitationsTest extends AssociationTestCase
{
    private function owner(int $category, string $identity): AppAccount
    {
        $person = $this->beneficiary($category, $identity);
        $user = User::create(['name' => 'Owner', 'email' => $identity.'@example.test', 'password' => 'owner-password', 'telegram_user_id' => $person]);
        return AppAccount::create(['user_id' => $user->id, 'association_enabled' => true]);
    }
    private function event(): int
    {
        return DB::table('events')->insertGetId(['name' => 'Event', 'event_date' => now()->toDateString(), 'event_time' => '00:00:00', 'invitation_message' => 'مرحبًا {name}']);
    }
    private function preview(int $event, int $category)
    {
        return $this->postJson('/api/v2/admin/association/events/'.$event.'/invitation-preview', ['audience' => ['category_ids' => [$category]], 'channel' => 'app']);
    }
    private function send(int $event, string $preview, ?string $operation = null)
    {
        return $this->postJson('/api/v2/admin/association/events/'.$event.'/invite', ['operation_id' => $operation ?? (string) Str::uuid(), 'preview_id' => $preview, 'flagged_ack_ids' => []]);
    }

    public function test_invitation_preview_capacity_and_retries_preserve_one_invitation_and_one_inbox_entry(): void
    {
        $category = $this->category(); $owner = $this->owner($category, '111111111');
        $unlinked = $this->beneficiary($category, '222222222');
        $event = $this->event();
        [$admin] = $this->actor();
        $this->withToken($this->token($admin));
        $preview = $this->preview($event, $category)->assertOk()->assertJsonPath('data.recipient_count', 1)->assertJsonPath('data.not_linked_to_app', 1)->json('data');
        DB::table('association_event_settings')->insert(['event_id' => $event, 'capacity' => 1]);
        $this->send($event, $preview['preview_id'])->assertConflict();
        $preview = $this->preview($event, $category)->assertOk()->json('data');
        $operation = (string) Str::uuid();
        $this->send($event, $preview['preview_id'], $operation)->assertOk()->assertJsonPath('data.invitations_created', 1);
        $this->send($event, $preview['preview_id'], $operation)->assertOk();
        $this->assertSame(1, MessageLog::where('message_type', 'invitation')->count());
        $this->assertSame(1, DB::table('association_notifications')->count());
        $this->assertSame(0, MessageLog::where('telegram_user_id', $unlinked)->count());
        $this->withToken($this->personal($owner))->getJson('/api/v2/association/invitations')->assertOk()->assertJsonCount(1, 'data.items');
    }

    public function test_receipt_confirmation_reply_actual_attendance_and_rating_remain_distinct(): void
    {
        $category = $this->category(); $owner = $this->owner($category, '111111111'); $other = $this->owner($this->category('Other'), '222222222');
        $event = $this->event(); [$admin] = $this->actor(); $adminToken = $this->token($admin);
        $this->withToken($adminToken); $preview = $this->preview($event, $category)->assertOk()->json('data');
        $this->send($event, $preview['preview_id'])->assertOk();
        $id = MessageLog::where('message_type', 'invitation')->value('id');
        $ownerToken = $this->personal($owner);
        $this->withToken($this->personal($other))->getJson('/api/v2/association/invitations/'.$id)->assertNotFound();
        $this->withToken($ownerToken);
        foreach (['confirmed', 'will_attend', 'declined'] as $action) {
            $this->postJson('/api/v2/association/invitations/'.$id.'/action', ['operation_id' => (string) Str::uuid(), 'action' => $action])->assertOk();
        }
        $this->assertNull(MessageLog::find($id)->attended_at);
        $this->assertSame(1, MessageLog::where('message_type', 'invitation')->count());
        $this->postJson('/api/v2/association/invitations/'.$id.'/action', ['operation_id' => (string) Str::uuid(), 'action' => 'rate', 'rating' => 5])->assertConflict();
        $this->withToken($adminToken)->postJson('/api/v2/admin/association/confirm/attendance', ['operation_id' => (string) Str::uuid(), 'invitation_id' => $id])->assertOk();
        $this->withToken($ownerToken)->postJson('/api/v2/association/invitations/'.$id.'/action', ['operation_id' => (string) Str::uuid(), 'action' => 'rate', 'rating' => 5, 'comment' => 'شكرًا'])->assertOk()->assertJsonPath('data.record.state.rating', 5);
    }

    public function test_manual_withdrawal_stops_attendance_without_automatically_replacing_anyone(): void
    {
        $category = $this->category(); $owner = $this->owner($category, '111111111');
        $event = $this->event(); [$admin] = $this->actor(); $adminToken = $this->token($admin);
        $this->withToken($adminToken); $preview = $this->preview($event, $category)->assertOk()->json('data');
        $this->send($event, $preview['preview_id'])->assertOk();
        $id = MessageLog::where('message_type', 'invitation')->value('id');
        $record = $this->getJson('/api/v2/admin/association/records/invitations/'.$id)->assertOk()->json('data.record');
        $this->postJson('/api/v2/admin/association/invitations/'.$id.'/withdraw', ['operation_id' => (string) Str::uuid(), 'reason' => 'سحب يدوي بناءً على المراجعة', 'expected_revision' => $record['_invitation_revision']])->assertOk();
        $this->postJson('/api/v2/admin/association/confirm/attendance', ['operation_id' => (string) Str::uuid(), 'invitation_id' => $id])->assertConflict();
        $this->withToken($this->personal($owner))->postJson('/api/v2/association/invitations/'.$id.'/action', ['operation_id' => (string) Str::uuid(), 'action' => 'will_attend'])->assertConflict();
        $this->assertSame(1, MessageLog::where('message_type', 'invitation')->count());
    }
}
