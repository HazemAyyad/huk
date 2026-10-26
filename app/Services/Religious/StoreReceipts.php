<?php

namespace App\Services\Religious;

use Firebase\JWT\JWT;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StoreReceipts
{
    public function ready(string $platform): bool
    {
        $c = config('religious_assistant.'.$platform);
        if (! is_array($c) || ! ($c['enabled'] ?? false) || empty($c['product'])) {
            return false;
        }
        if ($platform === 'android') {
            return is_string($c['credentials_path']) && is_file($c['credentials_path']);
        }

        return $platform === 'ios' && in_array($c['environment'], ['Sandbox', 'Production'], true) && $c['issuer'] && $c['key_id'] && $c['app_id'] && is_file($c['key_path']) && is_file($c['root_path']);
    }

    public function verify(string $platform, string $proof, string $ownerToken): array
    {
        abort_unless($this->ready($platform), 503, 'الاشتراك غير مهيأ حاليًا');

        return $platform === 'android' ? $this->android($proof, $ownerToken) : $this->apple($proof, $ownerToken);
    }

    protected function googleToken(): string
    {
        $credentials = new ServiceAccountCredentials('https://www.googleapis.com/auth/androidpublisher', json_decode(file_get_contents(config('religious_assistant.android.credentials_path')), true, 32, JSON_THROW_ON_ERROR));
        $token = $credentials->fetchAuthToken(fn ($request) => Http::connectTimeout(5)->timeout(12)->withHeaders($request->getHeaders())->send($request->getMethod(), (string) $request->getUri(), ['body' => (string) $request->getBody()])->toPsrResponse());
        if (empty($token['access_token'])) {
            throw new RuntimeException('store_unavailable');
        }

        return $token['access_token'];
    }

    private function android(string $proof, string $ownerToken): array
    {
        $c = config('religious_assistant.android');
        $token = $this->googleToken();
        $base = 'https://androidpublisher.googleapis.com/androidpublisher/v3/applications/'.rawurlencode($c['package']).'/purchases/';
        $response = Http::withToken($token)->connectTimeout(5)->timeout(12)->get($base.'subscriptionsv2/tokens/'.rawurlencode($proof));
        abort_unless($response->successful(), $response->status() === 404 ? 422 : 503, 'تعذّر التحقق من اشتراك المتجر');
        $data = $response->json();
        abort_unless(($data['externalAccountIdentifiers']['obfuscatedExternalAccountId'] ?? '') === $ownerToken, 403, 'الشراء مرتبط بحساب حق آخر');
        abort_if(isset($data['testPurchase']) && ! $c['allow_sandbox'], 422, 'شراء تجريبي غير مقبول في هذه البيئة');
        $line = collect($data['lineItems'] ?? [])->first(fn ($l) => ($l['productId'] ?? '') === $c['product']);
        abort_unless($line && isset($line['expiryTime']), 422, 'المنتج لا يطابق الباكج');
        $expiry = Carbon::parse($line['expiryTime'])->utc();
        $state = $data['subscriptionState'] ?? 'UNKNOWN';
        $active = in_array($state, ['SUBSCRIPTION_STATE_ACTIVE', 'SUBSCRIPTION_STATE_IN_GRACE_PERIOD', 'SUBSCRIPTION_STATE_CANCELED'], true) && $expiry->isFuture();
        if ($active && ($data['acknowledgementState'] ?? '') !== 'ACKNOWLEDGEMENT_STATE_ACKNOWLEDGED') {
            $ack = Http::withToken($token)->connectTimeout(5)->timeout(12)->withBody('{}', 'application/json')->post($base.'subscriptions/'.rawurlencode($c['product']).'/tokens/'.rawurlencode($proof).':acknowledge');
            abort_unless($ack->successful(), 503, 'تعذّر إكمال توثيق شراء المتجر');
        }

        return ['active' => $active, 'state' => $state, 'expires_at' => $expiry, 'product' => $c['product'], 'original_hash' => null,
            'linked_hash' => empty($data['linkedPurchaseToken']) ? null : hash('sha256', 'android:'.$data['linkedPurchaseToken'])];
    }

    private function apple(string $proof, string $ownerToken): array
    {
        abort_unless(preg_match('/^[0-9]{1,40}$/D', $proof), 422, 'معرّف عملية غير صالح');
        $c = config('religious_assistant.ios');
        $authorization = JWT::encode(['iss' => $c['issuer'], 'iat' => time(), 'exp' => time() + 300, 'aud' => 'appstoreconnect-v1', 'bid' => $c['bundle']], file_get_contents($c['key_path']), 'ES256', $c['key_id']);
        $host = $c['environment'] === 'Sandbox' ? 'https://api.storekit-sandbox.apple.com' : 'https://api.storekit.apple.com';
        $response = Http::withToken($authorization)->connectTimeout(5)->timeout(12)->get($host.'/inApps/v1/subscriptions/'.$proof);
        abort_unless($response->successful(), $response->status() === 404 ? 422 : 503, 'تعذّر التحقق من اشتراك المتجر');
        $body = $response->json();
        abort_unless(($body['bundleId'] ?? '') === $c['bundle'] && ($body['environment'] ?? '') === $c['environment'] && ($c['environment'] !== 'Production' || (string) ($body['appAppleId'] ?? '') === (string) $c['app_id']), 422, 'بيئة شراء غير مطابقة');
        $matches = [];
        foreach ($body['data'] ?? [] as $group) {
            foreach ($group['lastTransactions'] ?? [] as $last) {
                $t = app(AppleStoreSignature::class)->decode($last['signedTransactionInfo'] ?? '');
                if (($t['productId'] ?? '') !== $c['product']) {
                    continue;
                }
                abort_unless(($t['bundleId'] ?? '') === $c['bundle'] && ($t['environment'] ?? '') === $c['environment'] && strtolower($t['appAccountToken'] ?? '') === strtolower($ownerToken), 403, 'الشراء مرتبط بحساب أو تطبيق آخر');
                $expiry = Carbon::createFromTimestampMs($t['expiresDate'] ?? 0)->utc();
                $matches[] = ['active' => in_array($last['status'] ?? 0, [1, 4], true) && empty($t['revocationDate']) && $expiry->isFuture(), 'state' => 'APPLE_'.($last['status'] ?? 0),
                    'expires_at' => $expiry, 'product' => $c['product'], 'original_hash' => hash('sha256', 'ios:'.($t['originalTransactionId'] ?? '')), 'linked_hash' => null];
            }
        }
        abort_unless($matches, 422, 'لا يوجد اشتراك مطابق');
        usort($matches, fn ($a, $b) => $b['expires_at'] <=> $a['expires_at']);

        return $matches[0];
    }
}
