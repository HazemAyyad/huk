<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramUser;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use App\Models\MessageLog;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\FamilyMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\CaseProfile; 
use App\Models\TelegramUserNote;
use App\Models\Reminder;
use App\Models\OrphanCase;

use Maatwebsite\Excel\Facades\Excel;

class AdminTelegramController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin|adminorphans|adminamputation|admin|telegram']);
    }

    private function normalizeArabic($text)
    {
        $text = trim($text);

        $search  = ['أ','إ','آ','ة','ى','ئ','ي'];
        $replace = ['ا','ا','ا','ه','ي','ي','ي'];

        return str_replace($search, $replace, $text);
    }




    public function index(Request $request)
    {
        $query = TelegramUser::query();

        $autoMessage = null;

        // 🔥 القيم القادمة من الفعالية
        $eventId = $request->get('event_id');
        $messageType = $request->get('message_type');
        $selectedEvent = $eventId
            ? Event::find($eventId)
            : null;

        $sendMethod = 'with_notification';

        $today = now()->day;

        $notifications = Reminder::where('day_of_month','<=',$today)
            ->where(function ($q){

                $q->whereNull('completed_for_month')
                ->orWhereMonth('completed_for_month','!=',now()->month)
                ->orWhereYear('completed_for_month','!=',now()->year);

            })
            ->get();

        $count = $notifications->count();



        if ($selectedEvent && (
            $selectedEvent->is_archived ||
            $selectedEvent->without_notification
        )) {
            $sendMethod = 'without_notification';
        }

        // ✅ إذا جاي من زر فعالية
        if ($eventId) {
            $selectedEvent = Event::find($eventId);

            if ($selectedEvent) {

                if ($messageType === 'invitation') {
                    $autoMessage = $selectedEvent->invitation_message;
                }

                elseif ($messageType === 'received') {
                    $autoMessage = $selectedEvent->default_message;
                }
            }
        }

        // 🔹 استبعاد الإدارة
        if (!auth()->user()->hasRole('superadmin')) {
            $query->where(function($q) {
                $q->whereRelation('category.caseProfile', 'key', '!=', 'Administration')
                ->orWhereNull('category_id');
            });
        }

        // 🔍 البحث
        if ($request->filled('search')) {
            $search = trim($request->search);

            $blockedReasonsMap = [
                'هوية موجودة مسبقًا' => 'duplicate_national_id_bot',
                'هوية موجودة مسبقًا' => 'duplicate_national_id',
                'هوية مسجلة كمرافق' => 'national_id_registered_as_family_member',
                'هوية مرافق مكررة أثناء التسجيل' => 'Duplicate family member ID detected',
                'تجاوز الحد الأقصى للأخطاء' => 'Too many invalid inputs',
                'رسائل بعد اكتمال الطلب' => 'messages_after_request_completed',
            ];

            $searchBlockedReason = $blockedReasonsMap[$search] ?? null;

            $query->where(function ($q) use ($search, $searchBlockedReason) {

    $words = array_filter(explode(' ', $search));
    $normalizedSearch = $this->normalizeArabic($search);

    $q->where(function ($mainQ) use ($words, $normalizedSearch) {

        // =========================================
        // 🔥 1. بحث بالجملة (الأدق)
        // =========================================
        $mainQ->whereRaw("
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(full_name,
            'أ','ا'),'إ','ا'),'آ','ا'),'ة','ه'),'ى','ي'),'ئ','ي')
            LIKE ?
        ", ["%{$normalizedSearch}%"])

        // =========================================
        // 🔥 2. AND (كل الكلمات لازم تنوجد)
        // =========================================
        ->orWhere(function ($subQ) use ($words) {
            foreach ($words as $word) {
                $normalizedWord = $this->normalizeArabic($word);

                $subQ->where(function ($innerQ) use ($normalizedWord) {

                    $innerQ->whereRaw("
                        REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(full_name,
                        'أ','ا'),'إ','ا'),'آ','ا'),'ة','ه'),'ى','ي'),'ئ','ي')
                        LIKE ?
                    ", ["%{$normalizedWord}%"])

                    ->orWhereRaw("
                        REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(address,
                        'أ','ا'),'إ','ا'),'آ','ا'),'ة','ه'),'ى','ي'),'ئ','ي')
                        LIKE ?
                    ", ["%{$normalizedWord}%"])

                    ->orWhereRaw("
                        REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(city,
                        'أ','ا'),'إ','ا'),'آ','ا'),'ة','ه'),'ى','ي'),'ئ','ي')
                        LIKE ?
                    ", ["%{$normalizedWord}%"]);
                });
            }
        });

    })

    // باقي الحقول
    ->orWhere('first_name', 'like', "%{$search}%")
    ->orWhere('username', 'like', "%{$search}%")
    ->orWhere('national_id', 'like', "%{$search}%")
    ->orWhere('access_code', 'like', "%{$search}%")
    ->orWhere('whatsapp', 'like', "%{$search}%")
    ->orWhere('telegram_number', 'like', "%{$search}%")
    ->orWhere('egypt_number', 'like', "%{$search}%")
    ->orWhere('chat_id', 'like', "%{$search}%")
    ->orWhere('governorate', 'like', "%{$search}%")
    ->orWhere('target_case', 'like', "%{$search}%");

    if ($searchBlockedReason) {
        $q->orWhere('blocked_reason', $searchBlockedReason);
    }

    $q->orWhereHas('familyMembers', function ($q2) use ($search) {
        $q2->where('full_name', 'like', "%{$search}%")
           ->orWhere('national_id', 'like', "%{$search}%");
    });

    $q->orWhereHas('notes', function ($q2) use ($search) {
        $q2->where('note', 'like', "%{$search}%");
    });

});
        }

        // 🔍 الفلترة
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'completed':
                    $query->where('is_completed', true);
                    break;
                case 'blocked':
                    $query->where('is_blocked', true);
                    break;
                case 'pending':
                    $query->where('is_completed', false)
                        ->where('is_blocked', false);
                    break;
            }
        }

        // 📊 إحصائيات
        $totalUsers = TelegramUser::count();
        $is_blocked = TelegramUser::where('is_blocked', true)->count();
        $is_completed = TelegramUser::where('is_completed', true)->count();
        $pendingUsers = TelegramUser::where('is_completed', false)->where('is_blocked', false)->count();
        // 📊 إحصائيات الحالات
        // =========================================
        // 📊 إحصائيات الأيتام والبتر
        // =========================================

        // إجمالي المسجلين في فئة الأيتام
        $orphansCount = TelegramUser::where('target_case', 'الأيتام')
            ->count();

        // إجمالي المسجلين في فئة البتر
        $amputationsCount = TelegramUser::where('target_case', 'حالات البتر')
            ->count();


        // =========================================
        // 👶 الأيتام
        // =========================================

        // عدد الحالات المسجلة عبر رابط الأيتام
        $orphansRegisteredInLink = DB::table('orphan_cases')->count();

        // المتبقي غير المسجل في الرابط
        $orphansRemaining = max(0, $orphansCount - $orphansRegisteredInLink);

        // عدد الأيتام الذين لديهم كود
        $orphansWithCode = TelegramUser::where('target_case', 'الأيتام')
            ->whereNotNull('access_code')
            ->where('access_code', '!=', '')
            ->count();

        // عدد الأيتام الذين ليس لديهم كود
        $orphansWithoutCode = max(0, $orphansCount - $orphansWithCode);


        // =========================================
        // 🦽 حالات البتر
        // =========================================

        // عدد الحالات المسجلة عبر رابط البتر
        $amputationsRegisteredInLink = DB::table('amputation_detail_cases')->count();

        // المتبقي غير المسجل في الرابط
        $amputationsRemaining = max(0, $amputationsCount - $amputationsRegisteredInLink);

        // عدد حالات البتر الذين لديهم كود
        $amputationsWithCode = TelegramUser::where('target_case', 'حالات البتر')
            ->whereNotNull('access_code')
            ->where('access_code', '!=', '')
            ->count();

        // عدد حالات البتر الذين ليس لديهم كود
        $amputationsWithoutCode = max(0, $amputationsCount - $amputationsWithCode);

        $injuredCount = TelegramUser::where('target_case', 'مساكن الجرحى والمرضى')->count();

        $patientsCount = TelegramUser::where('target_case', 'الحالات المرضية')->count();

        $strandedCount = TelegramUser::whereNotIn('target_case', [
            'الأيتام',
            'حالات البتر',
            'مساكن الجرحى والمرضى',
            'الحالات المرضية'
        ])->count();
        
        $year = now()->year;

        // 📌 المستخدمين
        $users = $query
            ->select('telegram_users.*')

            ->selectSub(function ($q) use ($year) {
                $q->from('message_logs')
                ->selectRaw('COUNT(DISTINCT event_id)')
                ->whereColumn('message_logs.telegram_user_id', 'telegram_users.id')
                ->where('message_type', 'received')
                ->whereExists(function ($q2) use ($year) {
                    $q2->selectRaw(1)
                        ->from('events')
                        ->whereColumn('events.id', 'message_logs.event_id')
                        ->whereYear('event_date', $year);
                });
            }, 'benefit_2026_count')


            ->selectSub(function ($q) use ($year) {
                $q->from('message_logs')
                ->selectRaw('COUNT(DISTINCT event_id)')
                ->whereColumn('message_logs.telegram_user_id', 'telegram_users.id')
                ->where('message_type', 'received')
                ->whereExists(function ($q2) use ($year) {
                    $q2->selectRaw(1)
                        ->from('events')
                        ->whereColumn('events.id', 'message_logs.event_id')
                        ->where('aid_type', 'in_kind')
                        ->whereYear('event_date', $year);
                });
            }, 'in_kind_2026_count')


            ->selectSub(function ($q) use ($year) {
                $q->from('message_logs')
                ->selectRaw('COUNT(DISTINCT event_id)')
                ->whereColumn('message_logs.telegram_user_id', 'telegram_users.id')
                ->where('message_type', 'received')
                ->whereExists(function ($q2) use ($year) {
                    $q2->selectRaw(1)
                        ->from('events')
                        ->whereColumn('events.id', 'message_logs.event_id')
                        ->where('aid_type', 'cash')
                        ->whereYear('event_date', $year);
                });
            }, 'cash_2026_count')


            ->selectSub(function ($q) {
                $q->from('message_logs')
                ->selectRaw('COUNT(DISTINCT event_id)')
                ->whereColumn('message_logs.telegram_user_id', 'telegram_users.id')
                ->where('message_type', 'received');
            }, 'total_benefit_count')

            // 🔥 الترتيب النهائي
            // 🔥 الترتيب النهائي (الأكثر استفادة أولاً)
            ->orderByRaw("
                CASE 
                    WHEN benefit_2026_count = 0 THEN 0
                    WHEN in_kind_2026_count > 0 AND cash_2026_count = 0 THEN 1
                    ELSE 2
                END DESC
            ")
            ->orderBy('benefit_2026_count', 'asc')
            ->orderBy('total_benefit_count', 'asc')
            ->orderBy('id', 'desc')
            ->withCount('notes')
            ->paginate(25)
            ->withQueryString();

        // 🔥 إذا جاي من الفعالية → لا تجيب كل الفعاليات
        $events = $eventId 
            ? Event::where('id', $eventId)->get()
            : Event::orderBy('event_date','desc')->get();


        return view('dashboard.telegram.users', compact(
            'users',
            'events',
            'totalUsers',
            'is_blocked',
            'is_completed',
            'pendingUsers',
            'selectedEvent',
            'autoMessage',
            'messageType',
            'orphansCount',
            'amputationsCount',
            'injuredCount',
            'patientsCount',
            'strandedCount',
            'eventId',
            'sendMethod',
            'orphansRegisteredInLink',
            'orphansRemaining',
            'orphansWithCode',
            'orphansWithoutCode',

            'amputationsRegisteredInLink',
            'amputationsRemaining',
            'amputationsWithCode',
            'amputationsWithoutCode',
            'count'
        ));
    }


    public function approveForm($id)
{
    $user = TelegramUser::findOrFail($id);

    return view('dashboard.telegram.approve', compact('user'));
}

    public function approve(Request $request, $id)
{
    $request->validate([
        'approved_by' => 'required|string|max:255',
        'approval_description' => 'required|string|min:20',
    ]);

    $user = TelegramUser::findOrFail($id);

    if ($user->is_approved) {
        return redirect()->back()->with('error', 'هذا المستخدم معتمد مسبقاً');
    }

    $user->update([
        'is_approved' => 1,
        'approved_by' => $request->approved_by,
        'approval_description' => $request->approval_description,
    ]);

    return redirect()->route('admin.telegram.index')
        ->with('success', 'تم اعتماد المستخدم بنجاح');
}

    public function create(TelegramUser $user)
    {
        $events = Event::orderBy('event_date','desc')->get();
        return view('dashboard.telegram.send', compact('user', 'events'));
    }


    private function sendBusinessMessage(TelegramUser $user, string $text)
    {
        $params = [
            'chat_id' => $user->chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        // ✅ تحقق آمن بدون ما يكسر العلاقات
        $isVolunteer = false;

        if ($user->category && $user->category->caseProfile) {
            $isVolunteer = $user->category->caseProfile->key === 'Administration';
        }

        // 🔥 إذا مش متطوع → استخدم business (نفس منطقك القديم)
        if (!$isVolunteer && !empty($user->business_connection_id)) {
            $params['business_connection_id'] = $user->business_connection_id;
        }

        return Http::asJson()->post(
            "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage",
            $params
        );
    }

    public function sendDirect(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:telegram_users,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:telegram_users,id',
            'message' => 'required|string',
            'message_type' => 'required|in:invitation,attended,received,general',
            'event_id' => 'nullable|exists:events,id',
            'send_method' => 'required|in:with_notification,without_notification',
            'check_only' => 'nullable|boolean', // 👈 مهم
            'force_send' => 'nullable|boolean',
        ]);

        // 👇 إذا فحص فقط
        if ($request->boolean('check_only')) {
            return $this->handleCheckOnly($request);
        }

        // إرسال فعلي
        $isBulk = $request->filled('user_ids');

        return $isBulk
            ? $this->handleBulkSend($request)
            : $this->handleSingleSend($request);
    }

    private function handleCheckOnly(Request $request)
    {
        $user = TelegramUser::findOrFail($request->user_id);
        $notes = $user->notes->pluck('note')->toArray();

        // فقط تحذير الاستفادة المتكررة
        if ($request->message_type === 'received') {
            $lastMessage = MessageLog::where('telegram_user_id', $user->id)
                ->where('message_type', 'received')
                ->orderBy('sent_at', 'desc')
                ->first();

            if ($lastMessage) {
                $sentAt = Carbon::parse($lastMessage->sent_at);
                $time  = $this->calculateTimeDiff($sentAt);

                if ($time['days'] < 15) {
                    return response()->json([
                        'status' => 'warning',
                        'warning' => [
                            'days' => $time['days'],
                            'message' => "⚠️ هذا المستخدم قد استفاد قبل {$time['text']}"
                        ],
                        'notes' => $notes
                    ]);
                }

            }
        }

        if ($request->message_type === 'invitation') {
            $lastMessage = MessageLog::where('telegram_user_id', $user->id)
                ->where('message_type', 'invitation')
                ->orderBy('sent_at', 'desc')
                ->first();

            if ($lastMessage) {
                $sentAt = Carbon::parse($lastMessage->sent_at);
                $time  = $this->calculateTimeDiff($sentAt);

                if ($time['days'] < 15) {
                    return response()->json([
                        'status' => 'warning',
                        'warning' => [
                            'days' => $time['days'],
                            'message' => "⚠️ هذا المستخدم تم ارسال له دعوة حضور من قبل {$time['text']}"
                        ],
                        'notes' => $notes
                    ]);
                }

            }
        }
        // لا تحذير → OK
        return response()->json([
            'status' => 'ok',
            'notes' => $notes
        ]);
    }


    private function handleSingleSend(Request $request)
{
    $user     = TelegramUser::findOrFail($request->user_id);
    $event    = $request->filled('event_id') ? Event::find($request->event_id) : null;
    $authUser = Auth::user();

    // =========================
    // 📩 تحضير رسالة تيليجرام (مع تنسيق HTML)
    // =========================
    $telegramMessage  = "الاسم: {$user->full_name}\n";
    $telegramMessage .= "رقم الهوية: {$user->national_id}\n";

    $this->generateQrIfNotExists($user);

    if (!empty($user->access_code)) {
        $telegramMessage .= "رقم الكود: {$user->access_code}\n\n";
    }

    if ($event && !empty($event->id)) {
        $telegramMessage .= "رقم الكشف: {$event->id}\n";
    }

    if ($event) {
        $eventDate = $event->is_fixed
            ? now()->toDateString()   // 🔥 تاريخ اليوم
            : $event->event_date;     // 📅 تاريخ الفعالية

        $telegramMessage .= "تاريخ اليوم: {$eventDate}\n";
    }

    $telegramMessage .= "\n" . $request->message . "\n";

    if ($authUser && !empty($authUser->title)) {
        $telegramMessage .= "المرسل: {$authUser->title}\n";
    }

    // =========================
    // 💾 تحضير رسالة الداتابيز (بدون HTML)
    // =========================
    $dbMessage = strip_tags($telegramMessage);

    // =========================
    // 🧠 بدون إشعار (تسجيل فقط)
    // =========================
    if ($request->send_method === 'without_notification') {

        MessageLog::create([
            'telegram_user_id' => $user->id,
            'event_id'         => $request->event_id,
            'sent_by'          => $authUser->id,
            'sender_type'      => $authUser->name,
            'message'          => $dbMessage,
            'message_type'     => $request->message_type,
            'delivery_status'  => 'manual',
            'sent_at'          => now(),
            'notes'            => 'تم التسجيل يدوياً بدون إرسال تيليجرام',
        ]);

        if ($request->message_type === 'received') {
            if (!$event || !$event->is_archived) {
                $user->increment('benefit_count');
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => "✅ تم تسجيل الرسالة في قاعدة البيانات بدون إرسال تيليجرام و\n📋 تم نسخ نص الرسالة إلى الحافظة تلقائيًا",
            'copy_message' => $dbMessage,
        ]);
    }

    // =========================
    // ✅ التحقق من فترة الاستفادة
    // =========================
    if ($request->message_type === 'received') {

        $lastMessage = MessageLog::where('telegram_user_id', $user->id)
            ->where('message_type', 'received')
            ->whereNotNull('sent_at')
            ->orderBy('sent_at', 'desc')
            ->first();

        if ($lastMessage) {

            $sentAt = Carbon::parse($lastMessage->sent_at);
            $time   = $this->calculateTimeDiff($sentAt);

            if ($time['days'] < 15) {
                $warning = [
                    'days'    => $time['days'],
                    'message' => "⚠️ هذا المستخدم قد استفاد قبل {$time['text']}، يرجى مراجعة الإدارة قبل تأكيد الإرسال"
                ];
            }
        }
    }

    // =========================
    // ❌ لا يوجد Chat ID
    // =========================
    if (empty($user->chat_id)) {

        MessageLog::create([
            'telegram_user_id' => $user->id,
            'event_id'         => $request->event_id,
            'sent_by'          => $authUser->id,
            'sender_type'      => $authUser->name,
            'message'          => $dbMessage,
            'message_type'     => $request->message_type,
            'delivery_status'  => 'manual',
            'sent_at'          => now(),
        ]);

        if ($request->message_type === 'received') {
            if (!$event || !$event->is_archived) {
                $user->increment('benefit_count');
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => "ℹ️ المستخدم غير مرتبط بتليجرام، تم تسجيل الفاعلية وزيادة عدد الاستفادة بدون إرسال رسالة. ,\n📋 ,تم نسخ نص الرسالة إلى الحافظة تلقائيًا",
            'copy_message' => $dbMessage,
        ]);
    }

    // =========================
    // ✅ يوجد Chat ID → إرسال تيليجرام
    // =========================
    $log = MessageLog::create([
        'telegram_user_id' => $user->id,
        'event_id'         => $request->event_id,
        'sent_by'          => $authUser->id,
        'sender_type'      => $authUser->name,
        'message'          => $dbMessage,
        'message_type'     => $request->message_type,
        'delivery_status'  => 'pending',
    ]);

    $response = $this->sendPhotoWithMessage($user, $telegramMessage);

    if ($response->successful()) {

        $log->update([
            'delivery_status' => 'sent',
            'sent_at'         => now(),
        ]);

        if ($request->message_type === 'received') {
            if (!$event || !$event->is_archived) {
                $user->increment('benefit_count');
            }
        }

    return response()->json([
        'status'  => 'success',
        'message' => "✅ تم إرسال الرسالة عبر تيليجرام وتسجيلها بنجاح\n📋 تم نسخ نص الرسالة إلى الحافظة تلقائيًا",
        'copy_message' => $dbMessage,
    ]);
    }
    $log->update([
        'delivery_status' => 'failed',
    ]);

    return response()->json([
        'status'  => 'error',
        'message' => 'خطأ تيليجرام: ' . $response->body(),
    ]);
}


