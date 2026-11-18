<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\MessageLog;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin|admin|adminattendance']);
    }

    // صفحة التسليم
    public function delivery($event_id)
    {
        $event = Event::findOrFail($event_id);

        $baseQuery = MessageLog::where('event_id', $event_id)
            ->where('message_type', 'invitation')
            ->whereIn('delivery_status', ['sent', 'manual']);

        // غير المستلمين فقط
        $logs = (clone $baseQuery)
            ->whereDoesntHave('user.messages', function ($q) use ($event_id) {
                $q->where('event_id', $event_id)
                  ->where('message_type', 'received');
            })
            ->with('user')
            ->latest()
            ->get();

        // عدد المستلمين
        $receivedCount = MessageLog::where('event_id', $event_id)
            ->where('message_type', 'received')
            ->count();

        return view('dashboard.events.delivery', compact(
            'event',
            'logs',
            'receivedCount'
        ));
    }

    // تسجيل التسليم
    public function markDelivery(Request $request, $id)
    {
        $staff = Auth::user();
        $override = $request->boolean('override');
        if ($override) {
            abort_unless($staff->hasRole('superadmin') || $staff->hasPermission(\App\Services\Association\AssociationModules::PERMISSIONS['association.deliveries.override']), 403);
            $request->validate(['reason' => 'required|string|min:3|max:2000']);
        }
        $result = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $staff, $id, $override) {
            $source = MessageLog::whereKey($id)->where('message_type', 'invitation')->firstOrFail();
            TelegramUser::whereKey($source->telegram_user_id)->lockForUpdate()->firstOrFail();
            $event = Event::whereKey($source->event_id)->lockForUpdate()->firstOrFail();
            abort_unless(app(\App\Services\Association\AssociationEntities::class)->eventActive($event), 409, 'الفعالية غير نشطة.');
            if (\Illuminate\Support\Facades\Schema::hasTable('association_invitation_states')) {
                abort_if(\Illuminate\Support\Facades\DB::table('association_invitation_states')->where('message_log_id', $id)->whereNotNull('withdrawn_at')->exists(), 409, 'سُحبت الدعوة.');
            }
            $result = app(\App\Services\Association\AssociationDeliveryFacts::class)->apply($staff, (int) $id, now(), $override);
            if (! $result['already_applied'] && \Illuminate\Support\Facades\Schema::hasTable('association_audit')) {
                \Illuminate\Support\Facades\DB::table('association_audit')->insert(['staff_id' => $staff->id, 'module' => 'deliveries',
                    'record_id' => $id, 'action' => 'web.confirm', 'after' => json_encode(['receipt_id' => $result['receipt']->id]),
                    'reason' => $override ? $request->input('reason') : null, 'created_at' => now()]);
                if ($request->send_method !== 'without_notification') {
                    app(\App\Services\Association\AssociationNotifications::class)->beneficiary($source->telegram_user_id, $staff->id,
                        'تأكيد استلام المساعدة', $result['receipt']->message, ['type' => 'receipt', 'id' => $result['receipt']->id, 'event_id' => $event->id]);
                }
            }
            return $result;
        }, 3);
        if ($result['already_applied']) {
            return response()->json(['success' => true, 'already_applied' => true, 'message' => 'تم تسجيل التسليم مسبقًا.']);
        }
        $log = $result['receipt'];
        $person = TelegramUser::findOrFail($log->telegram_user_id);
        if ($request->send_method === 'without_notification' || empty($person->chat_id)) {
            return response()->json(['success' => true, 'message' => 'تم تسجيل التسليم دون إشعار خارجي.']);
        }
        try {
            $response = $this->sendBusinessMessage($person, htmlspecialchars($log->message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            if ($response->successful()) {
                $log->update(['delivery_status' => 'sent']);
                return response()->json(['success' => true, 'message' => 'تم تسجيل التسليم وإرسال الرسالة.']);
            }
        } catch (\Throwable) {
            // Delivery remains committed even when Telegram is unavailable.
        }
        return response()->json(['success' => true, 'notification_sent' => false, 'message' => 'تم تسجيل التسليم، وتعذّر إرسال الإشعار الخارجي.']);
    }

    // إرسال تيليجرام
    private function sendBusinessMessage(TelegramUser $user, string $text)
    {
        $params = [
            'chat_id' => $user->chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        $isVolunteer = false;

        if ($user->category && $user->category->caseProfile) {
            $isVolunteer = $user->category->caseProfile->key === 'Administration';
        }

        if (!$isVolunteer && !empty($user->business_connection_id)) {
            $params['business_connection_id'] = $user->business_connection_id;
        }

        return Http::asJson()->connectTimeout(5)->timeout(15)->post(
            "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage",
            $params
        );
    }
}