<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\AssistanceRequestController;
use App\Http\Controllers\Admin\CaseController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\EmployeePaymentController;
use App\Http\Controllers\Admin\GovernorateController;
use App\Http\Controllers\Admin\HumanitarianCaseController;
use App\Http\Controllers\Admin\HumanitarianCases\AmputationController;
use App\Http\Controllers\Admin\HumanitarianCases\FamilyBreadwinnerController;
use App\Http\Controllers\Admin\HumanitarianCases\InjuredController;
use App\Http\Controllers\Admin\HumanitarianCases\OrphansController;
use App\Http\Controllers\Admin\HumanitarianCases\PatientController;
use App\Http\Controllers\Admin\HumanitarianCases\SolidarityHousingController;
use App\Http\Controllers\Admin\HumanitarianCases\StrandedFamilyController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Http\Controllers\AdminTelegramController;
use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\MessageLogTelegramController;
use App\Http\Controllers\PhotographerController;
use App\Http\Controllers\RamadanController;
use App\Http\Controllers\OrphanReviewController;
use App\Http\Controllers\EmbassyEventController;
use App\Http\Controllers\AmputationReviewController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MeatController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\WheelchairReviewController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\SessionController;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
    ],
    function () {
        Route::group(['prefix' => 'admin'], function () {
            Route::get('/', [AdminController::class, 'indexadmin'])->name('admin.index');
            Route::get('/get-analysis', [AdminController::class, 'getAnalysis']);
            Route::get('/human-cases', [AdminController::class, 'getHumanCases']);
            Route::get('/search', [AdminController::class, 'index'])->name('admin.search');
            Route::get('/location', [AdminController::class, 'index'])->name('admin.location');
            Route::get('/general-research', [AdminController::class, 'generalResearch']);
            Route::get('/reviews', [AdminController::class, 'getReviews'])->name('admin.reviews.index');
            Route::get('/reviews/index', [AdminController::class, 'getAllReviews']);
            Route::patch('/reviews/{id}/update-to-confirm', [AdminController::class, 'updateReviewConfirm']);
            Route::patch('/reviews/{id}/update-to-unconfirm', [AdminController::class, 'updateReviewUnConfirm']);
            Route::delete('/reviews/{id}/destroy', [AdminController::class, 'updateReviewDestroy']);

            Route::get('/useradmin', [UserAdminController::class, 'useradmin'])
                ->name('admin.useradmin');

            Route::get('/useradmin/{id}/edit', [UserAdminController::class, 'edit'])
                ->name('admin.useradmin.edit');

            Route::put('/useradmin/{id}', [UserAdminController::class, 'update'])
                ->name('admin.useradmin.update');


             Route::get('/sessions', [SessionController::class, 'index'])
                ->name('admin.sessions.index');

            Route::delete('/sessions/{id}', [SessionController::class, 'destroy'])
                ->name('admin.sessions.destroy');

            Route::delete('/sessions-others', [SessionController::class, 'destroyOthers'])
                ->name('admin.sessions.destroy-others');



            Route::get('/guardian-cases', [AdminController::class, 'getGuardianCases']);
            
            Route::get('/wheelchair-requests', [WheelchairReviewController::class, 'index'])
                ->name('wheelchair.index');

            Route::get('/wheelchair-requests/{id}', [WheelchairReviewController::class,'show'])
                ->name('wheelchair.show');

            Route::post('/wheelchair-requests/{id}/review', [WheelchairReviewController::class,'review'])
                ->name('wheelchair.review');

            Route::get(
                '/wheelchair/{id}/check-previous-benefit',
                [WheelchairReviewController::class, 'checkPreviousWheelchair']
            )->name('wheelchair.check.previous');

            Route::get('/telegram', [AdminTelegramController::class, 'index'])->name('admin.telegram.index');
            Route::post('/telegram/send-direct', [AdminTelegramController::class, 'sendDirect']);
            Route::post('/telegram/delete-users', [AdminTelegramController::class, 'deleteUsers'])->name('admin.telegram.delete-users');
            Route::post('/telegram/unblock/{user}', [AdminTelegramController::class, 'unblock'])->name('admin.telegram.unblock');
            Route::get('/telegram/details/{id}', [AdminTelegramController::class, 'showDetails'])->name('admin.telegram.details');
            Route::delete('/telegram/{id}', [AdminTelegramController::class, 'destroy'])
                ->name('admin.telegram.destroy')
                ->middleware(['auth', 'role:superadmin']);

            // 🗑️ صفحة المحذوفين
            Route::get('/telegram/trashed', [AdminTelegramController::class, 'trashed'])
                ->name('admin.telegram.trashed')
                ->middleware(['auth', 'role:superadmin']);

            // ♻️ استعادة مستخدم محذوف
            Route::post('/telegram/restore/{id}', [AdminTelegramController::class, 'restore'])
                ->name('admin.telegram.restore')
                ->middleware(['auth', 'role:superadmin']);

            // 💥 حذف نهائي
            Route::delete('/telegram/force-delete/{id}', [AdminTelegramController::class, 'forceDelete'])
                ->name('admin.telegram.force-delete')
                ->middleware(['auth', 'role:superadmin']);

                

            Route::get('/telegram/{id}/approve', [AdminTelegramController::class, 'approveForm'])
                ->name('admin.telegram.approve.form');

            Route::post('/telegram/{id}/approve', [AdminTelegramController::class, 'approve'])
                ->name('admin.telegram.approve');
            
            Route::resource('reminders', ReminderController::class);

            Route::post('/reminders/{reminder}/complete',
                [ReminderController::class,'complete'])
                ->name('reminders.complete');
            
            Route::get('/telegram/users/create', [AdminTelegramController::class, 'createUser'])
                ->name('admin.telegram.create');

            Route::post('/telegram/users/store', [AdminTelegramController::class, 'storeUser'])
                ->name('admin.telegram.store');

            Route::get('/telegram/unblock/{id}', [AdminTelegramController::class, 'unblockPage'])
                ->name('admin.telegram.unblock.page');

            Route::post('/telegram/unblock/{id}', [AdminTelegramController::class, 'unblockUser'])
                ->name('admin.telegram.unblock');
    
            Route::put('/telegram/users/{id}', [AdminTelegramController::class, 'update'])->name('admin.telegram.update');
            Route::get('/telegram/users/{id}/edit', [AdminTelegramController::class, 'edit'])->name('admin.telegram.edit');
            Route::get('/user/{id}/qr', [QrController::class, 'generateQr'])->name('admin.telegram.generate-qr');
            
            Route::get('/export-orphans', [AdminTelegramController::class, 'exportOrphans'])->name('admin.telegram.export-orphans');


            Route::get('/search-advance', [ExcelController::class, 'search'])->name('search.advance');
            Route::post('/excel-check', [ExcelController::class, 'check'])->name('excel.check');
            Route::get('/export', [ExcelController::class, 'export'])->name('search.export');

            Route::get('/Eid-al-Adha-Mubarak', [MeatController::class, 'adminIndex'])->name('meat.adminindex');
            Route::post('/toggle-meat-check', [MeatController::class, 'toggleMeatCheck'])->name('toggle.meat.check');
            Route::get('/meat/export', [MeatController::class, 'export'])
                ->name('meat.export');
            
            Route::get('/events/{event}/delivery', [DeliveryController::class, 'delivery'])
                ->name('events.delivery');

            Route::post('/delivery/{id}/mark', [DeliveryController::class, 'markDelivery'])
                ->name('delivery.mark');
                    
            Route::get('/messages', [MessageLogTelegramController::class, 'index'])->name('admin.messages.index');
            Route::get('/messages/{id}/edit', [MessageLogTelegramController::class, 'edit'])->name('admin.messages.edit');
            Route::put('/messages/{id}', [MessageLogTelegramController::class, 'update'])->name('admin.messages.update');
            Route::delete('/messages/{id}', [MessageLogTelegramController::class, 'destroy'])->name('admin.messages.destroy');
            
            Route::get('events', [AdminEventController::class, 'index'])->name('events.index');
            Route::get('events/create', [AdminEventController::class, 'create'])->name('events.create');
            Route::post('events/store', [AdminEventController::class, 'store'])->name('events.store');
            Route::get('reports/event-distribution', [AdminEventController::class, 'reports'])->name('reports.event-distribution');
            // روابط تعديل الفعاليات
            Route::get('events/{id}/edit', [AdminEventController::class, 'edit'])->name('events.edit');
            Route::put('events/{id}/update', [AdminEventController::class, 'update'])->name('events.update');
            // صفحة عرض النموذج
            Route::get('events/{event}/task-distribution', [AdminEventController::class, 'taskDistributionForm'])
                ->name('events.task-distribution');

            // حفظ التوزيع بعد التحديد
            Route::post('events/{event}/task-distribution', [AdminEventController::class, 'storeTaskDistribution'])
                ->name('events.task-distribution.store');

            Route::get('/events/{event}/tasks', [AdminEventController::class, 'tasks'])->name('events.tasks');

            // روابط تعديل الفعاليات
            Route::get('events/{id}/edit', [AdminEventController::class, 'edit'])->name('events.edit');
            Route::put('events/{id}/update', [AdminEventController::class, 'update'])->name('events.update');

            // صفحة عرض النموذج
            Route::get('events/{event}/task-distribution', [AdminEventController::class, 'taskDistributionForm'])
                ->name('events.task-distribution');

            // حفظ التوزيع بعد التحديد
            Route::post('events/{event}/task-distribution', [AdminEventController::class, 'storeTaskDistribution'])
                ->name('events.task-distribution.store');
            
            Route::post('/attendance/{id}/excuse', [AdminEventController::class, 'markExcused'])
                ->name('attendance.excuse');

            // صفحة عرض الحضور جبر
            Route::get('/events/{id}/attendance', [AttendanceController::class, 'attendance'])
                ->name('events.attendance');

            Route::post('/attendance/{id}', [AttendanceController::class, 'markAttendance'])
                ->name('attendance.mark');

            Route::get('/fixed-events', [AdminEventController::class, 'fixedEvents'])
                ->name('events.fixed');


            Route::get('/events/{event}/import', [AdminEventController::class, 'showImport'])
                ->name('admin.events.import');

            Route::post('/events/import', [AdminEventController::class, 'handleImport'])
                ->name('admin.events.import.store');


            Route::post('/telegram/users/{user}/notes', [AdminTelegramController::class,'storeNote'])
                ->name('admin.telegram.notes.store');

            Route::put('/telegram/notes/{note}', [AdminTelegramController::class,'updateNote'])
                ->name('admin.telegram.notes.update');

            Route::delete('/telegram/notes/{note}', [AdminTelegramController::class,'deleteNote'])
                ->name('admin.telegram.notes.delete');
                
            Route::get('/orphans', [OrphanReviewController::class, 'index'])
                ->name('admin.orphans.index');

            Route::get('/orphans/{id}', [OrphanReviewController::class, 'show'])
                ->name('admin.orphans.show');

            Route::post('/orphans/{id}/review', [OrphanReviewController::class, 'review'])
                ->name('admin.orphans.review');

            Route::post('/admin/orphans/{id}/attachment', [OrphanReviewController::class, 'updateAttachment'])
                ->name('admin.orphans.attachment');

            Route::delete('/admin/orphans/{id}', [OrphanReviewController::class, 'destroy'])
                ->name('admin.orphans.destroy');

            Route::post('/admin/orphans/{id}/update-image', 
                [OrphanReviewController::class, 'updateImage'])
                ->name('admin.orphans.updateImage');

            ############################################################
            Route::get('/admin/orphans/{id}/edit', [OrphanReviewController::class, 'edit'])
                ->name('admin.orphans.edit');

            Route::put('/admin/orphans/{id}', [OrphanReviewController::class, 'update'])
                ->name('admin.orphans.update');

            Route::post('/admin/orphans/{id}/children', [OrphanReviewController::class, 'storeChild'])
                ->name('admin.orphans.storeChild');

            Route::put('/admin/orphans/children/{child}', [OrphanReviewController::class, 'updateChild'])
                ->name('admin.orphans.updateChild');  // ✅ تغيير الاسم

            Route::delete('/admin/orphans/children/{child}', [OrphanReviewController::class, 'destroyChild'])
                ->name('admin.orphans.destroyChild');  // ✅ تغيير الاسم

            ############################################################

            #حالات البتر عرض الطلبات
            Route::get('/amputations', [AmputationReviewController::class, 'index'])->name('admin.amputations.index');
            Route::get('/amputations/{id}', [AmputationReviewController::class, 'show'])->name('admin.amputations.show');
            Route::post('/amputations/review/{id}', [AmputationReviewController::class, 'review'])->name('admin.amputations.review');
            Route::delete('/amputations/{id}', [AmputationReviewController::class, 'destroy'])->name('admin.amputations.destroy');
            Route::post('/amputations/update-image/{id}', [AmputationReviewController::class, 'updateImage'])->name('admin.amputations.updateImage');
            Route::post('/amputations/update-attachment/{id}',
                [AmputationReviewController::class, 'updateAttachment'])
                ->name('admin.amputations.updateAttachment');    

            Route::get('/messages/triple-beneficiaries', 
                [MessageLogTelegramController::class, 'tripleBeneficiaries']
            )->name('admin.messages.triple');
            
            // روابط الفئات (Categories)
            Route::get('/categories', [CategoriesController::class, 'listCategories'])->name('admin.categories.index');
            Route::post('/categories/store', [CategoriesController::class, 'storecategories'])->name('admin.categories.store');
            Route::get(
                '/categories/{category}/users',
                [CategoriesController::class, 'categoryUsers']
            )->name('admin.categories.users');

            Route::get(
                '/indexfilter',
                [CategoriesController::class, 'indexfilter']
            )->name('admin.categories.indexfilter');


            Route::get('/photographer-applications', 
                [PhotographerController::class, 'applications']
            )->name('photographer.applications');
            Route::get('/photographer/applications/advanced-search', [PhotographerController::class, 'advancedSearch'])
                ->name('photographer.applications.advanced-search');
            Route::get('/photographer/applications/export', [PhotographerController::class, 'exportApplications'])
                ->name('photographer.applications.export');
            
            Route::prefix('ramadan')->group(function () {
                Route::get('/', [RamadanController::class, 'index'])->name('ramadan.index');
                Route::post('/receive/{id}', [RamadanController::class, 'receive'])->name('ramadan.receive');
                Route::get('/report', [RamadanController::class, 'report'])->name('ramadan.report');
                Route::get('/beneficiary/edit/{id}', [RamadanController::class, 'editBeneficiary'])->name('ramadan.edit');
                Route::post('/beneficiary/update/{id}', [RamadanController::class, 'updateBeneficiary'])->name('ramadan.update');
                Route::delete('/beneficiary/delete/{id}', [RamadanController::class, 'deleteBeneficiary'])->name('ramadan.delete');
                Route::delete('/distribution/delete/{id}', [RamadanController::class, 'deleteDistribution'])->name('ramadan.distribution.delete');
                Route::get('/create', [RamadanController::class, 'create'])->name('ramadan.create');
                Route::post('/store', [RamadanController::class, 'store'])->name('ramadan.store');

            });

            // صفحة عرض كل الحضور
            Route::get('/embassy-event', [EmbassyEventController::class, 'index'])->name('embassy.index');

            // إضافة سجل جديد
            Route::post('/embassy-event/store', [EmbassyEventController::class, 'store'])->name('embassy.store');

            // تسجيل حضور
            Route::post('/embassy-event/attend/{id}', [EmbassyEventController::class, 'updateAttendance'])->name('embassy.attend');

            // حذف سجل
            Route::delete('/embassy-event/delete/{id}', [EmbassyEventController::class, 'destroy'])->name('embassy.destroy');
            Route::post('/embassy-event/import', [EmbassyEventController::class, 'import'])->name('embassy.import');
            // لانشاء حساب بطريقة مباشرة برقم الهوية
            Route::post('/embassy-event/update', [EmbassyEventController::class,'update'])->name('embassy.update');
            Route::get('/embassy-event/report',[EmbassyEventController::class,'report'])->name('embassy.report');
            
            
            // لانشاء حساب بطريقة مباشرة برقم الهوية
            Route::post(
                '/admin/telegram/{user}/create-login',
                [AdminTelegramController::class, 'createLoginUser']
            )->name('admin.telegram.createLoginUser');


            Route::group(['prefix' => "users"], function () {
                Route::get('/', [UserController::class, 'index'])->name('admin.users.index');
                Route::get('/managers', [UserController::class, 'index'])->name('admin.users.managers.index');
                Route::get('/create', [UserController::class, 'index']);
                Route::get('/{id}/edit', [UserController::class, 'index']);

                Route::get('/managers/index', [UserController::class, 'getManagers']);
                Route::get('/index', [UserController::class, 'getItems']);
                Route::get('/all', [UserController::class, 'getAll']);
                Route::get('/{id}/show', [UserController::class, 'getItem']);
                Route::get('/roles-permissions', [UserController::class, 'getRolesPermissions']);
                Route::post('/store', [UserController::class, 'store']);
                Route::patch('/{id}/update', [UserController::class, 'update']);
                Route::delete('/{id}/destroy', [UserController::class, 'destroy']);
            });

            Route::group(['prefix' => "governorates"], function () {
                Route::get('/', [GovernorateController::class, 'index'])->name('admin.governorates.index');
                Route::get('/create', [GovernorateController::class, 'index']);
                Route::get('/{id}/edit', [GovernorateController::class, 'index']);

                Route::get('/index', [GovernorateController::class, 'getItems']);
                Route::get('/{id}/show', [GovernorateController::class, 'getItem']);
                Route::post('/store', [GovernorateController::class, 'store']);
                Route::patch('/{id}/update', [GovernorateController::class, 'update']);
                Route::delete('/{id}/destroy', [GovernorateController::class, 'destroy']);
            });

            Route::group(['prefix' => "cities"], function () {
                Route::get('/', [CityController::class, 'index'])->name('admin.cities.index');
                Route::get('/create', [CityController::class, 'index']);
                Route::get('/{id}/edit', [CityController::class, 'index']);

                Route::get('/index', [CityController::class, 'getItems']);
                Route::get('/{id}/show', [CityController::class, 'getItem']);
                Route::get('/governorates', [CityController::class, 'getGovernorates']);
                Route::post('/store', [CityController::class, 'store']);
                Route::patch('/{id}/update', [CityController::class, 'update']);
                Route::delete('/{id}/destroy', [CityController::class, 'destroy']);
            });

            Route::group(['prefix' => "areas"], function () {
                Route::get('/', [AreaController::class, 'index'])->name('admin.areas.index');
                Route::get('/create', [AreaController::class, 'index']);
                Route::get('/{id}/edit', [AreaController::class, 'index']);

                Route::get('/index', [AreaController::class, 'getItems']);
                Route::get('/{id}/show', [AreaController::class, 'getItem']);
                Route::get('/cities', [AreaController::class, 'getCities']);
                Route::post('/store', [AreaController::class, 'store']);
                Route::patch('/{id}/update', [AreaController::class, 'update']);
                Route::delete('/{id}/destroy', [AreaController::class, 'destroy']);
            });

            Route::group(['prefix' => "employee-payments"], function () {
                Route::get('/', [EmployeePaymentController::class, 'index'])->name('employee.payments.index');
                Route::get('/{userId}/show', [EmployeePaymentController::class, 'index']);
                Route::get('/{userId}/get-item', [EmployeePaymentController::class, 'getItem']);
                Route::post('/store', [EmployeePaymentController::class, 'store']);
            });

            Route::group(['prefix' => "programs"], function () {
                Route::get('/', [ProgramController::class, 'index'])->name('admin.programs.index');
                Route::get('/create', [ProgramController::class, 'index']);
                Route::get('/{id}/edit', [ProgramController::class, 'index']);

                Route::get('/index', [ProgramController::class, 'getItems']);
                Route::get('/{id}/show', [ProgramController::class, 'getItem']);
                Route::post('/store', [ProgramController::class, 'store']);
                Route::patch('/{id}/update', [ProgramController::class, 'update']);
                Route::delete('/{id}/destroy', [ProgramController::class, 'destroy']);
            });

            Route::group(['prefix' => "humanitarian-cases"], function () {
                Route::get('/assistance-request', [AdminController::class, 'index'])
                    ->name('admin.humanitarian.cases.assistance-request.index');

                Route::group(['prefix' => 'orphans'], function () {
                    Route::get('/', [OrphansController::class, 'index'])->name('admin.humanitarian.cases.orphans.index');
                    Route::get('/{id}/show', [OrphansController::class, 'index'])->name('admin.humanitarian.cases.orphans.show');
                    Route::get('/create', [OrphansController::class, 'index']);
                    Route::get('/{id}/edit', [OrphansController::class, 'index']);

                    Route::get('/index', [OrphansController::class, 'getItems']);
                    Route::patch('/{id}/sent-attendance', [OrphansController::class, 'sentAttendance']);
                    Route::patch('/{id}/attended', [OrphansController::class, 'attended']);
                    Route::patch('/{id}/benefited', [OrphansController::class, 'benefited']);
                    Route::patch('/{id}/researcher-opinion', [OrphansController::class, 'updateResearcherOpinion']);
                    Route::get('/programs', [OrphansController::class, 'getPrograms']);
                    Route::get('/{id}/get-item', [OrphansController::class, 'getItem']);
                    Route::post('/store', [OrphansController::class, 'store']);
                    Route::patch('/{id}/update', [OrphansController::class, 'update']);
                    Route::delete('/{id}/destroy', [OrphansController::class, 'destroy']);
                });

                Route::group(['prefix' => 'amputation'], function () {
                    Route::get('/', [AmputationController::class, 'index'])->name('admin.humanitarian.cases.amputation.index');
                    Route::get('/create', [AmputationController::class, 'index']);
                    Route::get('/{id}/show', [AmputationController::class, 'index'])->name('admin.humanitarian.cases.amputation.show');
                    Route::get('/{id}/edit', [AmputationController::class, 'index']);

                    Route::get('/index', [AmputationController::class, 'getItems']);
                    Route::patch('/{id}/sent-attendance', [AmputationController::class, 'sentAttendance']);
                    Route::patch('/{id}/attended', [AmputationController::class, 'attended']);
                    Route::patch('/{id}/benefited', [AmputationController::class, 'benefited']);
                    Route::patch('/{id}/researcher-opinion', [AmputationController::class, 'updateResearcherOpinion']);
                    Route::get('/{id}/get-item', [AmputationController::class, 'getItem']);
                    Route::post('/store', [AmputationController::class, 'store']);
                    Route::patch('/{id}/update', [AmputationController::class, 'update']);
                    Route::delete('/{id}/destroy', [AmputationController::class, 'destroy']);
                });

                Route::group(['prefix' => 'solidarity-housing'], function () {
                    Route::get('/', [SolidarityHousingController::class, 'index'])->name('admin.humanitarian.cases.solidarity-housing.index');
                    Route::get('/create', [SolidarityHousingController::class, 'index']);
                    Route::get('/{id}/show', [SolidarityHousingController::class, 'index'])->name('admin.humanitarian.cases.solidarity-housing.show');
                    Route::get('/{id}/edit', [SolidarityHousingController::class, 'index']);

                    Route::get('/index', [SolidarityHousingController::class, 'getItems']);
                    Route::patch('/{id}/sent-attendance', [SolidarityHousingController::class, 'sentAttendance']);
                    Route::patch('/{id}/attended', [SolidarityHousingController::class, 'attended']);
                    Route::patch('/{id}/benefited', [SolidarityHousingController::class, 'benefited']);
                    Route::patch('/{id}/researcher-opinion', [SolidarityHousingController::class, 'updateResearcherOpinion']);
                    Route::get('/{id}/get-item', [SolidarityHousingController::class, 'getItem']);
                    Route::post('/store', [SolidarityHousingController::class, 'store']);
                    Route::patch('/{id}/update', [SolidarityHousingController::class, 'update']);
                    Route::delete('/{id}/destroy', [SolidarityHousingController::class, 'destroy']);
                });

                Route::group(['prefix' => 'family-breadwinners'], function () {
                    Route::get('/', [FamilyBreadwinnerController::class, 'index'])->name('admin.humanitarian.cases.family-breadwinner.index');
                    Route::get('/create', [FamilyBreadwinnerController::class, 'index']);
                    Route::get('/{id}/show', [FamilyBreadwinnerController::class, 'index'])->name('admin.humanitarian.cases.family-breadwinner.show');
                    Route::get('/{id}/edit', [FamilyBreadwinnerController::class, 'index']);

                    Route::get('/index', [FamilyBreadwinnerController::class, 'getItems']);
                    Route::patch('/{id}/sent-attendance', [FamilyBreadwinnerController::class, 'sentAttendance']);
                    Route::patch('/{id}/attended', [FamilyBreadwinnerController::class, 'attended']);
                    Route::patch('/{id}/benefited', [FamilyBreadwinnerController::class, 'benefited']);
                    Route::patch('/{id}/researcher-opinion', [FamilyBreadwinnerController::class, 'updateResearcherOpinion']);
                    Route::get('/{id}/get-item', [FamilyBreadwinnerController::class, 'getItem']);
                    Route::post('/store', [FamilyBreadwinnerController::class, 'store']);
                    Route::patch('/{id}/update', [FamilyBreadwinnerController::class, 'update']);
                    Route::delete('/{id}/destroy', [FamilyBreadwinnerController::class, 'destroy']);
                });

                Route::group(['prefix' => 'stranded-families'], function () {
                    Route::get('/', [StrandedFamilyController::class, 'index'])->name('admin.humanitarian.cases.stranded-family.index');
                    Route::get('/create', [StrandedFamilyController::class, 'index']);
                    Route::get('/{id}/show', [StrandedFamilyController::class, 'index'])->name('admin.humanitarian.cases.stranded-family.show');
                    Route::get('/{id}/edit', [StrandedFamilyController::class, 'index']);

                    Route::get('/index', [StrandedFamilyController::class, 'getItems']);
                    Route::patch('/{id}/sent-attendance', [StrandedFamilyController::class, 'sentAttendance']);
                    Route::patch('/{id}/attended', [StrandedFamilyController::class, 'attended']);
                    Route::patch('/{id}/benefited', [StrandedFamilyController::class, 'benefited']);
                    Route::patch('/{id}/researcher-opinion', [StrandedFamilyController::class, 'updateResearcherOpinion']);
                    Route::get('/{id}/get-item', [StrandedFamilyController::class, 'getItem']);
                    Route::post('/store', [StrandedFamilyController::class, 'store']);
                    Route::patch('/{id}/update', [StrandedFamilyController::class, 'update']);
                    Route::delete('/{id}/destroy', [StrandedFamilyController::class, 'destroy']);
                });

                Route::group(['prefix' => 'injured-cases'], function () {
                    Route::get('/', [InjuredController::class, 'index'])->name('admin.humanitarian.cases.injured.index');
                    Route::get('/create', [InjuredController::class, 'index']);
                    Route::get('/{id}/show', [InjuredController::class, 'index'])->name('admin.humanitarian.cases.injured.show');
                    Route::get('/{id}/edit', [InjuredController::class, 'index']);

                    Route::get('/index', [InjuredController::class, 'getItems']);
                    Route::patch('/{id}/sent-attendance', [InjuredController::class, 'sentAttendance']);
                    Route::patch('/{id}/attended', [InjuredController::class, 'attended']);
                    Route::patch('/{id}/benefited', [InjuredController::class, 'benefited']);
                    Route::patch('/{id}/researcher-opinion', [InjuredController::class, 'updateResearcherOpinion']);
                    Route::get('/{id}/get-item', [InjuredController::class, 'getItem']);
                    Route::post('/store', [InjuredController::class, 'store']);
                    Route::patch('/{id}/update', [InjuredController::class, 'update']);
                    Route::delete('/{id}/destroy', [InjuredController::class, 'destroy']);
                });

                Route::group(['prefix' => 'patient-cases'], function () {
                    Route::get('/', [PatientController::class, 'index'])->name('admin.humanitarian.cases.patients.index');
                    Route::get('/create', [PatientController::class, 'index']);
                    Route::get('/{id}/show', [PatientController::class, 'index'])->name('admin.humanitarian.cases.patients.show');
                    Route::get('/{id}/edit', [PatientController::class, 'index']);

                    Route::get('/index', [PatientController::class, 'getItems']);
                    Route::patch('/{id}/sent-attendance', [PatientController::class, 'sentAttendance']);
                    Route::patch('/{id}/attended', [PatientController::class, 'attended']);
                    Route::patch('/{id}/benefited', [PatientController::class, 'benefited']);
                    Route::patch('/{id}/researcher-opinion', [PatientController::class, 'updateResearcherOpinion']);
                    Route::get('/{id}/get-item', [PatientController::class, 'getItem']);
                    Route::post('/store', [PatientController::class, 'store']);
                    Route::patch('/{id}/update', [PatientController::class, 'update']);
                    Route::delete('/{id}/destroy', [PatientController::class, 'destroy']);
                });

                
            });
        });

        Route::middleware('auth')->group(function () {
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });
    }
);
