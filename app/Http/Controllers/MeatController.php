<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use App\Models\FamilyMember;
use App\Models\MeatRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Exports\MeatRegistrationsExport;
use Maatwebsite\Excel\Facades\Excel;

class MeatController extends Controller
{
    // =========================
    // صفحة الفحص
    // =========================
    public function index()
    {
        return view('meat.check');
    }

    // =========================
    // فحص الهوية
    // =========================
    public function check(Request $request)
    {
        // 🔴 أهم إضافة (منع الفحص إذا مغلق)
        if (Cache::get('meat_check_status', 'open') === 'closed') {
            return back()->with('error', 'الفحص مغلق حالياً بسبب الضغط');
        }

        $request->validate([
            'national_id' => 'required'
        ]);

        $nationalId = $request->national_id;

        // ❌ منع إعادة التسجيل
        $registration = MeatRegistration::where('national_id', $nationalId)->first();

        if ($registration) {
            return back()->with('success',
                'تم تسجيلك بنجاح وسيتم ترشيحك للاستفادة من برنامج توزيع الأضاحي. ' .
                'مع العلم أن ترشيحك لا يعني تأكيد الاستلام. ' .
                'نرجو متابعة التيليجرام ايام عيد الاضحى لان رسائل الاستلام سترسل عبر التيليجرام. ' .
                'شاكرين حسن تعاونكم'
            );
        }

        // 🔍 رب الأسرة
        $user = TelegramUser::where('national_id', $nationalId)->first();

        if ($user) {

            // 🔥 لازم يكون مكمل بياناته
            if (!$user->is_completed) {
                return back()->with('error', 'يجب إكمال البيانات في التيليجرام أولاً');
            }

            session(['allowed_national_id' => $nationalId]);

            return redirect('/meat-register');
        }

        // 🔍 مرافق
        $companion = FamilyMember::where('national_id', $nationalId)->first();

        if ($companion) {

            $head = TelegramUser::find($companion->telegram_user_id);

            $headNationalId = optional($head)->national_id ?? 'غير معروف';

            return back()->with('companion_error', $headNationalId);
        }

        // ❌ غير مسجل
        return back()->with('not_found', true);
    }

    // =========================
    // صفحة التسجيل
    // =========================
    public function register()
    {
        // 🔴 حماية إضافية (حتى لو دخل مباشر)
        if (Cache::get('meat_check_status', 'open') === 'closed') {
            return redirect('/Eid-al-Adha-Mubarak')
                ->with('error', 'الفحص مغلق حالياً');
        }

        if (!session()->has('allowed_national_id')) {
            return redirect('/Eid-al-Adha-Mubarak')
                ->with('error', 'يجب فحص رقم الهوية أولاً');
        }

        $nationalId = session('allowed_national_id');

        $user = TelegramUser::where('national_id', $nationalId)->first();

        if (!$user) {
            return redirect('/Eid-al-Adha-Mubarak')
                ->with('error', 'المستخدم غير موجود');
        }

        $companions = FamilyMember::where('telegram_user_id', $user->id)->get();

        return view('meat.register', compact('user', 'companions'));
    }

    // =========================
    // حفظ التسجيل
    // =========================
    public function submit(Request $request)
    {
        // 🔴 حماية إضافية
        if (Cache::get('meat_check_status', 'open') === 'closed') {
            return redirect('/Eid-al-Adha-Mubarak')
                ->with('error', 'تم إغلاق التسجيل حالياً');
        }

        $nationalId = session('allowed_national_id');

        if (!$nationalId) {
            return redirect('/Eid-al-Adha-Mubarak')
                ->with('error', 'انتهت الجلسة، أعد الفحص');
        }

        $user = TelegramUser::where('national_id', $nationalId)->first();

        if (!$user) {
            return redirect('/Eid-al-Adha-Mubarak')
                ->with('error', 'المستخدم غير موجود');
        }

        // ❌ منع التكرار
        if (MeatRegistration::where('national_id', $nationalId)->exists()) {
            session()->forget('allowed_national_id');

            return redirect('/Eid-al-Adha-Mubarak')
                ->with('error', 'تم التسجيل مسبقًا');
        }

        // =========================
        // حفظ التسجيل
        // =========================
        MeatRegistration::create([
            'telegram_user_id' => $user->id,
            'national_id' => $nationalId,
            'want_tripe' => $request->has('want_tripe'),
            'want_legs' => $request->has('want_legs'),
            'separate_family' => $request->has('separate_family'),
            'notes' => $request->notes,
        ]);

        // =========================
        // تحديث رب الأسرة
        // =========================
        $user->update([
            'birth_date' => $request->birth_date ?? $user->birth_date,
            'entry_reason' => $request->entry_reason ?? $user->entry_reason,
            'entry_to_egypt' => $request->entry_to_egypt,
            'rent_amount' => $request->rent_amount,
            'landlord_phone' => $request->landlord_phone,
            'whatsapp' => $request->whatsapp,
            'telegram_number' => $request->telegram_number,
            'egypt_number' => $request->egypt_number,
        ]);

        // =========================
        // تحديث المرافقين
        // =========================
        if ($request->companions) {
            foreach ($request->companions as $comp) {
                FamilyMember::where('id', $comp['id'])->update([
                    'relation' => $comp['relation'] ?? null,
                    'birth_date' => $comp['birth_date'] ?? null,
                ]);
            }
        }

        // =========================
        // إضافة مرافقين جدد
        // =========================
        if ($request->new_companions) {
            foreach ($request->new_companions as $new) {

                if (empty($new['full_name']) || empty($new['national_id'])) {
                    continue;
                }

                FamilyMember::create([
                    'telegram_user_id' => $user->id,
                    'full_name' => $new['full_name'],
                    'national_id' => $new['national_id'],
                    'relation' => $new['relation'] ?? null,
                    'birth_date' => $new['birth_date'] ?? null,
                ]);
            }
        }

        session()->forget('allowed_national_id');

        return redirect('/Eid-al-Adha-Mubarak')->with('success',
            'تم تسجيلك بنجاح وسيتم ترشيحك للاستفادة من برنامج توزيع الأضاحي.'
        );
    }

