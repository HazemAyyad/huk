<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\MessageLog;
use App\Models\TaskAssignment;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Auth;
use App\Imports\BenefitsImport;
use Maatwebsite\Excel\Facades\Excel;

class AdminEventController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin|adminorphans|adminamputation|admin']);
    }

    // عرض جميع الفعاليات
    public function index()
    {
        $events = Event::orderBy('event_date', 'desc')->get();
        return view('dashboard.events.index', compact('events'));
    }

    // إنشاء فاعلية جديدة
    public function create()
    {
        return view('dashboard.events.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'aid_type' => 'required|in:in_kind,cash,other',
            'value_type' => 'required|in:number,text',
            'value_number' => 'nullable|numeric',
            'value_text' => 'nullable|string|max:255',
            'extras' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'default_message' => 'required|string',
            'invitation_message' => 'required|string',
            'is_archived' => 'nullable|boolean',
            'is_fixed' => 'nullable|boolean',
            'without_notification' => 'nullable|boolean',
        ]);

        Event::create([
            'name' => $request->name,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'aid_type' => $request->aid_type,
            'value_type' => $request->value_type,
            'value' => $request->value_type == 'number' ? $request->value_number : null,
            'value_text' => $request->value_type == 'text' ? $request->value_text : null,
            'extras' => $request->extras,
            'location' => $request->location,
            'default_message' => $request->default_message,
            'invitation_message' => $request->invitation_message,
            'is_archived' => $request->has('is_archived'),
            'is_fixed' => $request->has('is_fixed'),
            'without_notification' => $request->has('without_notification'),
        ]);

        return redirect()->route('events.index')
            ->with('success', '✅ تم إضافة الفاعلية بنجاح');
    }

    // تعديل فاعلية
    public function edit($id)
    {
        $event = Event::findOrFail($id);
        return view('dashboard.events.edit', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'aid_type' => 'required|in:in_kind,cash,other',
            'value_type' => 'required|in:number,text',
            'value_number' => 'nullable|numeric',
            'value_text' => 'nullable|string|max:255',
            'extras' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'default_message' => 'required|string',
            'invitation_message' => 'required|string',
            'is_archived' => 'nullable|boolean',
            'is_fixed' => 'nullable|boolean',
            'without_notification' => 'nullable|boolean',
        ]);

        $event->update([
            'name' => $request->name,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'aid_type' => $request->aid_type,
            'value_type' => $request->value_type,
            'value' => $request->value_type == 'number' ? $request->value_number : null,
            'value_text' => $request->value_type == 'text' ? $request->value_text : null,
            'extras' => $request->extras,
            'location' => $request->location,
            'default_message' => $request->default_message,
            'invitation_message' => $request->invitation_message,
            'is_archived' => $request->has('is_archived'),
            'is_fixed' => $request->has('is_fixed'),
            'without_notification' => $request->has('without_notification'),
        ]);

        return redirect()->route('events.index')
            ->with('success', '🔄 تم تحديث الفاعلية بنجاح');
    }

