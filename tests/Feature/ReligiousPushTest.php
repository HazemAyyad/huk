<?php

namespace Tests\Feature;

use App\Services\Religious\ReligiousPush;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FakeCredentialReligiousPush extends ReligiousPush
{
    protected function accessToken(): string
    {
        return 'test-oauth-token';
    }
}

final class ReligiousPushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'push_test', 'database.connections.push_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true],
            'religious_community.enabled' => true, 'religious_push.enabled' => true, 'religious_push.project' => 'haq-test-project', 'religious_push.credentials_path' => __FILE__]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        foreach (['2026_10_13_120000_create_religious_accounts_and_quran_sync', '2026_10_26_150000_create_religious_community', '2026_10_27_120000_create_religious_push_outbox'] as $migration) {
            (require database_path('migrations/'.$migration.'.php'))->up();
        }
        $this->travelTo(now()->startOfSecond());
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        DB::purge('push_test');
        parent::tearDown();
    }

    private function publicationFixture(string $status = 'published', string $channel = 'message', int $minutes = 0): int
    {
        return DB::table('religious_publications')->insertGetId(['channel' => $channel, 'kind' => 'link', 'title' => 'Private title must not leave server', 'body' => 'Private body must not leave server',
            'link' => 'https://example.test', 'status' => $status, 'publish_at' => now()->addMinutes($minutes), 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_disabled_or_missing_schema_and_credentials_do_not_send(): void
    {
        $push = new FakeCredentialReligiousPush;
        config(['religious_push.enabled' => false]);
        $this->assertFalse($push->ready());
        $this->assertSame(0, $push->dispatch());
        config(['religious_push.enabled' => true, 'religious_push.credentials_path' => '/missing/credentials.json']);
        $this->assertFalse($push->ready());
        $this->assertSame(0, $push->dispatch());
        Http::assertNothingSent();
    }

    public function test_only_new_approved_public_messages_send_once_with_generic_payload(): void
    {
        $old = $this->publicationFixture(minutes: -20);
        $push = new FakeCredentialReligiousPush;
        $this->assertSame(0, $push->dispatch());
        $this->travel(1)->seconds();
        $id = $this->publicationFixture();
        $this->publicationFixture('draft');
        $this->publicationFixture('hidden');
        $this->publicationFixture(channel: 'story');
        $this->publicationFixture('scheduled', minutes: 20);
        Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'projects/haq-test-project/messages/test-1'])]);
        $this->assertSame(1, $push->dispatch());
        $this->assertSame(0, $push->dispatch());
        $this->assertSame('sent', DB::table('religious_push_outbox')->where('publication_id', $id)->value('status'));
        $this->assertFalse(DB::table('religious_push_outbox')->where('publication_id', $old)->exists());
        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($id) {
            $this->assertSame('haq_public_messages_v1', $request['message']['topic']);
            $this->assertSame((string) $id, $request['message']['data']['publication_id']);
            $this->assertStringNotContainsString('Private', json_encode($request->data()));
            $this->assertArrayNotHasKey('sound', $request['message']['apns']['payload']['aps']);

            return $request->hasHeader('Authorization', 'Bearer test-oauth-token');
        });
    }

    public function test_provider_busy_retries_after_delay_but_hidden_or_expired_posts_are_rechecked(): void
    {
        $push = new FakeCredentialReligiousPush;
        $push->dispatch();
        $this->travel(1)->seconds();
        $id = $this->publicationFixture();
        Http::fake(['fcm.googleapis.com/*' => Http::response(['error' => 'busy'], 429, ['Retry-After' => '300'])]);
        $this->assertSame(0, $push->dispatch());
        $this->assertSame('pending', DB::table('religious_push_outbox')->value('status'));
        $push->dispatch();
        Http::assertSentCount(1);
        DB::table('religious_publications')->where('id', $id)->update(['status' => 'hidden']);
        $this->travel(6)->minutes();
        $push->dispatch();
        $this->assertSame('skipped', DB::table('religious_push_outbox')->value('status'));
        Http::assertSentCount(1);
    }

    public function test_ambiguous_transport_or_interrupted_claim_never_automatically_resends(): void
    {
        $push = new FakeCredentialReligiousPush;
        $push->dispatch();
        $this->travel(1)->seconds();
        $id = $this->publicationFixture();
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection lost after request'));
        $push->dispatch();
        $this->assertSame('uncertain', DB::table('religious_push_outbox')->where('publication_id', $id)->value('status'));
        $this->travel(1)->minutes();
        $other = $this->publicationFixture();
        DB::table('religious_push_outbox')->insert(['publication_id' => $other, 'status' => 'sending', 'attempts' => 1, 'available_at' => now(), 'claimed_at' => now()->subMinutes(6), 'claim' => (string) \Illuminate\Support\Str::uuid(), 'created_at' => now(), 'updated_at' => now()]);
        $this->assertSame(0, $push->dispatch());
        $this->assertSame('uncertain', DB::table('religious_push_outbox')->where('publication_id', $other)->value('status'));
    }

    public function test_scheduled_publication_waits_for_time_and_does_not_expose_content(): void
    {
        $push = new FakeCredentialReligiousPush;
        $push->dispatch();
        $this->travel(1)->seconds();
        $id = $this->publicationFixture('scheduled', minutes: 5);
        $this->assertSame(0, $push->dispatch());
        $this->travel(6)->minutes();
        Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'projects/haq-test-project/messages/scheduled'])]);
        $this->assertSame(1, $push->dispatch());
        $this->assertSame('sent', DB::table('religious_push_outbox')->where('publication_id', $id)->value('status'));
        Http::assertSentCount(1);
    }
}
