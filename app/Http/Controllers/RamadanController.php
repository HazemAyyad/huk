<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RamadanBeneficiary;
use App\Models\RamadanDistribution;
use App\Models\User;

class RamadanController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin|admin|telegram']);
    }

    public function index(Request $request)
{
    $query = RamadanBeneficiary::query();

    // 🔎 فلتر الكود (بحث دقيق أو جزئي)
    if ($request->filled('code')) {
        $query->where('code', $request->code);
    }

    // 🔎 فلتر الاسم
    if ($request->filled('full_name')) {
        $query->where('full_name', 'like', '%' . $request->full_name . '%');
    }

    // 🔎 فلتر الهوية
    if ($request->filled('national_id')) {
        $query->where('national_id', 'like', '%' . $request->national_id . '%');
    }

    // الترتيب (نفس ترتيبك الحالي)
    $beneficiaries = $query
        ->orderByRaw("
            CASE 
                WHEN code IS NULL OR code = '' THEN 1
                ELSE 0
            END
        ")
        ->orderByRaw("CAST(code AS UNSIGNED) ASC")
        ->get();

    $stats = [
        'total_count' => $beneficiaries->count(),
        'total_meals' => $beneficiaries->sum('daily_meals'),
        'total_family_members' => $beneficiaries->sum('family_members_count'),
    ];

    $today = now()->toDateString();

    $receivedTodayIds = RamadanDistribution::whereDate('distribution_date', $today)
        ->pluck('beneficiary_id')
        ->toArray();

    return view('ramadan.index', compact(
        'beneficiaries',
        'receivedTodayIds',
        'stats'
    ));
}


public function receive($id)
{
    $beneficiary = RamadanBeneficiary::findOrFail($id);
    $today = now()->toDateString();

    $alreadyReceived = RamadanDistribution::where('beneficiary_id', $id)
        ->whereDate('distribution_date', $today)
        ->exists();

    if ($alreadyReceived) {
        return back()->with('error', 'استلم اليوم مسبقاً');
    }

    RamadanDistribution::create([
        'beneficiary_id' => $beneficiary->id,
        'distribution_date' => $today,
        'meals_taken' => $beneficiary->daily_meals,
        'created_by' => auth()->id(),
    ]);

    return back()->with('success', 'تم تسجيل الاستفادة');
}

public function report(Request $request)
{
    $date = $request->date ?? today();

    $distributed = RamadanDistribution::with(['beneficiary','creator'])
    ->whereDate('distribution_date', $date)
    ->get();

    $totalMeals = $distributed->sum('meals_taken');

    $notReceived = RamadanBeneficiary::whereDoesntHave('distributions', function($q) use ($date){
        $q->whereDate('distribution_date', $date);
    })->get();

    return view('ramadan.report', compact(
        'distributed',
        'totalMeals',
        'notReceived',
        'date'
    ));
}

public function editBeneficiary($id)
{
    $beneficiary = RamadanBeneficiary::findOrFail($id);
    return view('ramadan.edit', compact('beneficiary'));
}
public function updateBeneficiary(Request $request, $id)
{
    $beneficiary = RamadanBeneficiary::findOrFail($id);

    $beneficiary->update([
        'full_name' => $request->full_name,
        'national_id' => $request->national_id,
        'code' => $request->code,
        'family_members_count' => $request->family_members_count,
        'daily_meals' => $request->daily_meals,
        'updated_by' => auth()->id(),
    ]);

    return redirect()->route('ramadan.index')->with('success', 'تم التعديل بنجاح');
}

public function deleteBeneficiary($id)
{
    $beneficiary = RamadanBeneficiary::findOrFail($id);
    $beneficiary->update([
        'deleted_by' => auth()->id()
    ]);

    $beneficiary->delete();

    return back()->with('success', 'تم الحذف بنجاح');
}

public function deleteDistribution($id)
{
    $today = now()->toDateString();

    RamadanDistribution::where('beneficiary_id', $id)
        ->whereDate('distribution_date', $today)
        ->delete();

    return back()->with('success', 'تم إلغاء استفادة اليوم بنجاح');
}

// عرض صفحة الإضافة
public function create()
{
    return view('ramadan.create');
}

// تخزين مستفيد جديد
public function store(Request $request)
{
    $request->validate([
        'full_name'            => 'nullable|string|max:255',
        'national_id'          => 'nullable|string|max:20|unique:ramadan_beneficiaries,national_id',
        'code'                 => 'nullable|string|max:50|unique:ramadan_beneficiaries,code',
        'family_members_count' => 'nullable|integer|min:1',
        'daily_meals'          => 'nullable|integer|min:1',
    ]);

    RamadanBeneficiary::create([
        'full_name'            => $request->full_name,
        'national_id'          => $request->national_id,
        'code'                 => $request->code,
        'family_members_count' => $request->family_members_count,
        'daily_meals'          => $request->daily_meals,
        'created_by'           => auth()->id(),
    ]);

    return redirect()->route('ramadan.index')->with('success', 'تمت إضافة المستفيد بنجاح');
}


}

