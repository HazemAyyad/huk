<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramUser;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use App\Models\MessageLog;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\FamilyMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\CaseProfile; 
use App\Models\TelegramUserNote;

use Maatwebsite\Excel\Facades\Excel;

class CategoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin|adminorphans|adminamputation|admin|telegram']);
    }

    // ######################### الفئات####################### 
    // تعديل دالة العرض
    public function listCategories()
    {
        if (!auth()->user()->hasRole('superadmin|admin')) {
            return redirect()->route('index');
        }
        
        $categories = Category::orderBy('id', 'desc')->get();
        // جلب جميع التصنيفات الأساسية لعرضها في القائمة المنسدلة
        $caseProfiles = CaseProfile::where('is_active', 1)->get(); 
        
        return view('dashboard.categories.categories', compact('categories', 'caseProfiles'));
    }

    public function storecategories(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'prefix' => 'required|string|unique:categories,prefix',
            'case_profile_id' => 'required|exists:case_profiles,id', // التأكد من وجود التصنيف
        ]);

        Category::create([
            'name' => $request->name,
            'prefix' => strtoupper($request->prefix),
            'case_profile_id' => $request->case_profile_id, // ربط الفئة بالتصنيف
        ]);

        return redirect()->back()->with('success', 'تم إنشاء الفئة بنجاح');
    }



    public function categoryUsers(Request $request, Category $category)
{
    if (!auth()->user()->hasRole('superadmin|admin')) {
        return redirect()->route('index');
    }

    // =========================
    // إعدادات الفلترة والترتيب
    // =========================

    $sortField = $request->get('sort_field', 'benefit_count');

    // عند فتح الصفحة أول مرة يكون الترتيب تصاعدي
    $sortDirection = $request->get('sort_direction', 'asc');

    $status = $request->get('status', 'all');

    $search = trim($request->get('search', ''));


    // =========================
    // الحقول المسموح بالترتيب عليها
    // =========================

    $allowedSortFields = [
        'access_code',
        'full_name',
        'national_id',
        'family_members',
        'benefit_count',
        'cash_benefit_count',
        'last_received_message_at',
    ];

    if (!in_array($sortField, $allowedSortFields)) {
        $sortField = 'benefit_count';
    }

    if (!in_array($sortDirection, ['asc', 'desc'])) {
        $sortDirection = 'asc';
    }


    // =========================
    // الاستعلام الأساسي
    // =========================

    $query = TelegramUser::query()
        ->select('telegram_users.*')


        // =====================================================
        // عدد الاستفادات النقدية في سنة 2026 فقط
        // =====================================================

        ->selectSub(function ($query) {

            $query->from('message_logs')

                ->join(
                    'events',
                    'events.id',
                    '=',
                    'message_logs.event_id'
                )

                ->whereColumn(
                    'message_logs.telegram_user_id',
                    'telegram_users.id'
                )

                // نوع المساعدة نقدية
                ->where(
                    'events.aid_type',
                    'cash'
                )

                // سنة 2026 فقط
                ->whereYear(
                    'events.event_date',
                    2026
                )

                // كل Event يحسب استفادة واحدة فقط
                ->selectRaw(
                    'COUNT(DISTINCT message_logs.event_id)'
                );

        }, 'cash_benefit_count')


        // =====================================================
        // آخر فعالية لها رسالة Received في سنة 2026
        // حسب تاريخ الفعالية وليس تاريخ إرسال الرسالة
        // =====================================================

        ->selectSub(function ($query) {

            $query->from('message_logs')

                ->join(
                    'events',
                    'events.id',
                    '=',
                    'message_logs.event_id'
                )

                ->whereColumn(
                    'message_logs.telegram_user_id',
                    'telegram_users.id'
                )

                // رسائل التسليم / الاستلام فقط
                ->where(
                    'message_logs.message_type',
                    'received'
                )

                // الفعالية يجب أن تكون في سنة 2026
                ->whereBetween(
                    'events.event_date',
                    [
                        '2026-01-01',
                        '2026-12-31'
                    ]
                )

                // نأخذ آخر تاريخ فعالية
                ->selectRaw(
                    'MAX(events.event_date)'
                );

        }, 'last_received_message_at')


        // =====================================================
        // اسم آخر فعالية Received في سنة 2026
        // =====================================================

        ->selectSub(function ($query) {

            $query->from('message_logs')

                ->join(
                    'events',
                    'events.id',
                    '=',
                    'message_logs.event_id'
                )

                ->whereColumn(
                    'message_logs.telegram_user_id',
                    'telegram_users.id'
                )

                // Received فقط
                ->where(
                    'message_logs.message_type',
                    'received'
                )

                // فعالية سنة 2026 فقط
                ->whereBetween(
                    'events.event_date',
                    [
                        '2026-01-01',
                        '2026-12-31'
                    ]
                )

                // الأحدث حسب تاريخ الفعالية
                ->orderByDesc(
                    'events.event_date'
                )

                // في حالة وجود أكثر من رسالة لنفس التاريخ
                ->orderByDesc(
                    'message_logs.id'
                )

                ->limit(1)

                ->select(
                    'events.name'
                );

        }, 'last_received_event_name')


        // =====================================================
        // تاريخ آخر فعالية Received في سنة 2026
        // =====================================================

        ->selectSub(function ($query) {

            $query->from('message_logs')

                ->join(
                    'events',
                    'events.id',
                    '=',
                    'message_logs.event_id'
                )

                ->whereColumn(
                    'message_logs.telegram_user_id',
                    'telegram_users.id'
                )

                // Received فقط
                ->where(
                    'message_logs.message_type',
                    'received'
                )

                // فعالية سنة 2026 فقط
                ->whereBetween(
                    'events.event_date',
                    [
                        '2026-01-01',
                        '2026-12-31'
                    ]
                )

                // الأحدث حسب تاريخ الفعالية
                ->orderByDesc(
                    'events.event_date'
                )

                ->orderByDesc(
                    'message_logs.id'
                )

                ->limit(1)

                ->select(
                    'events.event_date'
                );

        }, 'last_received_event_date')


        // =====================================================
        // المستخدمين التابعين للفئة الحالية
        // =====================================================

        ->whereHas('categories', function ($q) use ($category) {

            $q->where(
                'categories.id',
                $category->id
            );

        });


    // =========================================================
    // البحث
    // =========================================================

    if ($search !== '') {

        $query->where(function ($q) use ($search) {

            $q->where(
                'telegram_users.access_code',
                'LIKE',
                "%{$search}%"
            )

            ->orWhere(
                'telegram_users.full_name',
                'LIKE',
                "%{$search}%"
            )

            ->orWhere(
                'telegram_users.national_id',
                'LIKE',
                "%{$search}%"
            )

            ->orWhere(
                'telegram_users.egypt_number',
                'LIKE',
                "%{$search}%"
            )

            ->orWhere(
                'telegram_users.address',
                'LIKE',
                "%{$search}%"
            );

        });
    }


    // =========================================================
    // فلتر الحالة
    // =========================================================

    if ($status === 'blocked') {

        $query->where(
            'telegram_users.is_blocked',
            true
        );

    } elseif ($status === 'completed') {

        $query
            ->where(
                'telegram_users.is_blocked',
                false
            )
            ->where(
                'telegram_users.is_completed',
                true
            );

    } elseif ($status === 'active') {

        $query
            ->where(
                'telegram_users.is_blocked',
                false
            )
            ->where(
                'telegram_users.is_completed',
                false
            );
    }


    // =========================================================
    // الترتيب
    // =========================================================

    if ($sortField === 'cash_benefit_count') {

        // الاستفادة النقدية في 2026
        $query->orderBy(
            'cash_benefit_count',
            $sortDirection
        );


    } elseif ($sortField === 'last_received_message_at') {

        // آخر فعالية Received في 2026
        // الترتيب يعتمد على event_date
        // وليس created_at أو sent_at

        $query->orderBy(
            'last_received_message_at',
            $sortDirection
        );


    } elseif ($sortField === 'access_code') {

        $query->orderByRaw("
            CASE

                WHEN access_code REGEXP '^[A-Za-z]+-[0-9]+$'
                    THEN SUBSTRING_INDEX(access_code, '-', 1)

                WHEN access_code REGEXP '^[A-Za-z]+[0-9]+$'
                    THEN REGEXP_REPLACE(
                        access_code,
                        '[0-9]+$',
                        ''
                    )

                ELSE access_code

            END {$sortDirection}
        ");


        $query->orderByRaw("
            CASE

                WHEN access_code REGEXP '^[A-Za-z]+-[0-9]+$'

                    THEN CAST(
                        SUBSTRING_INDEX(
                            access_code,
                            '-',
                            -1
                        ) AS UNSIGNED
                    )

                WHEN access_code REGEXP '^[A-Za-z]+[0-9]+$'

                    THEN CAST(
                        REGEXP_SUBSTR(
                            access_code,
                            '[0-9]+$'
                        ) AS UNSIGNED
                    )

                ELSE 0

            END {$sortDirection}
        ");


    } elseif ($sortField === 'family_members') {

        $query->orderByRaw(
            "COALESCE(family_members, 0) {$sortDirection}"
        );


    } elseif ($sortField === 'benefit_count') {

        $query->orderByRaw(
            "COALESCE(benefit_count, 0) {$sortDirection}"
        );


    } else {

        $query->orderBy(
            $sortField,
            $sortDirection
        );
    }


    // =========================================================
    // يمنع تغير ترتيب الحالات التي لها نفس القيمة
    // =========================================================

    $query->orderBy(
        'telegram_users.id',
        'asc'
    );


    // =========================================================
    // Pagination
    // =========================================================

    $users = $query
        ->paginate(25)
        ->withQueryString();


    // =========================================================
    // جلب الفعاليات
    // =========================================================

    $events = Event::orderBy(
        'event_date',
        'desc'
    )->get();


    // =========================================================
    // إرسال البيانات للواجهة
    // =========================================================

    return view(
        'dashboard.categories.category_users',
        compact(
            'category',
            'users',
            'events'
        )
    );
}




    public function indexfilter(Request $request)
    {
        if (!auth()->user()->hasRole('superadmin|admin')) {
            return redirect()->route('index');
        }
        
        // جلب جميع التصنيفات والفئات للفلاتر
        $caseProfiles = CaseProfile::with('categories')->get();
        $categories = Category::with('caseProfile')->get();
        
        // بدء الاستعلام - استخدام TelegramUser بدلاً من User
        $query = TelegramUser::with(['category.caseProfile'])
            ->whereNotNull('category_id')
            ->whereNotNull('access_code')
            ->where('access_code', '!=', '');

        
        // تطبيق فلتر التصنيف
        if ($request->has('category') && $request->category != '') {
            $categoryId = $request->category;
            $query->whereHas('category.caseProfile', function ($q) use ($categoryId) {
                $q->where('id', $categoryId);
            });
            
            $selectedCategory = CaseProfile::find($categoryId);
        } else {
            $selectedCategory = null;
        }
        
        // تطبيق فلتر الفئة
        if ($request->has('subcategory') && $request->subcategory != '') {
            $subcategoryId = $request->subcategory;
            $query->whereHas('categories', function ($q) use ($subcategoryId) {
                $q->where('categories.id', $subcategoryId);
            });
            
            $selectedSubcategory = Category::find($subcategoryId);
        } else {
            $selectedSubcategory = null;
        }
        
        // جلب النتائج مع الترحيل
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // إحصائيات
        $totalUsers = TelegramUser::count(); // تغيير هنا
        $filteredUsers = $users->total();
        
        // الحصول على الفئات الفريدة في النتائج
        $uniqueCategories = $users->pluck('category')->unique()->filter();
        
        return view('dashboard.categories.index_filter', [
            'users' => $users,
            'caseProfiles' => $caseProfiles,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'selectedSubcategory' => $selectedSubcategory,
            'totalUsers' => $totalUsers,
            'filteredUsers' => $filteredUsers,
            'uniqueCategories' => $uniqueCategories,
        ]);
    }

}
