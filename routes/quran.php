<?php

use App\Http\Controllers\Api\QuranController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/quran')->group(function (): void {
    Route::get('manifest', [QuranController::class, 'manifest']);
    Route::get('dataset', [QuranController::class, 'dataset'])->name('quran.dataset');
    Route::get('surahs', [QuranController::class, 'surahs']);
    Route::get('juzs', [QuranController::class, 'juzs']);
    Route::get('verses', [QuranController::class, 'verses']);
    Route::get('search', [QuranController::class, 'search']);
});
