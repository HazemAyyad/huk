<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HumanitarianCases\AmputationController;
use App\Http\Controllers\HumanitarianCases\FamilyBreadwinnerController;
use App\Http\Controllers\HumanitarianCases\InjuredController;
use App\Http\Controllers\HumanitarianCases\OrphansController;
use App\Http\Controllers\HumanitarianCases\PatientController;
use App\Http\Controllers\HumanitarianCases\SolidarityHousingController;
use App\Http\Controllers\HumanitarianCases\StrandedFamilyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Http\Controllers\UserTelegramController;
use App\Http\Controllers\OrphansDetailController;
use App\Http\Controllers\PhotographerController;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ForcePasswordChange;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AmputationDetailController;
use App\Http\Controllers\MeatController;
use App\Http\Controllers\WheelchairRequestController;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
    ],
    function () {
        Route::get('/', [HomeController::class, "index"])->name("index");
        Route::get('/about-us', [HomeController::class, "about"])->name("about.index");

        Route::get('/governorates/get', [HomeController::class, 'getGovernorates']);
        Route::get('/{id}/cities/get', [HomeController::class, 'getCities']);
        Route::get('/{id}/areas/get', [HomeController::class, 'getAreas']);
        
        Route::get('/photographer-ad', [PhotographerController::class, 'show'])->name('photographer.index');

        Route::post('/photographer-ad/form', [PhotographerController::class, 'form'])->name('photographer.submit');
        Route::get('/contact', function () {
            return view('contact-links');
        });

        // طلب كرسي متحرك
        Route::get('/wheelchair', [WheelchairRequestController::class,'checkPage'])->name('wheelchair.check');

        Route::post('/wheelchair/check-id', [WheelchairRequestController::class,'checkId']);

        Route::get('/wheelchair/register', [WheelchairRequestController::class,'registerPage'])->name('wheelchair.register');

        Route::post('/wheelchair/register', [WheelchairRequestController::class,'store'])->name('wheelchair.register.submit');
        Route::get('/wheelchair/success', function () {
            return view('wheelchair.success');
        })->name('wheelchair.success');


        // فعالية اللحمة عيد الاضحى
        Route::get('/Eid-al-Adha-Mubarak', [MeatController::class, 'index']);
        Route::post('/meat-check', [MeatController::class, 'check']);
        Route::post('/meat-submit', [MeatController::class, 'submit'])->name('meat.submit');
        Route::get('/meat-register', [MeatController::class, 'register']);
        Route::get('/check-national-id', [MeatController::class, 'checkNationalId']);
        Route::middleware('auth')->group(function () {

            Route::get('/my-profile', [UserTelegramController::class, 'show'])
                ->name('user.telegram.profile');

            Route::post('/my-profile', [UserTelegramController::class, 'update'])
                ->name('user.telegram.profile.update');

            Route::middleware(['auth', ForcePasswordChange::class])->group(function () {
                // روابط استكمال بيانات الأيتام
                Route::get('/detail-cases/orphans/create', [OrphansDetailController::class, 'create'])
                    ->name('detail-cases.orphans.create');

                Route::post('/detail-cases/orphans/store', [OrphansDetailController::class, 'store'])
                    ->name('detail-cases.orphans.store');
                
                
                Route::get('/detail-cases/amputation/create', [AmputationDetailController::class, 'create'])
                    ->name('detail-cases.amputation.create');

                Route::post('/detail-cases/amputation/store', [AmputationDetailController::class, 'store'])
                    ->name('detail-cases.amputation.store');
            });

            Route::get('/change-password', function () {
                return view('auth.change-password');
            })->name('password.change')->middleware('auth');


            Route::post('/change-password', function (\Illuminate\Http\Request $request) {

                $request->validate([
                    'password' => 'required|min:6|confirmed'
                ]);

                $user = Auth::user();

                $user->password = Hash::make($request->password);
                $user->must_change_password = 0;
                $user->save();

                return redirect('/')->with('success','تم تغيير كلمة المرور بنجاح');

            })->name('password.change.submit')->middleware('auth');
        });

        Route::group(['prefix' => 'users/humanitarian-cases'], function () {
            Route::group(['prefix' => 'orphans'], function () {
                Route::get('/create', [OrphansController::class, 'create'])->name('humanitarian-cases.orphans.create');
                Route::post('/store', [OrphansController::class, 'store'])->name('humanitarian-cases.orphans.store');
            });

            Route::group(['prefix' => 'amputation-cases'], function () {
                Route::get('/create', [AmputationController::class, 'create'])->name('humanitarian-cases.amputation.create');
                Route::post('/store', [AmputationController::class, 'store']);
            });

            Route::group(['prefix' => 'solidarity-housing'], function () {
                Route::get('/create', [SolidarityHousingController::class, 'create'])->name('humanitarian-cases.solidarity.housing.create');
                Route::post('/store', [SolidarityHousingController::class, 'store']);
            });

            Route::group(['prefix' => 'family-breadwinners'], function () {
                Route::get('/create', [FamilyBreadwinnerController::class, 'create'])->name('humanitarian-cases.family-breadwinners.create');
                Route::post('/store', [FamilyBreadwinnerController::class, 'store']);
            });

            Route::group(['prefix' => 'stranded-families'], function () {
                Route::get('/create', [StrandedFamilyController::class, 'create'])->name('humanitarian-cases.stranded-families.create');
                Route::post('/store', [StrandedFamilyController::class, 'store']);
            });

            Route::group(['prefix' => 'injured-cases'], function () {
                Route::get('/create', [InjuredController::class, 'create'])->name('humanitarian-cases.injured-cases.create');
                Route::post('/store', [InjuredController::class, 'store']);
            });

            Route::group(['prefix' => 'patient-cases'], function () {
                Route::get('/create', [PatientController::class, 'create'])->name('humanitarian-cases.patient-cases.create');
                Route::post('/store', [PatientController::class, 'store']);
            });
        });

        Route::middleware('auth')->group(function () {
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });
    }
);

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/religious_admin.php';
