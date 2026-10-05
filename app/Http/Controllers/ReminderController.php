<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reminder;


class ReminderController extends Controller
{

    public function index()
    {
        $reminders = Reminder::orderBy('day_of_month')->get();

        return view('admin.reminders.index', compact('reminders'));
    }

    public function store(Request $request)
    {
        Reminder::create([
            'title'=>$request->title,
            'notes'=>$request->notes,
            'day_of_month'=>$request->day_of_month,
        ]);

        return back()->with('success','تمت الإضافة');
    }

    public function complete(Reminder $reminder)
    {
        $reminder->update([
            'completed_for_month'=>today()
        ]);

        return back();
    }

    public function update(Request $request, $id)
    {
        // التحقق من صحة البيانات
        $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'day_of_month' => 'required|integer|min:1|max:31',
        ]);

        // البحث عن التذكير
        $reminder = Reminder::findOrFail($id);

        // تحديث البيانات
        $reminder->update([
            'title' => $request->title,
            'notes' => $request->notes,
            'day_of_month' => $request->day_of_month,
        ]);

        // إعادة التوجيه مع رسالة نجاح
        return redirect()->route('reminders.index')
                         ->with('success', 'تم تحديث التذكير بنجاح');
    }

    public function destroy($id)
    {
        $reminder = Reminder::findOrFail($id);
        $reminder->delete();

        return redirect()->route('reminders.index')
                         ->with('success', 'تم حذف التذكير بنجاح');
    }
}
