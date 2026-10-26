<?php

use App\Http\Controllers\Api\ReligiousAssistantController as Assistant;
use App\Http\Controllers\Api\ReligiousBillingWebhookController as Hooks;
use App\Http\Middleware\ReligiousAccountOnly;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/religious')->middleware('throttle:40,1,religious-assistant')->group(function (): void {
    Route::get('assistant/capabilities', [Assistant::class, 'capabilities']);
    Route::post('billing/webhooks/google', [Hooks::class, 'google']);
    Route::post('billing/webhooks/apple', [Hooks::class, 'apple']);
    Route::middleware(['auth:sanctum', ReligiousAccountOnly::class])->group(function (): void {
        Route::get('billing/profile', [Assistant::class, 'billing']);
        Route::post('billing/verify', [Assistant::class, 'verify'])->middleware('throttle:8,1,religious-receipts');
        Route::get('assistant/history', [Assistant::class, 'history']);
        Route::delete('assistant/history/{entry}', [Assistant::class, 'erase'])->whereNumber('entry');
        Route::post('assistant/requests', [Assistant::class, 'ask'])->middleware('throttle:6,1,religious-questions');
        Route::get('assistant/requests/{operation}', [Assistant::class, 'receipt'])->whereUuid('operation');
    });
});
