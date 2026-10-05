<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramUser;
use App\Models\Category;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\FamilyMember;

class ExcelController extends Controller
{
    /**
     * 🧠 دالة مشتركة لبناء الاستعلام (مهم جداً)
     */
    private function buildQuery(Request $request)
    {
        $query = TelegramUser::query();

        // 🔍 الفلاتر
        if ($request->filled('target_case')) {
            $query->where('target_case', $request->target_case);
        }

        if ($request->filled('location')) {
            $location = $request->location;

            $query->where(function ($q) use ($location) {
                $q->where('city', 'like', "%$location%")
                ->orWhere('address', 'like', "%$location%");
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        if ($request->filled('benefit_from')) {
            $query->where('benefit_count', '>=', $request->benefit_from);
        }

        if ($request->filled('benefit_to')) {
            $query->where('benefit_count', '<=', $request->benefit_to);
        }

        // 🔎 بحث عام
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('national_id', 'like', "%$search%");
            });
        }

        // 🚀 الترتيب الديناميكي
        $sort = $request->get('sort', 'benefit_asc');

        switch ($sort) {
            case 'benefit_desc':
                $query->orderByRaw('COALESCE(benefit_count,0) DESC');
                break;

            case 'name_asc':
                $query->orderBy('full_name', 'ASC');
                break;

            case 'name_desc':
                $query->orderBy('full_name', 'DESC');
                break;

            case 'benefit_asc':
            default:
                $query->orderByRaw('COALESCE(benefit_count,0) ASC');
                break;
        }

        return $query;
    }

    /**
     * 🔍 صفحة البحث
     */
    public function search(Request $request)
    {
        $users = $this->buildQuery($request)->paginate(20);
        $categories = Category::pluck('name', 'id');

        return view('dashboard.telegram.search', compact('users', 'categories'));
    }

