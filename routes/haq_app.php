<?php

use App\Http\Controllers\Api\AppLocalizationController as Content;
use App\Http\Controllers\Api\AppSessionController as Session;
use App\Http\Controllers\Api\AppSourceController as Sources;
use App\Http\Middleware\AppAccountOnly;
use App\Http\Middleware\AppAdminOnly;
use App\Http\Middleware\ReligiousAccountOnly;
use Illuminate\Support\Facades\Route;

Route::prefix('v2')->middleware('throttle:60,1')->group(function (): void {
    Route::post('account/login', [Session::class, 'login'])->middleware('throttle:10,1');
    Route::post('account/exchange', [Session::class, 'exchange'])->middleware(['auth:sanctum', ReligiousAccountOnly::class]);
    Route::middleware(['auth:sanctum', AppAccountOnly::class])->group(function (): void {
        Route::get('account/session', [Session::class, 'current']);
        Route::post('account/logout', [Session::class, 'logout']);
        Route::post('account/link', [Session::class, 'link'])->middleware('throttle:5,1');
        Route::post('admin/session', [Session::class, 'admin']);
    });
    Route::get('app-content/bootstrap', [Content::class, 'bootstrap']);
    Route::get('app-content/releases/{release}', [Content::class, 'release'])->whereNumber('release');
    Route::prefix('admin')->middleware(['auth:sanctum', AppAdminOnly::class])->group(function (): void {
        Route::get('languages', [Content::class, 'languages']);
        Route::get('languages/{locale}/records', [Content::class, 'records']);
        Route::put('languages/{locale}/records', [Content::class, 'save']);
        Route::post('languages/{locale}/review', [Content::class, 'review']);
        Route::post('languages/{locale}/publish', [Content::class, 'publish']);
        Route::post('languages/{locale}/hide', [Content::class, 'hide']);
        Route::get('languages/{locale}/releases', [Content::class, 'releases']);
        Route::get('languages/{locale}/sources', [Sources::class, 'index']);
        Route::post('languages/{locale}/sources', [Sources::class, 'create']);
        Route::post('languages/{locale}/sources/selection', [Sources::class, 'select']);
        Route::get('languages/{locale}/sources/{id}', [Sources::class, 'preview'])->whereNumber('id');
        Route::post('languages/{locale}/sources/{id}/review', [Sources::class, 'review'])->whereNumber('id');
        Route::get('audit', [Content::class, 'audit']);
        Route::get('team', [Content::class, 'team']);
        Route::patch('team/{member}', [Content::class, 'updateMember'])->whereNumber('member');
    });
});