private function generateQrIfNotExists(TelegramUser $user)
{
    // ✅ إذا موجود لا تعيد التوليد
    if (!empty($user->qr_code) && file_exists(public_path($user->qr_code))) {
        return $user->qr_code;
    }

    // 🔥 توليد QR PNG
    $fileName = 'qr_' . $user->national_id . '.png';
    $folderPath = public_path('uploads/qr_codes');

    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    // استخدام endroid (اللي اشتغل معك)
    $result = \Endroid\QrCode\Builder\Builder::create()
        ->writer(new \Endroid\QrCode\Writer\PngWriter())
        ->data($user->national_id)
        ->size(300)
        ->margin(10)
        ->build();

    $result->saveToFile($folderPath . '/' . $fileName);

    // حفظ في DB
    $user->qr_code = 'uploads/qr_codes/' . $fileName;
    $user->save();

    return $user->qr_code;
}


private function sendPhotoWithMessage(TelegramUser $user, $caption)
{
    $qrPath = public_path($user->qr_code);

    $params = [
        'chat_id' => $user->chat_id,
        'caption' => $caption,
        'parse_mode' => 'HTML',
    ];

    // ✅ نفس منطق business
    $isVolunteer = false;

    if ($user->category && $user->category->caseProfile) {
        $isVolunteer = $user->category->caseProfile->key === 'Administration';
    }

    if (!$isVolunteer && !empty($user->business_connection_id)) {
        $params['business_connection_id'] = $user->business_connection_id;
    }

    $response = Http::attach(
        'photo',
        file_get_contents($qrPath),
        'qr.png'
    )->post(
        "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendPhoto",
        $params
    );

    // إذا كان متطوع، أرسل نسخة إلى حساب الإدارة داخل البوت
    if ($response->successful() && $isVolunteer) {

        Http::post(
            "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage",
            [
                'chat_id'    => 7479198254,
                'text'       => $caption,
                'parse_mode' => 'HTML',
            ]
        );
    }

    return $response;
}


    private function calculateTimeDiff(Carbon $sentAt): array
    {
        $now = Carbon::now();

        // لو التاريخ في المستقبل (غلط بيانات)
        if ($sentAt->greaterThan($now)) {
            return [
                'days' => 0,
                'text' => 'الآن'
            ];
        }

        $minutes = $sentAt->diffInMinutes($now);
        $hours   = $sentAt->diffInHours($now);
        $days    = $sentAt->diffInDays($now);

        if ($minutes < 60) {
            $text = $minutes . ' دقيقة';
        } elseif ($hours < 24) {
            $text = $hours . ' ساعة';
        } else {
            $text = $days . ' يوم';
        }

        return [
            'days' => $days,
            'text' => $text
        ];
    }

    private function handleBulkSend(Request $request)
    {
        $userIds = $request->user_ids;
        $results = [
            'success' => 0,
            'failed' => 0,
            'manual' => 0,
            'details' => []
        ];

        $allMessages = [];
        
        $authUser = Auth::user();
        $event = $request->filled('event_id') ? Event::find($request->event_id) : null;

        foreach ($userIds as $userId) {
            try {
                $user = TelegramUser::find($userId);
                $this->generateQrIfNotExists($user);

                if (!$user) {
                    $results['failed']++;
                    $results['details'][] = [
                        'user_id' => $userId,
                        'status' => 'failed',
                        'message' => 'المستخدم غير موجود'
                    ];
                    continue;
                }

                // تحضير الرسالة النهائية
                $finalMessage = "الاسم: {$user->full_name}\n";
                $finalMessage .= "رقم الهوية: {$user->national_id}\n";

                if (!empty($user->access_code)) {
                    $finalMessage .= "رقم الكود: {$user->access_code}\n\n";
                }

                if ($event && !empty($event->id)) {
                    $finalMessage .= "رقم الكشف: {$event->id}\n";
                }

                if ($event) {
                    $eventDate = $event->is_fixed
                        ? now()->toDateString()
                        : $event->event_date;

                    $finalMessage .= "تاريخ اليوم: {$eventDate}\n";
                }

                $finalMessage .= "\n" . $request->message . "\n";
                
                if ($authUser && !empty($authUser->title)) {
                    $finalMessage .= "المرسل: {$authUser->title}\n";
                }
                $finalMessage = strip_tags($finalMessage); // نص نظيف للنسخ

                // 👇 نجمّع كل رسالة مع فاصل واضح بينها
                $allMessages[] = "————————————————\n" . $finalMessage;
                
                if ($request->send_method === 'without_notification') {
                    // ✅ بدون إشعار - تسجيل فقط
                    MessageLog::create([
                        'telegram_user_id' => $user->id,
                        'event_id' => $request->event_id,
                        'sent_by' => $authUser->id,
                        'sender_type' => $authUser->name,
                        'message' => $finalMessage,
                        'message_type' => $request->message_type,
                        'delivery_status' => 'manual',
                        'sent_at' => now(),
                        'notes' => 'تم التسجيل يدوياً بدون إرسال تيليجرام',
                    ]);

                    if ($request->message_type === 'received') {
                        if (!$event || !$event->is_archived) {
                            $user->increment('benefit_count');
                        }
                    }

                    $results['manual']++;
                    $results['details'][] = [
                        'user_id' => $userId,
                        'user_name' => $user->full_name,
                        'status' => 'manual',
                        'message' => 'تم التسجيل يدوياً'
                    ];
                } else {
                    // ✅ بإشعار - إرسال تيليجرام
                    $log = MessageLog::create([
                        'telegram_user_id' => $user->id,
                        'event_id' => $request->event_id,
                        'sent_by' => $authUser->id,
                        'sender_type' => $authUser->name,
                        'message' => $finalMessage,
                        'message_type' => $request->message_type,
                        'delivery_status' => 'pending',
                    ]);

                    // إذا كان بدون chat_id، تسجيل يدوي
                    if (empty($user->chat_id)) {
                        $log->update([
                            'delivery_status' => 'manual',
                            'sent_at' => now(),
                        ]);

                        if ($request->message_type === 'received') {
                            $user->increment('benefit_count');
                        }

                        $results['manual']++;
                        $results['details'][] = [
                            'user_id' => $userId,
                            'user_name' => $user->full_name,
                            'status' => 'manual',
                            'message' => 'المستخدم غير مرتبط بتليجرام'
                        ];
                    } else {
                        // إرسال عبر تيليجرام
                        $response = $this->sendPhotoWithMessage($user, $finalMessage);

                        if ($response->successful()) {
                            $log->update([
                                'delivery_status' => 'sent',
                                'sent_at' => now(),
                            ]);

                            if ($request->message_type === 'received') {
                                if (!$event || !$event->is_archived) {
                                    $user->increment('benefit_count');
                                }
                            }

                            $results['success']++;
                            $results['details'][] = [
                                'user_id' => $userId,
                                'user_name' => $user->full_name,
                                'status' => 'sent',
                                'message' => 'تم الإرسال بنجاح'
                            ];
                        } else {
                            $log->update([
                                'delivery_status' => 'failed',
                            ]);

                            $results['failed']++;
                            $results['details'][] = [
                                'user_id' => $userId,
                                'user_name' => $user->full_name,
                                'status' => 'failed',
                                'message' => 'فشل الإرسال عبر تيليجرام'
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'user_id' => $userId,
                    'status' => 'failed',
                    'message' => 'خطأ: ' . $e->getMessage()
                ];
            }
        }

        // تحضير رسالة النتيجة
        $total = count($userIds);
        $message = "تمت العملية \n 📋 تم نسخ نص الرسائل إلى الحافظة تلقائيًا: ";
        if ($request->send_method === 'without_notification') {
            $message .= "تم تسجيل {$total} رسالة في قاعدة البيانات";
        } else {
            $message .= "تم إرسال {$results['success']} رسالة عبر تيليجرام، ";
            $message .= "تسجيل {$results['manual']} يدوياً، ";
            $message .= "فشل {$results['failed']} رسالة";
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'results' => $results,
            'copy_message' => implode("\n\n", $allMessages),
        ]);
    }



    /**
     * عرض تفاصيل مستخدم تيليجرام.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showDetails($id)
{
    $user = TelegramUser::with([
            'familyMembers',
            'category',
            'notes.creator',
            'notes.updater',
            'notes.deleter'
        ])
        ->withCount('messages')
        ->findOrFail($id);

    // جلب طلب الأيتام المرتبط بهذا المستخدم
    $orphanCase = OrphanCase::where('telegram_user_id', $user->id)->first();

    // جلب الرسائل مرتبة من الأحدث إلى الأقدم بناءً على تاريخ الإرسال
    // جلب الرسائل:
    // 1. الفعاليات غير المؤرشفة أولاً
    // 2. ترتيبها حسب تاريخ الإرسال من الأحدث للأقدم
    // 3. الفعاليات المؤرشفة في النهاية
    $messages = $user->messages()
        ->with('event')
        ->leftJoin('events', 'message_logs.event_id', '=', 'events.id')
        ->select('message_logs.*')
        ->orderByRaw('CASE WHEN events.is_archived = 1 THEN 1 ELSE 0 END ASC')
        ->orderBy('sent_at', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

    $notes = $user->notes->sortByDesc('created_at');

    return view(
        'dashboard.telegram.user_details',
        compact('user', 'messages', 'notes', 'orphanCase')
    );
}

    public function createUser()
    {
        $events = Event::orderBy('event_date', 'desc')->get();
        $categories = Category::where('is_active', true)->get();
        return view('dashboard.telegram.create', compact('events', 'categories'));
    }

    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'national_id' => 'required|string|max:255',
            'status' => 'required|in:pending,completed,blocked',
            'event_id' => 'nullable|exists:events,id',
            'message_type' => 'nullable|in:invitation,attended,received,general',
            'chat_id' => 'nullable|string|max:255|unique:telegram_users,chat_id',
            'whatsapp' => 'nullable|string|max:255',
            'telegram_number' => 'nullable|string|max:255',
            'egypt_number' => 'nullable|string|max:255',
            'governorate' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'target_case' => 'nullable|string|max:255',
            'family_members' => 'array',
        ]);

        // ✅ فليديشن مخصص بعد التحقق الأساسي
        $validator->after(function ($validator) use ($request) {

            $nationalId = $request->national_id;

            // 1️⃣ موجود كمستخدم رئيسي؟
            $existsInUsers = TelegramUser::where('national_id', $nationalId)->exists();

            // 2️⃣ موجود كمرافق؟
            $existsInFamily = \App\Models\FamilyMember::where('national_id', $nationalId)->exists();

            if ($existsInUsers || $existsInFamily) {
                $validator->errors()->add(
                    'national_id',
                    '❌ رقم الهوية مسجل مسبقًا سواء كمستخدم رئيسي أو كمرافق ولا يمكن إنشاء مستخدم جديد بنفس الرقم.'
                );
            }
        });

        // لو فشل الفليديشن
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        // =========================
        // ✅ إنشاء المستخدم
        // =========================

        $data = $request->except(['family_members', 'status']); // استبعاد status من المصفوفة الأساسية مؤقتاً
        $data['source'] = 'system';

        // تعيين القيم المنطقية بناءً على اختيار الـ Select
        $data['is_completed'] = ($request->status === 'completed');
        $data['is_blocked'] = ($request->status === 'blocked');
        $data['created_by'] = auth()->id() ?? null; // إذا جاي من النظام، يتم تسجيل من أنشأ المستخدم

        $user = TelegramUser::create($data);

        // =========================
        // ربط المستخدم بفاعلية (بدون تيليجرام)
        // =========================
        $event = $request->filled('event_id')
            ? Event::find($request->event_id)
            : null;

        $messageText =
            $request->message
            ?? $event?->default_message
            ?? 'تم الاستلام';

        if ($request->filled('event_id')) {
            $authUser = Auth::user();
            // تسجيل العملية كسجل رسالة (اختياري لكنه احترافي)
            MessageLog::create([
                'telegram_user_id' => $user->id,
                'event_id' => $request->event_id,
                'sent_by' => $authUser->id,
                'sender_type' => $authUser->name,
                'message' => $messageText,
                'message_type' => $request->message_type ?? 'general',
                'delivery_status' => 'manual',
                'sent_at' => now(),
            ]);

            // ✅ زيادة عدد مرات الاستفادة فقط في حالة "تم الاستلام"
            if ($request->message_type === 'received') {
                if (!$event || !$event->is_archived) {
                    $user->increment('benefit_count');
                }
            }
        }
        // داخل دالة storeUser بعد إنشاء المستخدم $user
        if ($request->filled('categories')) {

            $categories = $request->categories;

            // 🔹 ربط المستخدم بكل الفئات
            $user->categories()->sync($categories);

            // 🔥 أول فئة فقط هي اللي بتحدد الكود
            $firstCategoryId = $categories[0];

            DB::transaction(function () use ($user, $firstCategoryId) {

                $category = Category::lockForUpdate()->findOrFail($firstCategoryId);

                $code = $category->generateNextCode();

                $user->update([
                    'category_id' => $category->id, // 👉 الفئة الأساسية
                    'access_code' => $code,
                ]);

                $category->increment('last_number');
                $category->increment('total_assigned');
            });
        }

        // إضافة المرافقين
        if ($request->filled('family_members')) {
            foreach ($request->family_members as $member) {

                // فليديشن إضافي للمرافقين (اختياري لكن أنصح به)
                if (!empty($member['national_id'])) {
                    $exists = TelegramUser::where('national_id', $member['national_id'])
                        ->orWhereHas('familyMembers', function ($q) use ($member) {
                            $q->where('national_id', $member['national_id']);
                        })
                        ->exists();

                    if ($exists) {
                        return redirect()->back()
                            ->withErrors([
                                'family_members' => '❌ أحد أرقام هويات المرافقين مسجل مسبقًا.'
                            ])
                            ->withInput();
                    }
                }

                if (!empty($member['full_name'])) {
                    $user->familyMembers()->create([
                        'full_name' => $member['full_name'],
                        'national_id' => $member['national_id'] ?? null,
                    ]);
                }
            }
        }

        $user->update([
            'family_members' => $user->familyMembers()->count()
        ]);

        return redirect()
            ->route('admin.telegram.details', $user->id)
            ->with('success', '✅ تم إنشاء المستخدم بنجاح');
    }

    public function edit($id)
    {
        $user = TelegramUser::with(['familyMembers','notes.creator'])->findOrFail($id);
        $categories = Category::where('is_active', true)->get();

        return view('dashboard.telegram.edit', compact('user', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $user = TelegramUser::findOrFail($id);


        // 1. تجهيز كافة البيانات الأساسية باستثناء المصفوفات الخاصة
        $data = $request->except(['family_members', 'new_family_members', 'delete_members', 'status']);

        // 2. معالجة منطق الحالة (Status) ودمجها في مصفوفة البيانات الأساسية
        if ($request->has('status')) {
            $data['is_completed'] = ($request->status === 'completed');
            $data['is_blocked'] = ($request->status === 'blocked');

            // تنظيف بيانات الحظر إذا تغيرت الحالة
            if ($request->status !== 'blocked') {
                $data['blocked_reason'] = null;
                $data['spam_count'] = 0;
            }
        }

        if (!$user->access_code && $request->filled('category_id')) {
            DB::transaction(function () use ($user, $request) {
                $category = Category::lockForUpdate()->find($request->category_id);
                if ($category) {
                    $code = $category->generateNextCode();
                    $user->access_code = $code;
                    $user->category_id = $category->id;

                    $category->increment('last_number');
                    $category->increment('total_assigned');
                    // لاحظ أننا نعدل على الكائن مباشرة قبل عمل update العام
                }
            });
        }
        $data['updated_by'] = auth()->id(); // يسجل من قام بالتحديث
        // 3. تنفيذ أمر التحديث لمرة واحدة لكل البيانات
        $user->update($data);
        if ($request->has('categories')) {

            $categories = $request->categories ?? [];

            // =========================
            // 🟡 عنده كود
            // =========================
            if ($user->access_code) {

                // 👇 إذا فعّل تغيير الفئة الأساسية
                if ($request->has('change_main_category')) {

                    if (count($categories) == 0) {
                        return back()->with('error', '❌ يجب اختيار فئة واحدة على الأقل');
                    }

                    DB::transaction(function () use ($user, $categories) {

                        $newMainCategory = Category::lockForUpdate()->find($categories[0]);

                        if ($newMainCategory) {

                            // 🔥 توليد كود جديد
                            $newCode = $newMainCategory->generateNextCode();

                            $user->update([
                                'category_id' => $newMainCategory->id,
                                'access_code' => $newCode,
                            ]);

                            $newMainCategory->increment('last_number');
                            $newMainCategory->increment('total_assigned');
                        }

                        // تحديث كل الفئات
                        $oldCategories = $user->categories()->pluck('categories.id')->toArray();

                        $user->categories()->sync($categories);

                        $newCategories = $categories;

                        $added = array_diff($newCategories, $oldCategories);

                        foreach ($added as $catId) {
                            Category::where('id', $catId)->increment('total_assigned');
                        }
                    });

                } else {

                    // 🟢 فقط إضافة/حذف فئات بدون تغيير الأساسي
                    $mainCategoryId = $user->category_id;

                    if (!in_array($mainCategoryId, $categories)) {
                        $categories[] = $mainCategoryId;
                    }

                    $oldCategories = $user->categories()->pluck('categories.id')->toArray();

                    $user->categories()->sync($categories);

                    $newCategories = $categories;

                    // الفئات المضافة فقط
                    $added = array_diff($newCategories, $oldCategories);

                    foreach ($added as $catId) {
                        Category::where('id', $catId)->increment('total_assigned');
                    }
                }

            } 
            // =========================
            // 🔵 ما عنده كود
            // =========================
            else {

                if (count($categories) > 0) {

                    DB::transaction(function () use ($user, $categories) {

                        $category = Category::lockForUpdate()->find($categories[0]);

                        if ($category) {

                            $code = $category->generateNextCode();

                            $user->update([
                                'category_id' => $category->id,
                                'access_code' => $code,
                            ]);

                            $category->increment('last_number');
                            $category->increment('total_assigned');
                        }

                        $user->categories()->sync($categories);
                    });
                }
            }
        }
        // 2. معالجة الحذف
        if ($request->has('delete_members')) {
            $user->familyMembers()->whereIn('id', $request->delete_members)->delete();
        }

        // 3. تحديث أفراد العائلة الحاليين
        if ($request->has('family_members')) {
            foreach ($request->family_members as $memberId => $data) {
                $user->familyMembers()->where('id', $memberId)->update([
                    'full_name' => $data['full_name'],
                    'national_id' => $data['national_id'],
                ]);
            }
        }

        // 4. إضافة المرافقين الجدد (دعم الإضافة المتعددة)
        if ($request->has('new_family_members')) {
            foreach ($request->new_family_members as $newMember) {
                if (!empty($newMember['full_name'])) {
                    $user->familyMembers()->create([
                        'full_name' => $newMember['full_name'],
                        'national_id' => $newMember['national_id'],
                    ]);
                }
            }
        }

        // 5. تحديث العدد النهائي في جدول المستخدمين
        $user->update(['family_members' => $user->familyMembers()->count()]);

        return redirect()->route('admin.telegram.details', $user->id)
                        ->with('success', 'تم تحديث البيانات بنجاح.');
    }


public function storeNote(Request $request, $userId)
{
    $request->validate([
        'note' => 'required|string|max:2000'
    ]);

    $user = TelegramUser::findOrFail($userId);

    TelegramUserNote::create([
        'telegram_user_id' => $user->id,
        'note' => $request->note,
        'created_by' => auth()->id(),
    ]);

    return redirect()->back()->with('success','تم إضافة الملاحظة بنجاح');
}


public function updateNote(Request $request, $noteId)
{
    $request->validate([
        'note' => 'required|string|max:2000'
    ]);

    $note = TelegramUserNote::findOrFail($noteId);

    $note->update([
        'note' => $request->note,
        'updated_by' => auth()->id(),
    ]);

    return redirect()->back()->with('success','تم تعديل الملاحظة');
}

public function deleteNote($noteId)
{
    $note = TelegramUserNote::where('id',$noteId)->firstOrFail();


    $note->update([
        'deleted_by' => auth()->id(),
    ]);

    $note->delete();

    return redirect()->back()->with('success','تم حذف الملاحظة');
}

    /**
     * حذف مستخدم تيليجرام (Soft Delete)
     */
    /**
 * حذف مستخدم تيليجرام (Soft Delete)
 * متاح للسوبر أدمن فقط
 */
/**
 * حذف مستخدم تيليجرام (Soft Delete) - للسوبر أدمن فقط
 */
public function destroy($id)
{
    // التحقق من صلاحية السوبر أدمن
    if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
        return response()->json([
            'status' => 'error',
            'message' => '❌ ليس لديك صلاحية لحذف المستخدمين'
        ], 403);
    }

    try {
        // البحث اليدوي (لأن Route Model Binding لا يعمل مع SoftDeletes افتراضياً)
        $user = TelegramUser::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ المستخدم غير موجود أو محذوف مسبقاً'
            ], 404);
        }

        $userName = $user->full_name ?? $user->first_name ?? 'المستخدم';

        // 🔴 فصل عمليات التحديث والحذف لتجنب الأخطاء
        try {
            // تحديث من قام بالحذف
            $user->deleted_by = auth()->id();
            $user->saveQuietly(); // saveQuietly لتجنب triggering events
        } catch (\Exception $e) {
            \Log::warning('Could not update deleted_by: ' . $e->getMessage());
        }

        // تنفيذ الحذف
        $user->delete();

        \Log::info("User deleted successfully", [
            'user_id' => $id,
            'deleted_by' => auth()->id(),
            'user_name' => $userName
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "✅ تم حذف المستخدم \"{$userName}\" بنجاح"
        ]);

    } catch (\Throwable $e) { // 👈 Throwable بدلاً من Exception لالتقاط كل الأخطاء
        \Log::error('فشل حذف المستخدم', [
            'user_id' => $id,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => '❌ حدث خطأ أثناء الحذف: ' . $e->getMessage(),
            'debug' => config('app.debug') ? [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ] : null
        ], 500);
    }
}

    public function unblock(TelegramUser $user)
    {
        try {
            // التحقق إذا كان المستخدم محظورًا بالفعل
            if (!$user->is_blocked) {
                return response()->json([
                    'status' => 'info',
                    'message' => "⚠️ المستخدم {$user->full_name} غير محظور بالفعل."
                ]);
            }

            $user->update([
                'is_blocked' => false,
                'blocked_reason' => null,
                'spam_count' => 0,
                'is_completed' => false, // تحويله إلى قيد التسجيل
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "✅ تم فك الحظر عن المستخدم {$user->full_name}.",
                'user' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'status' => 'pending'
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('فك الحظر فشل: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => '❌ حدث خطأ أثناء فك الحظر: ' . $e->getMessage()
            ], 500);
        }
    }

    // ############################### الحسابات ###############################
    public function createLoginUser(TelegramUser $user)
    {
        // 1. التحقق من رقم الهوية
        if (!$user->national_id) {
            return redirect()->back()->with('error', '❌ لا يمكن إنشاء حساب: رقم الهوية غير مسجل.');
        }

        // 2. التحقق من عدم وجود حساب سابق (عبر العلاقة أو رقم الهوية)
        $exists = User::where('telegram_user_id', $user->id)
                    ->orWhere('email', $user->national_id . '@system.com') // افتراضي إذا كان الـ email مطلوباً
                    ->exists();

        if ($exists) {
            return redirect()->back()->with('error', '⚠️ يوجد حساب مسجل بالفعل لهذا الرقم.');
        }

        try {
            // 3. إنشاء الحساب
            $newUser = User::create([
                'name'             => $user->full_name,
                'email'            => $user->national_id . '@system.com', // أو username إذا أضفته للجدول
                'password'         => Hash::make($user->national_id),
                'telegram_user_id' => $user->id,
                'created_by'       => auth()->id(),
            ]);

            return redirect()->back()->with('success', '✅ تم إنشاء الحساب بنجاح.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ فشل إنشاء الحساب: ' . $e->getMessage());
        }
    }

// داخل AdminTelegramController
public function markAttendance(TelegramUser $user)
{
    $eventId = 92; // فعالية السفارة
    $event = Event::findOrFail($eventId);
    $authUser = Auth::user();

    // تحضير الرسالة
    $message = "الاسم: {$user->full_name}\n";
    $message .= "رقم الهوية: {$user->national_id}\n";
    $message .= "رقم الكشف: {$event->id}\n";
    $message .= "تاريخ اليوم: {$event->event_date}\n";
    $message .= "\n✅ تم تسجيل حضوركم في فعالية السفارة.\n";
    $message .= "المرسل: {$authUser->title}\n";

    $dbMessage = strip_tags($message);

    // تسجيل الرسالة في الداتابيز
    $log = MessageLog::create([
        'telegram_user_id' => $user->id,
        'event_id' => $event->id,
        'sent_by' => $authUser->id,
        'sender_type' => $authUser->name,
        'message' => $dbMessage,
        'message_type' => 'attended',
        'delivery_status' => 'pending',
    ]);

    // إذا كان لديه chat_id أرسل الرسالة
    if (!empty($user->chat_id)) {
        $response = $this->sendBusinessMessage($user, $message);
        if ($response->successful()) {
            $log->update([
                'delivery_status' => 'sent',
                'sent_at' => now(),
            ]);
            return response()->json(['status' => 'success', 'message' => 'تم تسجيل الحضور وإرسال الرسالة بنجاح']);
        } else {
            $log->update(['delivery_status' => 'failed']);
            return response()->json(['status' => 'error', 'message' => 'فشل إرسال الرسالة عبر تيليجرام']);
        }
    }

    // إذا لم يكن لديه chat_id، سجل فقط
    $log->update(['delivery_status' => 'manual', 'sent_at' => now()]);
    return response()->json(['status' => 'success', 'message' => 'تم تسجيل الحضور بدون إرسال تيليجرام']);
}


// public function exportOrphans()
// {
//     $data = TelegramUser::where('target_case', 'الأيتام')->get();

//     return Excel::download(new class($data) implements 
//         \Maatwebsite\Excel\Concerns\FromCollection,
//         \Maatwebsite\Excel\Concerns\WithHeadings {

//         private $data;

//         public function __construct($data)
//         {
//             $this->data = $data;
//         }

//         public function collection()
//         {
//             return $this->data->map(function ($item) {
//                 return [
//                     $item->full_name,
//                     $item->national_id,
//                     $item->egypt_number,
//                     $item->governorate,
//                     $item->city,
//                 ];
//             });
//         }

//         public function headings(): array
//         {
//             return [
//                 'الاسم',
//                 'رقم الهوية',
//                 'رقم الهاتف',
//                 'المحافظة',
//                 'المدينة',
//             ];
//         }

//     }, 'orphans.xlsx');
// }

/**
 * 🗑️ عرض المستخدمين المحذوفين (Recycle Bin)
 */
public function trashed(Request $request)
{
    // التحقق من الصلاحيات
    if (!auth()->user()->hasRole('superadmin')) {
        abort(403, 'ليس لديك صلاحية الوصول لهذه الصفحة');
    }

    $query = TelegramUser::onlyTrashed()
        ->with(['deleter', 'category']);

    // 🔍 البحث
    if ($request->filled('search')) {
        $search = trim($request->search);
        
        $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('national_id', 'like', "%{$search}%")
              ->orWhere('telegram_number', 'like', "%{$search}%")
              ->orWhere('egypt_number', 'like', "%{$search}%")
              ->orWhere('chat_id', 'like', "%{$search}%");
        });
    }

    // 📊 إحصائيات
    $totalTrashed = TelegramUser::onlyTrashed()->count();
    $trashedToday = TelegramUser::onlyTrashed()
        ->whereDate('deleted_at', today())
        ->count();
    $trashedThisWeek = TelegramUser::onlyTrashed()
        ->whereBetween('deleted_at', [now()->startOfWeek(), now()->endOfWeek()])
        ->count();
    $trashedThisMonth = TelegramUser::onlyTrashed()
        ->whereMonth('deleted_at', now()->month)
        ->whereYear('deleted_at', now()->year)
        ->count();

    $users = $query->orderBy('deleted_at', 'desc')->paginate(25)->withQueryString();

    return view('dashboard.telegram.trashed', compact(
        'users',
        'totalTrashed',
        'trashedToday',
        'trashedThisWeek',
        'trashedThisMonth'
    ));
}

