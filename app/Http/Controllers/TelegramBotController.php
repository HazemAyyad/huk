<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TelegramBotController extends Controller
{
    private $botToken;
    private $webhookSecret;
    private $orphansGroupId;
    private $amputationGroupId;
    private $medicalGroupId;
    private $blockedGroupId;

    const MAX_ERRORS = 6;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->webhookSecret = env('TELEGRAM_WEBHOOK_SECRET');
        $this->orphansGroupId = env('TELEGRAM_GROUP_ORPHANS_ID');
        $this->amputationGroupId = env('TELEGRAM_GROUP_AMPUTATION_ID');
        $this->medicalGroupId = env('TELEGRAM_GROUP_MEDICAL_ID');
        $this->blockedGroupId = env('TELEGRAM_GROUP_BLOCKED_ID');
    }

    // =====================
    // Webhook
    // =====================
    public function webhook(Request $request, $secret)
    {
        $hasLock = false;
        Log::info('🔥 WEBHOOK HIT 🔥');
        try {
            Log::info('STEP 1 passed secret check');
            if ($secret !== $this->webhookSecret) {
                Log::warning('Invalid Telegram webhook URL secret', [
                    'received' => $secret,
                ]);
                return response()->json(['status' => 'forbidden'], 403);
            }

            if (!$request->isMethod('post')) {
                return response()->json(['status' => 'method_not_allowed'], 405);
            }

            // 🔓 فك أي Lock عالق أقدم من دقيقتين
            TelegramUser::where('is_processing', true)
                ->where('updated_at', '<', now()->subMinutes(2))
                ->update(['is_processing' => false]);


            $data = $request->all();
            Log::info('Telegram webhook received', $data);

            // تجاهل أي رسالة ليست Business
            // ✅ دعم الرسائل العادية + business
            if (isset($data['business_message'])) {

                // ✅ هذا هو المسار الصحيح (Business)
                $message = $data['business_message'];
                $businessConnectionId = $message['business_connection_id'] ?? null;

            } elseif (isset($data['message'])) {

                // 🚫 أي دخول من البوت الأساسي → تحويل فقط
                $chatId = $data['message']['chat']['id'];

                $this->sendMessage(
                    $chatId,
                    "📌 يرجى إرسال البيانات داخل المحادثة عبر الرابط التالي:\n\nhttps://t.me/hakinsan"
                );

                return response()->json(['status' => 'redirected_to_business_chat']);

            } else {
                return response()->json(['status' => 'ignored']);
            }

            if (!isset($message['chat']['id'])) {
                return response()->json(['status' => 'ignored']);
            }

            if (!isset($message['from']) || $message['from']['is_bot']) {
                return response()->json(['status' => 'ignored_bot']);
            }


            $chatId = $message['chat']['id'];
            $text = trim($message['text'] ?? '');
            $text = $this->normalizeNumbers($text);
            // إزالة أي مسافات أو رموز غير ضرورية، مع السماح بالـ +
            $normalizedText = preg_replace('/[^\d+]/', '', $text);

            if ($text === '') {
                return response()->json(['status' => 'ok']);
            }

            // جلب أو إنشاء المستخدم
            $firstName = $message['chat']['first_name'] ?? '';
            $lastName  = $message['chat']['last_name'] ?? '';

            $fullTelegramName = trim($firstName . ' ' . $lastName);

            $user = TelegramUser::where('chat_id', $chatId)->first();

            if (!$user) {
                $user = TelegramUser::create([
                    'chat_id' => $chatId,
                    'username' => $message['chat']['username'] ?? null,
                    'first_name' => $firstName,
                    'full_name' => $fullTelegramName ?: null,

                    'source' => 'bot',
                    'step' => null,
                    'is_completed' => false,
                    'is_blocked' => false,
                ]);
            }

            if (!$user->full_name && $fullTelegramName) {
                $user->full_name = $fullTelegramName;
                $user->save();
            }


            // تحديث business_connection_id إذا تم إرساله
            if ($businessConnectionId && $user->business_connection_id !== $businessConnectionId) {
                $user->business_connection_id = $businessConnectionId;
                $user->save();
            }

            // إذا المستخدم محظور
            if ($user->is_blocked) {
                return response()->json(['status' => 'ok']); // لا نرد أبداً
            }

            // =====================
            // POST-COMPLETION MESSAGE CONTROL
            // =====================
            if ($user->step === 'done') {

                // ✅ إذا عنده نقص بالبيانات الجديدة → رجعه يكمل
                if (
                    is_null($user->entry_to_egypt) ||
                    $user->rent_amount === null ||
                    ($user->rent_amount > 0 && is_null($user->landlord_phone))
                ) {

                    // ❗ مهم جداً: ما نزيد عداد الإزعاج
                    $user->step = 'entry_to_egypt';
                    $user->is_completed = false;
                    $user->after_completed_messages_count = 0; // تصفير
                    $user->save();

                    // إذا أول مرة يدخل على الاستكمال
                    if ($user->step === 'entry_to_egypt') {
                        $this->sendMessage(
                            $chatId,
                            "📌 تم إضافة بيانات جديدة.\n\n📅 أدخل *تاريخ دخول مصر* (مثال: 2024-05-01):",
                            $businessConnectionId
                        );
                    }

                    return response()->json(['status' => 'ok']);
                }

                // =============================
                // 👇 المستخدم مكتمل 100% (قديم + جديد)
                // =============================

                $user->after_completed_messages_count =
                    ($user->after_completed_messages_count ?? 0) + 1;
                $user->save();

                // أول رسالة
                if ($user->after_completed_messages_count === 1) {
                    $this->sendMessage(
                        $chatId,
                        $this->alreadyContactedMessage(),
                        $businessConnectionId
                    );
                    return response()->json(['status' => 'ok']);
                }

                // تحذير
                if ($user->after_completed_messages_count === 2) {
                    $this->sendMessage(
                        $chatId,
                        "⚠️ نرجو عدم إرسال رسائل أخرى غير مطلوبة حتى لا يتم تشتيت فريق الدعم أثناء مراجعة الطلب.\n\n"
                        ."📌 في حال وجود أي تحديث سيتم التواصل معك مباشرة.",
                        $businessConnectionId
                    );
                    return response()->json(['status' => 'ok']);
                }

                // تجاهل
                if ($user->after_completed_messages_count >= 3) {
                    return response()->json(['status' => 'ok']);
                }

                return response()->json(['status' => 'ok']);
            }
            // إذا المستخدم قيد المعالجة حالياً
            $userToLock = TelegramUser::where('chat_id', $chatId)
                ->where('is_processing', false)
                ->first();

            if (!$userToLock) {
                return response()->json(['status' => 'ok']);
            }

            // بدلاً من save() الذي قد يسبب مشاكل مع updated_at
            TelegramUser::where('id', $userToLock->id)
                ->update(['is_processing' => true]);

            $hasLock = true;


            // إذا انتهى التسجيل
            if ($user->step === 'done' || $user->is_completed) {
                $this->sendMessage($chatId, $this->alreadyContactedMessage(), $businessConnectionId);

                return response()->json(['status' => 'ok']);
            }

            // =====================
            // مراحل التسجيل
            // =====================
            switch ($user->step) {
                case null: // البداية
                    $this->sendMessage(
                        $chatId,
                        "👋 مرحبًا بك في *جمعية حق الإنسانية*!\n\n"
                        ."من فضلك أدخل *رقم الهوية الفلسطينية* للحالة المستهدفة، بشكل دقيق (أرقام فقط)."
                        ."\n\n✍️ مثال: 987654321",
                        $businessConnectionId
                    );
                    $user->step = 'check_national_id';
                    $user->save();
                    break;

                case 'check_national_id':
                    // ❌ إذا الهوية موجودة كمرافق مسبقًا → حظر مباشر
                    if (\App\Models\FamilyMember::where('national_id', $normalizedText)->exists()) {

                        $user->is_blocked = true;
                        $user->blocked_reason = 'national_id_registered_as_family_member';
                        $user->save();
                        $this->sendBlockedUserToGroup($user);
                        $this->sendMessage(
                            $chatId,
                            "❌ *هذه الهوية مسجلة مسبقًا كمرافق في طلب آخر*.\n\n"
                            ."تم إيقاف هذا الحساب تلقائيًا لمنع التسجيل المزدوج.\n\n"
                            ."📌 إذا كنت تعتقد أن هذا خطأ، يرجى ترك رسالة واحدة لشرح المشكلة وسيتم مراجعتها.",
                            $businessConnectionId
                        );

                        return response()->json(['status' => 'ok']);
                    }

                    if (!preg_match('/^(9|8|7|4)\d{8}$/', $text)) {

                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ *رقم الهوية غير صالح*. حاول مجددًا \n يرجى ادخال رقم الهوية فقط دون حروف او اي تفاصيل اخرى \n الرقم يجب ان يكون كما في الهوية مكون من 9 ارقام.:"
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    $text = trim($text);
                    $normalizedText = $this->normalizeNumbers($text);
                    // ❌ هوية مسجلة مسبقًا عند مستخدم بوت
                    $botUserWithSameId = TelegramUser::where('national_id', $normalizedText)
                        ->whereNotNull('chat_id')
                        ->where('chat_id', '!=', $chatId)
                        ->first();


                    if ($botUserWithSameId) {
                        $user->is_blocked = true;
                        $user->blocked_reason = 'duplicate_national_id_bot';
                        $user->save();

                        $this->sendBlockedUserToGroup($user);

                        $this->sendMessage(
                            $chatId,
                            "❌ *هذه الهوية مسجلة مسبقًا في النظام*.\n\n"
                            ."📌 إذا كنت تعتقد أن هذا حدث عن طريق الخطأ، يرجى ترك رسالة واحدة توضح المشكلة وسيتم مراجعتها.",
                            $businessConnectionId
                        );

                        return response()->json(['status' => 'ok']);
                    }

                    $existingUser = TelegramUser::where('national_id', $normalizedText)
                        ->whereNull('chat_id')
                        ->where('source', 'system')
                        ->first();

                    if ($existingUser) {

                        // ✅ مستخدم قادم من السيستم
                        if ($existingUser->source === 'system' && $existingUser->chat_id === null) {

                            // 🧹 احذف المستخدم المؤقت أولاً
                            $user->delete();

                            // 🔗 الآن اربط المستخدم الحقيقي بأمان
                            $existingUser->chat_id = $chatId;
                            $existingUser->username = $message['chat']['username'] ?? null;
                            $existingUser->business_connection_id = $businessConnectionId;
                            $existingUser->source = 'bot';
                            $existingUser->national_id = $normalizedText;
                            $existingUser->is_blocked = false;
                            $existingUser->spam_count = 0;

                            if ($existingUser->is_completed) {

                                $existingUser->step = 'done';
                                $existingUser->save();

                                $this->sendMessage(
                                    $chatId,
                                    "✅ تم ربط حسابك بنجاح.\n\n📌 بياناتك مكتملة مسبقًا، وسيتم التواصل معك عند الحاجة.",
                                    $businessConnectionId
                                );

                                return response()->json(['status' => 'ok']);
                            }

                            // غير مكتمل
                            $existingUser->step = 'name';
                            $existingUser->save();

                            $this->sendMessage(
                                $chatId,
                                "👋 أهلاً بك مجدداً!\n\nنرجو ارسال *الاسم رباعي* حروف فقط من غير أي رقم.",
                                $businessConnectionId
                            );

                            return response()->json(['status' => 'ok']);
                        }

                        // ❌ تزوير حقيقي
                        $user->is_blocked = true;
                        $user->blocked_reason = 'duplicate_national_id';
                        $user->save();

                        $this->sendBlockedUserToGroup($user);

                        $this->sendMessage(
                            $chatId,
                            "❌ هذه الهوية مسجلة مسبقًا. إذا كنت تعتقد أن هذا حدث بشكل خاطئ، يرجى ترك رسالة واحدة شارحة المشكلة وسيم مراجعة الطلب باقرب وقت ممكن.",
                            $businessConnectionId
                        );

                        return response()->json(['status' => 'ok']);
                    }

                    // 👇👇👇 لو وصلنا هون → هوية جديدة (نجاح)
                    $user->spam_count = 0;
                    $user->national_id = $normalizedText;
                    $user->step = 'name';
                    $user->save();

                    $this->sendMessage(
                        $chatId,
                        "✅ رقم الهوية مقبول.\n\n👋 برجاء إدخال *الاسم الرباعي  للحالة المستهدفة\n ارسل الاسم رباعي من دون اي اضافة اخرى مباشرة\n مثال: احمد محمد احمد محمد.*:",
                        $businessConnectionId
                    );
                    break;

                case 'name':

                    $words = preg_split('/\s+/', trim($text));

                    if (
                        count($words) < 4 || count($words) > 6 ||
                        preg_match('/[0-9]/', $text) ||
                        preg_match('/[^\x{0621}-\x{064A}\s]/u', $text)
                    ) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "⚠️ الاسم غير صالح. يرجى إدخال *الاسم رباعي دون ارقام او اكثر من رباعي*."
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    // ✅ نجاح
                    $user->spam_count = 0;
                    $user->full_name = $text;
                    $user->step = 'case_description';
                    $user->save();

                    $this->sendMessage(
                        $chatId,
                        "📝 *شرح الحالة بالكامل*\n\nيرجى كتابة شرح كامل للحالة بالتفصيل، .\n\nمثال:\nعمر صاحب الطلب وعدد أفراد الأسرة، وضع الأسرة، سبب الحاجة, سبب دخول مصر وكيفية الدخول، وأي تفاصيل مهمة.",
                        $businessConnectionId
                    );
                    break; 

                case 'case_description':

                    if (mb_strlen(trim($text)) < 20) {

                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ يرجى كتابة شرح مفصل للحالة (20 حرفًا على الأقل)."
                        )) {
                            return response()->json(['status' => 'ok']);
                        }

                        break;
                    }

                    $user->companions = trim($text);
                    $user->spam_count = 0;
                    $user->step = 'whatsapp';
                    $user->save();

                    $this->sendMessage(
                        $chatId,
                        "📱 برجاء إرسال *رقم الواتساب لصاحب الطلب*:",
                        $businessConnectionId
                    );

                    break;

                case 'whatsapp':

                    if ($text !== $normalizedText) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ رقم الهاتف للواتساب يجب أن يحتوي على أرقام فقط."
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    if (!preg_match('/^\+?\d{10,15}$/', $normalizedText)) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ رقم الواتساب غير صحيح\nيرجى إدخال رقم فلسطيني أو مصري صحيح"
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    $user->whatsapp = $normalizedText; // ✅ الحفظ الصحيح
                    $user->spam_count = 0;
                    $user->step = 'telegram';
                    $user->save();

                    $this->sendMessage($chatId, "📨 برجاء ارسال *رقم التيليجرام*:", $businessConnectionId);
                    break;

                case 'telegram':

                    if ($text !== $normalizedText) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ رقم تيليجرام يجب أن يحتوي على أرقام فقط."
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    if (!preg_match('/^\+?\d{10,15}$/', $normalizedText)) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ رقم تيليجرام غير صالح"
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    $user->telegram_number = $normalizedText; // ✅
                    $user->spam_count = 0;
                    $user->step = 'egypt_number';
                    $user->save();

                    $this->sendMessage($chatId, "🇪🇬 برجاء ارسال *رقم الهاتف المصري* 11 رقم:", $businessConnectionId);
                    break;


                case 'egypt_number':

                    if ($text !== $normalizedText) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ رقم الهاتف يجب أن يكون مصري ومكون من 11 رقم يحتوي على أرقام فقط."
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    if (!preg_match('/^(010|011|012|015)\d{8}$/', $normalizedText)) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ رقم الهاتف المصري غير صالح! الرجاء إدخال رقم مصري صحيح"
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    $user->egypt_number = $normalizedText; // ✅
                    $user->spam_count = 0;
                    $user->step = 'family';
                    $user->save();
                    $this->sendMessage($chatId,
                    "👨‍👩‍👧‍👦 يرجى إرسال *عدد أفراد الأسرة*\n(أنت صاحب الطلب + جميع المرافقين الموجودين داخل مصر فقط):\n\n"
                    . "• سيطلب منك البرنامج لاحقاً إدخال بيانات وصور جوازات سفر كل فرد لتأكيد تواجدهم.\n"
                    . "• يرجى التأكد من العدد لتجنب إلغاء الطلب.\n\n"
                    . "✍️ *طريقة الإرسال:* أرسل الرقم فقط مثال: 4"
                    ,
                    $businessConnectionId);
                    break;

                case 'family':

                    if (!preg_match('/^\d+$/', $text)) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ يرجى إرسال عدد افراد الأسرة رقم فقط مثال: 4"
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    $familyCount = (int)$text;

                    if ($familyCount < 1 || $familyCount > 20) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ عدد أفراد الأسرة يجب أن يكون بين 1 و 20"
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    // ✅ نجاح
                    $user->spam_count = 0;
                    $user->family_members = $familyCount;
                    $user->save();

                    if ($familyCount === 1) {
                        // لا مرافقين
                        $user->step = 'governorate';
                        $user->save();

                        $msg = "📍 *برجاء اختيار المحافظة*:\n\n";
                        foreach ($this->governorates() as $num => $gov) {
                            $msg .= "{$num}. {$gov}\n";
                        }
                        $msg .= "\n✍️ مثال: 1 أو القاهرة";

                        $this->sendMessage($chatId, $msg, $businessConnectionId);

                    } else {
                        // يوجد مرافقين
                        $user->current_family_member_index = 1;
                        $user->step = 'family_member_name';
                        $user->save();

                        $this->sendMessage(
                            $chatId,
                            "👤 يرجى إدخال *الاسم رباعي للمرافق* رقم 1\n⚠️ ملاحظة:\n● لا تقم بإدراج قائمة بجميع المرافقين هنا.\n● لا تكرر اسم صاحب الطلب.\nفقط ارسل اسم رباعي للمرافق الاول\n● هكذا: احمد محمد محمود احمد",
                            $businessConnectionId
                        );
                    }

                    break;

                case 'family_member_name':

                    $name = trim($text);
                    $words = preg_split('/\s+/', $name);

                    if (
                        count($words) < 4 || count($words) > 6 ||
                        preg_match('/[0-9]/', $name) ||
                        preg_match('/[^\x{0621}-\x{064A}\s]/u', $name)
                    ) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ اسم المرافق غير صالح\n يرجى ادخال الاسم رباعي لشخص واحد فقط من المرافقين."
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    if ($name === $user->full_name) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "⚠️ لا يجوز تكرار اسم صاحب الطلب أدخل اسم المرافق الآخر"
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    // ✅ نجاح
                    $user->spam_count = 0;
                    $user->last_family_member_name = $name;
                    $user->step = 'family_member_national_id';
                    $user->save();

                    $this->sendMessage(
                        $chatId,
                        "🆔 برجاء إدخال *رقم هوية المرافق رقم {$user->current_family_member_index} الذي ارسلته للتو..*:",
                        $businessConnectionId
                    );
                    break;

                case 'family_member_national_id':

                    // ❌ تحقق من صحة الهوية
                    if (!preg_match('/^(9|8|7|4)\d{8}$/', $text)) {

                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ رقم الهوية غير صالح. حاول مجددًا\n  ادخل رقم هوية ارقام فقط للمرافق الذي ادخلته للتو رقم {$user->current_family_member_index}:"
                        )) {
                            return response()->json(['status' => 'ok']);
                        }

                        break;
                    }

                    // ❌ فحص التكرار (احتيال محتمل)
                    if (
                        \App\Models\TelegramUser::where('national_id', $text)->exists() ||
                        \App\Models\FamilyMember::where('national_id', $text)->exists()
                    ) {
                        $this->sendMessage(
                            $chatId,
                            "❌ رقم هوية المرافق مسجل مسبقًا.\n\n"
                            ."تم إيقاف هذا الحساب تلقائيًا لمنع أي تسجيل مزدوج.\n\n"
                            ."📌 إذا كنت تعتقد أن هذا حدث عن طريق الخطأ، يرجى ترك رسالة واحدة شارحة المشكلة وسيم مراجعة الطلب باقرب وقت ممكن.",
                            $businessConnectionId
                        );

                        // 🚫 حظر مباشر (هذا ليس Validation عادي)
                        $user->is_blocked = true;
                        $user->blocked_reason = 'Duplicate family member ID detected';
                        $user->save();

                        $this->sendBlockedUserToGroup($user);

                        return response()->json(['status' => 'ok']); // إيقاف أي معالجة أخرى
                    }

                    // ✅ نجاح → تصفير عداد الأخطاء
                    $user->spam_count = 0;
                    $user->save();

                    // حفظ المرافق في جدول family_members
                    \App\Models\FamilyMember::create([
                        'telegram_user_id' => $user->id,
                        'full_name' => $user->last_family_member_name,
                        'national_id' => $text,
                    ]);

                    if ($user->current_family_member_index >= $user->family_members - 1) {
                        // انتهينا من جميع المرافقين
                        $user->step = 'governorate';
                        $user->current_family_member_index = 0;
                        $user->last_family_member_name = null;
                        $user->save();

                        $msg = "✅ تم إدخال جميع المرافقين.\n\n📍 برجاء اختيار رقم المحافظة التي تسكن فيها الان:\n";
                        foreach ($this->governorates() as $num => $gov) {
                            $msg .= "{$num}. {$gov}\n";
                        }
                        $msg .= "\n✍️ مثال: 1 أو القاهرة";

                        $this->sendMessage($chatId, $msg, $businessConnectionId);

                    } else {
                        // الانتقال للمرافق التالي
                        $user->current_family_member_index += 1;
                        $user->step = 'family_member_name';
                        $user->last_family_member_name = null;
                        $user->save();

                        $this->sendMessage(
                            $chatId,
                            "👤 برجاء إدخال *الاسم رباعي للمرافق رقم {$user->current_family_member_index}*:",
                            $businessConnectionId
                        );
                    }

                    break;

                case 'governorate':

                    $selected = $this->resolveChoice($text, $this->governorates());

                    if (!$selected) {
                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ اختيار غير صحيح، يرجى اختيار رقم المحافظة من القائمة كما هي حرفيا او اختيار رقمها من القائمة وارسال رقم فقط \n مثال: 1 او القاهرة"
                        )) {
                            return response()->json(['status' => 'ok']);
                        }
                        break;
                    }

                    // ✅ نجاح
                    $user->spam_count = 0;
                    $user->governorate = $selected;
                    $user->step = 'city';
                    $user->save();

                    $this->sendMessage($chatId, "🏙️ برجاء ارسال *اسم المدينة التي تسكن فيها الان*:", $businessConnectionId);
                    break;



                case 'city':

                    // منع الأرقام والرموز في اسم المدينة
                    if (mb_strlen($text) < 2 || preg_match('/[0-9]/', $text)) {

                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ يرجى إدخال اسم مدينة صحيح (حروف فقط)."
                        )) {
                            return response()->json(['status' => 'ok']);
                        }

                        break;
                    }

                    // ✅ نجاح → تصفير العداد
                    $user->spam_count = 0;
                    $user->city = $text;
                    $user->step = 'address';
                    $user->save();

                    $this->sendMessage(
                        $chatId,
                        "🏠 برجاء ارسال *العنوان الكامل بالتفصيل*:",
                        $businessConnectionId
                    );
                    break;

                case 'address':

                    if (empty($text) || mb_strlen($text) < 5) {

                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ أدخل العنوان بالتفصيل (على الأقل 5 أحرف)."
                        )) {
                            return response()->json(['status' => 'ok']);
                        }

                        break;
                    }

                    // ✅ نجاح
                    $user->spam_count = 0;
                    $user->address = $text;
                    $user->step = 'benefited';
                    $user->save();

                    $this->sendMessage(
                        $chatId,
                        "❓ هل استفدت من المؤسسة من قبل؟\n (*اكتب نعم أو لا*):",
                        $businessConnectionId
                    );
                    break;

                case 'benefited':

                    $answer = trim($text);

                    if (!in_array($answer, ['نعم', 'لا'])) {

                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ يرجى كتابة *نعم* أو *لا* فقط."
                        )) {
                            return response()->json(['status' => 'ok']);
                        }

                        break;
                    }

                    // ✅ نجاح
                    $user->spam_count = 0;
                    $user->benefited_before = $answer === 'نعم' ? 1 : 0;
                    $user->step = 'case';
                    $user->save();

                    $this->sendMessage(
                        $chatId,
                        "🎯 *اختر الفئة المستهدفة*:\n"
                        ."1️⃣ الأيتام\n"
                        ."2️⃣ حالات البتر\n"
                        ."3️⃣ الحالات المرضية\n"
                        ."4️⃣ مساكن الجرحى والمرضى (العبور)\n"
                        ."5️⃣ فئات أخرى\n\n"
                        ."✍️ مثال: اذا كنت من فئة الايتام ارسل 1 او كلمة الأيتام",
                        $businessConnectionId
                    );
                    break;

                case 'case':

                    $selected = $this->resolveCaseFromText($text);

                    if (!$selected) {

                        $msg = "❌ اختيار غير صحيح\n\n🎯 يرجى كتابة رقم او اسم الفئة بشكل واضح:\n";
                        $msg .= "- الأيتام\n";
                        $msg .= "- حالات البتر\n";
                        $msg .= "- الحالات المرضية\n";
                        $msg .= "- مساكن الجرحى والمرضى (العبور)\n";
                        $msg .= "- فئات أخرى\n";

                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            $msg
                        )) {
                            return response()->json(['status' => 'ok']);
                        }

                        break;
                    }

                    // ✅ نجاح نهائي
                    $user->target_case = $selected;
                    $user->spam_count = 0;

                    if ($selected === 'حالات البتر') {

                        $user->step = 'amputation_type';
                        $user->save();

                        $this->sendMessage(
                            $chatId,
                            "🦿 ما هو *نوع البتر*؟\n\n"
                            ."مثال:\n"
                            ."• بتر قدم\n"
                            ."• بتر ساق\n"
                            ."• بتر يد\n"
                            ."• بتر ذراع",
                            $businessConnectionId
                        );

                        break;
                    }
                    if ($selected === 'الحالات المرضية') {

                        $user->step = 'disease_type';
                        $user->save();

                        $this->sendMessage(
                            $chatId,
                            "🩺 ما هو *نوع المرض*؟",
                            $businessConnectionId
                        );

                        break;
                    }

                    $user->step = 'entry_to_egypt';
                    $user->is_completed = false;
                    $user->save();

                    $this->sendMessage(
                        $chatId,
                        "📅 برجاء إدخال *تاريخ دخول مصر* مثال: 2024-05-01",
                        $businessConnectionId
                    );

                    // إرسال للمجموعة حسب الفئة
                    if ($selected === 'الأيتام' && $this->orphansGroupId) {
                        $this->sendUserToGroup($this->orphansGroupId, $user);
                    }

                    // if ($selected === 'الحالات المرضية' && $this->medicalGroupId) {
                    //     $this->sendUserToGroup($this->medicalGroupId, $user);
                    // }
                    // الرسالة المؤسسية حسب الفئة
                    switch ($selected) {

                        case 'مساكن الجرحى والمرضى (العبور)':
                            $followUpMsg = "🏠 نرجو إرسال *اسم رقم العمارة والشقة* بشكل واضح لتسهيل دراسة طلبكم.";
                            break;
                        
                        case 'فئات أخرى':
                            $followUpMsg = "📌 يرجى إرسال اسم الفئة التي تنتمي اليها..";
                            break;

                        default:
                            $followUpMsg = "📌 يرجى إرسال المعلومات أو المستندات الداعمة للفئة المختارة حسب تعليمات الجمعية.";
                            break;
                    }

                    // إرسال الرسائل
                    break;

                case 'disease_type':

                    if (mb_strlen($text) < 2 || preg_match('/[0-9]/', $text)) {

                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ يرجى كتابة نوع المرض بشكل صحيح."
                        )) {
                            return response()->json(['status' => 'ok']);
                        }

                        break;
                    }

                    $user->disease_type = $text;
                    $user->step = 'entry_to_egypt';
                    $user->is_completed = false;
                    $user->spam_count = 0;
                    $user->save();

                    // إرسال للجروب الطبي
                    if ($this->medicalGroupId) {
                        $this->sendUserToGroup($this->medicalGroupId, $user);
                    }

                    // ✅ هذا السطر الناقص
                    $this->sendMessage(
                        $chatId,
                        "📅 برجاء إدخال *تاريخ دخول مصر* مثال: 2024-05-01",
                        $businessConnectionId
                    );

                    break;

                case 'amputation_type':

                    if (mb_strlen($text) < 2 || preg_match('/[0-9]/', $text)) {

                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ يرجى كتابة نوع البتر بشكل صحيح."
                        )) {
                            return response()->json(['status' => 'ok']);
                        }

                        break;
                    }

                    $user->amputation_type = $text;
                    $user->step = 'entry_to_egypt';
                    $user->is_completed = false;
                    $user->spam_count = 0;
                    $user->save();

                    if ($this->amputationGroupId) {
                        $this->sendUserToGroup($this->amputationGroupId, $user);
                    }

                    // ✅ هذا السطر الناقص
                    $this->sendMessage(
                        $chatId,
                        "📅 برجاء إدخال *تاريخ دخول مصر* مثال: 2024-05-01",
                        $businessConnectionId
                    );

                    break;
                
                case 'entry_to_egypt':

                    // 🧹 تنظيف الإدخال (تحويل / و . إلى -)
                    $clean = str_replace(['/', '.', ' ','_'], '-', $text);

                    try {

                        // 🎯 جرّب أكثر من فورمات
                        $formats = [
                            'Y-m-d',
                            'd-m-Y',
                            'd-m-y',
                            'Y-m-d',
                        ];

                        $parsedDate = null;

                        foreach ($formats as $format) {
                            try {
                                $parsedDate = Carbon::createFromFormat($format, $clean);
                                break;
                            } catch (\Exception $e) {
                                continue;
                            }
                        }

                        // 🔁 إذا ما زبط أي فورمات → جرّب parse العادي
                        if (!$parsedDate) {
                            $parsedDate = Carbon::parse($clean);
                        }

                        // ❌ منع التواريخ المستقبلية (اختياري)
                        if ($parsedDate->isFuture()) {
                            throw new \Exception("future date");
                        }

                        // ✅ نجاح
                        $user->entry_to_egypt = $parsedDate->format('Y-m-d');
                        $user->spam_count = 0;
                        $user->step = 'rent_amount';
                        $user->save();

                        $this->sendMessage(
                            $chatId,
                            "💰 أدخل *قيمة الإيجار الشهري* (رقم فقط)\n\n📌 إذا كنت مستضاف أو لا تدفع إيجار اكتب 0:",
                            $businessConnectionId
                        );

                    } catch (\Exception $e) {

                        if ($this->registerValidationError(
                            $user,
                            $chatId,
                            $businessConnectionId,
                            "❌ ادخل تاريخ دخول مصر بشكل صحيح\n\n"
                            ."📌 أمثلة مقبولة:\n"
                            ."2024-05-01\n"
                            ."01-05-2024\n"
                            ."1/5/2024"
                        )) {
                            return response()->json(['status' => 'ok']);
                        }

                    }

                    break;
                
                    case 'rent_amount':

                        if (!preg_match('/^\d+$/', $text)) {

                            if ($this->registerValidationError(
                                $user,
                                $chatId,
                                $businessConnectionId,
                                "❌ أدخل رقم فقط لقيمة الإيجار\n\n📌 إذا كنت مستضاف أو لا تدفع إيجار اكتب 0"
                            )) {
                                return response()->json(['status' => 'ok']);
                            }

                            break;
                        }

                        $rent = (int)$text;

                        $user->rent_amount = $rent;
                        $user->spam_count = 0;

                        // ✅ إذا 0 → تخطي رقم صاحب الشقة
                        if ($rent === 0) {

                            $user->landlord_phone = null; // أو '0' إذا بدك
                            $user->is_completed = true;
                            $user->step = 'done';
                            $user->save();

                            $this->sendMessage(
                                $chatId,
                                $this->getRequiredImagesMessage($user),
                                $businessConnectionId
                            );

                        } else {

                            // 🏠 عنده إيجار → نطلب رقم صاحب الشقة
                            $user->step = 'landlord_phone';
                            $user->save();

                            $this->sendMessage(
                                $chatId,
                                "📞 أدخل *رقم هاتف صاحب الشقة*:",
                                $businessConnectionId
                            );
                        }

                        break;
                    case 'landlord_phone':

                        if (!preg_match('/^\+?\d{10,15}$/', $normalizedText)) {

                            if ($this->registerValidationError(
                                $user,
                                $chatId,
                                $businessConnectionId,
                                "❌ رقم غير صالح"
                            )) {
                                return response()->json(['status' => 'ok']);
                            }

                            break;
                        }

                        $user->landlord_phone = $normalizedText;
                        $user->spam_count = 0;

                        // ✅ رجعناه مكتمل
                        $user->is_completed = true;
                        $user->step = 'done';
                        $user->save();

                        $this->sendMessage(
                            $chatId,
                            $this->getRequiredImagesMessage($user),
                            $businessConnectionId
                        );

                        break;
            }
            return response()->json(['status' => 'ok']);

        }
        catch (\Throwable $e) {
            Log::error('Telegram Bot Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['status' => 'ok']);
        }
        finally {
            if (isset($user) && $hasLock && $user->exists) {
                $user->is_processing = false;
                $user->save();
            }
        }

    }
    /**
     * تحويل الأرقام العربية والفارسية إلى إنجليزية
     */
    private function normalizeNumbers(string $text): string
    {
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];

        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];

        return str_replace(array_merge($arabic, $persian), $english, $text);
    }
    // =====================
    // تسجيل خطأ في الإدخال
    // =====================
    private function registerValidationError(TelegramUser $user, $chatId, $businessConnectionId, string $message)
    {
        $user->spam_count += 1;
        $user->save();

        // 🚫 تجاوز الحد المسموح
        if ($user->spam_count >= self::MAX_ERRORS) {

            $user->is_blocked = true;
            $user->blocked_reason = 'Too many invalid inputs';
            $user->save();

            $this->sendBlockedUserToGroup($user);

            $this->sendMessage(
                $chatId,
                "🚫 *خطأ متكرر لخمس محاولات خاطئة نرجو ارسال المطلوب برسالة واحدة فقط*\n\n",
                $businessConnectionId
            );

            return true; // 🚨 تم الحظر
        }

        // ❗ خطأ عادي
        $this->sendMessage(
            $chatId,
            $message . "\n\n"
            ."⚠️ يرجى الاجابة كما هو مطلوب حرفيا " . (self::MAX_ERRORS - $user->spam_count) . " محاولات متبقية",
            $businessConnectionId
        );

        return false;
    }

    // =====================
    // إرسال رسالة نصية
    // =====================
    private function sendMessage($chatId, $text, $businessConnectionId = null)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];

        if ($businessConnectionId) {
            $params['business_connection_id'] = $businessConnectionId;
        }

        Http::timeout(5)
            ->asJson()
            ->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", $params);
    }

    private function getNextGroupNumber($groupKey)
    {
        $counter = \App\Models\GroupCounter::firstOrCreate(
            ['group_key' => $groupKey],
            ['counter' => 0]
        );

        $counter->counter += 1;
        $counter->save();

        return $counter->counter;
    }

    private function sendUserToGroup($groupId, TelegramUser $user)
    {
        // تحديد اسم المجموعة
        $groupKey = $user->target_case;

        $requestNumber = $this->getNextGroupNumber($groupKey);

        $text = "📥 طلب جديد #{$requestNumber}\n\n";
        $text .= "👤 الاسم: {$user->full_name}\n";
        $text .= "🆔 الهوية: {$user->national_id}\n";
        $text .= "📱 تيليجرام: {$user->telegram_number}\n";
        $text .= "📍 المحافظة: {$user->governorate}\n";
        $text .= "🏙️ المدينة: {$user->city}\n";
        $text .= "🏠 العنوان: {$user->address}\n";
        $text .= "🎯 الفئة: {$user->target_case}\n";

        if ($user->amputation_type) {
            $text .= "🦿 نوع البتر: {$user->amputation_type}\n";
        }
        if ($user->disease_type) {
            $text .= "🩺 نوع المرض: {$user->disease_type}\n";
        }

        $this->sendMessage($groupId, $text);
    }

    private function sendBlockedUserToGroup(TelegramUser $user)
    {
        if (!$this->blockedGroupId) return;

        // 🔹 نستخدم الترقيم لكل الحظر مثل أي طلب جديد
        $groupKey = $user->target_case ?? 'blocked'; // إذا ما فيه فئة، استخدم مفتاح عام
        $requestNumber = $this->getNextGroupNumber($groupKey);

        $text = "🚫 حساب تم حظره #{$requestNumber}\n\n";
        $text .= "👤 اسم تيليجرام: " . ($user->full_name ?? 'غير متوفر') . "\n";
        $text .= "🔗 يوزر نيم: @" . ($user->username ?? 'لا يوجد') . "\n";
        $text .= "🆔 Chat ID: {$user->chat_id}\n";

        if ($user->national_id) $text .= "🪪 الهوية: {$user->national_id}\n";
        if ($user->telegram_number) $text .= "📱 تيليجرام: {$user->telegram_number}\n";
        if ($user->target_case) $text .= "🎯 الفئة: {$user->target_case}\n";

        $text .= "\n⚠️ سبب الحظر:\n{$user->blocked_reason}";

        $this->sendMessage($this->blockedGroupId, $text);
    }
    // =====================
    // بيانات ثابتة
    // =====================
    private function governorates(): array
    {
        return [
            1 => 'القاهرة',
            2 => 'الجيزة',
            3 => 'الإسكندرية',
            4 => 'الدقهلية',
            5 => 'الشرقية',
            6 => 'المنوفية',
            7 => 'القليوبية',
            8 => 'الغربية',
            9 => 'كفر الشيخ',
            10 => 'البحيرة',
            11 => 'دمياط',
            12 => 'بورسعيد',
            13 => 'الإسماعيلية',
            14 => 'السويس',
            15 => 'شمال سيناء',
            16 => 'جنوب سيناء',
            17 => 'بني سويف',
            18 => 'الفيوم',
            19 => 'المنيا',
            20 => 'أسيوط',
            21 => 'سوهاج',
            22 => 'قنا',
            23 => 'الأقصر',
            24 => 'أسوان',
            25 => 'الوادي الجديد',
            26 => 'مطروح',
            27 => 'البحر الأحمر',
        ];
    }

    // =====================
    // فئات مستهدفة
    // =====================
    // private function targetCases(): array
    // {
    //     return [
    //         1 => 'الأيتام',
    //         2 => 'حالات البتر',
    //         3 => 'الحالات المرضية',
    //         4 => 'مساكن الجرحى والمرضى (العبور)',
    //         5 => 'فئات أخرى',
    //     ];
    // }
    private function resolveCaseFromText(string $input): ?string
    {
        $input = trim($input);

        // ✅ استخراج أول رقم من النص (حتى لو فيه كلام)
        if (preg_match('/\d+/', $input, $matches)) {
            $number = (int)$matches[0];

            switch ($number) {
                case 1: return 'الأيتام';
                case 2: return 'حالات البتر';
                case 3: return 'الحالات المرضية';
                case 4: return 'مساكن الجرحى والمرضى (العبور)';
                case 5: return 'فئات أخرى';
            }
        }

        // 🔽 إذا ما فيه رقم → نكمل بالنص
        $input = mb_strtolower($input);

        // الأيتام
        $orphansKeywords = ['الأيتام', 'الايتام', 'أيتام', 'ايتام', 'يتام', 'يتيم'];
        foreach ($orphansKeywords as $word) {
            if (mb_strpos($input, $word) !== false) {
                return 'الأيتام';
            }
        }

        // حالات البتر
        $amputationKeywords = ['بتر', 'حالات البتر', 'البتر'];
        foreach ($amputationKeywords as $word) {
            if (mb_strpos($input, $word) !== false) {
                return 'حالات البتر';
            }
        }

        // الحالات المرضية
        $medicalKeywords = ['المرضية', 'مرض', 'مرضيه', 'المرضيه'];
        foreach ($medicalKeywords as $word) {
            if (mb_strpos($input, $word) !== false) {
                return 'الحالات المرضية';
            }
        }

        // العبور
        $crossingKeywords = ['عبور', 'العبور', 'جرحى', 'مساكن'];
        foreach ($crossingKeywords as $word) {
            if (mb_strpos($input, $word) !== false) {
                return 'مساكن الجرحى والمرضى (العبور)';
            }
        }

        // أخرى
        $otherKeywords = ['أخرى', 'اخرى', 'غير', 'غير ذلك'];
        foreach ($otherKeywords as $word) {
            if (mb_strpos($input, $word) !== false) {
                return 'فئات أخرى';
            }
        }

        return null;
    }

    // =====================
    // اختيار من قائمة
    // =====================
    private function resolveChoice(string $input, array $options): ?string
    {
        $input = trim($input);

        // إذا رقم
        if (ctype_digit($input) && isset($options[(int)$input])) {
            return $options[(int)$input];
        }

        // إذا نص
        foreach ($options as $value) {
            if (mb_strtolower($input) === mb_strtolower($value)) {
                return $value;
            }
        }

        return null;
    }
