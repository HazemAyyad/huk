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
        $already = \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $source = MessageLog::whereKey($id)->where('message_type', 'invitation')->firstOrFail();
            TelegramUser::whereKey($source->telegram_user_id)->lockForUpdate()->firstOrFail();
            $event = Event::whereKey($source->event_id)->lockForUpdate()->firstOrFail();
            abort_unless(app(\App\Services\Association\AssociationEntities::class)->eventActive($event), 409, 'الفعالية غير نشطة.');
            if (\Illuminate\Support\Facades\Schema::hasTable('association_invitation_states')) {
                abort_if(\Illuminate\Support\Facades\DB::table('association_invitation_states')->where('message_log_id', $id)->whereNotNull('withdrawn_at')->exists(), 409, 'سُحبت الدعوة.');
            }
            return app(\App\Services\Association\AssociationAttendanceFacts::class)->apply(Auth::id(), (int) $id, now());
        }, 3);
        return back()->with('success', $already ? 'تم تسجيل الحضور مسبقًا.' : 'تم تسجيل الحضور.');
    }
}
