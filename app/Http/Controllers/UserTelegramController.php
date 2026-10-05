<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserTelegramController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // عرض بيانات المستخدم
    public function show()
    {
        $user = Auth::user()->load('telegramUser.category.caseProfile');

        if (!$user->telegramUser || !$user->telegramUser->category) {
            abort(403, 'لا يوجد ملف حالة مرتبط بك');
        }

        $caseKey = $user->telegramUser->category->caseProfile->key;

        // 🔀 التوجيه حسب الحالة
        return match ($caseKey) {
            'orphans'  => redirect()->route('detail-cases.orphans.create'),
            'amputees' => redirect()->route('detail-cases.amputation.create'),
            'housing'  => redirect()->route('detail-cases.solidarity.housing.create'),
            'family'   => redirect()->route('detail-cases.family-breadwinners.create'),
            'stranded' => redirect()->route('detail-cases.stranded-families.create'),
            'injured'  => redirect()->route('detail-cases.injured-cases.create'),
            'patients' => redirect()->route('detail-cases.patient-cases.create'),
            default    => abort(404),
        };
    }

    // // تحديث البيانات
    // public function update(Request $request)
    // {
    //     $telegramUser = Auth::user()->telegramUser;

    //     if (!$telegramUser) {
    //         abort(403);
    //     }

    //     $data = $request->validate([
    //         'whatsapp' => 'nullable|string|max:255',
    //         'telegram_number' => 'nullable|string|max:255',
    //         'egypt_number' => 'nullable|string|max:255',
    //         'governorate' => 'nullable|string|max:255',
    //         'city' => 'nullable|string|max:255',
    //         'address' => 'nullable|string',
    //     ]);

    //     $telegramUser->update($data);

    //     return redirect()->back()->with('success', '✅ تم تحديث بياناتك بنجاح');
    // }
}
