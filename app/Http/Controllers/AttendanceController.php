<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\MessageLog;
use App\Models\TaskAssignment;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Auth;


class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin|adminorphans|adminamputation|admin|adminattendance']);
    }

    
    // جبررررررررررررررررررررررررررررررر 
    // عرض صفحة الحضور
    public function attendance($event_id)
{
    $event = Event::findOrFail($event_id);

    // Query أساسي موحد
    $baseQuery = MessageLog::where('event_id', $event_id)
        ->where('message_type', 'invitation')
        ->whereIn('delivery_status', ['sent', 'manual']);

    // السجلات المعروضة (غير الحاضرين فقط)
    $logs = (clone $baseQuery)
        ->with('user')
        ->whereNull('attended_at')
        ->get();

    // عدد الحضور
    $attendedCount = (clone $baseQuery)
        ->whereNotNull('attended_at')
        ->count();

    return view('dashboard.events.attendance', compact(
        'event',
        'logs',
        'attendedCount'
    ));
}

    // تسجيل الحضور

    public function markAttendance($id)
    {
        $log = MessageLog::findOrFail($id);

        if ($log->attended_at) {
            return back()->with('error', 'تم تسجيل الحضور مسبقاً');
        }

        $log->update([
            'attended_at' => now(),
            'attended_by' => Auth::id(),
            'excused_at' => null,
            'excused_by' => null
        ]);

        return back()->with('success', '✅ تم تسجيل الحضور');
    }

}
