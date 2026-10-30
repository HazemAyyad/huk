<?php

namespace App\Services\Religious;

use App\Models\AppAccount;
use App\Models\ReligiousAccount;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

/** Password resets, provider unlinking and area revocation invalidate the next request. */
final class AppSessionProof
{
    public function abilities(AppAccount $account, string $provider): array
    {
        $owner = $provider === 'association' ? User::find($account->user_id) : ReligiousAccount::find($account->religious_account_id);
        abort_unless($owner && in_array($provider, $account->areas(), true), 403);

        return ['app:session', 'credential:'.$provider.':'.hash('sha256', $owner->getAuthPassword())];
    }

    public function valid(AppAccount $account, PersonalAccessToken $token): bool
    {
        if (! $account->active || ! $token->can('app:session') || ! $token->expires_at?->isFuture()) {
            return false;
        }
        foreach ($account->areas() as $provider) {
            $owner = $provider === 'association' ? User::find($account->user_id) : ReligiousAccount::find($account->religious_account_id);
            if (! $owner || $owner->getAttribute('deleted_by') || $owner->getAttribute('deleted_at') || $owner->getAttribute('must_change_password')) {
                continue;
            }
            $expected = $this->abilities($account, $provider)[1];
            if (in_array($expected, $token->abilities, true)) {
                return true;
            }
        }

        return false;
    }
}
