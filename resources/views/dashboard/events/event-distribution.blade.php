@php
    // استخراج قائمة بجميع الأسماء المكررة
    $duplicateNames = $logs->groupBy('user.full_name')
                           ->filter(function($group) {
                               return $group->count() > 1;
                           })
                           ->keys()
                           ->toArray();
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الفعاليات</title>
    <link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
        <circle cx='50' cy='50' r='48' fill='%231f6feb'/>
        <rect x='26' y='20' width='48' height='60' rx='6' fill='white'/>
        <rect x='32' y='28' width='36' height='6' rx='3' fill='%231f6feb'/>
        <rect x='32' y='40' width='28' height='5' rx='2' fill='%239aa5b1'/>
        <rect x='32' y='50' width='22' height='5' rx='2' fill='%239aa5b1'/>
        <rect x='60' y='52' width='5' height='14' fill='%231f6feb'/>
        <rect x='68' y='46' width='5' height='20' fill='%231f6feb'/>
        <rect x='76' y='58' width='5' height='8' fill='%231f6feb'/>
    </svg>">

    <!-- Bootstrap CSS مع نسخة RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome للأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- خط Tajawal للعربية -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1f6feb;
            --secondary-color: #2d3748;
            --light-color: #f8f9fa;
            --border-radius: 12px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            color: #333;
            min-height: 100vh;
            padding: 20px 0;
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* بطاقة التقرير الرئيسية */
        .report-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            border: none;
            overflow: hidden;
            transition: var(--transition);
        }

        .report-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .report-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0d4dbb 100%);
            color: white;
            padding: 25px 30px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            position: relative;
            overflow: hidden;
        }

        .report-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.2;
            animation: patternMove 20s linear infinite;
        }

        @keyframes patternMove {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .report-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }

        .report-title i {
            margin-left: 12px;
            font-size: 1.5rem;
        }

        .report-subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-top: 8px;
            position: relative;
            z-index: 1;
        }

        /* قسم الفلاتر */
        .filters-section {
            background: var(--light-color);
            padding: 25px 30px;
            border-bottom: 1px solid #e9ecef;
        }

        .filter-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }

        .form-select-custom {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: var(--transition);
            height: auto;
        }

        .form-select-custom:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(31, 111, 235, 0.1);
        }

        .filter-btn {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0d4dbb 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 13px 25px;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(31, 111, 235, 0.2);
        }

        .filter-btn:active {
            transform: translateY(0);
        }

        /* قسم الإحصائيات */
        .stats-section {
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            background: #f8fafc;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
            border: 1px solid #e9ecef;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.3rem;
            color: white;
        }

        .stat-icon.users {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }

        .stat-icon.family {
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
        }

        .stat-icon.bot {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .stat-icon.manual {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* جدول البيانات */
        .table-section {
            padding: 25px 30px;
        }

        .table-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
        }

        .custom-table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .custom-table thead {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        }

        .custom-table th {
            font-weight: 700;
            color: var(--secondary-color);
            padding: 18px 15px;
            border-bottom: 2px solid #cbd5e1;
            white-space: nowrap;
        }

        .custom-table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
            transition: var(--transition);
        }

        .custom-table tbody tr {
            transition: var(--transition);
        }

        .custom-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .custom-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-bot {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-manual {
            background-color: #fef3c7;
            color: #92400e;
        }

        /* حالة عدم وجود بيانات */
        .no-data {
            text-align: center;
            padding: 60px 20px;
            background: #f8fafc;
            border-radius: 10px;
        }

        .no-data-icon {
            font-size: 3.5rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .no-data-text {
            font-size: 1.2rem;
            color: #6b7280;
            margin-bottom: 15px;
        }

        .no-data-subtext {
            color: #9ca3af;
            font-size: 0.95rem;
        }

        /* زر التصدير */
        .export-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-right: auto;
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(16, 185, 129, 0.2);
            color: white;
        }

        /* تذييل الصفحة */
        .report-footer {
            padding: 20px 30px;
            background: #f8fafc;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }

        /* تنسيقات للهواتف */
        @media (max-width: 768px) {
            body {
                padding: 10px 0;
            }

            .main-container {
                padding: 0 10px;
            }

            .report-header {
                padding: 20px 15px;
            }

            .report-title {
                font-size: 1.5rem;
            }

            .filters-section, .stats-section, .table-section {
                padding: 20px 15px;
            }

            .filter-btn, .export-btn {
                padding: 12px 20px;
                font-size: 0.95rem;
            }

            .stat-card {
                margin-bottom: 15px;
            }

            .table-container {
                border-radius: 8px;
            }

            .custom-table {
                font-size: 0.85rem;
            }

            .custom-table th, .custom-table td {
                padding: 12px 8px;
            }
        }

        @media (max-width: 576px) {
            .report-title {
                font-size: 1.3rem;
            }

            .custom-table {
                font-size: 0.8rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .badge-status {
                font-size: 0.75rem;
                padding: 4px 8px;
            }
        }

        /* تخصيص شريط التمرير */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="report-card">
            <!-- رأس التقرير -->
            <div class="report-header">
                <h1 class="report-title">
                    <i class="fas fa-chart-pie"></i>
                    تقرير توزيع الفعاليات
                </h1>
                <p class="report-subtitle">
                    عرض وتحليل بيانات توزيع المساعدات حسب الفعاليات
                </p>
            </div>

            <!-- قسم الفلاتر -->
            <!-- قسم الفلاتر -->
            <div class="filters-section">
                <form method="GET" class="row g-3 align-items-end">

                    <!-- نوع الرسالة -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <label class="filter-label">
                        <i class="fas fa-envelope me-2"></i>
                        نوع الرسالة
                        </label>

                        <select name="message_type" class="form-select form-select-custom">

                        <option value="received"
                        {{ request('message_type') == 'received' ? 'selected' : '' }}>
                        تم الاستلام
                        </option>

                        <option value="invitation"
                        {{ request('message_type') == 'invitation' ? 'selected' : '' }}>
                        دعوة حضور
                        </option>
                        <option value="attended"
                        {{ request('message_type') == 'attended' ? 'selected' : '' }}>
                        تم الحضور
                        </option>
                        <option value="excused"
                        {{ request('message_type') == 'excused' ? 'selected' : '' }}>
                        معتذر
                        </option>
                        <option value="not_received"
                        {{ request('message_type') == 'not_received' ? 'selected' : '' }}>
                        لم يتم التسليم
                        </option>
                        <option value="not_attended"
                        {{ request('message_type') == 'not_attended' ? 'selected' : '' }}>
                        لم يحضر
                        </option>

                        </select>
                    </div>

                    <!-- الفعالية -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <label class="filter-label">
                        <i class="fas fa-calendar-alt me-2"></i>
                        اختر الفعالية
                        </label>

                        <select name="event_id" class="form-select form-select-custom">

                            <option value="">-- جميع الفعاليات --</option>

                            @foreach($events as $event)
                                <option value="{{ $event->id }}"
                                    {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                    {{ $event->name }} - {{ date('Y-m-d', strtotime($event->event_date)) }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <!-- زر البحث -->
                    <div class="col-lg-4 col-md-12 col-12">
                        <button type="submit" class="btn filter-btn">
                        <i class="fas fa-search"></i>
                        عرض التقرير
                        </button>
                    </div>

                </form>
            </div>
            <h2 class="text-center">عنوان الفعالية {{ $logs->first()->event->name ?? '-' }}</h2>

            <!-- قسم الإحصائيات -->
            @if(count($logs) > 0)
            <div class="stats-section">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="stat-card">
                            <div class="stat-icon users">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-value">{{ count($logs) }}</div>
                            <div class="stat-label">مستفيد</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="stat-card">
                            <div class="stat-icon family">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="stat-value">{{ $logs->sum('user.family_members') }}</div>
                            <div class="stat-label">فرد</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="stat-card">
                            <div class="stat-icon bot">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="stat-value">{{ $logs->where('delivery_status', 'sent')->count() }}</div>
                            <div class="stat-label">مرسل آلي</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="stat-card">
                            <div class="stat-icon manual">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="stat-value">{{ $logs->where('delivery_status', 'manual')->count() }}</div>
                            <div class="stat-label">مرسل يدوي</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="stat-card border-danger">
                            <div class="stat-icon bg-danger">
                                <i class="fas fa-copy"></i>
                            </div>
                            <div class="stat-value text-danger">{{ count($duplicateNames) }}</div>
                            <div class="stat-label">أسماء مكررة</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- جدول البيانات -->
            <div class="table-section">
                @if(count($logs))
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="mb-0">
                            <i class="fas fa-table me-2"></i>
                            تفاصيل المستفيدين
                        </h6>
                        <button class="btn export-btn" onclick="exportToExcel()">
                            <i class="fas fa-file-export"></i>
                            تصدير Excel
                        </button>
                    </div>

                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table custom-table">
                                <thead>
                                    <!-- صف البحث -->
                                    <tr>
                                        <th colspan="11">
                                            <input type="text" id="searchInput" class="form-control"
                                                placeholder="🔍 ابحث في الجدول..."
                                                onkeyup="searchTable()">
                                        </th>
                                    </tr>

                                    <!-- عناوين الأعمدة -->
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم الرباعي</th>
                                        <th>رقم مصري</th>
                                        <th>رقم الهوية</th>
                                        <th>الكود</th>
                                        <th>العنوان</th>
                                        <th>أفراد الأسرة</th>
                                        <th>عدد مرات الاستفادة</th>
                                        <th>المرسل</th>
                                        <th>طريقة الإرسال</th>
                                        <th>الإجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $index => $log)
                                        <tr>
                                            <td class="fw-bold">{{ $index + 1 }}</td>
                                            
                                            <td 
                                                style="{{ in_array(optional($log->user)->full_name, $duplicateNames) ? 'color: #dc3545; font-weight: bold;' : '' }}"
                                            >
                                                @if(in_array(optional($log->user)->full_name, $duplicateNames))
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                @endif

                                                {{ optional($log->user)->full_name ?? '-' }}
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $log->user->egypt_number ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $log->user->national_id ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $log->user->category->name ?? '-' }}</span>
                                            </td>

                                            <td>{{ $log->user->address ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    {{ $log->user->family_members ?? '0' }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $log->user->benefit_count ?? '0' }}
                                            </td>
                                            <td>
                                                {{ implode(' ', array_slice(explode(' ', $log->sender_type), 0, 2)) }}
                                            </td>

                                            <td>
                                                @if($log->delivery_status === 'sent')
                                                    <span class="badge-status badge-bot">
                                                        <i class="fas fa-robot me-1"></i> آلي
                                                    </span>
                                                @else
                                                    <span class="badge-status badge-manual">
                                                        <i class="fas fa-user-edit me-1"></i> يدوي
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(request('message_type') === 'invitation')
                                                    {{-- عرض زر الاعتذار إذا كنا في صفحة الدعوات --}}
                                                    <form method="POST" action="{{ route('attendance.excuse', $log->id) }}" style="display:inline;" 
                                                        onsubmit="return confirmExcuse(event, '{{ optional($log->user)->full_name }}')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-secondary btn-sm">
                                                            معتذر
                                                        </button>
                                                    </form>
                                                    
                                                @elseif(request('message_type') === 'excused')
                                                    {{-- عرض اسم الموظف الذي قام بالاعتذار إذا كنا في صفحة المعتذرين --}}
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="fas fa-user-check me-1"></i>
                                                        بواسطة: 
                                                        @if($log->excusedBy)
                                                            {{ collect(explode(' ', $log->excusedBy->name))->take(2)->implode(' ') }}
                                                        @else
                                                            غير محدد
                                                        @endif
                                                    </span>
                                                    <br>
                                                    <small class="text-muted" style="font-size: 10px;">
                                                        تاريخ: {{ $log->excused_at }}
                                                    </small>
                                                    
                                                @else
                                                    {{-- الحالات الأخرى (مثل تم الحضور أو لم يحضر) --}}
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="no-data">
                        <div class="no-data-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <div class="no-data-text">لا توجد بيانات للعرض</div>
                        <div class="no-data-subtext">
                            @if(request('event_id'))
                                لم يتم العثور على بيانات للفعالية المحددة
                            @else
                                يرجى اختيار فعالية لعرض بياناتها
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- تذييل الصفحة -->
            <div class="report-footer">
                <div class="row align-items-center">
                    <div class="col-md-6 col-12 text-md-start text-center mb-md-0 mb-2">
                        <i class="fas fa-calendar-alt me-1"></i>
                        تاريخ التقرير: {{ date('Y-m-d') }}
                    </div>
                    <div class="col-md-6 col-12 text-md-end text-center">
                        <i class="fas fa-info-circle me-1"></i>
                        النظام الآلي لتوزيع المساعدات
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SheetJS لتصدير Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        // دالة تصدير البيانات إلى Excel
        // توحيد رقم الجوال المصري
        function normalizeEgyptPhone(phone) {
            if (!phone) return '';

            // تحويل إلى نص + حذف أي شيء غير أرقام
            phone = phone.toString().replace(/\D/g, '');

            // إذا يبدأ بـ 20 → تمام
            if (phone.startsWith('20')) {
                return phone;
            }

            // إذا يبدأ بـ 0 → نحذف الصفر ونضيف 20
            if (phone.startsWith('0')) {
                return '20' + phone.substring(1);
            }

            // إذا يبدأ بـ 1 → نضيف 20
            if (phone.startsWith('1')) {
                return '20' + phone;
            }

            // أي حالة ثانية
            return phone;
        }


        // دالة تصدير Excel
        function exportToExcel() {
            let data = [];

            // عناوين الأعمدة فقط
            data.push(["الاسم الرباعي", "رقم الهوية", "رقم الجوال"]);

            // البيانات
            @foreach($logs as $log)
                data.push([
                    "{{ $log->user->full_name ?? '-' }}",
                    "{{ $log->user->national_id ?? '-' }}",
                    normalizeEgyptPhone("{{ $log->user->egypt_number ?? '' }}")
                ]);
            @endforeach

            // إنشاء ورقة
            let ws = XLSX.utils.aoa_to_sheet(data);

            // إنشاء ملف
            let wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "المستفيدين");

            // اسم الملف
            let fileName = "المستفيدين_{{ date('Y_m_d') }}.xlsx";

            // تصدير
            XLSX.writeFile(wb, fileName);

            alert("تم تصدير البيانات بنجاح");
        }

        // تأثيرات تفاعلية
        document.addEventListener('DOMContentLoaded', function() {
            // إضافة تأثير للبطاقات عند التحميل
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // تأثير للصفوف في الجدول
            const tableRows = document.querySelectorAll('.custom-table tbody tr');
            tableRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';

                setTimeout(() => {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, index * 50);
            });
        });

        function searchTable() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase();
            let table = document.querySelector(".custom-table");
            let tr = table.querySelectorAll("tbody tr");

            tr.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        }
    </script>
<script>
function confirmExcuse(event, userName) {
    // منع الإرسال التلقائي للنموذج حتى نتحقق من التأكيد
    event.preventDefault();
    
    // تخصيص الرسالة لتشمل اسم الشخص
    const message = `هل أنت متأكد أنك تريد تسجيل (${userName}) كمعتذر؟`;
    
    if (confirm(message)) {
        // إذا وافق المستخدم، قم بإرسال النموذج الذي يحتوي على الزر المضغوط
        event.target.submit();
    }
}
</script>
</body>
</html>
