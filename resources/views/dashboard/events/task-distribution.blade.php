<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>توزيع المهام</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome للأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Select2 للبحث في القوائم -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,
        <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
            <circle cx='50' cy='50' r='48' fill='%234361ee'/>
            <rect x='20' y='25' width='60' height='50' rx='8' ry='8' fill='white'/>
            <line x1='25' y1='38' x2='75' y2='38' stroke='%234361ee' stroke-width='4' stroke-linecap='round'/>
            <line x1='25' y1='50' x2='75' y2='50' stroke='%234361ee' stroke-width='4' stroke-linecap='round'/>
            <line x1='25' y1='62' x2='75' y2='62' stroke='%234361ee' stroke-width='4' stroke-linecap='round'/>
        </svg>">
    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #F0F8FF 0%, #E0F2FE 50%, #F8FAFC 100%);
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* خلفية متحركة ناعمة */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(135, 206, 235, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.3) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        /* بطاقات التصميم الجديدة - أبيض مع أزرق سماوي */
        .modern-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(173, 216, 230, 0.5);
            border-radius: 35px;
            box-shadow: 0 30px 60px rgba(135, 206, 235, 0.2), 0 0 0 1px rgba(173, 216, 230, 0.3) inset;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }
        
        .modern-card:hover {
            transform: translateY(-10px) scale(1.01);
            box-shadow: 0 40px 80px rgba(135, 206, 235, 0.3), 0 0 0 2px rgba(135, 206, 235, 0.4) inset;
        }
        
        /* رأس البطاقة الجديد - أبيض مع أزرق سماوي */
        .card-header-custom {
            background: linear-gradient(135deg, #FFFFFF 0%, #F0F8FF 50%, #E6F3FF 100%);
            color: #2C3E50;
            padding: 25px 30px;
            border: none;
            border-bottom: 3px solid #87CEEB;
            position: relative;
            overflow: hidden;
        }
        
        .card-header-custom::before {
            content: '✦';
            position: absolute;
            top: -20px;
            right: -20px;
            font-size: 150px;
            color: rgba(135, 206, 235, 0.1);
            transform: rotate(15deg);
        }
        
        .card-header-custom h4 {
            margin: 0;
            font-weight: 800;
            font-size: 2rem;
            text-shadow: 1px 1px 2px rgba(135, 206, 235, 0.3);
            position: relative;
            z-index: 1;
        }
        
        .card-header-custom i {
            margin-left: 15px;
            font-size: 2.2rem;
            color: #87CEEB;
            filter: drop-shadow(1px 1px 2px rgba(135, 206, 235, 0.3));
        }
        
        /* خيارات نوع الفعالية - أزرق سماوي */
        .event-mode-container {
            background: linear-gradient(135deg, #F8FCFE 0%, #F0F9FF 100%);
            border-radius: 25px;
            padding: 25px;
            margin-bottom: 25px;
            border: 2px solid #87CEEB;
            position: relative;
        }
        
        .event-mode-container::after {
            content: '⚡';
            position: absolute;
            top: -15px;
            left: 20px;
            font-size: 30px;
            background: #87CEEB;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(135, 206, 235, 0.5);
        }
        
        .custom-radio {
            display: inline-block;
            margin-left: 40px;
            cursor: pointer;
        }
        
        .custom-radio input[type="radio"] {
            display: none;
        }
        
        .custom-radio label {
            position: relative;
            padding-right: 45px;
            font-size: 1.2rem;
            font-weight: 600;
            color: #2C3E50;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .custom-radio label:before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 28px;
            height: 28px;
            border: 3px solid #87CEEB;
            border-radius: 50%;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(135, 206, 235, 0.3);
        }
        
        .custom-radio label:after {
            content: '';
            position: absolute;
            right: 9px;
            top: 50%;
            transform: translateY(-50%) scale(0);
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: linear-gradient(135deg, #87CEEB, #5F9EA0);
            transition: all 0.3s ease;
        }
        
        .custom-radio input[type="radio"]:checked + label:after {
            transform: translateY(-50%) scale(1);
        }
        
        .custom-radio input[type="radio"]:checked + label:before {
            border-color: #5F9EA0;
        }
        
        /* تنسيق الحقول الجديدة - حواف زرقاء سماوية */
        .form-control-custom {
            border: 2px solid #e0f0f5;
            border-radius: 20px;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            border: 2px solid rgba(135, 206, 235, 0.4);
        }
        
        .form-control-custom:focus {
            border-color: #5F9EA0;
            box-shadow: 0 0 0 4px rgba(135, 206, 235, 0.2);
            outline: none;
        }
        
        /* تنسيق Select2 للبحث */
        .select2-container--bootstrap-5 .select2-selection {
            border: 2px solid rgba(135, 206, 235, 0.4) !important;
            border-radius: 20px !important;
            min-height: 50px !important;
            padding: 5px !important;
        }
        
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #5F9EA0 !important;
            box-shadow: 0 0 0 4px rgba(135, 206, 235, 0.2) !important;
        }
        
        .select2-container--bootstrap-5 .select2-dropdown {
            border: 2px solid #87CEEB !important;
            border-radius: 20px !important;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(135, 206, 235, 0.2);
        }
        
        .select2-container--bootstrap-5 .select2-results__option--selected {
            background: linear-gradient(135deg, #87CEEB, #B0E0E6) !important;
            color: #2C3E50 !important;
            font-weight: bold;
        }
        
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background: linear-gradient(135deg, #ADD8E6, #87CEEB) !important;
            color: #2C3E50 !important;
        }
        
        .select2-search__field {
            border: 2px solid #87CEEB !important;
            border-radius: 15px !important;
            padding: 10px !important;
            font-family: 'Cairo', sans-serif !important;
        }
        
        /* تنسيق الجدول الجديد */
        .table-modern {
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(135, 206, 235, 0.15);
            background: white;
        }
        
        .table-modern thead {
            background: linear-gradient(135deg, #F0F8FF 0%, #E0F2FE 100%);
            color: #2C3E50;
            border-bottom: 3px solid #87CEEB;
        }
        
        .table-modern th {
            padding: 20px 15px;
            font-weight: 700;
            font-size: 1.2rem;
            border: none;
            text-align: center;
            letter-spacing: 0.5px;
        }
        
        .table-modern th i {
            color: #87CEEB;
            margin-left: 8px;
            font-size: 1.3rem;
        }
        
        .table-modern td {
            padding: 15px;
            background: white;
            border-bottom: 2px solid rgba(135, 206, 235, 0.15);
            vertical-align: middle;
        }
        
        .table-modern tr:hover td {
            background: linear-gradient(135deg, rgba(135, 206, 235, 0.05), rgba(255, 255, 255, 0.5));
        }
        
        /* الأزرار الجديدة */
        .btn-modern {
            padding: 14px 30px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 10px 20px rgba(135, 206, 235, 0.2);
        }
        
        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-modern:hover::before {
            left: 100%;
        }
        
        .btn-modern:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 30px rgba(135, 206, 235, 0.3);
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, #87CEEB, #ADD8E6, #B0E0E6);
            color: #2C3E50;
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .btn-success-modern {
            background: linear-gradient(135deg, #5F9EA0, #87CEEB);
            color: white;
        }
        
        .btn-dark-modern {
            background: linear-gradient(135deg, #F0F8FF, #E0F2FE, #D4F1F9);
            color: #2C3E50;
            border: 2px solid #87CEEB;
        }
        
        .btn-secondary-modern {
            background: linear-gradient(135deg, #FFFFFF, #F0F8FF);
            color: #2C3E50;
            border: 2px solid #87CEEB;
        }
        
        .btn-remove {
            background: linear-gradient(135deg, #FFB6C1, #FFC0CB);
            color: #2C3E50;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            padding: 0;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 182, 193, 0.4);
            border: none;
            margin: 0 auto;
        }
        
        .btn-remove:hover {
            transform: rotate(90deg) scale(1.1);
            box-shadow: 0 8px 25px rgba(255, 182, 193, 0.6);
        }
        
        /* تنسيق النص المولد - أزرق سماوي */
        .generated-box {
            background: linear-gradient(135deg, #F0F8FF, #FFFFFF);
            color: #2C3E50;
            padding: 25px;
            border-radius: 25px;
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            line-height: 2;
            white-space: pre-line;
            border: 3px solid #87CEEB;
            box-shadow: 0 0 30px rgba(135, 206, 235, 0.2);
            direction: ltr;
            text-align: left;
            position: relative;
        }
        
        .generated-box::before {
            content: '📋';
            position: absolute;
            top: -15px;
            right: -15px;
            font-size: 40px;
            background: #87CEEB;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 5px 20px rgba(135, 206, 235, 0.5);
        }
        
        /* تحسينات الهاتف */
        /* عرض بطاقات في الجوال */
        @media (max-width: 768px) {

            .table-modern thead{
                display:none;
            }

            .table-modern{
                box-shadow:none;
                background:transparent;
            }

            .table-modern tbody{
                display:flex;
                flex-direction:column;
                gap:15px;
            }

            .table-modern tr{
                display:block;
                background:white;
                border-radius:20px;
                padding:15px;
                box-shadow:0 10px 25px rgba(0,0,0,0.08);
                border:1px solid rgba(135,206,235,0.3);
            }

            .table-modern td{
                display:flex;
                flex-direction:column;
                margin-bottom:12px;
                border:none !important;
                padding:0;
            }

            .table-modern td:last-child{
                margin-bottom:0;
            }

            .table-modern td:before{
                content:attr(data-label);
                font-weight:800;
                font-size:0.9rem;
                color:#5F9EA0;
                margin-bottom:6px;
            }

            .btn-remove{
                margin-top:8px;
            }

        }
        
        /* شارة ذهبية للعناصر المهمة */
        .gold-badge {
            background: linear-gradient(135deg, #87CEEB, #ADD8E6);
            color: #2C3E50;
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 10px rgba(135, 206, 235, 0.3);
        }
        
        
        /* تنسيق البحث */
        .search-icon {
            position: relative;
        }
        
        .search-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #87CEEB;
            z-index: 10;
        }
        
        /* تنسيق الألوان الجديدة للحالات */
        .status-badge {
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-🟢 { background: linear-gradient(135deg, #90EE90, #98FB98); color: #2C3E50; }
        .status-🟡 { background: linear-gradient(135deg, #FFD700, #FFFACD); color: #2C3E50; }
        .status-✅ { background: linear-gradient(135deg, #32CD32, #98FB98); color: white; }
        .status-⛔ { background: linear-gradient(135deg, #FFB6C1, #FFC0CB); color: #2C3E50; }
        
        /* تلميحات جذابة */
        .hint-text {
            color: #5F9EA0;
            font-size: 0.9rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .hint-text i {
            color: #87CEEB;
        }

        /* خلفيات الصناديق */
        #systemEventBox, #manualEventBox {
            background: linear-gradient(135deg, #FFFFFF, #F8FCFE) !important;
        }
    </style>
</head>

<body>

<div class="main-container">
    <!-- بطاقة المعلومات الرئيسية -->
    <div class="modern-card mb-4">
        <div class="card-header-custom d-flex align-items-center">
            <i class="fas fa-snowflake"></i>
            <h4 class="mb-0">توزيع المهام</h4>
            <span class="gold-badge me-auto">
                <i class="fas fa-star"></i> {{ $event->name }}
            </span>
        </div>
        
        <div class="p-4">
            <!-- اختيار نوع الفعالية -->
            <div class="event-mode-container">
                <h5 class="mb-4 fw-bold" style="color: #2C3E50;">
                    <i class="fas fa-magic ms-2" style="color: #87CEEB;"></i>
                    اختر نوع الفعالية
                </h5>
                <div class="d-flex flex-wrap">
                    <div class="custom-radio">
                        <input type="radio" name="event_mode" value="system" id="modeSystem" checked onchange="toggleEventMode()">
                        <label for="modeSystem">
                            <i class="fas fa-database ms-1"></i>
                            فعالية من النظام
                        </label>
                    </div>
                    <div class="custom-radio">
                        <input type="radio" name="event_mode" value="manual" id="modeManual" onchange="toggleEventMode()">
                        <label for="modeManual">
                            <i class="fas fa-pen-fancy ms-1"></i>
                            فعالية يدوية
                        </label>
                    </div>
                </div>
            </div>

            <!-- بيانات الفعالية من النظام -->
            <div id="systemEventBox" class="modern-card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-md-4 col-sm-6 col-12">
                        <label class="fw-bold mb-2" style="color: #2C3E50;">
                            <i class="fas fa-tag ms-1" style="color: #87CEEB;"></i>
                            اسم الفعالية
                        </label>
                        <input type="text" class="form-control form-control-custom" value="{{ $event->name }}" readonly>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="fw-bold mb-2" style="color: #2C3E50;">
                            <i class="fas fa-calendar-alt ms-1" style="color: #87CEEB;"></i>
                            التاريخ
                        </label>
                        <input type="text" class="form-control form-control-custom" value="{{ $event->event_date }}" readonly>
                    </div>
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="fw-bold mb-2" style="color: #2C3E50;">
                            <i class="fas fa-clock ms-1" style="color: #87CEEB;"></i>
                            الوقت
                        </label>
                        <input type="text" class="form-control form-control-custom" value="{{ $event->event_time }}" readonly>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="fw-bold mb-2" style="color: #2C3E50;">
                            <i class="fas fa-map-pin ms-1" style="color: #87CEEB;"></i>
                            المكان
                        </label>
                        <input type="text" class="form-control form-control-custom" value="{{ $event->location }}" readonly>
                    </div>
                </div>
            </div>

            <!-- الفعالية اليدوية -->
            <div id="manualEventBox" class="modern-card p-4 mb-4" style="display:none;">
                <div class="row g-3">
                    <div class="col-md-4 col-sm-6 col-12">
                        <label class="fw-bold mb-2" style="color: #2C3E50;">
                            <i class="fas fa-tag ms-1" style="color: #87CEEB;"></i>
                            اسم الفعالية
                        </label>
                        <input type="text" id="manual_event_name" class="form-control form-control-custom" placeholder="أدخل اسم الفعالية">
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="fw-bold mb-2" style="color: #2C3E50;">
                            <i class="fas fa-calendar-alt ms-1" style="color: #87CEEB;"></i>
                            التاريخ
                        </label>
                        <input type="date" id="manual_event_date" class="form-control form-control-custom">
                    </div>
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="fw-bold mb-2" style="color: #2C3E50;">
                            <i class="fas fa-clock ms-1" style="color: #87CEEB;"></i>
                            الوقت
                        </label>
                        <input type="time" id="manual_event_time" class="form-control form-control-custom">
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="fw-bold mb-2" style="color: #2C3E50;">
                            <i class="fas fa-map-pin ms-1" style="color: #87CEEB;"></i>
                            المكان
                        </label>
                        <input type="text" id="manual_event_location" class="form-control form-control-custom" placeholder="أدخل المكان">
                    </div>
                </div>
            </div>

            <form action="{{ route('events.task-distribution.store',$event->id) }}" method="POST">
                @csrf

                <!-- حقول الفعالية اليدوية مخفية -->
                <input type="hidden" name="event_mode" id="event_mode_input" value="system">
                <input type="hidden" name="manual_event_name" id="manual_event_name_input">
                <input type="hidden" name="manual_event_date" id="manual_event_date_input">
                <input type="hidden" name="manual_event_time" id="manual_event_time_input">
                <input type="hidden" name="manual_event_location" id="manual_event_location_input">

                <!-- جدول المهام -->
                <div class="table-responsive">
                    <table class="table-modern w-100">
                        <thead>
                            <tr>
                                <th><i class="fas fa-tasks"></i> المهمة</th>
                                <th><i class="fas fa-user"></i> الشخص</th>
                                <th><i class="fas fa-check-circle"></i> الحالة</th>
                                <th><i class="fas fa-trash"></i> حذف</th>
                            </tr>
                        </thead>
                        <tbody id="tasksBody">
                            @php
                                $defaultRoles = [
                                    'مسؤول الشفت','توزيع بطاقات','توزيع بطاقات','كتابة بطاقات','كتابة بطاقات',
                                    'كتابة بطاقات','متابعة برنامج','متابعة برنامج','متابعة برنامج','متابعة برنامج',
                                    'متابعة برنامج','تصوير','تصوير','تنظيم الحضور','تنظيم الحضور','تنظيم الحضور',
                                    'تنظيم الحضور','تنظيم الحضور','تنظيم الحضور','كاشير','مساعد كاشير'
                                ];
                            @endphp

                            @if($tasks->count())
                                @foreach($tasks as $i => $task)
                                    <tr>
                                        <td data-label="المهمة">
                                            <input type="text" name="assignments[{{$i}}][role]" class="form-control form-control-custom roleInput" value="{{ $task->role }}">
                                        </td>
                                        <td data-label="الشخص">
                                            <select name="assignments[{{$i}}][user_id]" class="form-control form-control-custom userSelect select2-search">
                                                <option value="">🔍 ابحث عن شخص...</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" @if($user->id == $task->telegram_user_id) selected @endif>{{ $user->full_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td data-label="الحالة">
                                            <select name="assignments[{{$i}}][status]" class="form-control form-control-custom statusInput">
                                                <option value="">حدد الحالة</option>
                                                <option value="🟢" @if($task->status=='🟢') selected @endif>🟢</option>
                                                <option value="🟡" @if($task->status=='🟡') selected @endif>🟡</option>
                                                <option value="✅" @if($task->status=='✅') selected @endif>✅</option>
                                                <option value="⛔" @if($task->status=='⛔') selected @endif>⛔</option>
                                            </select>
                                        </td>
                                        <td data-label="حذف">
                                            <button type="button" class="btn btn-remove removeRow">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                @foreach($defaultRoles as $i => $role)
                                    <tr>
                                        <td data-label="المهمة">
                                            <input type="text" name="assignments[{{$i}}][role]" class="form-control form-control-custom roleInput" value="{{ $role }}">
                                        </td>
                                        <td data-label="الشخص">
                                            <select name="assignments[{{$i}}][user_id]" class="form-control form-control-custom userSelect select2-search">
                                                <option value="">🔍 ابحث عن شخص...</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td data-label="الحالة">
                                            <select name="assignments[{{$i}}][status]" class="form-control form-control-custom statusInput">
                                                <option value="">حدد الحالة</option>
                                                <option value="🟢">🟢</option>
                                                <option value="🟡">🟡</option>
                                                <option value="✅">✅</option>
                                                <option value="⛔">⛔</option>
                                            </select>
                                        </td>
                                        <td data-label="حذف">
                                            <button type="button" class="btn btn-remove removeRow">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="hint-text mb-3">
                    <i class="fas fa-lightbulb"></i>
                    يمكنك البحث عن اسم الشخص بسهولة من القائمة المنسدلة
                </div>

                 <!-- الأزرار -->
                <div class="d-flex gap-3 mt-4 flex-wrap justify-content-center">
                    <button type="button" class="btn btn-modern btn-primary-modern" id="addTask">
                        <i class="fas fa-plus-circle"></i> إضافة مهمة
                    </button>
                    <button type="submit" class="btn btn-modern btn-success-modern">
                        <i class="fas fa-save"></i> حفظ التوزيع
                    </button>
                    <button type="button" class="btn btn-modern btn-dark-modern" onclick="generateText()">
                        <i class="fas fa-bolt"></i> توليد النص
                    </button>
                    <button type="button" class="btn btn-modern btn-secondary-modern" onclick="copyText()">
                        <i class="fas fa-copy"></i> نسخ النص
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- بطاقة النص المولد -->
    <div class="modern-card mt-4">
        <div class="card-header-custom">
            <i class="fas fa-envelope-open-text"></i>
            <h5 class="mb-0">النص الجاهز</h5>
        </div>
        <div class="p-4">
            <div id="generatedText" class="generated-box">
                <!-- النص المولد سيظهر هنا -->
            </div>
            <div class="text-center mt-3">
                <span class="gold-badge">
                    <i class="fas fa-star"></i>
                    انقر على زر "توليد النص" لإنشاء النص
                </span>
            </div>
        </div>
    </div>
</div>

<!-- السكريبتات -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let index = document.querySelectorAll("#tasksBody tr").length;

    // تفعيل خاصية البحث في جميع قوائم الاختيار
    function initializeSelect2() {
        $('.select2-search').each(function() {
            if (!$(this).data('select2')) {
                $(this).select2({
                    theme: "bootstrap-5",
                    width: '100%',
                    placeholder: "🔍 ابحث عن شخص...",
                    allowClear: true,
                    language: {
                        searching: function() {
                            return "جاري البحث...";
                        },
                        noResults: function() {
                            return "لا توجد نتائج";
                        }
                    },
                    templateResult: formatUserOption,
                    templateSelection: formatUserSelection
                });
            }
        });
    }

    // تنسيق عرض النتائج
    function formatUserOption(user) {
        if (!user.id) {
            return user.text;
        }
        var $user = $(
            '<span><i class="fas fa-user-circle ms-2" style="color: #87CEEB;"></i>' + user.text + '</span>'
        );
        return $user;
    }

    // تنسيق العنصر المختار
    function formatUserSelection(user) {
        if (!user.id) {
            return user.text;
        }
        return "👤 " + user.text;
    }

    // تفعيل البحث عند تحميل الصفحة
    $(document).ready(function() {
        initializeSelect2();
    });

    function toggleEventMode() {
        let mode = document.querySelector('input[name="event_mode"]:checked').value;
        if (mode === "manual") {
            document.getElementById("manualEventBox").style.display = "block";
            document.getElementById("systemEventBox").style.display = "none";
        } else {
            document.getElementById("manualEventBox").style.display = "none";
            document.getElementById("systemEventBox").style.display = "block";
        }
    }

    document.getElementById("addTask").onclick = function() {
        let users = `
            <option value="">🔍 ابحث عن شخص...</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->full_name }}</option>
            @endforeach
        `;

        let row = `
            <tr>
                <td data-label="المهمة">
                    <input type="text" name="assignments[${index}][role]" class="form-control form-control-custom roleInput" placeholder="أدخل المهمة">
                </td>
                <td data-label="الشخص">
                    <select name="assignments[${index}][user_id]" class="form-control form-control-custom userSelect select2-search">
                        ${users}
                    </select>
                </td>
                <td data-label="الحالة">
                    <select name="assignments[${index}][status]" class="form-control form-control-custom statusInput">
                        <option value="">حدد الحالة</option>
                        <option value="🟢">🟢</option>
                        <option value="🟡">🟡</option>
                        <option value="✅">✅</option>
                        <option value="⛔">⛔</option>
                    </select>
                </td>
                <td data-label="حذف">
                    <button type="button" class="btn btn-remove removeRow">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;

        document.getElementById("tasksBody").insertAdjacentHTML("beforeend", row);
        
        // تفعيل البحث على الصف الجديد
        let newSelect = document.querySelector(`#tasksBody tr:last-child .select2-search`);
        if (newSelect) {
            $(newSelect).select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "🔍 ابحث عن شخص...",
                allowClear: true,
                language: {
                    searching: function() {
                        return "جاري البحث...";
                    },
                    noResults: function() {
                        return "لا توجد نتائج";
                    }
                },
                templateResult: formatUserOption,
                templateSelection: formatUserSelection
            });
        }
        
        index++;
    };

    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("removeRow") || e.target.closest(".removeRow")) {
            let btn = e.target.classList.contains("removeRow") ? e.target : e.target.closest(".removeRow");
            let row = btn.closest("tr");
            
            // إتلاف كائن Select2 قبل حذف الصف
            let select = $(row).find('.select2-search');
            if (select.length && select.data('select2')) {
                select.select2('destroy');
            }
            
            row.remove();
        }
    });

    function generateText() {
        let roles = document.querySelectorAll(".roleInput");
        let users = document.querySelectorAll(".userSelect");
        let status = document.querySelectorAll(".statusInput");

        let text = "";

        let mode = document.querySelector('input[name="event_mode"]:checked').value;

        let eventName = "{{ $event->name }}";
        let eventDate = "{{ $event->event_date }}";
        let eventTime = "{{ $event->event_time }}";
        let eventLocation = "{{ $event->location }}";

        if (mode === "manual") {
            eventName = document.getElementById("manual_event_name").value || "غير محدد";
            eventDate = document.getElementById("manual_event_date").value || "غير محدد";
            eventTime = document.getElementById("manual_event_time").value || "غير محدد";
            eventLocation = document.getElementById("manual_event_location").value || "غير محدد";
        }

        text += "🌟━━━━━━━━━━━━━━━━━━🌟\n";
        text += "            ✨ جــدول توزيــع المهــام ✨\n";
        text += "🌟━━━━━━━━━━━━━━━━━━🌟\n\n";
        text += "📅 التاريخ: " + eventDate + "\n";
        text += "⏰ الوقت: " + eventTime + "\n";
        text += "🏷️ الفعالية: " + eventName + "\n";
        text += "📍 المكان: " + eventLocation + "\n";
        text += "────────────────────────────────────\n\n";

        let counter = 1;
        for (let i = 0; i < roles.length; i++) {
            let role = roles[i].value;
            let userSelect = users[i];
            let userId = userSelect.value;
            let st = status[i].value;
            
            // الحصول على اسم الشخص المختار
            let userName = "غير محدد";
            if (userId) {
                let selectedOption = userSelect.options[userSelect.selectedIndex];
                userName = selectedOption ? selectedOption.text : "غير محدد";
            }

            if (userId && role) {
                text += counter + ". " + role + " " + st + "  👤 " + userName + "\n";
                counter++;
            }
        }

        if (counter === 1) {
            text += "⚠️ لا توجد مهام محددة بعد ⚠️\n";
        }

        text += "\n🌟━━━━━━━━━━━━━━━━━━🌟\n";
        text += "          ✅ تقبل الله منا ومنكم صالح الاعمال ✅\n";
        text += "🌟━━━━━━━━━━━━━━━━━━🌟";

        document.getElementById("generatedText").innerText = text;
    }

    function copyText() {
        let text = document.getElementById("generatedText").innerText;
        navigator.clipboard.writeText(text).then(function() {
            alert("✅ تم نسخ النص بنجاح!");
            
            // تأثير بسيط
            let btn = event.target;
            if (btn) {
                btn.style.transform = "scale(0.95)";
                setTimeout(() => {
                    btn.style.transform = "scale(1)";
                }, 200);
            }
        }).catch(function() {
            alert("");
        });
    }

    // إعادة تفعيل البحث إذا تم تغيير أي شيء
    $(document).on('select2:open', function() {
        document.querySelector('.select2-search__field').focus();
    });

document.querySelector('form').addEventListener('submit', function(e){
    e.preventDefault();

    let mode = document.querySelector('input[name="event_mode"]:checked').value;
    document.getElementById('event_mode_input').value = mode;
    document.getElementById('manual_event_name_input').value = document.getElementById('manual_event_name').value;
    document.getElementById('manual_event_date_input').value = document.getElementById('manual_event_date').value;
    document.getElementById('manual_event_time_input').value = document.getElementById('manual_event_time').value;
    document.getElementById('manual_event_location_input').value = document.getElementById('manual_event_location').value;

    // توليد النص قبل إرسال الفورم
    generateText();

    this.submit();
});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>