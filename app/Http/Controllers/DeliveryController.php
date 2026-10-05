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
        try {

            $invitationLog = MessageLog::findOrFail($id);

            $event = Event::findOrFail($invitationLog->event_id);

            $user = TelegramUser::findOrFail($invitationLog->telegram_user_id);

            // تحقق هل تم التسليم مسبقاً
            $alreadyReceived = MessageLog::where('telegram_user_id', $user->id)
                ->where('event_id', $event->id)
                ->where('message_type', 'received')
                ->exists();

            if ($alreadyReceived) {

                return response()->json([
                    'success' => false,
                    'message' => '⚠️ تم تسجيل التسليم مسبقاً'
                ]);
            }

            $authUser = Auth::user();

            $message = $event->default_message ?? 'تم التسليم بنجاح';

            $telegramMessage  = "<b>الاسم:</b> {$user->full_name}\n";
            $telegramMessage .= "<b>رقم الهوية:</b> {$user->national_id}\n";
            $telegramMessage .= "<b>رقم الكشف:</b> {$event->id}\n";
            $telegramMessage .= "<b>التاريخ:</b> " . now()->toDateString() . "\n\n";
            $telegramMessage .= $message . "\n\n";

            if ($authUser && !empty($authUser->title)) {
                $telegramMessage .= "<b>المرسل:</b> {$authUser->title}";
            }

            $dbMessage = strip_tags($telegramMessage);

            // إنشاء سجل التسليم
            $log = MessageLog::create([
                'telegram_user_id' => $user->id,
                'event_id'         => $event->id,
                'sent_by'          => $authUser->id,
                'sender_type'      => $authUser->name,
                'message'          => $dbMessage,
                'message_type'     => 'received',
                'delivery_status'  => 'pending',
            ]);

            // تسجيل بدون إشعار
            if ($request->send_method === 'without_notification') {

                $log->update([
                    'delivery_status' => 'manual',
                    'sent_at' => now(),
                ]);

                $user->increment('benefit_count');

                return response()->json([
                    'success' => true,
                    'message' => '✅ تم تسجيل التسليم بدون إشعار'
                ]);
            }

            // لا يوجد تيليجرام
            if (empty($user->chat_id)) {

                $log->update([
                    'delivery_status' => 'manual',
                    'sent_at' => now(),
                ]);

                $user->increment('benefit_count');

                return response()->json([
                    'success' => true,
                    'message' => 'ℹ️ لا يوجد حساب تيليجرام، تم التسجيل فقط'
                ]);
            }

            // إرسال تيليجرام
            $response = $this->sendBusinessMessage($user, $telegramMessage);

            if ($response->successful()) {

                $log->update([
                    'delivery_status' => 'sent',
                    'sent_at' => now(),
                ]);

                $user->increment('benefit_count');

                return response()->json([
                    'success' => true,
                    'message' => '✅ تم التسليم وإرسال الرسالة'
                ]);
            }

            // فشل الإرسال
            $log->delete();

            return response()->json([
                'success' => false,
                'message' => '❌ فشل إرسال تيليجرام'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
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

        return Http::asJson()->post(
            "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage",
            $params
        );
    }
}