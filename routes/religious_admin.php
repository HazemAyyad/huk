<?php

use App\Http\Controllers\Admin\ReligiousCommunityAdminController as CommunityAdmin;
use App\Http\Controllers\Admin\ReligiousContentAdminController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group(['prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function (): void {
        Route::prefix('admin/religious-community')->name('admin.religious.community.')->group(function (): void {
            Route::get('/', [CommunityAdmin::class, 'index'])->name('index');
            Route::post('/circles', [CommunityAdmin::class, 'circle'])->name('circles');
            Route::post('/entries/{entry}/review', [CommunityAdmin::class, 'review'])->whereNumber('entry')->name('review');
            Route::post('/reports/{report}', [CommunityAdmin::class, 'report'])->whereNumber('report')->name('reports');
            Route::post('/publications', [CommunityAdmin::class, 'publication'])->name('publications');
        });
        Route::prefix('admin/religious-content')->name('admin.religious.')->group(function (): void {
            Route::get('/', [ReligiousContentAdminController::class, 'index'])->name('index');
            Route::get('/create', [ReligiousContentAdminController::class, 'create'])->name('create');
            Route::post('/content', [ReligiousContentAdminController::class, 'save'])->name('save');
            Route::post('/categories', [ReligiousContentAdminController::class, 'saveCategory'])->name('categories.save');
            Route::get('/{id}/edit', [ReligiousContentAdminController::class, 'edit'])->name('edit');
        });
    });
