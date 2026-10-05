<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض بيانات حالة بتر - قيد المراجعة</title>

    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
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
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(145deg, #f0f7fa, #e4eef5);
            padding: 20px;
        }

        .card {
            border-radius: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .card-header {
            background: linear-gradient(135deg, #8e2a2a, #c0392b);
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            padding: 20px;
        }

        .info-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 15px;
        }

        .info-label {
            font-weight: bold;
            color: #666;
        }

        .info-value {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .photo img {
            width: 200px;
            border-radius: 15px;
            margin: 10px;
        }

        .btn-print {
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- حالة -->
    <div class="alert alert-warning text-center">
        <h4>البيانات قيد المراجعة</h4>
    </div>

    <!-- بيانات تيليجرام -->
    <div class="card">
        <div class="card-header">
            بيانات المستخدم من تيليجرام
        </div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">الاسم</div>
                <div class="info-value">{{ $telegramUser->full_name }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">رقم الهوية</div>
                <div class="info-value">{{ $telegramUser->national_id }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">رقم الهاتف</div>
                <div class="info-value">{{ $telegramUser->egypt_number }}</div>
            </div>
        </div>
    </div>

    <!-- بيانات الحالة -->
    <div class="card">
        <div class="card-header">
            بيانات حالة البتر
        </div>

        <div class="info-grid">

            <div class="info-item">
                <div class="info-label">الاسم الكامل</div>
                <div class="info-value">{{ $amputationCase->full_name }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">العمر</div>
                <div class="info-value">{{ $amputationCase->age }} سنة</div>
            </div>

            <div class="info-item">
                <div class="info-label">الجنس</div>
                <div class="info-value">{{ $amputationCase->gender == 'male' ? 'ذكر' : 'أنثى' }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">الحالة الاجتماعية</div>
                <div class="info-value">{{ $amputationCase->marital_status }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">نوع البتر</div>
                <div class="info-value">{{ $amputationCase->amputation_type }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">تاريخ البتر</div>
                <div class="info-value">{{ $amputationCase->amputation_date }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">سبب الإصابة</div>
                <div class="info-value">{{ $amputationCase->injury_reason }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">يحتاج طرف صناعي</div>
                <div class="info-value">{{ $amputationCase->needs_prosthetic ? 'نعم' : 'لا' }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">استلم مساعدة</div>
                <div class="info-value">{{ $amputationCase->received_help ? 'نعم' : 'لا' }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">نوع المساعدة</div>
                <div class="info-value">{{ $amputationCase->help_type }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">الوصف الصحي</div>
                <div class="info-value">{{ $amputationCase->health_description }}</div>
            </div>

        </div>
    </div>

    <!-- الصور -->
    <div class="card">
        <div class="card-header">
            الصور
        </div>

        <div class="text-center photo">
            @if($amputationCase->person_image)
                <img src="{{ asset($amputationCase->person_image) }}">
            @endif

            @if($amputationCase->amputation_image)
                <img src="{{ asset($amputationCase->amputation_image) }}">
            @endif
        </div>
    </div>

    <!-- الملفات -->
    <div class="card">
        <div class="card-header">
            الملفات
        </div>

        <div class="info-grid">

            @if($amputationCase->guardian_passport)
            <div class="info-item">
                <a href="{{ asset($amputationCase->guardian_passport) }}" target="_blank">
                    جواز ولي الأمر
                </a>
            </div>
            @endif

            @if($amputationCase->entry_stamp)
            <div class="info-item">
                <a href="{{ asset($amputationCase->entry_stamp) }}" target="_blank">
                    ختم الدخول
                </a>
            </div>
            @endif

            @if($amputationCase->medical_reports)
            <div class="info-item">
                <a href="{{ asset($amputationCase->medical_reports) }}" target="_blank">
                    التقارير الطبية
                </a>
            </div>
            @endif

            @if($amputationCase->rent_contract)
            <div class="info-item">
                <a href="{{ asset($amputationCase->rent_contract) }}" target="_blank">
                    عقد الإيجار
                </a>
            </div>
            @endif

        </div>
    </div>

    <!-- زر طباعة -->
    <div class="text-center">
        <button onclick="window.print()" class="btn btn-dark btn-print">
            طباعة
        </button>
    </div>

</div>

</body>
</html>