<?php

namespace App\Http\Controllers;

use App\Models\MessageLog;
use App\Models\TelegramUser;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageLogTelegramController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    public function index(Request $request) {
        $query = MessageLog::with(['user', 'event'])
            ->orderByDesc('sent_at');

        
        // البحث
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                ->orWhereHas('user', function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            });
        }
        
        // الفلترة
        if ($request->has('status')) {
            $query->where('delivery_status', $request->status);
        }
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('sent_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }
        
        // إحصائيات
        $totalMessages = MessageLog::count();
        $sentCount = MessageLog::where('delivery_status', 'sent')->count();
        $pendingCount = MessageLog::where('delivery_status', 'pending')->count();
        $failedCount = MessageLog::where('delivery_status', 'failed')->count();
        
        $messages = $query->paginate(20);
        
        return view('dashboard.telegram.messages.index', compact(
            'messages', 
            'totalMessages',
            'sentCount',
            'pendingCount', 
            'failedCount'
        ));
    }

    // صفحة التعديل
    public function edit($id) {
        $message = MessageLog::with(['user', 'event'])->findOrFail($id);
        
        // جلب المستخدمين مع الحقول الأساسية فقط
        $users = TelegramUser::select('id', 'full_name', 'username', 'telegram_number', 'benefit_count')
                            ->orderBy('full_name')
                            ->get();
        
        // جلب الفعاليات مع الحقول الأساسية فقط
        $events = Event::select('id', 'name', 'event_date')
                      ->orderBy('event_date', 'desc')
                      ->get();
        
        return view('dashboard.telegram.messages.edit', compact('message', 'users', 'events'));
    }

    // حفظ التعديلات
    public function update(Request $request, $id) {
        $validated = $request->validate([
            'telegram_user_id' => 'required|exists:telegram_users,id',
            'event_id'         => 'nullable|exists:events,id',
            'message'          => 'required|string|max:4096',
            'delivery_status'  => 'required',
            'message_type'     => 'required|in:invitation,attended,received,general',
            'benefit_count'    => 'required|integer|min:0',
            'message_date'     => 'required|date', // التأكد من وصول التاريخ
        ]);
        
        $messageLog = MessageLog::findOrFail($id);
        
        DB::transaction(function () use ($messageLog, $validated) {
            // 1. تحديث بيانات الرسالة مع التاريخ
            $messageLog->update([
                'telegram_user_id' => $validated['telegram_user_id'],
                'event_id'         => $validated['event_id'],
                'message'          => $validated['message'],
                'delivery_status'  => $validated['delivery_status'],
                'message_type'     => $validated['message_type'],
                'sent_at'          => $validated['message_date'], // ✅ هنا الصح
            ]);
            
            // 2. تحديث عدد مرات الاستفادة في جدول المستخدم
            TelegramUser::where('id', $validated['telegram_user_id'])
                    ->update(['benefit_count' => $validated['benefit_count']]);
        });
        
        return redirect()
            ->route('admin.messages.index')
            ->with('success', 'تم تحديث الرسالة والتاريخ بنجاح');
    }
    public function destroy($id)
{
    $messageLog = MessageLog::with('user')->findOrFail($id);

    DB::transaction(function () use ($messageLog) {

        // تسجيل مين حذف
        $messageLog->update([
            'deleted_by' => auth()->id()
        ]);

        // تقليل الاستفادة
        if ($messageLog->message_type === 'received') {
            if ($messageLog->user) {
                $messageLog->user->decrement('benefit_count');

                if ($messageLog->user->benefit_count < 0) {
                    $messageLog->user->update(['benefit_count' => 0]);
                }
            }
        }

        // حذف (Soft Delete)
        $messageLog->delete();
    });

    return redirect()
        ->route('admin.messages.index')
        ->with('success', 'تم حذف الرسالة بنجاح');
}

public function tripleBeneficiaries()
{
    $eventIds = [14,34,46]; // ضع هنا IDs الفعاليات

    $users = DB::table('message_logs')
        ->join('telegram_users', 'telegram_users.id', '=', 'message_logs.telegram_user_id')
        ->select(
            'telegram_users.id',
            'telegram_users.full_name',
            'telegram_users.national_id'
        )
        ->whereIn('message_logs.event_id', $eventIds)
        ->where('message_logs.message_type', 'received')
        ->groupBy(
            'telegram_users.id',
            'telegram_users.full_name',
            'telegram_users.national_id'
        )
        ->havingRaw('COUNT(DISTINCT message_logs.event_id) = 3')
        ->get();

    return view('dashboard.telegram.messages.triple', compact('users'));
}
}