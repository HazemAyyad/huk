<?php

namespace App\Http\Middleware;

use App\Models\ReligiousAccount;
use Closure;
use Illuminate\Http\Request;

final class ReligiousAccountOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user() instanceof ReligiousAccount && $request->user()->tokenCan('quran:sync'), 403);

        return $next($request);
    }
}
