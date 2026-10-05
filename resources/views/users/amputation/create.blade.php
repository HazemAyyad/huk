<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <title>استكمال بيانات حالات البتر</title>
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Google Fonts - Tajawal -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,
        <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
            <circle cx='50' cy='50' r='48' fill='%23e3f2fd'/>
            <path d='M20 55 L50 30 L80 55 V80 H20 Z' fill='%231976d2'/>
            <circle cx='75' cy='25' r='12' fill='%23ffb300'/>
            <path d='M40 80 V65 H60 V80' fill='white'/>
        </svg>">
    <style>
        * {
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(145deg, #f4f9fc 0%, #e9f2f8 100%);
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }

        /* الكاميرا */
        .camera-card {
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            border-radius: 20px;
            padding: 1rem;
            margin: 0 auto 1rem auto;
            border: 2px solid rgba(14,107,140,0.1);
            box-shadow: 0 8px 20px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
            max-width: 420px;
        }

        .camera-preview-container {
            background: #0a1a1f;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            max-height: 350px;
        }

        .camera-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 16px;
        }

        .camera-controls {
            display: flex;
            gap: 10px;
            padding: 0.8rem;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            justify-content: center;
            backdrop-filter: blur(2px);
        }

        .btn-camera-action {
            background: white;
            border: none;
            border-radius: 30px;
            padding: 0.5rem 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #0a4f6e;
            transition: 0.2s;
            font-size: 0.9rem;
        }

        .btn-camera-action:hover {
            transform: scale(1.05);
            background: #f0f0f0;
        }

        .photo-preview-card {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 2px solid #0e6b8c;
            margin-top: 0.8rem;
        }

        .photo-preview-image {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(14,107,140,0.2);
        }

        .btn-camera-main {
            background: linear-gradient(135deg, #6b4f7c, #533f63);
            color: white;
            border-radius: 50px;
            padding: 0.9rem 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            border: none;
            width: 100%;
            font-size: 1.05rem;
            transition: 0.2s;
            box-shadow: 0 6px 18px rgba(83,63,99,0.25);
        }

        .btn-camera-main:hover {
            background: #533f63;
            transform: translateY(-3px);
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        /* النجمة الحمراء */
        label.required-label::after {
            content: " *";
            color: red;
            font-weight: bold;
        }

        /* عند وجود خطأ */
        .field-error {
            border: 2px solid #dc3545 !important;
            background-color: #fff5f5;
        }

        
        .card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 12px 35px rgba(0, 30, 50, 0.06);
            transition: all 0.2s ease;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(3px);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            border-bottom: none;
            font-weight: 800;
            font-size: 1.25rem;
            padding: 1.2rem 1.8rem;
            background: linear-gradient(135deg, #0a4f6e, #0e6b8c);
            color: white;
            border-radius: 24px 24px 0 0 !important;
            display: flex;
            align-items: center;
            gap: 14px;
            letter-spacing: 0.3px;
        }

        .card-header i {
            font-size: 1.6rem;
            opacity: 0.95;
        }

        .bg-info {
            background: linear-gradient(135deg, #1a6b5e, #218c74) !important;
        }

        .bg-warning {
            background: linear-gradient(135deg, #b45f2a, #d17d3c) !important;
        }

        /* تيليجرام كارد */
        .telegram-card {
            background: white;
            border-right: 10px solid #0088cc;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0,136,204,0.08);
        }

        .telegram-card .card-body {
            padding: 1.8rem 2rem;
        }

        .telegram-data-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.2rem 2rem;
        }

        .telegram-data-item {
            background: #f2f9ff;
            padding: 0.7rem 1.5rem;
            border-radius: 40px;
            border: 1px solid rgba(0,136,204,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.05rem;
        }

        .telegram-data-item i {
            color: #0088cc;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .telegram-data-item strong {
            color: #0a4f6e;
            margin-left: 6px;
            font-weight: 700;
        }

        /* الحقول */
        .form-control, .form-select {
            border: 2px solid #dee6ed;
            border-radius: 18px;
            padding: 0.75rem 1.2rem;
            font-size: 1rem;
            transition: all 0.2s;
            background: white;
            height: auto;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0e6b8c;
            box-shadow: 0 0 0 5px rgba(14,107,140,0.08);
            background: white;
        }

        label {
            font-weight: 700;
            color: #1a3b4e;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.98rem;
        }

        label i {
            color: #0e6b8c;
            font-size: 1rem;
        }

        /* ================ */
        /* خرائط دقيقة جداً - موقع ثابت لا يمكن تغييره */
        /* ================ */
        .map-precision-card {
            background: linear-gradient(145deg, #ffffff, #f8fcff);
            border: 2px solid #cde3ed;
            border-radius: 28px;
            padding: 1.5rem;
            margin-top: 1rem;
            box-shadow: 0 8px 25px rgba(14,107,140,0.08);
        }

        .location-accuracy-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #edf7fa;
            color: #0a4f6e;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            font-weight: 700;
            font-size: 0.95rem;
            border: 1px solid #b8d9e6;
        }

        .location-coordinates {
            background: #0a1a1f;
            color: #e2f0f9;
            padding: 1rem 1.5rem;
            border-radius: 20px;
            font-family: monospace;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            direction: ltr;
            text-align: left;
            border: 1px solid #275f6e;
        }

        .coordinate-value {
            background: #162e36;
            padding: 0.3rem 1rem;
            border-radius: 30px;
            color: #b1e4ff;
        }

        #map {
            height: 400px;
            width: 90%;
            border-radius: 20px;
            border: 3px solid white;
            box-shadow: 0 12px 35px rgba(0,40,60,0.12);
            
            /* التعديل هنا: auto تجعل العنصر يتوسط أفقياً */
            margin: 1rem auto; 
            
            z-index: 5;
            pointer-events: none;
            filter: grayscale(0.2);
        }

        /* تخصيص شكل المؤشر - للعرض فقط */
        .fixed-location-marker {
            position: relative;
        }

        .btn-get-location {
            background: linear-gradient(135deg, #2c5f73, #1e4957);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 1.2rem 2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            width: 100%;
            font-size: 1.2rem;
            transition: 0.3s;
            box-shadow: 0 8px 25px rgba(30,73,87,0.3);
            margin: 1rem 0;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .btn-get-location:hover:not(:disabled) {
            background: #1e4957;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(30,73,87,0.4);
        }

        .btn-get-location:disabled {
            opacity: 0.7;
            cursor: wait;
            transform: none;
        }

        .btn-get-location i {
            font-size: 1.4rem;
        }

        /* سبينر التحميل */
        .spinner {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 4px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        /* كارد كل يتيم */
        .orphan-member-card {
            background: linear-gradient(145deg, #fffaf4, #fff3e8);
            border-radius: 24px;
            padding: 1.8rem;
            border: 2px solid rgba(180,95,42,0.15);
            box-shadow: 0 10px 30px rgba(180,95,42,0.08);
            margin-bottom: 2rem;
            position: relative;
            transition: 0.3s ease;
        }

        /* فاصل بين الأيتام إذا كانوا أكثر من واحد */
        .orphan-member-card:not(:last-child)::after {
            content: "";
            position: absolute;
            bottom: -1.2rem;
            right: 10%;
            width: 80%;
            height: 3px;
            background: linear-gradient(to left, transparent, #d17d3c, transparent);
            border-radius: 50px;
        }

        /* عنوان اليتيم */
        .orphan-title {
            font-weight: 900;
            font-size: 1.3rem;
            color: #b45f2a;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* شارة عرض الاسم والهوية */
        .member-info-badge {
            background: white;
            border-radius: 50px;
            padding: 0.8rem 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(180,95,42,0.2);
            box-shadow: 0 5px 15px rgba(0,0,0,0.04);
            font-size: 1.05rem;
        }

        /* أيقونات داخل الشارة */
        .member-info-badge i {
            color: #b45f2a;
            margin-left: 6px;
        }

        /* تمييز النص */
        .member-info-badge strong {
            color: #0a4f6e;
        }
@media (max-width: 768px) {

    .orphan-member-card {
        padding: 1.2rem;
    }

    .orphan-title {
        font-size: 1.05rem;
        flex-wrap: wrap;
    }

    .member-info-badge {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        font-size: 0.95rem;
        border-radius: 18px;
    }

    .member-info-badge span {
        width: 100%;
    }

    .orphan-member-card .row > div {
        width: 100%;
    }

    .orphan-member-card .form-control,
    .orphan-member-card .form-select {
        font-size: 0.95rem;
    }

}

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* رسالة طلب الإذن */
        .permission-request {
            background: linear-gradient(145deg, #fff9e6, #fff3d6);
            border: 2px solid #ffb347;
            border-radius: 24px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .permission-icon {
            font-size: 3rem;
            color: #ff9800;
            margin-bottom: 1rem;
        }

        .btn-permission {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            border: none;
            border-radius: 60px;
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            font-weight: 800;
            margin-top: 1rem;
            transition: 0.3s;
        }

        .btn-permission:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(255,152,0,0.4);
        }

        .location-info-card {
            background: linear-gradient(145deg, #e3f2fd, #d1ecfd);
            border-radius: 24px;
            padding: 1.5rem;
            border-right: 8px solid #0a4f6e;
        }

        .location-success {
            color: #0a4f6e;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }

        .location-locked {
            background: rgba(10,79,110,0.1);
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            color: #0a4f6e;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .location-locked i {
            color: #0a4f6e;
        }

        .btn-save {
            background: linear-gradient(135deg, #0a4f6e, #0e6b8c);
            border: none;
            border-radius: 80px;
            padding: 1.2rem 3rem;
            font-size: 1.4rem;
            font-weight: 800;
            color: white;
            width: 100%;
            transition: 0.25s;
            box-shadow: 0 14px 30px rgba(10,79,110,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 1.5rem 0 2rem;
        }

        .btn-save:hover {
            transform: translateY(-6px);
            box-shadow: 0 22px 45px rgba(10,79,110,0.4);
            background: linear-gradient(135deg, #0e6b8c, #1482a8);
        }

        .hidden {
            display: none !important;
        }

        /* Toast messages */
        .toast-message {
            position: fixed;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 1rem 2rem;
            border-radius: 16px;
            background: white;
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
            font-weight: 600;
            font-size: 1rem;
            animation: slideIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            max-width: 90%;
            direction: rtl;
        }

        .toast-message.success {
            background: linear-gradient(145deg, #00a86b, #00875a);
            color: white;
        }

        .toast-message.error {
            background: linear-gradient(145deg, #d1453b, #b33a30);
            color: white;
        }

        .toast-message.info {
            background: linear-gradient(145deg, #0288d1, #0277bd);
            color: white;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -20px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }

        .income-field {
            direction: ltr;
            text-align: left;
        }


        @media (max-width: 768px) {
            body { padding: 12px; }
            #map { height: 300px; }
            .location-coordinates { flex-direction: column; gap: 10px; }
            .btn-get-location { font-size: 1rem; padding: 1rem; }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- العنوان -->
    <h2 class="mb-4 fw-bold text-primary">
        <i class="fas fa-user-injured"></i>
        استكمال بيانات البتر
    </h2>

    <!-- رسائل -->
        <!-- مكان عرض الرسائل المنبثقة -->
        <div id="toastContainer"></div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- بيانات تيليجرام -->
    <div class="card mb-4">
        <div class="card-header bg-info">
            بيانات تيليجرام
        </div>
        <div class="card-body">
            <p><strong>الاسم:</strong> {{ $telegramUser->full_name }}</p>
            <p><strong>الهوية:</strong> {{ $telegramUser->national_id }}</p>
            <p><strong>الهاتف:</strong> {{ $telegramUser->egypt_number }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('detail-cases.amputation.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- بيانات الشخص -->
        <div class="card mb-4">
            <div class="card-header">بيانات الحالة</div>
            <div class="card-body row">
                <div class="col-md-4 mb-3">
                    <label><i class="fas fa-calendar-alt"></i> تاريخ الميلاد</label>
                    <input type="date" name="birth_date" id="birthDateGuardian" class="form-control" 
                    value="{{ old('birth_date') }}"
                    required onchange="calculateAge(this, 'ageGuardian')">
                </div>
                <div class="col-md-2 mb-3">
                    <label><i class="fas fa-birthday-cake"></i> العمر</label>
                    <input type="number" name="age" id="ageGuardian" class="form-control" 
                    value="{{ old('age') }}"
                    required readonly style="background-color: #f5f9fc;">
                </div>

                <div class="col-md-3 mb-3">
                    <label>الجنس</label>
                    <select name="gender" class="form-control">
                        <option value="{{ old('gender', 'male') }}">ذكر</option>
                        <option value="{{ old('gender', 'female') }}">أنثى</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label><i class="fas fa-home"></i> الحالة السكنية</label>
                    
                    <input type="text" name="housing_status" 
                    value="{{ old('housing_status') }}"
                    class="form-control" list="housingOptions" placeholder="اختر أو اكتب" required>
                    <datalist id="housingOptions">
                        <option value="ملك">
                        <option value="إيجار">
                        <option value="سكن عائلي">
                        <option value="وقف">
                    </datalist>
                </div>

                <div class="col-md-4 mb-3">
                    <label>الحالة الاجتماعية</label>

                    <input type="text" name="marital_status"
                    value="{{ old('marital_status') }}"
                    class="form-control" list="maritalStatusOptions" placeholder="اختر أو اكتب" required>
                    <datalist id="maritalStatusOptions">
                        <option value="أعزب">
                        <option value="متزوج">
                        <option value="مطلق">
                        <option value="أرمل">
                    </datalist>
                </div>

                <div class="col-md-4 mb-3">
                    <label>تاريخ الدخول مصر</label>
                    <input type="date" name="entry_date" class="form-control" value="{{ old('entry_date') }}" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label>طريقة دخول مصر</label>
                    <input type="text" name="entry_method" class="form-control" value="{{ old('entry_method') }}" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>هل لديه دخل؟</label>
                    <select name="has_income" class="form-control">
                        <option value="1">نعم</option>
                        <option value="0">لا</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label><i class="fas fa-money-bill-wave"></i> قيمة الإيجار (جنيه)</label>
                    <input 
                        type="number" 
                        name="rent_value" 
                        class="form-control"
                        value="{{ old('rent_value') }}"
                        step="0.01"
                        min="0"
                        placeholder="مثال: 1500"
                        required
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label>هل يحتاج مرافق؟</label>
                    <select name="needs_companion" id="needs_companion" class="form-control" required>
                        <option value="0">لا</option>
                        <option value="1">نعم</option>
                    </select>
                </div>

            </div>
        </div>

        <!-- بيانات البتر -->
        <div class="card mb-4">
            <div class="card-header bg-warning">تفاصيل البتر</div>
            <div class="card-body row">

                <div class="col-md-4 mb-3">
                    <label>تاريخ البتر</label>
                    <input type="date" name="amputation_date" class="form-control" value="{{ old('amputation_date') }}" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>نوع البتر</label>
                    <select name="amputation_type" class="form-control" required>
                        <option value="">اختر نوع البتر</option>
                        <option value="قدم" {{ old('amputation_type') == 'قدم' ? 'selected' : '' }}>قدم</option>
                        <option value="يد" {{ old('amputation_type') == 'يد' ? 'selected' : '' }}>يد</option>
                        <option value="قدم ويد" {{ old('amputation_type') == 'قدم ويد' ? 'selected' : '' }}>قدم ويد</option>
                        <option value="كسور" {{ old('amputation_type') == 'كسور' ? 'selected' : '' }}>كسور</option>
                        <option value="مقعد" {{ old('amputation_type') == 'مقعد' ? 'selected' : '' }}>مقعد</option>
                        <option value="عيون" {{ old('amputation_type') == 'عيون' ? 'selected' : '' }}>عيون</option>  
                        <option value="أخرى" {{ old('amputation_type') == 'أخرى' ? 'selected' : '' }}>أخرى</option>                  
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>سبب الإصابة</label>
                    <input type="text" name="injury_reason" class="form-control" value="{{ old('injury_reason') }}" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>يحتاج طرف صناعي؟</label>
                    <select name="needs_prosthetic" class="form-control">
                        <option value="1">نعم</option>
                        <option value="0">لا</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>هل تلقى طرف صناعي او كرسي متحرك او أجهزة أخرى؟</label>
                    <select name="received_help" class="form-control">
                        <option value="1">نعم</option>
                        <option value="0">لا</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>نوع المساعدة: طرف صناعي - كرسي كهربائي - أجهزة أخرى</label>
                    <input type="text" name="help_type" placeholder="يرجى ذكر نوع المساعدة مع اسم الجهة المانحة" class="form-control" value="{{ old('help_type') }}">
                </div>

                <div class="col-12 mb-3">
                    <label>الوصف الصحي</label>
                    <textarea name="health_description" class="form-control" required>{{ old('health_description') }}</textarea>
                </div>

            </div>
        </div>

        <!-- حاوين الإحداثيات المخفية -->
        <input type="hidden" name="latitude" id="latitudeField" value="" required>
        <input type="hidden" name="longitude" id="longitudeField" value="" required>
        <input type="hidden" name="location_accuracy" id="locationAccuracy" value="" required>
        <input type="hidden" name="address_detected" id="addressDetected" value="" required>

        <!-- خريطة Leaflet - للعرض فقط، بدون تفاعل -->
        <div class="map-precision-card">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>
                <i class="fas fa-map-marked-alt"></i>
                موقع السكن
            </h5>
            <p style="color: #dc3545; font-size: 0.9rem; margin: 0;">
                ملاحظة هامة : التقاط اللوكيشن يكون داخل المنزل فقط.
            </p>
            <span id="accuracyBadge" class="location-accuracy-badge">
                <i class="fas fa-shield-alt"></i>
                <span id="accuracyText">لم يتم تحديد الموقع</span>
            </span>
        </div>

        <div id="map"></div>

        <!-- معلومات الموقع بعد التحديد -->
        <div id="locationInfoContainer" class="location-info-card hidden">
            <div class="location-success">
                <i class="fas fa-check-circle" style="font-size: 2rem;"></i>
                تم تحديد موقعك بدقة
            </div>
            
            <!-- إحداثيات دقيقة -->
            <div id="coordinatesBox" class="location-coordinates mb-3">
                <span>
                    <i class="fas fa-latitude"></i>
                    <span id="latDisplay">0.0000000</span>
                </span>
                <span>
                    <i class="fas fa-longitude"></i>
                    <span id="lngDisplay">0.0000000</span>
                </span>
                <span class="coordinate-value" id="accuracyValue">
                    <i class="fas fa-bullseye"></i>
                    الدقة: 0 م
                </span>
            </div>
            
            <!-- العنوان المفصل -->
            <div id="addressBox" class="mt-2 p-3" style="background: rgba(255,255,255,0.5); border-radius: 18px;">
                <i class="fas fa-map-pin" style="color: #0a4f6e; margin-left: 8px;"></i>
                <span id="addressText" style="font-weight: 600;">جاري تحميل العنوان...</span>
            </div>
            
            <!-- تأكيد عدم قابلية التعديل -->
            <div class="location-locked mt-3">
                <i class="fas fa-lock"></i>
                موقع ثابت - لا يمكن تعديله
            </div>
        </div>

        <!-- رسالة طلب الإذن (تظهر قبل تحديد الموقع) -->
        <div id="permissionRequestCard" class="permission-request">
            <div class="permission-icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <h4 style="font-weight: 800; color: #b45f2a; margin-bottom: 0.8rem;">
                نحتاج تحديد موقعك بدقة
            </h4>
            <p style="font-size: 1.1rem; color: #5a6c7a;">
                لتقديم المساعدة المناسبة، يرجى الضغط على الزر أدناه للسماح بتحديد موقعك الجغرافي بدقة عالية
            </p>
            
            <!-- زر تحديد الموقع الحالي - الطريقة الوحيدة -->
            <button type="button" id="getCurrentLocationBtn" class="btn-get-location" onclick="getUserLocation()">
                <i class="fas fa-location-dot"></i>
                <span id="locationBtnText">تحديد موقعي الحالي بدقة</span>
            </button>
            
            <p class="mt-3 mb-0 text-muted">
                <i class="fas fa-info-circle"></i>
                الموقع سيُستخدم فقط لهذه الحالة ولن يتم مشاركته
            </p>
        </div>
    </div>
</div>

 <div class="card mb-4">
            <div class="card-header bg-warning"> الصورة الشخصية </div>
            <div class="card-body row">
<!-- كارد الكاميرا للصورة الشخصية -->
<div class="col-12 mb-3">
    <label class="required-label">
        <i class="fas fa-camera"></i> الصورة الشخصية 
    </label>
    <div id="cameraCard" class="camera-card">
        <!-- واجهة الكاميرا -->
        <div id="cameraPreviewContainer" class="camera-preview-container hidden">
            <video id="cameraPreview" class="camera-preview" autoplay playsinline></video>
            <div class="camera-controls">
                <button type="button" class="btn-camera-action" onclick="capturePhoto()">
                    <i class="fas fa-camera"></i> التقاط
                </button>

                <!-- ⭐ زر جديد للتبديل -->
                <button type="button" class="btn-camera-action" onclick="switchCamera()">
                    <i class="fas fa-sync"></i> تبديل
                </button>

                <button type="button" class="btn-camera-action" onclick="closeCamera()">
                    <i class="fas fa-times"></i> إغلاق
                </button>
            </div>
        </div>
        
        <!-- معاينة الصورة الملتقطة -->
        <div id="photoPreviewContainer" class="hidden">
            <div class="photo-preview-card">
                <img id="capturedImage" class="photo-preview-image" alt="الصورة الملتقطة">
                <div class="photo-preview-info">
                    <div class="photo-preview-title">
                        <i class="fas fa-check-circle" style="color: #00a86b;"></i>
                        تم التقاط الصورة
                    </div>
                    <div class="photo-preview-text">
                        الصورة الشخصية
                    </div>
                </div>
                <button type="button" class="btn-reset" onclick="resetPhoto()">
                    <i class="fas fa-redo-alt"></i> إعادة
                </button>
            </div>
        </div>
        
        <!-- شاشة البداية -->
        <div id="cameraStartScreen" class="text-center py-3">
            <div class="mb-2">
                <i class="fas fa-camera-retro" style="font-size: 2.5rem; color: #0e6b8c; opacity: 0.5;"></i>
            </div>
            <p class="text-muted mb-2">لم تقم بالتقاط صورة بعد</p>
            <button type="button" class="btn-camera-main" onclick="openCamera()" style="width: auto; display: inline-flex;">
                <i class="fas fa-camera"></i> فتح الكاميرا
            </button>
        </div>
    </div>
    
    <input type="file" name="person_image" id="personalPhoto" class="form-control hidden">
    <small class="text-muted mt-2 d-block">التقط صورة شخصية واضحة</small>
</div>
</div>                    
</div>
</div>

<div class="card mb-4" id="companionCard" style="display:none;">
    <div class="card-header bg-info text-white">
        تفاصيل المرافق
    </div>
    <div class="card-body row">

        <div class="col-md-4 mb-3">
            <label>اسم المرافق رباعي</label>
            <input type="text" name="companion_full_name" class="form-control">
        </div>

        <div class="col-md-4 mb-3">
            <label>رقم هوية المرافق</label>
            <input type="text" name="companion_national_id" class="form-control">
        </div>

        <div class="col-md-4 mb-3">
            <label>تاريخ الميلاد</label>
            <input type="date" name="companion_birth_date" class="form-control">
        </div>

        <div class="col-md-4 mb-3">
            <label>صلة القرابة</label>
            <input type="text" name="companion_relationship" class="form-control">
        </div>

    </div>
</div>
        <!-- الملفات -->
        <div class="card mb-4">
            <div class="card-header">الملفات</div>
            <div class="card-body row">

                <div class="col-md-6 mb-3">
                    <label>صورة البتر</label>
                    <input type="file" name="amputation_image" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>الهوية</label>
                    <input type="file" name="guardian_id" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>جواز السفر</label>
                    <input type="file" name="guardian_passport" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>ختم الدخول</label>
                    <input type="file" name="entry_stamp" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>تقارير طبية</label>
                    <input type="file" name="medical_reports" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>عقد إيجار</label>
                    <input type="file" name="rent_contract" class="form-control">
                </div>

            </div>
        </div>

        <button class="btn btn-save w-100 py-3">
            <i class="fas fa-save"></i>
            حفظ البيانات
        </button>

    </form>

</div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // ================================================
        // 1. النظام المتقدم للموقع - دقيق وثابت لا يمكن تغييره
        // ================================================
        
        // متغيرات الخريطة
        let map;
        let marker;
        let isMapInitialized = false;
        let locationDetermined = false;
        
        // الإحداثيات الافتراضية (تظهر مؤقتاً)
        const DEFAULT_LAT = 30.044420;
        const DEFAULT_LNG = 31.235712;
        
        // تهيئة الخريطة عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            initializeMap();
            
            // ربط تواريخ الميلاد
            const birthGuardian = document.getElementById('birthDateGuardian');
            if (birthGuardian) {
                birthGuardian.addEventListener('change', function() {
                    calculateAge(this, 'ageGuardian');
                });
            }

            document.querySelectorAll('.birth-date-orphan').forEach(function(input) {
                input.addEventListener('change', function() {
                    const index = this.dataset.index;
                    calculateAge(this, 'ageOrphan_' + index);
                });
            });
            
            // تنسيق الدخل
            const incomeDisplay = document.getElementById('monthlyIncomeDisplay');
            if (incomeDisplay) {
                incomeDisplay.addEventListener('input', function() {
                    formatNumberWithCommas(this);
                });
            }
        });
        
        function initializeMap() {
            // إنشاء الخريطة - للعرض فقط
            map = L.map('map', {
                zoomControl: false, // إخفاء أدوات التحكم بالتكبير
                dragging: false,    // منع السحب
                touchZoom: false,   // منع التكبير باللمس
                scrollWheelZoom: false, // منع التكبير بالعجلة
                doubleClickZoom: false, // منع التكبير بالنقر المزدوج
                boxZoom: false,     // منع تحديد منطقة
                keyboard: false,    // منع التحكم بلوحة المفاتيح
                attributionControl: false // إخفاء الإسناد لتقليل العناصر
            }).setView([DEFAULT_LAT, DEFAULT_LNG], 13);
            
            // إضافة طبقة الخريطة
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(map);
            
            // إضافة مؤشر ثابت (للعرض فقط)
            marker = L.marker([DEFAULT_LAT, DEFAULT_LNG], {
                interactive: false // غير قابل للتفاعل
            }).addTo(map);
            
            isMapInitialized = true;
        }
        
        // ================================================
        // 2. تحديد الموقع الحالي بدقة - الطريقة الوحيدة
        // ================================================
        function getUserLocation() {
            const btn = document.getElementById('getCurrentLocationBtn');
            const btnText = document.getElementById('locationBtnText');
            
            if (!navigator.geolocation) {
                showToast('⚠️ المتصفح لا يدعم تحديد الموقع', 'error');
                return;
            }
            
            // تغيير حالة الزر للتحميل
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> جاري تحديد موقعك بدقة عالية...';
            
            // رسالة للمستخدم
            showToast('🔄 جاري طلب إذن الوصول للموقع...', 'info');
            
            // استخدام إعدادات دقيقة جداً مع طلب الإذن صراحة
            navigator.geolocation.getCurrentPosition(
                // نجاح تحديد الموقع
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = position.coords.accuracy; // الدقة بالمتر
                    
                    // تحديث الخريطة
                    map.setView([lat, lng], 17);
                    marker.setLatLng([lat, lng]);
                    
                    // تحديث الحقول المخفية
                    document.getElementById('latitudeField').value = lat.toFixed(7);
                    document.getElementById('longitudeField').value = lng.toFixed(7);
                    document.getElementById('locationAccuracy').value = accuracy;
                    
                    // عرض الإحداثيات
                    document.getElementById('latDisplay').textContent = lat.toFixed(7);
                    document.getElementById('lngDisplay').textContent = lng.toFixed(7);
                    
                    // عرض الدقة
                    let accuracyText = '';
                    let accuracyColor = '';
                    
                    if (accuracy < 10) {
                        accuracyText = 'دقة عالية جداً';
                        accuracyColor = '#00a86b';
                    } else if (accuracy < 50) {
                        accuracyText = 'دقة جيدة';
                        accuracyColor = '#ff9800';
                    } else {
                        accuracyText = 'دقة متوسطة';
                        accuracyColor = '#f44336';
                    }
                    
                    document.getElementById('accuracyValue').innerHTML = `<i class="fas fa-bullseye"></i> الدقة: ${accuracy.toFixed(0)} م (${accuracyText})`;
                    
                    // إخفاء طلب الإذن وإظهار معلومات الموقع
                    document.getElementById('permissionRequestCard').classList.add('hidden');
                    document.getElementById('locationInfoContainer').classList.remove('hidden');
                    
                    // تحديث badge
                    document.getElementById('accuracyText').textContent = 'تم تحديد الموقع';
                    document.getElementById('accuracyBadge').style.background = '#d4edda';
                    
                    // الحصول على العنوان
                    reverseGeocode(lat, lng);
                    
                    // رسالة نجاح مع الدقة
                    showToast(`📍 تم تحديد موقعك بدقة ${accuracy.toFixed(0)} متر`, 'success');
                    
                    // تم تحديد الموقع
                    locationDetermined = true;
                    
                    // إعادة الزر لحالته الطبيعية
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-location-dot"></i> تم تحديد الموقع بنجاح ✓';
                    
                    // رسم دائرة الدقة على الخريطة
                    if (window.accuracyCircle) {
                        map.removeLayer(window.accuracyCircle);
                    }
                    
                    window.accuracyCircle = L.circle([lat, lng], {
                        radius: accuracy,
                        color: '#4285F4',
                        weight: 1,
                        opacity: 0.3,
                        fillColor: '#4285F4',
                        fillOpacity: 0.1,
                        interactive: false
                    }).addTo(map);
                },
                // خطأ تحديد الموقع
                function(error) {
                    // إعادة الزر لحالته الطبيعية
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-location-dot"></i> تحديد موقعي الحالي بدقة';
                    
                    let errorMessage = '';
                    let suggestion = '';
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'تم رفض إذن الوصول للموقع';
                            suggestion = 'يرجى السماح بتحديد الموقع من إعدادات المتصفح';
                            showToast('⚠️ ' + errorMessage + ' - ' + suggestion, 'error');
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'معلومات الموقع غير متوفرة';
                            showToast('⚠️ ' + errorMessage + ' - يرجى التأكد من اتصال GPS', 'error');
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'انتهت مهلة تحديد الموقع';
                            showToast('⚠️ ' + errorMessage + ' - يرجى المحاولة مرة أخرى', 'error');
                            break;
                        default:
                            errorMessage = 'فشل تحديد الموقع';
                            showToast('⚠️ ' + errorMessage, 'error');
                    }
                },
                // إعدادات دقيقة جداً
                {
                    enableHighAccuracy: true,
                    timeout: 30000,
                    maximumAge: 0
                }
            );
        }
        
        // الحصول على العنوان من الإحداثيات
        function reverseGeocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=ar`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        document.getElementById('addressDetected').value = data.display_name;
                        
                        // عرض العنوان المفصل
                        let addressHtml = '';
                        
                        if (data.address) {
                            const parts = [];
                            if (data.address.road) parts.push(data.address.road);
                            if (data.address.suburb) parts.push(data.address.suburb);
                            if (data.address.city || data.address.town || data.address.village) {
                                parts.push(data.address.city || data.address.town || data.address.village);
                            }
                            if (data.address.country) parts.push(data.address.country);
                            
                            addressHtml = parts.join('، ');
                        } else {
                            addressHtml = data.display_name.substring(0, 100);
                        }
                        
                        document.getElementById('addressText').innerHTML = `<i class="fas fa-map-pin" style="color: #0a4f6e; margin-left: 8px;"></i> ${addressHtml}`;
                    }
                })
                .catch(error => {
                    document.getElementById('addressText').innerHTML = '<i class="fas fa-map-pin" style="color: #0a4f6e; margin-left: 8px;"></i> تم تحديد الموقع بنجاح';
                });
        }

        // ====================================
        // 3. حساب العمر
        // ====================================
        function calculateAge(dateInput, ageFieldId) {
            if (!dateInput.value) return;
            
            const birthDate = new Date(dateInput.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            document.getElementById(ageFieldId).value = age >= 0 ? age : 0;
        }

        // ====================================
        // 4. تنسيق الدخل
        // ====================================
        function formatNumberWithCommas(input) {
            let value = input.value.replace(/[^\d.]/g, '');
            let parts = value.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            input.value = parts.join('.');
            
            let hiddenField = document.getElementById('monthlyIncomeHidden');
            if (hiddenField) {
                hiddenField.value = value;
            }
        }

        // ====================================
        // 5. نظام الرسائل
        // ====================================
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast-message ${type} animate__animated animate__bounceInDown`;
            
            let icon = 'fa-info-circle';
            if (type === 'success') icon = 'fa-check-circle';
            if (type === 'error') icon = 'fa-exclamation-circle';
            
            toast.innerHTML = `
                <i class="fas ${icon}" style="font-size: 1.3rem;"></i>
                <span style="font-weight: 600;">${message}</span>
            `;
            
            toastContainer.innerHTML = '';
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('animate__fadeOutUp');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 500);
            }, 4000);
        }

        // ====================================
        // 6. الكاميرا لولي الأمر
        // ====================================
        let mediaStream = null;
let currentFacingMode = "user"; // user = سيلفي / environment = خلفية

function openCamera() {
    startCamera();
}

async function startCamera() {

    try {

        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
        }

        mediaStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: currentFacingMode,
                width: { ideal: 1280 },
                height: { ideal: 1280 }
            },
            audio: false
        });

        const video = document.getElementById('cameraPreview');
        video.srcObject = mediaStream;

        document.getElementById('cameraStartScreen').classList.add('hidden');
        document.getElementById('cameraPreviewContainer').classList.remove('hidden');
        document.getElementById('cameraCard').classList.add('active');

    } catch (error) {
        console.log(error);
        showToast('⚠️ لا يمكن تشغيل الكاميرا', 'error');
    }
}

function switchCamera() {

    currentFacingMode = currentFacingMode === "user"
        ? "environment"
        : "user";

    startCamera();

    showToast(
        currentFacingMode === "user"
            ? "📸 الكاميرا الأمامية"
            : "📷 الكاميرا الخلفية",
        "info"
    );
}
        
        function closeCamera() {

    if (mediaStream) {
        mediaStream.getTracks().forEach(track => track.stop());
        mediaStream = null;
    }

    const video = document.getElementById('cameraPreview');
    video.srcObject = null;

    document.getElementById('cameraPreviewContainer').classList.add('hidden');
    document.getElementById('cameraStartScreen').classList.remove('hidden');
    document.getElementById('cameraCard').classList.remove('active');
}
        
        function capturePhoto() {
            const video = document.getElementById('cameraPreview');
            const canvas = document.createElement('canvas');
            const size = Math.min(video.videoWidth, video.videoHeight);
            canvas.width = size;
            canvas.height = size;

            const offsetX = (video.videoWidth - size) / 2;
            const offsetY = (video.videoHeight - size) / 2;

            canvas.getContext('2d').drawImage(
                video,
                offsetX,
                offsetY,
                size,
                size,
                0,
                0,
                size,
                size
            );

            canvas.toBlob(function(blob) {

                if (!blob) {
                    showToast('❌ فشل التقاط الصورة', 'error');
                    return;
                }

                const file = new File([blob], "guardian_photo.jpg", {
                    type: "image/jpeg"
                });

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);

                const fileInput = document.getElementById('personalPhoto');
                fileInput.files = dataTransfer.files;

                console.log(fileInput.files); // 🔥 تأكد إنها مش فاضية

                const imageUrl = URL.createObjectURL(blob);
                document.getElementById('capturedImage').src = imageUrl;

                document.getElementById('cameraPreviewContainer').classList.add('hidden');
                document.getElementById('cameraStartScreen').classList.add('hidden');
                document.getElementById('photoPreviewContainer').classList.remove('hidden');

                closeCamera();
                showToast('📸 تم التقاط الصورة بنجاح', 'success');

            }, 'image/jpeg', 0.9);
        }

        function resetPhoto() {
            document.getElementById('photoPreviewContainer').classList.add('hidden');
            document.getElementById('cameraStartScreen').classList.remove('hidden');
            document.getElementById('capturedImage').src = '';
            document.getElementById('personalPhoto').value = '';
            openCamera();
        }

    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            document.querySelectorAll(".is-orphan-select").forEach(function(select){

                select.addEventListener("change", function(){

                    let index = this.dataset.index;
                    let container = document.getElementById("orphanFields_" + index);

                    let inputs = container.querySelectorAll("input, select, textarea");

                    if(this.value == "1"){

                        container.style.display = "block";

                        // إضافة required
                        inputs.forEach(function(input){
                            if(input.dataset.originalRequired === "true"){
                                input.setAttribute("required", "required");
                            }
                        });

                    } else {

                        container.style.display = "none";

                        // إزالة required
                        inputs.forEach(function(input){
                            if(input.hasAttribute("required")){
                                input.dataset.originalRequired = "true";
                                input.removeAttribute("required");
                            }
                        });

                    }

                });

            });

        });
    </script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    @if(session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            showToast("{{ session('success') }}", "success");
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            showToast("{{ session('error') }}", "error");
        });
    </script>
    @endif

    // إضافة نجمة تلقائيًا لكل حقل required
    document.querySelectorAll("input[required], select[required], textarea[required]").forEach(function (field) {
        const label = field.closest(".mb-2, .mb-3, .col-md-2, .col-md-3, .col-md-4, .col-md-6, .col-12")?.querySelector("label");
        if (label) {
            label.classList.add("required-label");
        }
    });

    // عند الإرسال: تلوين الحقول الفارغة
    const form = document.querySelector("form");
    form.addEventListener("submit", function (e) {

        let hasError = false;

        document.querySelectorAll("input[required], select[required], textarea[required]").forEach(function (field) {

            if (!field.value.trim()) {
                field.classList.add("field-error");
                hasError = true;
            } else {
                field.classList.remove("field-error");
            }

        });

        if (hasError) {
            e.preventDefault();
            showToast("⚠️ يرجى تعبئة جميع الحقول المطلوبة", "error");
        }

    });

});
</script>   
<script>
document.getElementById('needs_companion').addEventListener('change', function () {
    let card = document.getElementById('companionCard');

    if (this.value == "1") {
        card.style.display = "block";
    } else {
        card.style.display = "none";
    }
});
</script>   
</body>
</html>