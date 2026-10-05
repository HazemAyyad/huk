<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaidSalary;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeePaymentController extends Controller
{

    public function index()
    {
        return view('admin.index');
    }

    public function getItems()
    {
        return response()->json(User::all(), 200);
    }

    public function getItem($userId)
    {
        $employee = User::findOrFail($userId);
        $payments = $employee->payments;
        $paidSalaries = $employee->paidSalaries()->orderBy('month')->get();

        return response()->json([
            'employee' => $employee,
            'payments' => $payments,
            'paidSalaries' => $paidSalaries,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();
            $payment = Payment::create($request->all());
            Payment::processMonthlyPayments($payment->user_id, $payment->payment_date);
            DB::commit();
            return response()->json(['message' => "Done"], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ], 500);
        }
    }

    public static function processMonthlyPayments($userId, $month)
    {
        $user = User::findOrFail($userId);
        $basicSalary = $user->basic_salary;
        if ($basicSalary <= 0) return; // تجنب قسمة على صفر

        // جيب إجمالي الدفعات في الشهر ده
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();
        $totalAmount = Payment::where('user_id', $userId)
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // حساب عدد الشهور الكاملة
        $fullMonths = floor($totalAmount / $basicSalary);
        $remaining = $totalAmount % $basicSalary; // الباقي

        // تسجيل الشهور الكاملة
        $currentMonth = Carbon::parse($month)->startOfMonth();
        for ($i = 0; $i < $fullMonths; $i++) {
            PaidSalary::updateOrCreate(
                ['user_id' => $userId, 'month' => $currentMonth],
                ['status' => 'full', 'partial_amount' => 0]
            );
            $currentMonth = $currentMonth->addMonth();
        }

        // تسجيل الباقي على الشهر التالي لو موجود
        if ($remaining > 0) {
            PaidSalary::updateOrCreate(
                ['user_id' => $userId, 'month' => $currentMonth],
                ['status' => 'partial', 'partial_amount' => $remaining]
            );
        }
    }
}