    // =========================
    // صفحة الأدمن
    // =========================
    // =========================
// صفحة الأدمن مع بحث متقدم
// =========================
public function adminIndex(Request $request)
{
    if (!auth()->user()->hasRole('admin|superadmin')) {
        abort(403, 'غير مصرح');
    }
    
    $query = MeatRegistration::with('telegramUser')->latest();
    
    // 🔍 بحث سريع (الاسم، الهوية، الهاتف، العنوان)
    // البحث
    if ($request->filled('search')) {
        $search = $request->search;
        $query->whereHas('telegramUser', function($q) use ($search) {
            $q->where(function($sub) use ($search) {
                $sub->where('full_name', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%")
                    ->orWhere('egypt_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('target_case', 'like', "%{$search}%");
            });
        });
    }

    // boolean filters
    if ($request->filled('want_tripe')) {
        $query->where('want_tripe', (int)$request->want_tripe);
    }

    if ($request->filled('want_legs')) {
        $query->where('want_legs', (int)$request->want_legs);
    }

    if ($request->filled('separate_family')) {
        $query->where('separate_family', (int)$request->separate_family);
    }

    // family members
    if ($request->filled('min_members')) {
        $query->whereHas('telegramUser', function($q) use ($request) {
            $q->whereNotNull('family_members')
            ->where('family_members', '>=', $request->min_members);
        });
    }

    if ($request->filled('max_members')) {
        $query->whereHas('telegramUser', function($q) use ($request) {
            $q->whereNotNull('family_members')
            ->where('family_members', '<=', $request->max_members);
        });
    }

    // dates
    if ($request->filled('date_from')) {
        $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
    }

    if ($request->filled('date_to')) {
        $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
    }

    // rent
    if ($request->filled('max_rent')) {
        $query->whereHas('telegramUser', function($q) use ($request) {
            $q->whereNotNull('rent_amount')
            ->where('rent_amount', '<=', $request->max_rent);
        });
    }
    
    // تاريخ ميلاد المرافقين
    if ($request->filled('child_birth_from') || $request->filled('child_birth_to')) {

        $from = $request->child_birth_from ?: '1900-01-01';
        $to   = $request->child_birth_to ?: now()->format('Y-m-d');

        $query->whereHas('telegramUser.familyMembers', function ($q) use ($from, $to) {
            $q->whereNotNull('birth_date')
            ->whereBetween('birth_date', [$from, $to]);
        });
    }

    $registrations = $query->paginate(50);
    
    return view('meat.adminindex', compact('registrations'));
}

    // =========================
    // تشغيل / إيقاف الرابط
    // =========================
    public function toggleMeatCheck()
    {
        $status = Cache::get('meat_check_status', 'open');

        $newStatus = $status === 'open' ? 'closed' : 'open';

        Cache::put('meat_check_status', $newStatus);

        return back()->with('success', 'تم تغيير الحالة إلى: ' . ($newStatus === 'open' ? 'تشغيل' : 'إغلاق'));
    }

    public function export(Request $request)
{
    return Excel::download(
        new MeatRegistrationsExport($request),
        'meat_registrations.xlsx'
    );
}
}