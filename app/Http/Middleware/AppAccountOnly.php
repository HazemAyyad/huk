<?php

namespace App\Http\Middleware;

use App\Models\AppAccount;
use App\Services\Religious\AppSessionProof;
use Closure;
use Illuminate\Http\Request;

final class AppAccountOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(config('haq_app.enabled'), 503, 'app_accounts_disabled');
        $account = $request->user();
        abort_unless($account instanceof AppAccount && $account->active && $account->currentAccessToken()?->can('app:session'), 403);
        abort_unless(app(AppSessionProof::class)->valid($account, $account->currentAccessToken()), 401);

        return $next($request);
    }
}
