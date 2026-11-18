<?php

namespace Tests\Feature;

use App\Services\Association\AssociationInbox;
use App\Services\Association\AssociationNotifications;
use App\Services\Association\AssociationPush;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

class FakeAssociationPush extends AssociationPush
{
    public function ready(): bool { return true; }
    protected function accessToken(): string { return 'isolated-test-credentials'; }
}

final class AssociationPushTest extends AssociationTestCase
{
    private function notification(bool $confirmation = false): array
    {
        [$account, $staff] = $this->actor();
        $id = app(AssociationNotifications::class)->system([$account->id], $staff->id, 'Private title', 'Private beneficiary content', ['type' => 'home']);
        DB::table('association_notifications')->where('id', $id)->update(['requires_confirmation' => $confirmation, 'first_reminder_minutes' => 10, 'reminder_interval_minutes' => 60, 'reminder_count' => $confirmation ? 2 : 0]);
        DB::table('association_devices')->insert(['account_id' => $account->id, 'device_hash' => hash('sha256', 'test-device'),
            'platform' => 'android', 'fcm_token' => Crypt::encryptString('isolated-test-registration'), 'fcm_hash' => hash('sha256', 'isolated-test-registration')]);
        config(['religious_push.project' => 'haq-project']);
        Http::preventStrayRequests();

        return [$account, $id, app(FakeAssociationPush::class)];
    }

    public function test_provider_acceptance_is_separate_from_device_delivery_and_ack_stops_reminders(): void
    {
        [$account, $id, $push] = $this->notification(true);
        Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'projects/haq-project/messages/test'], 200)]);
        $this->assertSame(1, $push->dispatch());
        $this->assertNotNull(DB::table('association_notification_recipients')->value('sent_at'));
        $this->assertNull(DB::table('association_notification_recipients')->value('delivered_at'));
        Http::assertSent(fn ($request) => $request['message']['notification']['body'] !== 'Private beneficiary content' && $request['message']['data']['account_id'] === (string) $account->id);
        app(AssociationInbox::class)->acknowledge($account, $id, ['action' => 'confirmed', 'operation_id' => (string) Str::uuid()]);
        $this->travel(11)->minutes();
        $this->assertSame(0, $push->dispatch());
        $this->assertSame(1, DB::table('association_push_attempts')->count());
        Http::assertSentCount(1);
        $this->travelBack();
    }

    public function test_an_ambiguous_transport_is_never_automatically_replayed_as_an_initial_push(): void
    {
        [, , $push] = $this->notification();
        Http::fake(fn () => throw new ConnectionException('isolated transport failure'));
        $this->assertSame(0, $push->dispatch());
        $this->assertSame('uncertain', DB::table('association_push_attempts')->value('status'));
        $this->travel(6)->minutes();
        $this->assertSame(0, $push->dispatch());
        $this->assertSame(1, DB::table('association_push_attempts')->count());
        $this->assertNull(DB::table('association_notification_recipients')->value('sent_at'));
        $this->travelBack();
    }

    public function test_an_explicit_reminder_can_follow_an_ambiguous_attempt_without_fabricating_acceptance(): void
    {
        [, , $push] = $this->notification(true);
        $fail = true;
        Http::fake(function () use (&$fail) {
            if ($fail) { throw new ConnectionException('isolated transport failure'); }
            return Http::response(['name' => 'projects/haq-project/messages/reminder'], 200);
        });
        $push->dispatch();
        $this->travel(11)->minutes();
        $fail = false;
        $this->assertSame(1, $push->dispatch());
        $this->assertSame(2, DB::table('association_push_attempts')->count());
        $this->assertSame(1, DB::table('association_notification_recipients')->value('reminders_sent'));
        $this->assertSame(1, DB::table('association_push_attempts')->where('status', 'uncertain')->count());
        $this->travelBack();
    }
}
