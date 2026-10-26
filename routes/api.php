<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook']);
Route::post('/telegram/webhook/{secret}', [TelegramBotController::class, 'webhook']);

require __DIR__.'/quran.php';
require __DIR__.'/religious.php';
require __DIR__.'/religious_community.php';
