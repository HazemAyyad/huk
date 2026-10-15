<?php

use App\Http\Controllers\Api\ReligiousContentController;
use App\Http\Middleware\ReligiousAccountOnly;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/religious')->middleware('throttle:60,1')->group(function (): void {
    Route::get('manifest', [ReligiousContentController::class, 'manifest']);
    Route::get('dataset', [ReligiousContentController::class, 'dataset']);
    Route::get('categories', [ReligiousContentController::class, 'categories']);
    Route::get('content', [ReligiousContentController::class, 'index']);
    Route::get('content/{id}', [ReligiousContentController::class, 'show']);
    Route::post('sync', [ReligiousContentController::class, 'sync'])
        ->middleware(['auth:sanctum', ReligiousAccountOnly::class]);
});
