<?php

namespace App\Http\Controllers;

use App\Models\WheelchairRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\TelegramUser;
use App\Models\MessageLog;

class WheelchairReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin|adminorphans|adminamputation|admin|medical']);
    }
    /**
     * عرض صفحة التحقق من رقم الهوية
     */
    public function index(Request $request)
    {
        $query = WheelchairRequest::query();

        // الاسم
        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        // رقم الهوية
        if ($request->filled('national_id')) {
            $query->where('national_id', 'like', '%' . $request->national_id . '%');
        }

        // الهاتف
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        // الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // العمر من
        if ($request->filled('age_from')) {
            $query->where('age', '>=', $request->age_from);
        }

        // العمر إلى
        if ($request->filled('age_to')) {
            $query->where('age', '<=', $request->age_to);
        }

        // تاريخ الطلب من
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // تاريخ الطلب إلى
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('wheelchair.index', compact('requests'));
    }

public function show($id)
{
    $request = WheelchairRequest::findOrFail($id);

    return view('wheelchair.show', compact('request'));
}

public function review(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,approved_first,approved_second,approved_third,rejected_previous,rejected_not_eligible',
        'received_wheelchair' => 'required|boolean',
        'rejection_reason' => 'nullable|string|max:1000',
    ], [
        'status.required' => 'يرجى اختيار حالة الطلب.',
        'status.in' => 'حالة الطلب غير صحيحة.',
        'received_wheelchair.required' => 'يرجى تحديد هل تم تسليم كرسي.',
        'rejection_reason.max' => 'سبب الرفض يجب ألا يتجاوز 1000 حرف.',
    ]);

    $wheelchair = WheelchairRequest::findOrFail($id);

    DB::beginTransaction();

    try {

        // تحديث حالة الطلب
        $wheelchair->status = $request->status;

        // هل تم تسليم كرسي؟
        $wheelchair->received_wheelchair = $request->received_wheelchair;

        // الشخص الذي قام بالمراجعة
        $wheelchair->reviewed_by = Auth::id();

        // تاريخ المراجعة
        $wheelchair->reviewed_at = now();

        /*
        |--------------------------------------------------------------------------
        | سبب الرفض
        |--------------------------------------------------------------------------
        */

        if (in_array($request->status, [
            'rejected_previous',
            'rejected_not_eligible'
        ])) {

            $wheelchair->rejection_reason = $request->rejection_reason;

        } else {

            $wheelchair->rejection_reason = null;

        }

        /*
        |--------------------------------------------------------------------------
        | تاريخ تسليم الكرسي
        |--------------------------------------------------------------------------
        */

        if ($request->received_wheelchair == 1) {

            $wheelchair->delivered_at = now();

        } else {

            $wheelchair->delivered_at = null;

        }

        // حفظ التعديلات
        $wheelchair->save();

        DB::commit();

        return redirect()
            ->back()
            ->with('success', 'تم حفظ القرار وتحديث بيانات الطلب بنجاح.');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'حدث خطأ أثناء حفظ القرار: ' . $e->getMessage());
    }
}

public function checkPreviousWheelchair($id)
{
    $wheelchair = WheelchairRequest::findOrFail($id);

    $nationalId = trim($wheelchair->national_id);

    // البحث عن الشخص في سجلات الفعالية رقم 20
    $previousBenefit = MessageLog::where('event_id', 20)
        ->whereHas('telegramUser', function ($query) use ($nationalId) {
            $query->where('national_id', $nationalId);
        })
        ->exists();

    return response()->json([
        'success' => true,
        'benefited_before' => $previousBenefit,
        'message' => $previousBenefit
            ? 'هذا الشخص استفاد من كرسي سابقًا.'
            : 'هذا الشخص لم يستفد من كرسي سابقًا.',
    ]);
}

}