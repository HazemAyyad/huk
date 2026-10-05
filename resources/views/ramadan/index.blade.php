<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>توزيع رمضان - نظام المستفيدين</title>
    <link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
        <circle cx='50' cy='50' r='48' fill='%232d5a27'/>
        <path d='M70 65c-20 0-35-15-35-35 0-5 1-10 3-15-15 5-25 20-25 35 0 20 15 35 35 35 15 0 30-10 35-25-5 3-10 5-13 5z' fill='white'/>
        <polygon points='65,25 68,33 77,33 70,38 73,47 65,42 57,47 60,38 53,33 62,33' fill='%23ffc107'/>
    </svg>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 15px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ترويسة الصفحة */
        .page-header {
            background: linear-gradient(135deg, #2d5a27 0%, #1e3d1a 100%);
            color: white;
            padding: 25px 20px;
            border-radius: 20px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            text-align: center;
        }

        .page-header h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .page-header p {
            font-size: 16px;
            opacity: 0.95;
            color: #f0f7ee;
        }

        .ramadan-icon {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
        }

        /* رسائل التنبيه */
        .alert {
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: none;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-right: 5px solid #28a745;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-right: 5px solid #dc3545;
        }

        .alert i {
            font-size: 22px;
        }

        /* نموذج البحث */
        .search-section {
            background: white;
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
        }

        .search-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 14px;
            font-size: 16px;
            transition: all 0.3s;
            background: #f8fafc;
            font-family: inherit;
        }

        .search-input:focus {
            outline: none;
            border-color: #2d5a27;
            background: white;
            box-shadow: 0 0 0 4px rgba(45, 90, 39, 0.1);
        }

        .search-input::placeholder {
            color: #adb5bd;
            font-size: 15px;
        }

        .search-btn {
            background: #2d5a27;
            color: white;
            border: none;
            padding: 0 35px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(45, 90, 39, 0.3);
        }

        .search-btn:hover {
            background: #1e3d1a;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(45, 90, 39, 0.4);
        }

        .search-btn:active {
            transform: translateY(0);
        }

        /* بطاقة الإحصائيات السريعة */
        .stats-card {
            background: white;
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            padding: 12px 20px;
            border-radius: 14px;
            flex: 1;
            min-width: 180px;
        }

        .stat-icon {
            background: #2d5a27;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .stat-info h4 {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .stat-info span {
            font-size: 24px;
            font-weight: 700;
            color: #1e3d1a;
        }

        /* قائمة المستفيدين */
        .beneficiaries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .beneficiary-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .beneficiary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(45, 90, 39, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 18px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1e3d1a;
        }

        .badge {
            background: #2d5a27;
            color: white;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
        }

        .card-body {
            padding: 18px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6c757d;
            font-weight: 500;
            font-size: 15px;
        }

        .info-value {
            font-weight: 600;
            color: #1e3d1a;
            font-size: 16px;
        }

        .card-footer {
            padding: 18px;
            background: #f8fafc;
            border-top: 1px solid #e9ecef;
        }

        .receive-form {
            width: 100%;
        }

        .receive-btn {
            width: 100%;
            background: linear-gradient(135deg, #2d5a27 0%, #1e3d1a 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(45, 90, 39, 0.2);
        }

        .receive-btn:hover {
            background: linear-gradient(135deg, #1e3d1a 0%, #0f2a0c 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(45, 90, 39, 0.3);
        }

        .receive-btn:active {
            transform: translateY(0);
        }

        .receive-btn:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* حالة عدم وجود نتائج */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            margin-top: 20px;
        }

        .no-results i {
            font-size: 60px;
            color: #adb5bd;
            margin-bottom: 20px;
            display: block;
        }

        .no-results p {
            font-size: 18px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .clear-search {
            background: #e9ecef;
            color: #495057;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .clear-search:hover {
            background: #dee2e6;
        }

        /* تذييل الصفحة */
        .footer-note {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        /* للهواتف الصغيرة */
        @media (max-width: 480px) {
            .page-header h2 {
                font-size: 24px;
            }
            
            .search-btn {
                width: 100%;
                padding: 15px;
            }
            
            .beneficiaries-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-item {
                min-width: 100%;
            }
        }

        /* تصميم شريط التمرير */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #2d5a27;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #1e3d1a;
        }

/* تنسيق زر الإضافة في الهيدر */
.add-beneficiary-btn {
    display: inline-flex;
    align-items: center;
    background: #ffffff; /* لون أبيض ليبرز فوق الخلفية الخضراء */
    color: #2d5a27;    /* نص بلون أخضر الهيدر */
    text-decoration: none;
    padding: 10px 25px;
    border-radius: 30px;
    font-weight: 700;
    margin-top: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.add-beneficiary-btn:hover {
    background: #f0f7ee;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    color: #1e3d1a;
}

/* تعديل للهواتف لضمان تناسق الحجم */
@media (max-width: 480px) {
    .add-beneficiary-btn {
        width: 100%;
        justify-content: center;
        padding: 12px;
    }
}        
    </style>
</head>
<body>
    <div class="container">
        <!-- الترويسة الرئيسية -->
        <div class="page-header">
            <span class="ramadan-icon">🌙</span>
            <h2>برنامج توزيع رمضان</h2>
            <p>نظام إدارة المستفيدين وتوزيع الوجبات اليومية</p>
            <a href="{{ route('ramadan.create') }}" class="add-beneficiary-btn">
                <span>➕ إضافة مستفيد جديد</span>
            </a>
        </div>

        <!-- رسائل التنبيه -->
        @if(session('success'))
            <div class="alert alert-success">
                <i>✅</i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <i>⚠️</i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- رابط التقرير السريع -->
        <div style="text-align: left; margin-top: 20px;">
            <a href="{{ route('ramadan.report') }}" style="
                display: inline-block;
                background: white;
                color: #2d5a27;
                text-decoration: none;
                padding: 12px 25px;
                border-radius: 30px;
                font-weight: 600;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                transition: all 0.3s;
                border: 2px solid #2d5a27;
            ">
                📊 عرض تقرير اليوم ←
            </a>
        </div>
        <br>
        <!-- قسم البحث والإحصائيات -->
        <div class="search-section">
    <form method="GET" class="search-form">

        <input 
            type="text" 
            name="code" 
            class="search-input"
            placeholder="🔢 البحث بالكود"
            value="{{ request('code') }}"
        >

        <input 
            type="text" 
            name="full_name" 
            class="search-input"
            placeholder="👤 البحث بالاسم"
            value="{{ request('full_name') }}"
        >

        <input 
            type="text" 
            name="national_id" 
            class="search-input"
            placeholder="🪪 البحث برقم الهوية"
            value="{{ request('national_id') }}"
        >


        <button type="submit" class="search-btn">
            بحث 🔎
        </button>

    </form>
</div>

        <!-- إحصائيات سريعة -->
        <div class="stats-card">
            <div class="stat-item">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h4>إجمالي المستفيدين</h4>
                    <span>{{ $beneficiaries->count() }}</span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">🍽️</div>
                <div class="stat-info">
                    <h4>إجمالي الوجبات اليومية</h4>
                    <span>{{ $beneficiaries->sum('daily_meals') }}</span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">👪</div>
                <div class="stat-info">
                    <h4>إجمالي أفراد الأسر</h4>
                    <span>{{ $stats['total_family_members'] }}</span>
                </div>
            </div>
        </div>

        <!-- قائمة المستفيدين -->
        @if($beneficiaries->count() > 0)
            <div class="beneficiaries-grid">
                @foreach($beneficiaries as $b)
                    <div class="beneficiary-card">
                        <div class="card-header">
                            <h3>{{ $b->full_name }}</h3>
                            <span class="badge">كود: {{ $b->code }}</span>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="info-label">رقم الهوية</span>
                                <span class="info-value">{{ $b->national_id }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">عدد أفراد الأسرة</span>
                                <span class="info-value">{{ $b->family_members_count }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">الوجبات اليومية</span>
                                <span class="info-value">{{ $b->daily_meals }} وجبة</span>
                            </div>
                        </div>
                        <div class="card-footer">

                            {{-- زر تسجيل الاستفادة --}}
                            <form method="POST" 
                                action="{{ route('ramadan.receive', $b->id) }}"
                                onsubmit="return confirmReceive({{ $b->daily_meals }})">
                                @csrf

                                <button type="submit" class="receive-btn"
                                    @if(in_array($b->id, $receivedTodayIds)) disabled @endif>

                                    @if(in_array($b->id, $receivedTodayIds))
                                        ✔ تم الاستلام اليوم
                                    @else
                                        🍴 تسجيل استفادة اليوم ({{ $b->daily_meals }} وجبة)
                                    @endif

                                </button>
                            </form>

                            {{-- أزرار التعديل والحذف --}}
                            <div style="margin-top: 12px; display: flex; gap: 10px;">

                                {{-- زر التعديل --}}
                                <a href="{{ route('ramadan.edit', $b->id) }}"
                                style="
                                    flex:1;
                                    text-align:center;
                                    background:#ffc107;
                                    color:#000;
                                    padding:10px;
                                    border-radius:10px;
                                    text-decoration:none;
                                    font-weight:600;
                                ">
                                    ✏ تعديل
                                </a>

                                {{-- زر إلغاء الاستفادة (حذف توزيع اليوم فقط) --}}
                                <form method="POST"
                                    action="{{ route('ramadan.distribution.delete', $b->id) }}"
                                    style="flex:1;">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        onclick="return confirm('هل أنت متأكد من إلغاء استفادة اليوم؟')"
                                        style="
                                            width:100%;
                                            background:#dc3545;
                                            color:white;
                                            border:none;
                                            padding:10px;
                                            border-radius:10px;
                                            font-weight:600;
                                            cursor:pointer;
                                        ">
                                        🗑 إلغاء اليوم
                                    </button>
                                </form>

                            </div>

                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <!-- حالة عدم وجود نتائج -->
            <div class="no-results">
                <i>😔</i>
                <p>لا توجد نتائج مطابقة لبحثك</p>
                @if(request('search'))
                    <button onclick="window.location.href='{{ route('ramadan.index') }}'" class="clear-search">
                        مسح البحث وعرض الكل
                    </button>
                @endif
            </div>
        @endif


        <!-- تذييل -->
        <div class="footer-note">
            <p>© 2026 برنامج توزيع رمضان - جميع الحقوق محفوظة</p>
            <p style="margin-top: 5px; font-size: 12px;">شهر رمضان المبارك 🌙</p>
        </div>
    </div>

<script>
function confirmReceive(meals) {
    return confirm("هل أنت متأكد من تسجيل استفادة اليوم؟\n\nعدد الوجبات: " + meals + " وجبة");
}
</script>

</body>
</html>