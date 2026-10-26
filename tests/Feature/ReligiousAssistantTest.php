<?php

namespace Tests\Feature;

use App\Models\ReligiousAccount;
use App\Models\User;
use App\Services\Religious\AppleStoreSignature;
use App\Services\Religious\AssistantSources;
use App\Services\Religious\ReligiousSubscriptions;
use App\Services\Religious\StoreReceipts;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssistantTestStore extends StoreReceipts
{
    public function ready(string $platform): bool
    {
        return (bool) config('religious_assistant.'.$platform.'.enabled', false);
    }

    protected function googleToken(): string
    {
        return 'synthetic-google-token';
    }
}

final class ReligiousAssistantTest extends TestCase
{
    private ReligiousAccount $owner;

    private ReligiousAccount $other;

    private array $store;

    private mixed $model;

    private int $inputCount = 2000;

    private array $appleBody = [];

    private ?array $googleKeys = null;

    private const QUESTION = 'أذكار النوم';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'assistant_test', 'database.connections.assistant_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true],
            'religious_assistant.enabled' => true, 'religious_assistant.api_key' => 'synthetic-provider-key', 'religious_assistant.android.enabled' => true,
            'religious_assistant.android.product' => 'synthetic.haq.monthly', 'religious_assistant.android.allow_sandbox' => false]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        foreach (['2026_10_13_120000_create_religious_accounts_and_quran_sync', '2026_10_26_120000_extend_religious_account_identity', '2026_10_26_180000_create_religious_assistant_and_billing'] as $migration) {
            (require database_path('migrations/'.$migration.'.php'))->up();
        }
        $this->owner = ReligiousAccount::create(['name' => 'One', 'email' => 'one@example.test', 'password' => 'test-password']);
        $this->other = ReligiousAccount::create(['name' => 'Two', 'email' => 'two@example.test', 'password' => 'test-password']);
        $this->app->bind(StoreReceipts::class, AssistantTestStore::class);
        $token = app(ReligiousSubscriptions::class)->storeToken($this->owner);
        $this->store = ['externalAccountIdentifiers' => ['obfuscatedExternalAccountId' => $token], 'subscriptionState' => 'SUBSCRIPTION_STATE_ACTIVE', 'acknowledgementState' => 'ACKNOWLEDGEMENT_STATE_ACKNOWLEDGED',
            'lineItems' => [['productId' => 'synthetic.haq.monthly', 'expiryTime' => now()->addMonth()->toIso8601String()]]];
        $sources = app(AssistantSources::class)->find(self::QUESTION);
        $this->assertNotEmpty($sources);
        $this->model = ['model' => 'gpt-6-luna', 'status' => 'completed', 'usage' => ['input_tokens' => 2000, 'output_tokens' => 250, 'input_tokens_details' => ['cached_tokens' => 0, 'cache_write_tokens' => 0]],
            'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => json_encode(['answer' => 'شرح اختبار للمحتوى الموثق، وليس نصًا دينيًا.', 'source_ids' => [$sources[0]['id']], 'supported' => true])]]]]];
        Http::preventStrayRequests();
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'www.googleapis.com/oauth2/v3/certs')) {
                return Http::response(['keys' => $this->googleKeys ?? []]);
            }
            if (str_starts_with($request->url(), 'https://api.storekit')) {
                return Http::response($this->appleBody);
            }
            if (str_starts_with($request->url(), 'https://androidpublisher.googleapis.com/')) {
                return Http::response($this->store);
            }
            if ($request->url() === 'https://api.openai.com/v1/responses/input_tokens') {
                return Http::response(['input_tokens' => $this->inputCount]);
            }
            if ($request->url() === 'https://api.openai.com/v1/responses') {
                return is_array($this->model) ? Http::response($this->model) : Http::failedConnection();
            }

            return Http::response([], 404);
        });
        $this->sign();
    }

    protected function tearDown(): void
    {
        DB::purge('assistant_test');
        parent::tearDown();
    }

    private function sign(?ReligiousAccount $account = null): void
    {
        Sanctum::actingAs($account ?? $this->owner, ['quran:sync']);
    }

    private function url(string $path): string
    {
        return '/api/v1/religious/'.$path;
    }

    private function subscribe(string $proof = 'synthetic-purchase'): void
    {
        $this->postJson($this->url('billing/verify'), ['platform' => 'android', 'proof' => $proof])->assertOk()->assertJsonPath('data.active', true);
    }

    private function ask(?string $operation = null, string $question = self::QUESTION): \Illuminate\Testing\TestResponse
    {
        return $this->postJson($this->url('assistant/requests'), ['operation_id' => $operation ?? (string) Str::uuid(), 'question' => $question]);
    }

    private function generations(): int
    {
        return Http::recorded(fn ($r) => $r->url() === 'https://api.openai.com/v1/responses')->count();
    }

    public function test_disabled_missing_keys_and_staff_cannot_access_paid_service(): void
    {
        config(['religious_assistant.enabled' => false]);
        $this->getJson($this->url('assistant/capabilities'))->assertOk()->assertJsonPath('data.enabled', false)->assertJsonPath('data.stores.android.enabled', false);
        $this->ask()->assertStatus(503);
        $this->actingAs(new User(['name' => 'Staff']), 'sanctum');
        $this->getJson($this->url('assistant/history'))->assertForbidden();
        $this->sign();
        config(['religious_assistant.enabled' => true, 'religious_assistant.api_key' => '']);
        $this->ask()->assertStatus(503);
        $this->assertSame(0, $this->generations());
    }

    public function test_store_verification_binds_identity_and_encrypts_proof_without_profile_disclosure(): void
    {
        $this->subscribe();
        $row = DB::table('religious_subscriptions')->first();
        $this->assertNotSame('synthetic-purchase', $row->proof);
        $this->assertSame('synthetic-purchase', Crypt::decryptString($row->proof));
        $this->getJson($this->url('billing/profile'))->assertOk()->assertJsonPath('data.subscription.active', true)->assertDontSee('synthetic-purchase');
        $this->sign($this->other);
        $this->postJson($this->url('billing/verify'), ['platform' => 'android', 'proof' => 'synthetic-purchase'])->assertForbidden();
        $this->postJson($this->url('billing/verify'), ['platform' => 'android', 'proof' => 'other-proof'])->assertForbidden();
    }

    public function test_pending_refunded_expired_wrong_product_and_sandbox_never_grant_access(): void
    {
        foreach (['SUBSCRIPTION_STATE_PENDING', 'SUBSCRIPTION_STATE_ON_HOLD', 'SUBSCRIPTION_STATE_EXPIRED'] as $state) {
            $this->store['subscriptionState'] = $state;
            $this->postJson($this->url('billing/verify'), ['platform' => 'android', 'proof' => 'purchase-'.$state])->assertOk()->assertJsonPath('data.active', false);
        }
        $this->store['subscriptionState'] = 'SUBSCRIPTION_STATE_CANCELED';
        $this->subscribe('canceled-but-paid');
        $this->store['lineItems'][0]['expiryTime'] = now()->subSecond()->toIso8601String();
        $this->postJson($this->url('billing/verify'), ['platform' => 'android', 'proof' => 'canceled-but-paid'])->assertJsonPath('data.active', false);
        $this->store['lineItems'][0]['productId'] = 'another-product';
        $this->postJson($this->url('billing/verify'), ['platform' => 'android', 'proof' => 'bad-product'])->assertStatus(422);
        $this->store['testPurchase'] = [];
        $this->postJson($this->url('billing/verify'), ['platform' => 'android', 'proof' => 'sandbox-proof'])->assertStatus(422);
        $this->assertSame(0, $this->generations());
    }

    public function test_acknowledgement_and_replaced_purchase_do_not_grant_a_second_entitlement(): void
    {
        $this->subscribe('old-token');
        $this->store['acknowledgementState'] = 'ACKNOWLEDGEMENT_STATE_PENDING';
        $this->store['linkedPurchaseToken'] = 'old-token';
        $this->subscribe('new-token');
        Http::assertSent(fn ($r) => $r->method() === 'POST' && str_ends_with($r->url(), ':acknowledge'));
        $this->assertFalse((bool) DB::table('religious_subscriptions')->where('purchase_hash', hash('sha256', 'android:old-token'))->value('active'));
        $this->postJson($this->url('billing/verify'), ['platform' => 'android', 'proof' => 'old-token'])->assertStatus(422);
    }

    public function test_each_question_refreshes_store_and_never_trusts_cached_active_flag(): void
    {
        $this->ask()->assertStatus(402);
        $this->subscribe();
        $this->store['subscriptionState'] = 'SUBSCRIPTION_STATE_EXPIRED';
        $this->ask()->assertStatus(402);
        $this->assertSame(0, $this->generations());
    }

    public function test_paid_answer_is_source_checked_private_bounded_and_charged_once(): void
    {
        $this->subscribe();
        $operation = (string) Str::uuid();
        $first = $this->ask($operation)->assertOk()->assertJsonPath('data.status', 'completed');
        $this->ask($operation)->assertJsonPath('data.id', $first->json('data.id'));
        $this->ask($operation, 'نص مختلف')->assertStatus(409);
        $this->assertSame(1, $this->generations());
        $budget = DB::table('religious_assistant_budgets')->first();
        $this->assertSame(0, (int) $budget->reserved);
        $this->assertSame(325, (int) $budget->spent);
        $row = DB::table('religious_assistant_requests')->first();
        $this->assertNotSame(self::QUESTION, $row->question);
        Http::assertSent(fn ($r) => $r->url() === 'https://api.openai.com/v1/responses' && $r['store'] === false && $r['max_output_tokens'] === 600 && $r['prompt_cache_options']['mode'] === 'explicit' && ! str_contains($r->body(), 'one@example.test') && ! isset($r['tools']));
        $this->sign($this->other);
        $this->getJson($this->url('assistant/requests/'.$operation))->assertNotFound();
        $this->getJson($this->url('assistant/history'))->assertJsonCount(0, 'data.items');
    }

    public function test_unknown_generation_outcome_keeps_reservation_and_retry_never_calls_model_twice(): void
    {
        $this->subscribe();
        $this->model = null;
        $operation = (string) Str::uuid();
        $this->ask($operation)->assertOk()->assertJsonPath('data.status', 'uncertain');
        $this->ask($operation)->assertJsonPath('data.status', 'uncertain');
        $this->assertSame(1, $this->generations());
        $this->assertSame(600, (int) DB::table('religious_assistant_budgets')->value('reserved'));
    }

    public function test_count_failure_is_not_sent_or_charged_and_malformed_sources_are_not_shown(): void
    {
        $this->subscribe();
        $this->inputCount = 3001;
        $this->ask()->assertJsonPath('data.status', 'failed')->assertJsonPath('data.quota.monthly_used', 0);
        $this->assertSame(0, $this->generations());
        $this->assertSame(0, (int) DB::table('religious_assistant_budgets')->value('reserved'));
        $this->inputCount = 2000;
        $this->model['output'][0]['content'][0]['text'] = json_encode(['answer' => 'untrusted', 'source_ids' => ['invented-source'], 'supported' => true]);
        $this->ask()->assertJsonPath('data.status', 'failed_billed')->assertJsonPath('data.answer', null);
        $this->assertSame(325, (int) DB::table('religious_assistant_budgets')->value('spent'));
    }

    public function test_daily_monthly_and_atomic_global_reservations_gate_before_generation(): void
    {
        $this->subscribe();
        config(['religious_assistant.daily_limit' => 1]);
        $this->ask()->assertJsonPath('data.status', 'completed');
        $this->ask()->assertStatus(429);
        $this->assertSame(1, $this->generations());
        config(['religious_assistant.daily_limit' => 3, 'religious_assistant.monthly_limit' => 1]);
        $this->ask()->assertStatus(429);
        config(['religious_assistant.monthly_limit' => 25]);
        DB::table('religious_assistant_budgets')->update(['reserved' => 17999900]);
        $this->ask()->assertStatus(503);
        $this->assertSame(1, $this->generations());
    }

    public function test_unexpected_usage_halts_the_month_instead_of_silently_overspending(): void
    {
        $this->subscribe();
        $this->model['usage']['output_tokens'] = 601;
        $this->ask()->assertJsonPath('data.status', 'uncertain')->assertJsonPath('data.error_code', 'usage_unverified');
        $this->assertTrue((bool) DB::table('religious_assistant_budgets')->value('halted'));
        $this->ask()->assertStatus(503);
        $this->assertSame(1, $this->generations());
    }

    public function test_erasing_history_erases_private_text_but_does_not_refund_quota_or_republish(): void
    {
        $this->subscribe();
        $operation = (string) Str::uuid();
        $row = $this->ask($operation)->assertOk()->json('data');
        $this->deleteJson($this->url('assistant/history/'.$row['id']))->assertOk();
        $this->getJson($this->url('assistant/history'))->assertJsonCount(0, 'data.items');
        $this->ask($operation)->assertJsonPath('data.question', null)->assertJsonPath('data.answer', null)->assertJsonPath('data.quota.monthly_used', 1);
        $this->assertSame(1, $this->generations());
        $this->sign($this->other);
        $this->deleteJson($this->url('assistant/history/'.$row['id']))->assertNotFound();
    }

    public function test_account_deletion_does_not_reset_global_spend_or_expose_another_accounts_history(): void
    {
        $this->subscribe();
        $this->ask()->assertOk();
        $this->owner->delete();
        $this->assertSame(0, DB::table('religious_assistant_requests')->count());
        $this->assertSame(0, DB::table('religious_subscriptions')->count());
        $this->assertSame(325, (int) DB::table('religious_assistant_budgets')->value('spent'));
    }

    public function test_real_es256_chain_validation_rejects_tamper_and_an_untrusted_root(): void
    {
        $path = base_path('tests/Fixtures/apple-test-chain');
        config(['religious_assistant.ios.root_path' => $path.'/root.pem']);
        $x5c = [];
        foreach (['leaf', 'intermediate', 'root'] as $cert) {
            $x5c[] = preg_replace('/-----[^\n]+-----|\s+/', '', file_get_contents($path.'/'.$cert.'.pem'));
        }
        $jwt = JWT::encode(['signedDate' => now()->getTimestampMs(), 'bundleId' => 'synthetic.bundle'], file_get_contents($path.'/SYNTHETIC-TEST-KEY.pem'), 'ES256', null, ['x5c' => $x5c]);
        $this->assertSame('synthetic.bundle', app(AppleStoreSignature::class)->decode($jwt)['bundleId']);
        $parts = explode('.', $jwt);
        $parts[1] = JWT::urlsafeB64Encode('{"signedDate":0,"bundleId":"forged"}');
        try {
            app(AppleStoreSignature::class)->decode(implode('.', $parts));
            $this->fail('Accepted forged signature');
        } catch (\RuntimeException|\UnexpectedValueException) {
        }
        config(['religious_assistant.ios.root_path' => resource_path('certificates/AppleRootCA-G3.pem')]);
        $this->expectException(\RuntimeException::class);
        app(AppleStoreSignature::class)->decode($jwt);
    }

    private function appleSigner(): \Closure
    {
        $path = base_path('tests/Fixtures/apple-test-chain');
        config(['religious_assistant.ios.enabled' => true, 'religious_assistant.ios.product' => 'synthetic.apple.monthly', 'religious_assistant.ios.environment' => 'Sandbox',
            'religious_assistant.ios.issuer' => 'synthetic-issuer', 'religious_assistant.ios.key_id' => 'synthetic-key', 'religious_assistant.ios.app_id' => '12345',
            'religious_assistant.ios.key_path' => $path.'/SYNTHETIC-TEST-KEY.pem', 'religious_assistant.ios.root_path' => $path.'/root.pem']);
        $x5c = [];
        foreach (['leaf', 'intermediate', 'root'] as $cert) {
            $x5c[] = preg_replace('/-----[^\n]+-----|\s+/', '', file_get_contents($path.'/'.$cert.'.pem'));
        }

        return fn (array $data) => JWT::encode(['signedDate' => now()->getTimestampMs(), ...$data], file_get_contents($path.'/SYNTHETIC-TEST-KEY.pem'), 'ES256', null, ['x5c' => $x5c]);
    }

    public function test_apple_authoritative_status_checks_signed_owner_environment_product_and_revocation(): void
    {
        $sign = $this->appleSigner();
        $transaction = ['bundleId' => config('religious_assistant.ios.bundle'), 'environment' => 'Sandbox', 'productId' => 'synthetic.apple.monthly', 'appAccountToken' => app(ReligiousSubscriptions::class)->storeToken($this->owner),
            'expiresDate' => now()->addMonth()->getTimestampMs(), 'originalTransactionId' => '123456789'];
        $this->appleBody = ['bundleId' => $transaction['bundleId'], 'environment' => 'Sandbox', 'appAppleId' => 12345, 'data' => [['lastTransactions' => [['status' => 1, 'signedTransactionInfo' => $sign($transaction)]]]]];
        $this->postJson($this->url('billing/verify'), ['platform' => 'ios', 'proof' => '123456789'])->assertOk()->assertJsonPath('data.active', true);
        $this->appleBody['data'][0]['lastTransactions'][0]['signedTransactionInfo'] = $sign([...$transaction, 'appAccountToken' => 'wrong-account']);
        $this->postJson($this->url('billing/verify'), ['platform' => 'ios', 'proof' => '123456789'])->assertForbidden();
        $this->appleBody['data'][0]['lastTransactions'][0]['signedTransactionInfo'] = $sign([...$transaction, 'revocationDate' => now()->getTimestampMs()]);
        $this->postJson($this->url('billing/verify'), ['platform' => 'ios', 'proof' => '123456789'])->assertJsonPath('data.active', false);
        $this->appleBody['environment'] = 'Production';
        $this->postJson($this->url('billing/verify'), ['platform' => 'ios', 'proof' => '123456789'])->assertStatus(422);
    }

    public function test_apple_notification_is_signed_deduplicated_and_refreshes_store_instead_of_trusting_event_status(): void
    {
        $sign = $this->appleSigner();
        $transaction = $sign(['bundleId' => config('religious_assistant.ios.bundle'), 'environment' => 'Sandbox', 'productId' => 'synthetic.apple.monthly', 'appAccountToken' => app(ReligiousSubscriptions::class)->storeToken($this->owner), 'expiresDate' => now()->addMonth()->getTimestampMs(), 'originalTransactionId' => '123456789']);
        $this->appleBody = ['bundleId' => config('religious_assistant.ios.bundle'), 'environment' => 'Sandbox', 'appAppleId' => 12345, 'data' => [['lastTransactions' => [['status' => 1, 'signedTransactionInfo' => $transaction]]]]];
        $this->postJson($this->url('billing/verify'), ['platform' => 'ios', 'proof' => '123456789'])->assertOk();
        $this->appleBody['data'][0]['lastTransactions'][0]['status'] = 5;
        $payload = $sign(['notificationUUID' => (string) Str::uuid(), 'data' => ['bundleId' => config('religious_assistant.ios.bundle'), 'environment' => 'Sandbox', 'signedTransactionInfo' => $transaction]]);
        $this->postJson($this->url('billing/webhooks/apple'), ['signedPayload' => 'forged'])->assertUnauthorized();
        $this->postJson($this->url('billing/webhooks/apple'), ['signedPayload' => $payload])->assertOk();
        $this->postJson($this->url('billing/webhooks/apple'), ['signedPayload' => $payload])->assertOk();
        $this->assertSame(1, DB::table('religious_billing_events')->count());
        $this->assertFalse((bool) DB::table('religious_subscriptions')->value('active'));
        $this->assertSame(2, Http::recorded(fn ($r) => str_starts_with($r->url(), 'https://api.storekit'))->count());
    }

    public function test_google_notification_checks_real_rsa_signature_audience_email_and_deduplicates(): void
    {
        $this->subscribe();
        $key = \phpseclib3\Crypt\RSA::createKey(2048);
        $jwk = json_decode($key->getPublicKey()->toString('JWK'), true)['keys'][0];
        $this->googleKeys = [['kid' => 'haq-hook-test', 'kty' => 'RSA', 'alg' => 'RS256', 'n' => $jwk['n'], 'e' => $jwk['e']]];
        config(['religious_assistant.android.notification_audience' => 'https://synthetic.test/hook', 'religious_assistant.android.notification_email' => 'pubsub@synthetic.test']);
        $claims = ['iss' => 'https://accounts.google.com', 'sub' => 'synthetic-pubsub', 'aud' => 'https://synthetic.test/hook', 'email' => 'pubsub@synthetic.test', 'email_verified' => true, 'iat' => time(), 'exp' => time() + 3600];
        $body = ['message' => ['messageId' => 'synthetic-event', 'data' => base64_encode(json_encode(['packageName' => config('religious_assistant.android.package'), 'subscriptionNotification' => ['purchaseToken' => 'synthetic-purchase']]))]];
        foreach ([['aud' => 'wrong'], ['email' => 'wrong@synthetic.test']] as $change) {
            $token = JWT::encode([...$claims, ...$change], $key->toString('PKCS8'), 'RS256', 'haq-hook-test');
            $this->withToken($token)->postJson($this->url('billing/webhooks/google'), $body)->assertUnauthorized();
        }
        $token = JWT::encode($claims, $key->toString('PKCS8'), 'RS256', 'haq-hook-test');
        $this->store['subscriptionState'] = 'SUBSCRIPTION_STATE_EXPIRED';
        $this->withToken($token)->postJson($this->url('billing/webhooks/google'), $body)->assertOk();
        $this->withToken($token)->postJson($this->url('billing/webhooks/google'), $body)->assertOk();
        $this->assertSame(1, DB::table('religious_billing_events')->count());
        $this->assertFalse((bool) DB::table('religious_subscriptions')->value('active'));
    }

    public function test_abandoned_worker_keeps_cost_reservation_and_history_is_cursor_ordered(): void
    {
        $this->subscribe();
        $operation = (string) Str::uuid();
        $this->ask($operation)->assertOk();
        DB::table('religious_assistant_requests')->where('operation_id', $operation)->update(['status' => 'reserved', 'updated_at' => now()->subMinutes(3)]);
        DB::table('religious_assistant_budgets')->update(['reserved' => 600, 'spent' => 0]);
        $this->getJson($this->url('assistant/requests/'.$operation))->assertJsonPath('data.status', 'uncertain');
        $this->ask($operation)->assertJsonPath('data.status', 'uncertain');
        $this->assertSame(1, $this->generations());
        $this->assertSame(600, (int) DB::table('religious_assistant_budgets')->value('reserved'));
        $row = (array) DB::table('religious_assistant_requests')->first();
        unset($row['id']);
        for ($i = 0; $i < 22; $i++) {
            DB::table('religious_assistant_requests')->insert([...$row, 'operation_id' => (string) Str::uuid()]);
        }
        $page = $this->getJson($this->url('assistant/history'))->assertJsonCount(20, 'data.items')->json('data');
        $ids = array_column($page['items'], 'id');
        $this->assertSame(array_reverse(range(4, 23)), $ids);
        $this->getJson($this->url('assistant/history?cursor='.$page['next_cursor']))->assertJsonCount(3, 'data.items');
    }
}