/**
 * ♻️ استعادة مستخدم محذوف
 */
public function restore($id)
{
    // التحقق من الصلاحيات
    if (!auth()->user()->hasRole('superadmin')) {
        return response()->json([
            'status' => 'error',
            'message' => '❌ ليس لديك صلاحية لاستعادة المستخدمين'
        ], 403);
    }

    try {
        $user = TelegramUser::onlyTrashed()->find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ المستخدم غير موجود في المحذوفين'
            ], 404);
        }

        $userName = $user->full_name ?? $user->first_name ?? 'المستخدم';

        // استعادة المستخدم
        $user->restore();

        // مسح deleted_by
        $user->deleted_by = null;
        $user->saveQuietly();

        \Log::info('User restored successfully', [
            'user_id' => $id,
            'restored_by' => auth()->id(),
            'user_name' => $userName
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "✅ تم استعادة المستخدم \"{$userName}\" بنجاح"
        ]);

    } catch (\Throwable $e) {
        \Log::error('فشل استعادة المستخدم', [
            'user_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => '❌ حدث خطأ أثناء الاستعادة: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * 💥 حذف نهائي (Force Delete) - لا يمكن التراجع
 */
public function forceDelete($id)
{
    // التحقق من الصلاحيات
    if (!auth()->user()->hasRole('superadmin')) {
        return response()->json([
            'status' => 'error',
            'message' => '❌ ليس لديك صلاحية للحذف النهائي'
        ], 403);
    }

    try {
        $user = TelegramUser::onlyTrashed()->find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ المستخدم غير موجود في المحذوفين'
            ], 404);
        }

        $userName = $user->full_name ?? $user->first_name ?? 'المستخدم';

        DB::beginTransaction();
        try {
            // حذف العلاقات المرتبطة
            $user->familyMembers()->forceDelete();
            $user->notes()->forceDelete();
            $user->categories()->detach();
            
            // حذف حساب المستخدم إذا وجد
            if ($user->user) {
                $user->user->forceDelete();
            }

            // الحذف النهائي
            $user->forceDelete();

            DB::commit();

            \Log::warning('User force deleted', [
                'user_id' => $id,
                'user_name' => $userName,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "💥 تم حذف المستخدم \"{$userName}\" نهائياً"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    } catch (\Throwable $e) {
        \Log::error('فشل الحذف النهائي', [
            'user_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => '❌ حدث خطأ: ' . $e->getMessage()
        ], 500);
    }
}
}
