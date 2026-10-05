<?php

namespace App\Http\Controllers;

use App\Models\EmbassyEventAttendee;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmbassyEventAttendeesImport;

class EmbassyEventController extends Controller
{
    public function index(Request $request)
{

    $query = EmbassyEventAttendee::query();

    // 🔎 البحث بالكود
    if ($request->filled('code')) {
        $query->where('code', $request->code);
    }

    // 🔎 البحث بالاسم
    if ($request->filled('full_name')) {
        $query->where('full_name', 'like', '%' . $request->full_name . '%');
    }

    // 🔎 البحث بالهوية
    if ($request->filled('national_id')) {
        $query->where('national_id', 'like', '%' . $request->national_id . '%');
    }

    $attendees = $query
        ->orderByRaw("
            CASE 
                WHEN code IS NULL OR code = '' THEN 1
                ELSE 0
            END
        ")
        ->orderByRaw("CAST(code AS UNSIGNED) ASC")
        ->get();

    // 📊 إحصائيات
    $stats = [
        'total' => $attendees->count(),
        'attended' => $attendees->where('attended',1)->count(),
        'absent' => $attendees->where('attended',0)->count(),
    ];

    return view('embassy.index', compact(
        'attendees',
        'stats'
    ));
}

    public function store(Request $request)
    {
        $request->validate([
            'type'=>'required',
            'full_name'=>'required'
        ]);

        EmbassyEventAttendee::create($request->all());
        return back()->with('success','تمت الإضافة بنجاح');
    }

    public function updateAttendance($id)
    {
        $attendee = EmbassyEventAttendee::findOrFail($id);
        $attendee->attended = true;
        $attendee->save();
        return back();
    }

    public function destroy($id)
    {
        $attendee = EmbassyEventAttendee::findOrFail($id);
        $attendee->delete();
        return back()->with('success','تم الحذف');
    }


public function import(Request $request)
{
    Excel::import(new EmbassyEventAttendeesImport, $request->file('file'));

    return back()->with('success','تم رفع البيانات');
}

public function update(Request $request)
{

    $person = EmbassyEventAttendee::findOrFail($request->id);

    $person->update([
        'type'=>$request->type,
        'code'=>$request->code,
        'full_name'=>$request->full_name,
        'national_id'=>$request->national_id,
        'notes'=>$request->notes,
    ]);

    return redirect()->route('embassy.index')->with('success','تم التعديل بنجاح');

}
public function report()
{

    $attended = EmbassyEventAttendee::where('attended',1)->get();

    $notAttended = EmbassyEventAttendee::where('attended',0)->get();

    $total = EmbassyEventAttendee::count();

    return view('embassy.report',compact(
        'attended',
        'notAttended',
        'total'
    ));
}
}