private function getRequiredImagesMessage(TelegramUser $user): string
{
    $base = "📸 *يرجى إرسال الصور التالية لاستكمال الطلب:*\n\n";

    switch ($user->target_case) {
        case 'الأيتام':
            $base .= "• صورة *شهادة الوفاة*\n";
            break;

        case 'حالات البتر':
            $base .= "• صورة *العضو المبتور*\n";
            break;

        case 'الحالات المرضية':
            $base .= "• صورة *التقرير الطبي*\n";
            break;

        default:
            $base .= "• أي مستند داعم للحالة\n";
            break;
    }

    $base .= "\n📌 بالإضافة إلى:\n";
    $base .= "• صورة *جواز السفر لجميع افراد الاسرة*\n";
    $base .= "• صورة *ختم دخول مصر*\n";
    $base .= "• صورة شخصية رسمية\n";

    // ✅ فقط إذا عنده إيجار
    if ($user->rent_amount > 0) {
        $base .= "• صورة *عقد الإيجار*\n";
    }

    return $base;
}

    // =====================
    // رسائل جاهزة
    // =====================
private function welcomeMessage(): string
{
    return "👋 شكرًا لك تم ارسال البيانات الى *جمعية حق الإنسانية*!\n\n"
        . "✅ وتم استلام بياناتك بنجاح.\n"
        . "سيقوم فريقنا بدراسة طلبك والتواصل معك عند توفر كشوفات جديدة\n"
        . "مع العلم ان الفئات المستهدفة حالياً هي:\n"
        . "(*الأيتام، حالات البتر، مساكن الجرحى والمرضى*).\n\n"
        . "📌 نقدر ثقتك وتعاونك معنا.";
}

private function alreadyContactedMessage(): string
{
    return "👋 شكرًا لك تم ارسال البيانات الى *جمعية حق الإنسانية*!\n\n"
        . "✅ وتم استلام بياناتك بنجاح.\n"
        . "سيقوم فريقنا بدراسة طلبك والتواصل معك عند توفر كشوفات جديدة\n"
        . "مع العلم ان الفئات المستهدفة حالياً هي:\n"
        . "(*الأيتام، حالات البتر، مساكن الجرحى والمرضى*).\n\n"
        . "📌 نقدر ثقتك وتعاونك معنا.";
}
}