private function normalizeArabic($text)
{
    $text = trim($text);

    $search  = ['أ','إ','آ','ة','ى','ئ','ي'];
    $replace = ['ا','ا','ا','ه','ي','ي','ي'];

    return str_replace($search, $replace, $text);
}


    // تقارير الرسائل
    public function reports(Request $request)
{
    $events = Event::orderBy('event_date', 'desc')->get();
    $logs = collect();

    if ($request->filled('event_id')) {

        $messageType = $request->message_type;

        $query = MessageLog::with(['user','event'])
            ->where('event_id', $request->event_id);

        // 🔍 البحث الذكي
        if ($request->filled('search')) {

            $search = trim($request->search);
            $words = array_filter(explode(' ', $search));
            $normalizedSearch = $this->normalizeArabic($search);

            $query->whereHas('user', function ($q) use ($search, $words, $normalizedSearch) {

                $q->where(function ($mainQ) use ($search, $words, $normalizedSearch) {

                    // 🔥 بحث بالجملة
                    $mainQ->whereRaw("
                        REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(full_name,
                        'أ','ا'),'إ','ا'),'آ','ا'),'ة','ه'),'ى','ي'),'ئ','ي')
                        LIKE ?
                    ", ["%{$normalizedSearch}%"]);

                    // 🔥 AND لكل كلمة
                    $mainQ->orWhere(function ($subQ) use ($words) {
                        foreach ($words as $word) {
                            $normalizedWord = $this->normalizeArabic($word);

                            $subQ->whereRaw("
                                REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(full_name,
                                'أ','ا'),'إ','ا'),'آ','ا'),'ة','ه'),'ى','ي'),'ئ','ي')
                                LIKE ?
                            ", ["%{$normalizedWord}%"]);
                        }
                    });

                    // باقي الحقول
                    $mainQ->orWhere('national_id', 'like', "%{$search}%")
                          ->orWhere('egypt_number', 'like', "%{$search}%")
                          ->orWhere('whatsapp', 'like', "%{$search}%")
                          ->orWhere('telegram_number', 'like', "%{$search}%")
                          ->orWhere('address', 'like', "%{$search}%");
                });

            });
        }

        // 🎯 الفلترة الأصلية (كما هي)
        if ($messageType == 'received') {

            $query->where('message_type', 'received')
                  ->whereIn('delivery_status', ['sent','manual']);

        } elseif ($messageType == 'invitation') {

            $query->where('message_type', 'invitation')
                  ->whereIn('delivery_status', ['sent','manual'])
                  ->whereNull('excused_at');
            
        } elseif ($messageType == 'attended') {
            $query->whereNotNull('attended_at');
        }

        elseif ($messageType == 'excused') {
            $query->whereNotNull('excused_at');

        } elseif ($messageType == 'not_attended') {

            $query->where('message_type', 'invitation')
                ->whereIn('delivery_status', ['sent','manual'])
                ->whereNull('excused_at')
                ->whereNotIn('telegram_user_id', function ($sub) use ($request) {

                    $sub->select('telegram_user_id')
                        ->from('message_logs')
                        ->where('event_id', $request->event_id)
                        ->whereNotNull('attended_at');

                });
        }
        elseif ($messageType == 'not_received') {

            $query->where('message_type', 'invitation')
                ->whereIn('delivery_status', ['sent','manual'])
                ->whereNull('excused_at')
                ->whereNotIn('telegram_user_id', function ($sub) use ($request) {

                    $sub->select('telegram_user_id')
                        ->from('message_logs')
                        ->where('event_id', $request->event_id)
                        ->where('message_type', 'received')
                        ->whereIn('delivery_status', ['sent','manual']);

                });
        }

        $logs = $query->get();
    }

    return view('dashboard.events.event-distribution', compact('events', 'logs'));
}


    // عرض نموذج توزيع المهام (فئة الإدارة فقط)
    public function taskDistributionForm($event_id)
    {
        $event = Event::findOrFail($event_id);

        $users = TelegramUser::whereHas('category.caseProfile', function ($q) {
            $q->where('key', 'Administration');
        })
        ->orderBy('full_name')
        ->get();

        $tasks = TaskAssignment::where('event_id', $event_id)
            ->with('telegramUser')
            ->get();

        return view('dashboard.events.task-distribution', compact('event', 'users', 'tasks'));
    }

    // حفظ توزيع المهام
    public function storeTaskDistribution(Request $request, $event_id)
{
    // جلب الفعالية أولاً
    $event = Event::findOrFail($event_id);

    // حذف المهام القديمة
    TaskAssignment::where('event_id', $event_id)->delete();

    $eventMode = $request->input('event_mode');
    $eventName = $request->input('manual_event_name');
    $eventDate = $request->input('manual_event_date');
    $eventTime = $request->input('manual_event_time');
    $eventLocation = $request->input('manual_event_location');

    if ($eventMode == 'manual') {
        $event->update([
            'name' => $eventName,
            'event_date' => $eventDate,
            'event_time' => $eventTime,
            'location' => $eventLocation,
        ]);
    }

    if ($request->has('assignments')) {
        foreach ($request->assignments as $assignment) {
            if (empty($assignment['telegram_user_id'])) { // استخدم user_id وليس telegram_user_id كما في الفورم
                continue;
            }

            TaskAssignment::create([
                'event_id' => $event_id,
                'telegram_user_id' => $assignment['telegram_user_id'], // هنا المهم
                'role' => $assignment['role'],
                'status' => $assignment['status'] ?? null
            ]);
        }
    }

    return redirect()
        ->route('events.task-distribution', $event_id)
        ->with('success', 'تم حفظ توزيع المهام');
}

    // عرض المهام لفئة الإدارة
    public function tasks($id)
    {
        $event = Event::findOrFail($id);

        $users = TelegramUser::whereHas('category.caseProfile', function ($q) {
            $q->where('key', 'Administration');
        })
        ->orderBy('full_name')
        ->get();

        $tasks = TaskAssignment::where('event_id', $event->id)
            ->with('telegramUser')
            ->get();

        return view('dashboard.events.task-distribution', compact('event', 'users', 'tasks'));
    }
    
    // المعتذر
    public function markExcused($id)
{
    $log = MessageLog::findOrFail($id);

    if ($log->excused_at) {
        return back()->with('error', 'تم تسجيل الاعتذار مسبقاً');
    }

    $log->update([
        'excused_at' => now(),
        'excused_by' => Auth::id(),
        'attended_at' => null,
        'attended_by' => null
    ]);

    return back()->with('success', '📌 تم تسجيل معتذر');
}

public function fixedEvents()
{
    $events = Event::where('is_fixed', true)
        ->orderBy('id', 'desc')
        ->get();

    return view('dashboard.events.fixed', compact('events'));
}



public function importBenefits(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls',
        'event_id' => 'required|exists:events,id',
        'message' => 'required|string',
    ]);

    $import = new BenefitsImport(
        $request->event_id,
        $request->message
    );

    Excel::import($import, $request->file('file'));

    $notFound = $import->getNotFound();

    return back()->with([
        'success' => '✅ تم تسجيل الاستفادة بنجاح',
        'not_found' => $notFound
    ]);
}
public function showImport(Event $event)
{
    return view('dashboard.events.import', compact('event'));
}
public function handleImport(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls',
        'event_id' => 'required|exists:events,id',
    ]);

    $event = Event::findOrFail($request->event_id);

    $notFound = [];
    $successCount = 0;

    $rows = Excel::toCollection(null, $request->file('file'))->first();

    foreach ($rows as $index => $row) {

        if ($index == 0) continue;

        $nationalId = trim($row[0]);

        if (!$nationalId) continue;

        $user = TelegramUser::where('national_id', $nationalId)->first();

        if (!$user) {
            $notFound[] = $nationalId;
            continue;
        }

        // ❌ إذا الفعالية مؤرشفة لا تحسب
        if ($event->is_archived) {
            continue;
        }

        MessageLog::create([
            'telegram_user_id' => $user->id,
            'event_id'         => $event->id,
            'sent_by'          => auth()->id(),
            'sender_type'      => auth()->user()->name,
            'message'          => 'تم تسجيل الاستفادة عبر رفع كشف',
            'message_type'     => 'received',
            'delivery_status'  => 'manual',
            'sent_at'          => now(),
        ]);

        $user->increment('benefit_count');

        $successCount++;
    }

    return back()->with([
        'success_count' => $successCount,
        'not_found' => $notFound
    ]);
}
}