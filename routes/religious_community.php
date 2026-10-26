<?php

use App\Http\Controllers\Api\ReligiousCommunityController as Community;
use App\Http\Middleware\ReligiousAccountOnly;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/religious/community')->middleware('throttle:60,1,religious-community')->group(function (): void {
    Route::get('capabilities', [Community::class, 'capabilities']);
    Route::get('publications', [Community::class, 'publications']);
    Route::post('publications/query', [Community::class, 'publications']);
    Route::middleware(['auth:sanctum', ReligiousAccountOnly::class])->group(function (): void {
        Route::get('circles', [Community::class, 'circles']);
        Route::get('circles/{circle}', [Community::class, 'circle'])->whereNumber('circle');
        Route::put('circles/{circle}/membership', [Community::class, 'membership'])->whereNumber('circle');
        Route::post('circles/{circle}/counts', [Community::class, 'count'])->whereNumber('circle');
        Route::get('entries', [Community::class, 'entries']);
        Route::post('entries', [Community::class, 'create'])->middleware('throttle:10,1,religious-posting');
        Route::get('entries/{entry}', [Community::class, 'entry'])->whereNumber('entry');
        Route::delete('entries/{entry}', [Community::class, 'destroy'])->whereNumber('entry');
        Route::put('entries/{entry}/like', [Community::class, 'like'])->whereNumber('entry');
        Route::post('reports', [Community::class, 'report'])->middleware('throttle:5,1,religious-reports');
        Route::get('blocks', [Community::class, 'blocks']);
        Route::put('blocks/{account}', [Community::class, 'block'])->whereNumber('account');
    });
});
