<?php

namespace App\Services\Religious;

use Google\Auth\AccessToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class GoogleIdentity
{
    public function verify(string $token): array
    {
        $audience = config('religious_account.google_client_id');
        abort_unless(is_string($audience) && $audience !== '', 503, 'Google غير مفعّل حاليًا');
        try {
            $claims = (new AccessToken(fn ($request) => Http::connectTimeout(5)->timeout(10)->get((string) $request->getUri())->toPsrResponse()))
                ->verify($token, ['audience' => $audience]);
        } catch (\Throwable) {
            abort(503, 'تعذّر التحقق من Google؛ أعد المحاولة');
        }
        if (! $claims || ! is_string($claims['sub'] ?? null) || $claims['sub'] === '' ||
            ! filter_var($claims['email'] ?? '', FILTER_VALIDATE_EMAIL) || ($claims['email_verified'] ?? false) !== true ||
            ($claims['exp'] ?? 0) <= time() || ($claims['iat'] ?? PHP_INT_MAX) > time() + 60) {
            throw ValidationException::withMessages(['id_token' => 'تعذّر التحقق من هوية Google']);
        }

        return $claims;
    }
}
