<?php

use App\Http\Controllers\Api\AdhanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/religious/adhans')->group(function () {
    Route::get('catalog', [AdhanController::class, 'catalog'])->name('religious.adhans.catalog');
    Route::get('{voice}/{kind}', [AdhanController::class, 'audio'])
        ->where('voice', 'nusuk-[1-9][0-9]*')->where('kind', 'full|takbeer')
        ->name('religious.adhans.audio');
});
