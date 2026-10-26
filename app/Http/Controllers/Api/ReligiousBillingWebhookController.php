<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReligiousAccount;
use App\Services\Religious\AppleStoreSignature;
use App\Services\Religious\ReligiousSubscriptions;
use App\Services\Religious\StoreReceipts;
use Google\Auth\AccessToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

final class ReligiousBillingWebhookController extends Controller
{
    public function google(Request $request)
    {
        $c = config('religious_assistant.android');
        abort_unless(app(StoreReceipts::class)->ready('android') && $c['notification_audience'] && $c['notification_email'] && app(ReligiousSubscriptions::class)->schemaReady(), 503);
        try {
            $claims = (new AccessToken(fn ($r) => Http::connectTimeout(5)->timeout(10)->get((string) $r->getUri())->toPsrResponse()))->verify($request->bearerToken() ?? '', ['audience' => $c['notification_audience']]);
        } catch (\Throwable) {
            abort(401);
        }
        abort_unless($claims && ($claims['email'] ?? '') === $c['notification_email'] && ($claims['email_verified'] ?? false) === true && ($claims['exp'] ?? 0) > time(), 401);
        $data = $request->validate(['message.messageId' => 'required|string|max:90', 'message.data' => 'required|string|max:24000']);
        $decoded = base64_decode($data['message']['data'], true);
        abort_unless($decoded !== false, 422);
        $body = json_decode($decoded, true, 32);
        abort_unless(is_array($body), 422);
        abort_unless(($body['packageName'] ?? '') === $c['package'], 422);
        $proof = $body['subscriptionNotification']['purchaseToken'] ?? null;
        abort_unless($proof === null || (is_string($proof) && strlen($proof) <= 6000), 422);

        return $this->apply('google:'.$data['message']['messageId'], $proof === null ? null : hash('sha256', 'android:'.$proof), false);
    }

    public function apple(Request $request)
    {
        abort_unless(app(StoreReceipts::class)->ready('ios') && app(ReligiousSubscriptions::class)->schemaReady(), 503);
        $data = $request->validate(['signedPayload' => 'required|string|max:50000']);
        try {
            $body = app(AppleStoreSignature::class)->decode($data['signedPayload']);
        } catch (\Throwable) {
            abort(401);
        }
        $c = config('religious_assistant.ios');
        abort_unless(($body['data']->bundleId ?? null) === $c['bundle'] && ($body['data']->environment ?? null) === $c['environment'] && ($c['environment'] !== 'Production' || (string) ($body['data']->appAppleId ?? '') === (string) $c['app_id']), 422);
        $id = $body['notificationUUID'] ?? '';
        abort_unless(is_string($id) && strlen($id) <= 80 && $id !== '', 422);
        $transaction = $body['data']->signedTransactionInfo ?? null;
        try {
            $t = $transaction === null ? null : app(AppleStoreSignature::class)->decode($transaction);
        } catch (\Throwable) {
            abort(401);
        }

        return $this->apply('apple:'.$id, $t === null ? null : hash('sha256', 'ios:'.($t['originalTransactionId'] ?? '')), true);
    }

    private function apply(string $event, ?string $hash, bool $original)
    {
        if (DB::table('religious_billing_events')->where('id', $event)->exists()) {
            return response()->json(['received' => true]);
        }
        if ($hash !== null) {
            // Authenticate the event, but obtain entitlement only from a fresh store response.
            $row = DB::table('religious_subscriptions')->where($original ? 'original_hash' : 'purchase_hash', $hash)->first();
            if ($row && ($account = ReligiousAccount::find($row->account_id))) {
                app(ReligiousSubscriptions::class)->verify($account, $row->platform, Crypt::decryptString($row->proof));
            }
        }
        DB::table('religious_billing_events')->insertOrIgnore(['id' => $event, 'created_at' => now()]);

        return response()->json(['received' => true]);
    }
}
