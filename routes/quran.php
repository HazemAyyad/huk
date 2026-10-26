<?php

use App\Http\Controllers\Api\QuranContentController;
use App\Http\Controllers\Api\QuranController;
use App\Http\Controllers\Api\ReligiousAccountController;
use App\Http\Controllers\Api\ReligiousIdentityController;
use App\Http\Middleware\ReligiousAccountOnly;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/religious/account')->middleware('throttle:15,1')->group(function (): void {
    Route::post('register', [ReligiousAccountController::class, 'register']);
    Route::post('login', [ReligiousAccountController::class, 'login']);
    Route::get('capabilities', [ReligiousIdentityController::class, 'capabilities']);
    Route::post('google', [ReligiousIdentityController::class, 'google']);
    Route::post('forgot-password', [ReligiousIdentityController::class, 'forgot'])->middleware('throttle:5,1,religious-recovery');
    Route::post('reset-password', [ReligiousIdentityController::class, 'reset'])->middleware('throttle:5,1,religious-recovery');
    Route::middleware(['auth:sanctum', ReligiousAccountOnly::class])->group(function (): void {
        Route::get('profile', [ReligiousAccountController::class, 'profile']);
        Route::post('logout', [ReligiousAccountController::class, 'logout']);
        Route::patch('profile', [ReligiousIdentityController::class, 'profile']);
        Route::post('google/link', [ReligiousIdentityController::class, 'link']);
        Route::delete('', [ReligiousIdentityController::class, 'destroy']);
    });
});
Route::post('v1/quran/sync', [ReligiousAccountController::class, 'sync'])
    ->middleware(['auth:sanctum', ReligiousAccountOnly::class, 'throttle:60,1']);

Route::prefix('v1/quran')->group(function (): void {
    Route::get('manifest', [QuranController::class, 'manifest']);
    Route::get('dataset', [QuranController::class, 'dataset'])->name('quran.dataset');
    Route::get('surahs', [QuranController::class, 'surahs']);
    Route::get('juzs', [QuranController::class, 'juzs']);
    Route::get('verses', [QuranController::class, 'verses']);
    Route::get('search', [QuranController::class, 'search']);
    Route::prefix('content')->middleware('throttle:60,1')->group(function (): void {
        Route::get('pages/{page}', [QuranContentController::class, 'page']);
        Route::get('tafsirs/{source}/{surah}/{ayah}', [QuranContentController::class, 'tafsir']);
        Route::get('meanings/{surah}/{ayah}', [QuranContentController::class, 'meanings']);
        Route::get('words/{surah}/{ayah}', [QuranContentController::class, 'words']);
        Route::get('recitations', [QuranContentController::class, 'recitations']);
    });
});
