<?php

namespace App\Http\Middleware;

use App\Models\AppAccount;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppSessionProof;
use Closure;
use Illuminate\Http\Request;

final class AppAdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(config('haq_app.enabled'), 503, 'app_admin_disabled');
        $account = $request->user();
        abort_unless($account instanceof AppAccount && $account->currentAccessToken()?->can('app:admin'), 403);
        // Expiry is also checked by Sanctum. A web session cannot substitute for this token.
        abort_unless($account->currentAccessToken()?->expires_at?->isFuture(), 401);
        $parent = (int) str_replace('app-admin:', '', $account->currentAccessToken()->name);
        $source = $account->tokens()->where('id', $parent)->first();
        abort_unless($source && app(AppSessionProof::class)->valid($account, $source), 401);
        app(AppAdminAccess::class)->require($account);

        return $next($request);
    }
}
