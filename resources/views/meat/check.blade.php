@php
    $status = \Illuminate\Support\Facades\Cache::get('meat_check_status', 'open');
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=yes">
    <title>برنامج توزيع لحوم الأضاحي</title>
    <!-- Font Awesome 6 (Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Cairo + Tajawal for modern Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(145deg, #f0f7f0 0%, #e9f0e6 100%);
            font-family: 'Cairo', 'Tajawal', sans-serif;
            padding: 20px 16px 50px;
            min-height: 100vh;
        }

        /* main container */
        .page-container {
            max-width: 600px;
            margin: 0 auto;
        }

        /* header / hero */
        .hero-section {
            background: #2d6a4f;
            background-image: radial-gradient(circle at 10% 20%, rgba(255,255,240,0.08) 2%, transparent 2.5%);
            background-size: 28px 28px;
            border-radius: 2rem;
            padding: 1.6rem 1.2rem;
            margin-bottom: 1.8rem;
            box-shadow: 0 12px 25px rgba(0,0,0,0.1);
            text-align: center;
            color: white;
        }

        .hero-section h1 {
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: -0.3px;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .hero-section h1 i {
            background: #f4a261;
            padding: 8px;
            border-radius: 60%;
            color: #2d6a4f;
            font-size: 1.3rem;
        }

        .hero-section p {
            font-size: 1rem;
            opacity: 0.92;
            margin-top: 8px;
            font-weight: 500;
        }

        .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,240,0.2);
            backdrop-filter: blur(4px);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.85rem;
            margin-top: 12px;
            font-weight: 500;
        }

        /* card style */
        .card {
            background: white;
            border-radius: 2rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05), 0 5px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 1.6rem;
            transition: transform 0.2s ease;
        }

        /* instructions section */
        .instructions-box {
            background: #fff9e8;
            border-right: 8px solid #f4a261;
            padding: 1.3rem 1.3rem;
            margin-bottom: 1.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 5px 12px rgba(0,0,0,0.03);
        }

        .instructions-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 1.25rem;
            color: #bc6c25;
            margin-bottom: 1rem;
        }

        .instructions-title i {
            font-size: 1.5rem;
            color: #e76f51;
        }

        .instructions-list {
            list-style: none;
            padding-right: 0;
        }

        .instructions-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            font-weight: 500;
            color: #2d3e2b;
            line-height: 1.4;
        }

        .instructions-list li i {
            color: #2a9d8f;
            font-size: 1.2rem;
            margin-top: 2px;
            min-width: 22px;
            text-align: center;
        }

        .highlight {
            background: #fef2e0;
            font-weight: 700;
            color: #b45309;
            border-radius: 30px;
            padding: 0 4px;
        }

        .form-section {
            padding: 1.2rem 1.2rem 1.6rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c4b2c;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            right: 16px;
            color: #9aa67b;
            font-size: 1.1rem;
        }

        input {
            width: 100%;
            padding: 15px 45px 15px 16px;
            border: 1.5px solid #e2e8d5;
            border-radius: 2rem;
            font-size: 1rem;
            font-family: 'Cairo', monospace;
            background: #fefcf5;
            transition: all 0.2s;
            color: #1f3b1a;
            font-weight: 500;
        }

        input:focus {
            outline: none;
            border-color: #2a9d8f;
            box-shadow: 0 0 0 3px rgba(42,157,143,0.2);
            background: white;
        }

        button[type="submit"] {
            width: 100%;
            background: #2d6a4f;
            border: none;
            padding: 16px;
            font-size: 1.15rem;
            font-weight: 800;
            font-family: 'Cairo', sans-serif;
            color: white;
            border-radius: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 8px 18px rgba(0,0,0,0.1);
            letter-spacing: 0.3px;
        }

        button[type="submit"]:hover {
            background: #1b4d3e;
            transform: scale(0.98);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .alert {
            border-radius: 1.5rem;
            padding: 1rem 1.2rem;
            margin-top: 1rem;
            margin-bottom: 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            backdrop-filter: blur(2px);
            border-right: 6px solid;
        }

        .alert-danger {
            background: #ffe5e5;
            color: #b91c1c;
            border-right-color: #dc2626;
        }

        .alert-warning {
            background: #fff1e0;
            color: #b45309;
            border-right-color: #f97316;
        }

        .alert-success {
            background: #dff1e4;
            color: #166534;
            border-right-color: #2e7d32;
        }

        .telegram-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #1e3a2f;
            color: white;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 3rem;
            font-weight: 600;
            margin-top: 12px;
            transition: 0.2s;
            font-size: 0.9rem;
        }

        .telegram-btn i {
            font-size: 1.2rem;
        }

        .telegram-btn:hover {
            background: #0f2c22;
            transform: translateY(-2px);
        }

        .footer-note {
            text-align: center;
            font-size: 0.75rem;
            color: #6b6e5c;
            margin-top: 2rem;
            border-top: 1px dashed #cbd5b0;
            padding-top: 1rem;
        }

        /* responsive touch improvements */
        @media (max-width: 480px) {
            body {
                padding: 12px 12px 40px;
            }
            .hero-section h1 {
                font-size: 1.6rem;
            }
            .instructions-list li {
                font-size: 0.85rem;
            }
            button[type="submit"] {
                padding: 14px;
            }
        }

        /* animation for messages */
        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert, .instructions-box, .card {
            animation: fadeSlide 0.35s ease-out;
        }
    </style>
