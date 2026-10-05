<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المستخدم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
    <circle cx='50' cy='50' r='48' fill='%234361ee'/>
    <circle cx='50' cy='38' r='18' fill='white'/>
    <path d='M20 90c5-20 55-20 60 0' fill='white'/>
    </svg>">


    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }

        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --dark: #1f2937;
            --gray: #6b7280;
            --light-gray: #f3f4f6;
            --border-gray: #e5e7eb;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: var(--border-radius);
            color: white;
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .back-btn {
            position: absolute;
            right: 25px;
            top: 25px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* User Info Cards */
        .user-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            border-right: 5px solid var(--primary);
        }

        .info-card h3 {
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px dashed var(--border-gray);
        }

        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-item strong {
            color: var(--dark);
        }

        .info-item span {
            color: var(--gray);
            text-align: left;
        }

        /* Family Members Section */
        .family-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            border-right: 5px solid var(--success);
        }

        .family-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .family-card {
            background: var(--light-gray);
            border-radius: var(--border-radius);
            padding: 20px;
            border: 1px solid var(--border-gray);
        }

        /* Messages Section */
        .messages-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            border-right: 5px solid var(--info);
        }

        .messages-list {
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        .message-item {
            background: var(--light-gray);
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 10px;
            border-right: 4px solid var(--info);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .message-content {
            color: var(--dark);
            line-height: 1.5;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-completed {
            background: #d1fae5;
            color: #047857;
        }

        .status-blocked {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-pending {
            background: #fef3c7;
            color: #b45309;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
        }

        .btn-telegram {
            background: linear-gradient(135deg, #0088cc 0%, #006699 100%);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        /* ====== تحسين عرض الرسائل على الجوال ====== */

@media (max-width: 768px) {

    .messages-section table {
        display: none; /* إخفاء الجدول */
    }

    .mobile-messages {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-top: 20px;
    }

    .mobile-message-card {
        background: white;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        border-right: 5px solid var(--info);
        position: relative;
        transition: 0.3s;
    }

    .mobile-message-card:hover {
        transform: translateY(-3px);
    }

    .mobile-message-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 13px;
        color: var(--gray);
    }

    .mobile-message-type {
        font-weight: bold;
        color: var(--primary);
    }

    .mobile-message-text {
        background: var(--light-gray);
        padding: 12px;
        border-radius: 12px;
        margin: 10px 0;
        white-space: pre-line;
        font-size: 14px;
    }

    .mobile-message-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
    }

    .mobile-copy-btn {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 20px;
        background: var(--primary);
        color: white;
        border: none;
        cursor: pointer;
    }

    .mobile-copy-btn:hover {
        background: var(--primary-dark);
    }
}
.notes-warning-box{
    background:#fee2e2;
    border:2px solid #ef4444;
    border-right:6px solid #b91c1c;
    padding:20px;
    border-radius:12px;
    margin-bottom:25px;
    box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

.notes-warning-title{
    font-size:18px;
    font-weight:bold;
    color:#b91c1c;
    margin-bottom:15px;
    display:flex;
    align-items:center;
    gap:10px;
}

.note-item{
    background:white;
    border:1px solid #fecaca;
    padding:12px;
    border-radius:8px;
    margin-bottom:10px;
}

.note-meta{
    font-size:13px;
    color:#6b7280;
    margin-bottom:6px;
}

.note-text{
    font-weight:500;
    color:#111827;
}

/* على الديسكتوب نخفي كروت الموبايل */
@media (min-width: 769px) {
    .mobile-messages {
        display: none;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($user->notes->count() > 0)

<div class="notes-warning-box">

    <div class="notes-warning-title">
        <i class="fas fa-triangle-exclamation"></i>
        ⚠ يوجد {{ $user->notes->count() }} ملاحظة على هذا المستخدم
    </div>

    @foreach($user->notes->sortByDesc('created_at') as $note)

        <div class="note-item">

            <div class="note-meta">
                كتبها:
                <strong>{{ $note->creator->name ?? 'النظام' }}</strong>
                —
                {{ $note->created_at->format('Y-m-d H:i') }}
            </div>

            <div class="note-text">
                {{ $note->note }}
            </div>

        </div>

    @endforeach

</div>

@endif

            <button class="back-btn" onclick="goBack()">
                <i class="fas fa-arrow-right"></i> رجوع
            </button>

            <h1><i class="fas fa-user-circle"></i> تفاصيل المستخدم</h1>
            <p>عرض كامل بيانات المستخدم والمرافقين والرسائل</p>
        </div>

        @if(session('success'))
            <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #10b981; text-align: center; font-weight: bold;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        
        <div class="info-card" style="border: 2px solid var(--primary);">
        <h3><i class="fas fa-lock"></i> إدارة حساب الدخول للموقع</h3>
            <div class="card-body">
                @if(!$user->user)
                    {{-- فورم مستقل تماماً --}}
                    <form action="{{ route('admin.telegram.createLoginUser', $user->id) }}" method="POST">
                        @csrf
                        <p class="text-muted">
                            المستخدم لا يملك حساب دخول حالياً.
                            <br>
                            سيتم استخدام رقم الهوية <strong>({{ $user->national_id }})</strong> كـ "اسم مستخدم" و "كلمة مرور".
                        </p>
                        <button type="submit" class="btn btn-primary" style="background: var(--primary);">
                            🔐 إنشاء حساب دخول الآن
                        </button>
                    </form>
                @else
                    <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px;">
                        <i class="fas fa-check-circle"></i> <strong>الحساب مفعل وجاهز.</strong>
                        <br>
                        <strong>اسم المستخدم:</strong>
                        <span style="direction: ltr; display: inline-block;">
                            {{ $user->user->email ?? $user->national_id }}
                        </span>
                    </div>
                @endif
            </div>
            <b>تم إنشاء المستخدم بواسطة:</b>
            {{ $user->creator->name ?? 'غير معروف' }} <br>

            <b>تم إنشاء الحساب بواسطة:</b>
            {{ $user->user?->creator->name ?? 'غير معروف' }} <br>

            <b>آخر تعديل بواسطة:</b>
            {{ $user->updater->name ?? 'غير معروف' }} <br>

            <b>تم الحذف بواسطة:</b>
            {{ $user->deleter->name ?? 'غير معروف' }}
        </div>
        <br>
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">

            <button class="btn btn-success" onclick="copyBasicData(event)">
                <i class="fas fa-copy"></i> نسخ البيانات الأساسية
            </button>

            @if($orphanCase)
                <a href="{{ route('admin.orphans.show', $orphanCase->id) }}"
                class="btn btn-primary"
                style="text-decoration:none; display:inline-flex; align-items:center; gap:7px;">
                    <i class="fas fa-child"></i>
                    عرض بيانات الأيتام
                </a>
            @endif

        </div>
        <br>
        <!-- User Information -->
        <div class="user-info-grid">
            <!-- Basic Information -->
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> المعلومات الأساسية</h3>
                <div class="info-item">
                    <strong>رقم المستخدم:</strong>
                    <span>#{{ $user->id }}</span>
                </div>
                <div class="info-item">
                    <strong>الاسم الكامل:</strong>
                    <span>{{ $user->full_name ?? $user->first_name ?? 'غير متوفر' }}</span>
                </div>
                <div class="info-item">
                    <strong>اسم المستخدم:</strong>
                    <span>{{ $user->username ?? 'غير متوفر' }}</span>
                </div>
                <div class="info-item">
                    <strong>رقم الهوية:</strong>
                    <span>{{ $user->national_id ?? 'غير متوفر' }}</span>
                </div>
                <div class="info-item">
                    <strong>تاريخ التسجيل:</strong>
                    <span>
                        @if($user->created_at)
                            {{ $user->created_at->format('Y-m-d H:i') }}
                        @else
                            غير متوفر
                        @endif
                    </span>
                </div>
            </div>
            <div class="info-card">
    <h3><i class="fas fa-check-circle"></i> اعتماد الحالة</h3>

    <div class="info-item">
        <strong>تمت الزيارة بواسطة:</strong>
        <span>{{ $user->approved_by ?: 'لم يتم الاعتماد بعد' }}</span>
    </div>

    <div class="info-item" style="display:block;">
        <strong>شرح الحالة:</strong>

        <div style="
            margin-top:10px;
            background:#f8fafc;
            border:1px solid #e5e7eb;
            border-radius:8px;
            padding:12px;
            white-space:pre-line;
            line-height:1.8;
        ">
            {{ $user->approval_description ?: 'لا يوجد شرح.' }}
        </div>
    </div>
</div>
            <div class="info-card">
                <h3><i class="fas fa-key"></i> شرح الحالة</h3>
                <div class="info-item">
                    <span>{{ $user->companions ?? 'غير محدد' }}</span>
                </div>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-key"></i> بيانات الوصول والفئة</h3>

                <div class="info-item">
                    <strong>الفئة:</strong>
                    <span>{{ $user->category->name ?? 'غير محدد' }}</span>
                </div>

                <div class="info-item">
                    <strong>كود الوصول:</strong>
                    <span style="font-weight: bold; color: #28a745;">
                        {{ $user->access_code ?? 'لم يتم توليد كود' }}
                    </span>
                </div>
                <div class="info-item">
                    <strong>تاريخ الدخول إلى مصر:</strong>
                    <span>{{ $user->entry_to_egypt ?? 'غير متوفر' }}</span>
                </div>
                <div class="info-item">
                    <strong>قيمة الإيجار:</strong>
                    <span>{{ $user->rent_amount ?? 'غير متوفر' }}</span>
                </div>
                <div class="info-item">
                    <strong>رقم هاتف صاحب الشقة:</strong>
                    <span>{{ $user->landlord_phone ?? 'غير متوفر' }}</span>
                </div>
            </div>
            <!-- Contact Information -->
            <div class="info-card">
                <h3><i class="fas fa-address-book"></i> معلومات الاتصال</h3>
                <div class="info-item">
                    <strong>رقم الواتساب:</strong>
                    <span>{{ $user->whatsapp ?? 'غير متوفر' }}</span>
                </div>
                <div class="info-item">
                    <strong>رقم التيليجرام:</strong>
                    <span>{{ $user->telegram_number ?? 'غير متوفر' }}</span>
                </div>
                <div class="info-item">
                    <strong>رقم مصر:</strong>
                    <span>{{ $user->egypt_number ?? 'غير متوفر' }}</span>
                </div>
                <div class="info-item">
                    <strong>معرف المحادثة:</strong>
                    <span>{{ $user->chat_id ?? 'غير متوفر' }}</span>
                </div>
            </div>

            <!-- Location Information -->
            <div class="info-card">
                <h3><i class="fas fa-map-marker-alt"></i> المعلومات الجغرافية</h3>
                <div class="info-item">
                    <strong>المحافظة:</strong>
                    <span>{{ $user->governorate ?? 'غير متوفر' }}</span>
                </div>
                <div class="info-item">
                    <strong>المدينة:</strong>
                    <span>{{ $user->city ?? 'غير متوفر' }}</span>
                </div>
                <div class="info-item">
                    <strong>العنوان:</strong>
                    <span>{{ $user->address ?? 'غير متوفر' }}</span>
                </div>
            </div>

            <!-- Status Information -->
            <div class="info-card">
                <h3><i class="fas fa-chart-line"></i> الحالة والإحصائيات</h3>
                <div class="info-item">
                    <strong>الحالة:</strong>
                    <span>
                        @if($user->is_blocked)
                            <span class="status-badge status-blocked">
                                <i class="fas fa-ban"></i> محظور
                            </span>
                        @elseif($user->is_completed)
                            <span class="status-badge status-completed">
                                <i class="fas fa-check-circle"></i> مكتمل
                            </span>
                        @else
                            <span class="status-badge status-pending">
                                <i class="fas fa-clock"></i> قيد التسجيل
                            </span>
                        @endif
                    </span>
                </div>
                    <!-- إضافة سبب الحظر إذا كان المستخدم محظورًا -->
                @if($user->is_blocked && $user->blocked_reason)
                <div class="info-item">
                    <strong>سبب الحظر:</strong>
                    <span style="color: var(--danger); font-weight: 500; background: #fee2e2; padding: 5px 10px; border-radius: 6px; display: inline-block; margin-top: 5px;">
                        <i class="fas fa-exclamation-circle"></i>
                        @if($user->blocked_reason === 'duplicate_national_id_bot')
                            *هوية موجودة مسبقًا وتمت محاولة استخدامها مجددًا في حساب جديد*.
                        @elseif($user->blocked_reason === 'duplicate_national_id')
                            *هوية موجودة مسبقًا وتمت محاولة استخدامها مجددًا في حساب جديد*.
                        @elseif($user->blocked_reason === 'national_id_registered_as_family_member')
                            *الهوية مسجلة مسبقًا كمرافق في طلب اخر*.
                        @elseif($user->blocked_reason === 'Duplicate family member ID detected')
                            *تم إدخال هوية مرافق مكررة أثناء التسجيل*.
                        @elseif($user->blocked_reason === 'Too many invalid inputs')
                            *تجاوز الحد الأقصى للأخطاء*.
                        @elseif($user->blocked_reason === 'messages_after_request_completed')
                            *إرسال رسائل بعد اكتمال الطلب*
                        @endif
                    </span>
                </div>
                @endif

                <div class="info-item">
                    <strong>الفئة المستهدفة:</strong>
                    <span>{{ $user->target_case ?? 'غير متوفر' }}</span>
                </div>
                <div class="info-item">
                    <strong>عدد أفراد الأسرة:</strong>
                    <span>{{ $user->family_members ?? 0 }}</span>
                </div>
                <div class="info-item">
                    <strong>عدد الرسائل:</strong>
                    <span>{{ $user->messages_count ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Family Members -->
        @if($user->familyMembers->count() > 0)
        <div class="family-section">
            <h3><i class="fas fa-users"></i> المرافقين (أفراد الأسرة)</h3>

            <div class="family-grid">
                @foreach($user->familyMembers as $index => $member)
                <div class="family-card">
                    <div class="info-item">
                        <strong>رقم المرافق:</strong>
                        <span>{{ $index + 1 }}</span>
                    </div>

                    <div class="info-item">
                        <strong>الاسم الكامل:</strong>
                        <span>{{ $member->full_name }}</span>
                    </div>

                    <div class="info-item">
                        <strong>رقم الهوية:</strong>
                        <span>{{ $member->national_id }}</span>
                    </div>
                    <div class="info-item">
                        <strong>تاريخ الميلاد:</strong>
                        <span>{{ $member->birth_date }}</span>
                    </div>
                    <div class="info-item">
                        <strong>صلة القرابة:</strong>
                        <span>{{ $member->relation }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Messages -->
        <div class="messages-section">
            <h3><i class="fas fa-calendar-check"></i> سجل الفعاليات والرسائل</h3>

            @php
            $messagesFiltered = $messages->where('delivery_status','!=','failed');

            // ترتيب الأنواع (الدعوة بالأخير)
            $order = [
                'received',
                'general',
                'invitation'
            ];

            // تجميع حسب النوع
            $messagesByType = $messagesFiltered->groupBy('message_type');

            // ترتيبهم حسب المصفوفة
            $messagesByType = collect($order)
                ->mapWithKeys(fn($type) => [$type => $messagesByType[$type] ?? collect()])
                ->filter(fn($group) => $group->isNotEmpty());
            @endphp

            @forelse($messagesByType as $type => $typeMessages)

            <h3 style="margin-top:25px;color:#4361ee">

            @switch($type)
                @case('invitation')

                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">

                    <span>📩 رسائل الدعوات ({{ $typeMessages->count() }})</span>

                    <button 
                    class="btn btn-primary"
                    style="padding:5px 12px;font-size:13px"
                    onclick="toggleInvitations()"
                    >
                    <i class="fas fa-eye"></i> عرض / إخفاء
                    </button>

                    </div>

            
            @break

            @case('received')
                📦 رسائل التسليم ({{ $typeMessages->count() }})
            @break

            @default

                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">

                <span>📢 رسائل عامة ({{ $typeMessages->count() }})</span>

                <button 
                class="btn btn-primary"
                style="padding:5px 12px;font-size:13px"
                onclick="toggleGeneral()"
                >
                <i class="fas fa-eye"></i> عرض / إخفاء
                </button>

                </div>
            @endswitch

            </h3>

            <table
            @if($type === 'invitation')
                id="invitationTable"
                style="display:none;width:100%; margin-top:10px; border-collapse:collapse;"
            @elseif($type === 'general')
                id="generalTable"
                style="display:none;width:100%; margin-top:10px; border-collapse:collapse;"
            @else
                style="width:100%; margin-top:10px; border-collapse:collapse;"
            @endif
            >

            <thead>
            <tr style="background:#f3f4f6;">
            <th style="text-align:center; vertical-align:middle;">الفعالية</th>
            <th style="color:#b91c1c;">تاريخ الفعالية</th>

            <th>نص الرسالة</th>
            <th>تاريخ الإرسال</th>
            <th>حالة الإرسال</th>
            <th>المرسل</th>

            </tr>
            </thead>

<tbody>

@foreach($typeMessages as $message)

<tr style="border-bottom:1px solid #e5e7eb;">
<td style="text-align:center; vertical-align:middle;">
    {{ $message->event->name ?? 'رسالة عامة' }}
</td>
<td style="color:#b91c1c; font-weight:bold; text-align:center; vertical-align:middle;">

    @if($message->event && $message->event->is_archived)
        
        أرشفة

    @elseif($message->event && $message->event->is_fixed)

        {{ \Carbon\Carbon::parse(
            $message->sent_at ?? $message->created_at
        )->format('Y-m-d H:i') }}

    @elseif($message->event && $message->event->event_date)

        {{ \Carbon\Carbon::parse($message->event->event_date)->format('Y-m-d') }}

    @else

        ---

    @endif

</td>
<td style="text-align:center; vertical-align:middle;">

<div style="white-space: pre-line; text-align:center;" id="msg-{{ $message->id }}">
{{ $message->message }}
</div>

<button
class="btn btn-primary"
style="margin-top:8px; padding:5px 10px; font-size:12px;"
onclick="copyMessage({{ $message->id }}, event)"
>
<i class="fas fa-copy"></i> نسخ
</button>

</td>

<td>

{{
\Carbon\Carbon::parse(
$message->sent_at ?? $message->created_at
)->format('Y-m-d H:i')
}}

</td>

<td>

@if($message->delivery_status === 'sent')
<span style="color:green;">✔ تم الإرسال</span>

@elseif($message->delivery_status === 'manual')
<span style="color:green;">✔ تم الإرسال دون بوت</span>

@else
<span style="color:orange;">⏳ قيد الإرسال</span>
@endif

</td>

<td>
{{ implode(' ', array_slice(explode(' ', $message->sender_type), 0, 2)) }}
</td>

</tr>

@endforeach

</tbody>
</table>

@empty

<div style="text-align:center; padding:20px;">
لا توجد رسائل مرسلة
</div>

@endforelse
<div class="mobile-messages">

@foreach($messagesByType as $type => $typeMessages)

@php
$isCollapsible = ($type === 'invitation' || $type === 'general');
@endphp

<div class="mobile-group" style="margin-bottom:20px;">

    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:10px;">

        <span style="font-weight:bold;color:#4361ee;">

            @switch($type)
                @case('invitation')
                    📩 رسائل الدعوات ({{ $typeMessages->count() }})
                    @break

                @case('received')
                    📦 رسائل التسليم ({{ $typeMessages->count() }})
                    @break

                @default
                    📢 رسائل عامة ({{ $typeMessages->count() }})
            @endswitch

        </span>

        @if($isCollapsible)
        <button 
            class="btn btn-primary"
            style="padding:5px 12px;font-size:13px"
            onclick="toggleMobileGroup(this)"
        >
            <i class="fas fa-eye"></i> عرض
        </button>
        @endif

    </div>

    <!-- Content -->
    <div class="mobile-group-content"
         @if($isCollapsible) style="display:none;" @endif>

        @foreach($typeMessages as $message)

        <div class="mobile-message-card">

            <div class="mobile-message-header">
                <span>{{ $message->event->name ?? 'رسالة عامة' }}</span>
                <span>
                    {{
                        \Carbon\Carbon::parse(
                            $message->sent_at ?? $message->created_at
                        )->format('Y-m-d H:i')
                    }}
                </span>
            </div>

            <div style="color:#b91c1c;font-weight:bold;margin-bottom:6px;">
                📅

                @if($message->event && $message->event->is_archived)

                    أرشفة

                @elseif($message->event && $message->event->is_fixed)

                    {{ \Carbon\Carbon::parse(
                        $message->sent_at ?? $message->created_at
                    )->format('Y-m-d H:i') }}

                @elseif($message->event && $message->event->event_date)

                    {{ \Carbon\Carbon::parse($message->event->event_date)->format('Y-m-d') }}

                @else

                    ---

                @endif
            </div>

            <div class="mobile-message-type">
                @switch($message->message_type)
                    @case('invitation') دعوة @break
                    @case('received') تم التسليم @break
                    @default عامة
                @endswitch
            </div>

            <div class="mobile-message-text" id="mobile-msg-{{ $message->id }}">
                {{ $message->message }}
            </div>

            <div class="mobile-message-footer">
                <span>
                    @if($message->delivery_status === 'sent')
                        ✔ تم الإرسال
                    @elseif($message->delivery_status === 'manual')
                        ✔ تم الإرسال دون بوت
                    @else
                        ⏳ قيد الإرسال
                    @endif
                </span>

                <button 
                    class="mobile-copy-btn"
                    onclick="copyMobileMessage({{ $message->id }}, event)"
                >
                    <i class="fas fa-copy"></i> نسخ
                </button>
            </div>

        </div>

        @endforeach

    </div>

</div>

@endforeach

</div>
            <div style="margin-top:15px; font-weight:bold;">
                🔁 عدد مرات الاستفادة: {{ $user->benefit_count }}
            </div>
        </div>


        <!-- Action Buttons -->
        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> طباعة
            </button>
        </div>
    </div>

    <script>
        function sendMessageToUser(userId) {
            // يمكنك فتح نافذة لإرسال رسالة مباشرة
            const message = prompt('أدخل نص الرسالة التي تريد إرسالها:');
            if (message) {
                // هنا سيكون كود إرسال الرسالة عبر AJAX
                alert('سيتم إرسال الرسالة: ' + message);
                // axios.post('/admin/send-message', { user_id: userId, message: message })
                // .then(response => {
                //     alert('تم إرسال الرسالة بنجاح');
                // })
                // .catch(error => {
                //     alert('حدث خطأ في الإرسال');
                // });
            }
        }

        function unblockUser(userId) {
            if (confirm('هل أنت متأكد من فك الحظر عن هذا المستخدم؟')) {
                // كود فك الحظر عبر AJAX
                // axios.post('/admin/unblock-user', { user_id: userId })
                // .then(response => {
                //     alert('تم فك الحظر بنجاح');
                //     location.reload();
                // })
                // .catch(error => {
                //     alert('حدث خطأ في فك الحظر');
                // });
            }
        }
        function goBack() {
            if (document.referrer !== "") {
                window.location.href = document.referrer;
            } else {
                window.location.href = "{{ route('admin.telegram.index') }}";
            }
        }
    </script>
    <script>
function copyMessage(messageId, event) {
    // الحصول على العنصر الذي يحتوي النص
    const messageElement = document.getElementById('msg-' + messageId);

    if (!messageElement) return;

    // استخدام innerText مع trim لإزالة الفراغات الزائدة
    const text = messageElement.innerText.trim();

    // التأكد من دعم المتصفح للـ Clipboard API
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function () {
            
            // الوصول للزر الذي تم الضغط عليه
            const btn = event.currentTarget || event.target.closest('button');
            const originalText = btn.innerHTML;

            // تغيير شكل الزر مؤقتاً للتأكيد
            btn.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
            const originalBg = btn.style.background;
            btn.style.background = '#10b981';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = originalBg;
            }, 1500);

        }).catch(function (err) {
            console.error('خطأ في النسخ:', err);
            alert('فشل النسخ ❌');
        });
    } else {
        // طريقة احتياطية للمتصفحات القديمة جداً أو البيئات غير الآمنة (HTTP)
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            alert('تم النسخ (طريقة احتياطية) ✅');
        } catch (err) {
            alert('المتصفح لا يدعم النسخ التلقائي ❌');
        }
        document.body.removeChild(textArea);
    }
}
function copyMobileMessage(messageId, event) {
    const messageElement = document.getElementById('mobile-msg-' + messageId);
    if (!messageElement) return;

    const text = messageElement.innerText.trim();

    // إذا Clipboard API مدعوم وبيئة آمنة
    if (navigator.clipboard && window.isSecureContext) {

        navigator.clipboard.writeText(text)
        .then(() => showCopySuccess(event))
        .catch(() => fallbackCopy(text, event));

    } else {
        fallbackCopy(text, event);
    }
}

function fallbackCopy(text, event) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.opacity = "0";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    document.body.removeChild(textArea);
}

function showCopySuccess(event) {
    const btn = event.currentTarget;
    const originalText = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
    btn.style.background = '#10b981';

    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.style.background = '';
    }, 1500);
}

function toggleInvitations(){

    const table = document.getElementById("invitationTable");

    if(!table) return;

    if(table.style.display === "none"){
        table.style.display = "table";
    }else{
        table.style.display = "none";
    }

}

function toggleGeneral(){

    const table = document.getElementById("generalTable");

    if(!table) return;

    if(table.style.display === "none"){
        table.style.display = "table";
    }else{
        table.style.display = "none";
    }

}

function toggleMobileGroup(btn) {
    const group = btn.closest('.mobile-group');
    const content = group.querySelector('.mobile-group-content');

    if (!content) return;

    if (content.style.display === "none") {
        content.style.display = "block";
        btn.innerHTML = '<i class="fas fa-eye-slash"></i> إخفاء';
    } else {
        content.style.display = "none";
        btn.innerHTML = '<i class="fas fa-eye"></i> عرض';
    }
}

</script>
<script>
function copyBasicData(event) {

    const text = `📋 البيانات الأساسية

👤 اسم صاحب الطلب: {{ $user->full_name ?? $user->first_name ?? 'غير متوفر' }}
🆔 رقم الهوية: {{ $user->national_id ?? 'غير متوفر' }}

📱 رقم الواتساب: {{ $user->whatsapp ?? 'غير متوفر' }}
🇪🇬 رقم مصر: {{ $user->egypt_number ?? 'غير متوفر' }}

📍 العنوان:
{{ $user->address ?? 'غير متوفر' }}

👨‍👩‍👧‍👦 عدد المرافقين:
{{ $user->familyMembers->count() }}

🎯 الفئة المستهدفة:
{{ $user->target_case ?? 'غير متوفر' }}

📝 شرح الحالة:
{{ $user->companions ?? 'غير متوفر' }}

💰 قيمة الإيجار:
{{ $user->rent_amount ?? 'غير متوفر' }}

🛬 تاريخ الدخول إلى مصر:
{{ $user->entry_to_egypt ?? 'غير متوفر' }}

🔁 عدد مرات الاستفادة:
{{ $user->benefit_count ?? 0 }}`;

    navigator.clipboard.writeText(text).then(() => {

        const btn = event.currentTarget;
        const original = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
        btn.style.background = '#10b981';

        setTimeout(() => {
            btn.innerHTML = original;
            btn.style.background = '';
        }, 1500);

    }).catch(() => {
        alert('تم النسخ بنجاح');
    });
}
</script>
</body>
</html>