    /**
     * 📥 تصدير Excel (نفس نتائج البحث تماماً)
     */
    public function export(Request $request)
    {
        $data = $this->buildQuery($request)
            ->get()
            ->map(function ($user) {
                return [
                    'الاسم' => $user->full_name,
                    'رقم الهوية' => $user->national_id,
                    'المدينة' => $user->city,
                    'العنوان' => $user->address,
                    'عدد الاستفادة' => $user->benefit_count === null ? 0 : $user->benefit_count,
                    'الفئة' => $user->target_case,
                ];
            })
            ->toArray();

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {

            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

        }, 'users.xlsx');
    }

    /**
     * 📤 رفع ملف + فحص الهويات
     */
    public function check(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls'
    ]);

    $data = Excel::toArray([], $request->file('file'));
    $rows = $data[0];

    $ids = [];

    // 🧠 استخراج أرقام الهويات من ملف Excel
    foreach ($rows as $index => $row) {

        if ($index == 0) {
            continue;
        }

        $id = isset($row[1])
            ? preg_replace('/\D/', '', (string) $row[1])
            : null;

        if ($id) {
            $ids[] = $id;
        }
    }

    // إزالة التكرار
    $ids = array_unique($ids);


    /*
    |--------------------------------------------------------------------------
    | 👤 جلب أصحاب الطلبات من TelegramUser
    |--------------------------------------------------------------------------
    */

    $users = TelegramUser::whereIn('national_id', $ids)
        ->with([
            'messages.event'
        ])
        ->get()
        ->keyBy('national_id');


    /*
    |--------------------------------------------------------------------------
    | 👨‍👩‍👧 جلب المرافقين
    |--------------------------------------------------------------------------
    */

    $familyMembers = \App\Models\FamilyMember::whereIn('national_id', $ids)
        ->get()
        ->keyBy('national_id');


    $results = [];


    /*
    |--------------------------------------------------------------------------
    | 📊 معالجة كل صف في ملف Excel
    |--------------------------------------------------------------------------
    */

    foreach ($rows as $index => $row) {

        if ($index == 0) {
            continue;
        }

        // الاسم الموجود في ملف Excel
        $name = $row[0] ?? null;

        // رقم الهوية الموجود في ملف Excel
        $national_id = isset($row[1])
            ? preg_replace('/\D/', '', (string) $row[1])
            : null;

        if (!$national_id) {

            $results[] = [
                'الاسم' => $name,
                'رقم الهوية' => 'لا يوجد رقم هوية',
                'عدد الاستفادة' => 'غير متوفر',
                'تاريخ آخر فعالية' => 'غير متوفر',
                'نوع المطابقة' => 'لا يوجد رقم هوية',
            ];

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | الحالة الأولى:
        | الهوية موجودة في TelegramUser
        |--------------------------------------------------------------------------
        */

        if (isset($users[$national_id])) {

            $user = $users[$national_id];


            // 🔥 الحصول على آخر فعالية حسب event_date
            $lastMessageWithEvent = $user->messages
                ->filter(function ($message) {

                    return $message->event
                        && !empty($message->event->event_date);

                })
                ->sortByDesc(function ($message) {

                    return $message->event->event_date;

                })
                ->first();


            $lastEventDate = $lastMessageWithEvent?->event?->event_date;


            $results[] = [

                'الاسم' => $name,

                'رقم الهوية' => $national_id,

                'عدد الاستفادة' => $user->benefit_count ?? 0,

                'تاريخ آخر فعالية' => $lastEventDate
                    ? \Carbon\Carbon::parse($lastEventDate)
                        ->format('d/m/Y')
                    : 'لا توجد استفادة',

                'نوع المطابقة' => 'صاحب الطلب',

            ];

        }


        /*
        |--------------------------------------------------------------------------
        | الحالة الثانية:
        | الهوية غير موجودة في TelegramUser
        | نبحث عنها في FamilyMember
        |--------------------------------------------------------------------------
        */

        elseif (isset($familyMembers[$national_id])) {

            $familyMember = $familyMembers[$national_id];


            /*
            |--------------------------------------------------------------------------
            | 🔗 المرافق مرتبط برب الأسرة عن طريق:
            |
            | family_members.telegram_user_id
            |            ↓
            | telegram_users.id
            |--------------------------------------------------------------------------
            */

            $user = $users->firstWhere(
                'id',
                $familyMember->telegram_user_id
            );


            /*
            |--------------------------------------------------------------------------
            | 👤 إذا لم يكن رب الأسرة موجودًا ضمن قائمة الهويات
            |
            | نبحث عنه مباشرة في قاعدة البيانات
            |--------------------------------------------------------------------------
            */

            if (!$user) {

                $user = TelegramUser::with([
                    'messages.event'
                ])->find(
                    $familyMember->telegram_user_id
                );
            }


            if ($user) {


                // 🔥 الحصول على آخر فعالية لرب الأسرة
                $lastMessageWithEvent = $user->messages
                    ->filter(function ($message) {

                        return $message->event
                            && !empty($message->event->event_date);

                    })
                    ->sortByDesc(function ($message) {

                        return $message->event->event_date;

                    })
                    ->first();


                $lastEventDate = $lastMessageWithEvent?->event?->event_date;


                $results[] = [

                    'الاسم' => $name,

                    'رقم الهوية' => $national_id,

                    // عدد استفادات رب الأسرة
                    'عدد الاستفادة' => $user->benefit_count ?? 0,

                    'تاريخ آخر فعالية' => $lastEventDate
                        ? \Carbon\Carbon::parse($lastEventDate)
                            ->format('d/m/Y')
                        : 'لا توجد استفادة',

                    'نوع المطابقة' => 'مرافق',

                ];

            } else {

                $results[] = [

                    'الاسم' => $name,

                    'رقم الهوية' => $national_id,

                    'عدد الاستفادة' => 'غير موجود',

                    'تاريخ آخر فعالية' => 'لا توجد استفادة',

                    'نوع المطابقة' => 'مرافق - رب الأسرة غير موجود',

                ];
            }

        }


        /*
        |--------------------------------------------------------------------------
        | الحالة الثالثة:
        | الهوية غير موجودة في أي من الجدولين
        |--------------------------------------------------------------------------
        */

        else {

            $results[] = [

                'الاسم' => $name,

                'رقم الهوية' => $national_id,

                'عدد الاستفادة' => 'غير موجود',

                'تاريخ آخر فعالية' => 'غير موجود',

                'نوع المطابقة' => 'غير موجود',

            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | 📥 تصدير النتائج إلى Excel
    |--------------------------------------------------------------------------
    */

    return Excel::download(

        new class($results) implements \Maatwebsite\Excel\Concerns\FromArray {

            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

        },

        'result.xlsx'
    );
}
}