</head>
<body>
<div class="page-container">
    <!-- header section جذاب ومتجانس -->
    <div class="hero-section">
        <h1>
            <i class="fas fa-hand-holding-heart"></i>
            برنامج توزيع اللحوم لعيد الأضحى المبارك
        </h1>
        <div class="location-badge">
            <i class="fas fa-map-marker-alt"></i> مدينة العبور – القاهرة
        </div>
    </div>

    <!-- تعليمات هامة جداً كما طلبت: الأولوية للعائلات الأكثر عدداً + التوزيع في مدينة العبور + التسجيل لا يعني الاستفادة -->
    <div class="instructions-box">
        <div class="instructions-title">
            <i class="fas fa-circle-info"></i>
            <span>تعليمات هامة قبل التسجيل</span>
        </div>
        <ul class="instructions-list">
            <li>
                <i class="fas fa-users"></i>
                <span><span class="highlight">الأولوية للعائلات الأكثر عددًا</span> – يتم تقييم الطلبات بناءً على عدد أفراد الأسرة، والأسر الأكبر عددًا والاكثر احتياجا تحصل على الأولوية في التوزيع.</span>
            </li>
            <li>
                <i class="fas fa-city"></i>
                <span><span class="highlight">التوزيع سيكون في القاهرة – مدينة العبور</span>
            </li>
            <li>
                <i class="fas fa-file-signature"></i>
                <span><span class="highlight">التسجيل لا يعني بالضرورة الاستفادة من البرنامج</span> – تخضع البيانات للتدقيق والفرز حسب المعايير واللجنة المشرفة، والتسجيل شرط أساسي للمفاضلة.</span>
            </li>
            <li>
                <i class="fas fa-id-card"></i>
                <span>يُسمح فقط لرب الأسرة بالتسجيل باستخدام رقم الهوية. لا يمكن التسجيل كمرافق.</span>
            </li>
            <li>
                <i class="fab fa-telegram"></i>
                <span>في حالة عدم وجود بيانات مسجلة مسبقًا، يجب التسجيل أولاً عبر التيليجرام عبر الرقم الرسمي للجمعية ثم العودة للفحص.</span>
            </li>
        </ul>
    </div>

    @if($status === 'closed')
    <div class="alert alert-warning" style="text-align:center;">
        <i class="fas fa-circle-exclamation" style="font-size: 1.5rem;"></i>
        <div style="width:100%;">
            <strong>نظراً للضغط الشديد</strong><br>
            تم إغلاق الفحص مؤقتاً<br>
            وسيتم فتحه على فترات متباعدة<br><br>
            <span style="font-size:0.9rem;">شاكرين لكم حسن تعاونكم</span>
        </div>
    </div>
     @endif

    {{-- ✅ إذا مفتوح --}}
    @if($status === 'open')
    <!-- card: نموذج الفحص + البحث -->
    <div class="card">
        <div class="form-section">
            <form method="POST" action="/meat-check">
                @csrf
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> رقم الهوية (رب الأسرة)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-regular fa-fingerprint"></i>
                        <input type="text" name="national_id" placeholder="ادخل رقم الهوية" required inputmode="numeric" pattern="[0-9]*" maxlength="14" title="أدخل الرقم القومي مكون من 14 رقم">
                    </div>
                </div>
                <button type="submit">
                    <i class="fas fa-search"></i> فحص الأهلية والاستحقاق
                </button>
            </form>
        </div>
    </div>
    @endif
    <!-- عرض رسائل الجلسة (الخلفية) بطريقة جذابة ومتوافقة مع التصميم -->
    @if(session('companion_error'))
        <div class="alert alert-danger">
            <i class="fas fa-user-slash" style="font-size: 1.3rem;"></i>
            <div style="flex:1;">
                أنت مسجل كمرافق مع هذه الهوية 
                <strong>( {{ session('companion_error') }} )</strong><br>
                <span style="font-size: 0.85rem;">⚠️ لا يمكن التسجيل إلا باسم رب الأسرة فقط.</span>
            </div>
        </div>
    @endif

    @if(session('not_found'))
        <div class="alert alert-warning">
            <i class="fab fa-telegram" style="font-size: 1.5rem;"></i>
            <div>
                <strong>يجب التسجيل أولًا عبر التيليجرام</strong><br>
                <span style="font-size: 0.9rem;">لم يتم العثور على بيانات رب الأسرة، يرجى التسجيل عبر البوت الرسمي ثم العودة للفحص عبر الرقم الرسمي للجمعية 201063532055.</span>
                <div>
                    <a href="https://t.me/hakinsan" target="_blank" class="telegram-btn">
                        <i class="fab fa-telegram"></i> اضغط هنا للذهاب إلى التيليجرام للتسجيل اولا 
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
            <div style="flex:1;">
                {{ session('success') }}
            </div>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem;"></i>
            <div style="flex:1;">
                {{ session('error') }}
            </div>
            <div>
                <a href="https://t.me/hakinsan" target="_blank" class="telegram-btn">
                    <i class="fab fa-telegram"></i> اضغط هنا للذهاب إلى التيليجرام للتسجيل اولا 
                </a>
            </div>
        </div>
    @endif
    
    <!-- إضافة معلومات إضافية: تذكير بالمعايير والعدد -->
    <div class="card" style="background: #fef7e6; border: 1px solid #f0e2bc;">
        <div style="padding: 1rem 1.3rem;">
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <i class="fas fa-chart-line" style="font-size: 1.8rem; color: #bc6c25;"></i>
                <div>
                    <h3 style="font-size: 1rem; font-weight: 800; color:#7f4f24;">آلية التوزيع</h3>
                    <p style="font-size: 0.8rem; color: #5e4b34;">يتم فرز الطلبات وفقاً لعدد أفراد الأسرة (الأكثر عدداً أولاً)، ثم الالتزام للحالات الأكثر حاجة. التسجيل لا يضمن الحصول على المساعدة، ويتم الإعلان عن المستفيدين عبر البوت والرسائل النصية.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-note">
        <i class="fas fa-shield-alt"></i> برنامج توزيع لحوم الأضاحي | جميع الحقوق محفوظة
    </div>
</div>

<!-- إضافة tiny script لتحسين تجربة الإدخال (اختياري) -->
<script>
    (function() {
        // تحسين تجربة المستخدم: حصر الإدخال للأرقام في انسانل الهوية
        const nationalInput = document.querySelector('input[name="national_id"]');
        if(nationalInput) {
            nationalInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 14);
            });
        }

        // إضافة تأثير بسيط عند النقر على أي رسالة اختيارية
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.addEventListener('click', function() {
                // مجرد تحسين تفاعلي بسيط، لا يؤثر على الوظيفة
                this.style.transition = '0.1s';
                this.style.transform = 'scale(0.99)';
                setTimeout(() => { this.style.transform = ''; }, 150);
            });
        });
    })();
</script>
</body>
</html>