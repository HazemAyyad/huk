{{-- resources/views/photographer/form.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>مطلوب مصور فلسطيني للتطوع مع جمعية حق الإنسانية</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" 
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
        <defs>
            <linearGradient id='grad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'>
                <stop offset='0%25' style='stop-color:%23667eea;stop-opacity:1' />
                <stop offset='100%25' style='stop-color:%23764ba2;stop-opacity:1' />
            </linearGradient>
        </defs>
        <circle cx='50' cy='50' r='48' fill='url(%23grad)'/>
        <rect x='25' y='35' width='50' height='35' rx='5' fill='white'/>
        <circle cx='50' cy='52.5' r='12' fill='url(%23grad)'/>
        <circle cx='50' cy='52.5' r='5' fill='white'/>
        <rect x='30' y='30' width='15' height='5' rx='2' fill='white'/>
        <circle cx='68' cy='42' r='3' fill='%23f6ad55'/>
    </svg>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* أنيميشن الخلفية */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: float 20s infinite;
        }

        .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
            animation-delay: -5s;
            background: rgba(255, 255, 255, 0.03);
        }

        .shape:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -10s;
            background: rgba(255, 255, 255, 0.02);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            33% { transform: translateY(-50px) rotate(120deg); }
            66% { transform: translateY(50px) rotate(240deg); }
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* بطاقة الإعلان - المعدلة */
        .ad-card {
            background: white;
            border-radius: 40px 40px 40px 40px;
            padding: 40px 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            animation: slideDown 0.8s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .ad-badge {
            position: absolute;
            top: 20px;
            left: -30px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 8px 40px;
            transform: rotate(-45deg);
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* قسم الشعار والجمعية */
        .association-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 30px;
            border: 2px solid rgba(102, 126, 234, 0.2);
        }

        .logo-container {
            flex-shrink: 0;
            width: 120px;
            height: 120px;
            background: white;
            /* التعديل هنا: 50% تجعل الشكل دائرياً تماماً */
            border-radius: 50%; 
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
            border: 3px solid #667eea;
            /* لضمان عدم خروج الصورة عن حدود الدائرة */
            overflow: hidden; 
        }
        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* أو cover حسب تفضيلك لملء الدائرة */
        }
        
        .logo-container svg {
            width: 100%;
            height: 100%;
        }

        .association-info {
            flex: 1;
        }

        .association-name {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .association-name i {
            color: #667eea;
            font-size: 28px;
        }

        .association-bio {
            color: #4a5568;
            line-height: 1.7;
            font-size: 15px;
            padding: 15px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-right: 4px solid #667eea;
        }

        .ad-title {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin-top: 10px;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .ad-title:after {
            content: '';
            position: absolute;
            bottom: -8px;
            right: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        .ad-description {
            color: #4a5568;
            line-height: 1.8;
            font-size: 16px;
            margin-bottom: 25px;
            padding: 20px;
            background: #f7fafc;
            border-radius: 25px;
            border-right: 5px solid #667eea;
        }

        .ad-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .ad-meta-item {
            background: #edf2f7;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 14px;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ad-meta-item i {
            color: #667eea;
        }

        /* باقي التنسيقات كما هي */
        .form-card {
            background: white;
            border-radius: 40px;
            padding: 40px 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.8s ease-out 0.2s both;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.98);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .form-header h2 {
            font-size: 28px;
            color: #2d3748;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .form-header p {
            color: #718096;
            font-size: 16px;
        }

        .form-header .steps {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .step {
            width: 40px;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .step.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 60px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .association-header {
                flex-direction: column;
                text-align: center;
            }
            
            .logo-container {
                width: 100px;
                height: 100px;
            }
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #2d3748;
            font-weight: 600;
            font-size: 14px;
        }

        .form-label i {
            color: #667eea;
            margin-left: 6px;
            width: 20px;
        }

        .required-star {
            color: #f56565;
            margin-right: 4px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            font-family: 'Cairo', sans-serif;
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-control.error {
            border-color: #f56565;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            transition: all 0.3s ease;
        }

        .form-control:focus + .input-icon {
            color: #667eea;
        }

        .alert-success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 25px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideInRight 0.5s ease-out;
            box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3);
        }

        .alert-success i {
            font-size: 30px;
        }

        .alert-success-content {
            flex: 1;
        }

        .alert-success-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .alert-success-text {
            font-size: 14px;
            opacity: 0.9;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .error-message {
            color: #f56565;
            font-size: 13px;
            margin-top: 6px;
            padding-right: 18px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .error-message i {
            font-size: 14px;
        }

        .submit-wrapper {
            margin-top: 40px;
            text-align: center;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 18px 50px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            max-width: 300px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        }

        .btn-submit i {
            transition: transform 0.3s ease;
        }

        .btn-submit:hover i {
            transform: translateX(-5px);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .info-note {
            margin-top: 25px;
            padding: 20px;
            background: #fefcbf;
            border-radius: 25px;
            color: #744210;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-right: 5px solid #ecc94b;
        }

        .info-note i {
            font-size: 24px;
            color: #ecc94b;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: white;
            font-size: 14px;
            opacity: 0.8;
        }

        .footer a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-submit.loading .spinner {
            display: inline-block;
        }

        .btn-submit.loading .btn-text {
            display: none;
        }
    /* تنسيق حاوية رفع الملفات */
input[type="file"] {
    padding: 10px;
    cursor: pointer;
    background: #f8fafc;
    border: 2px dashed #cbd5e0; /* حدود منقطة لتعطي إيحاء بمنطقة رفع ملفات */
    transition: all 0.3s ease;
}

input[type="file"]:hover {
    border-color: #667eea;
    background: #f0f4ff;
}

/* تنسيق مخصص لزر "Choose File" الداخلي في المتصفحات الحديثة */
input[type="file"]::file-selector-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 15px;
    margin-left: 15px; /* مسافة لجهة اليسار لأن الاتجاه RTL */
    cursor: pointer;
    font-family: 'Cairo', sans-serif;
    font-size: 13px;
    transition: all 0.3s ease;
}

input[type="file"]::file-selector-button:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

/* إضافة أيقونة توضيحية بجانب الحقل إذا أردت */
.file-input-wrapper {
    position: relative;
}

.file-hint {
    font-size: 11px;
    color: #718096;
    margin-top: 5px;
    display: block;
}
    </style>
</head>
<body>
    <!-- عناصر الخلفية المتحركة -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container">
        @if(session('success'))
            <div class="alert-success">
                <i class="fas fa-check-circle"></i>
                <div class="alert-success-content">
                    <div class="alert-success-title">تم إرسال الطلب بنجاح!</div>
                    <div class="alert-success-text">شكراً لتقديمك طلب الانضمام. سنقوم بمراجعة طلبك والتواصل معك قريباً</div>
                </div>
                <i class="fas fa-times" style="cursor:pointer; opacity:0.8;" onclick="this.parentElement.remove()"></i>
            </div>
        @endif

        <!-- بطاقة الإعلان - المعدلة مع الشعار والنبذة -->
        <div class="ad-card">
            <div class="ad-badge">فرصة مميزة</div>
            
            <!-- قسم الشعار ونبذة عن الجمعية -->
            <div class="association-header">
                <div class="logo-container">
                    <!-- شعار الجمعية - يمكن استبدال الـ SVG بالشعار المرفق -->
                    <img src="{{ asset('files/logo2.png') }}" alt="شعار جمعية حق الإنسانية" style="width: 100px; height: 100px;">
                </div>
                <div class="association-info">
                    <div class="association-name">
                        <i class="fas fa-camera-retro"></i>
                        جمعية حق الإنسانية
                    </div>
                    <div class="association-bio">
                        <i class="fas fa-quote-right" style="color: #667eea; opacity: 0.5; margin-left: 10px;"></i>
                        هي منظمة تركية غير ربحية تأسست على مبدأ تقديم المساعدة لأهلنا من قطاع غزة العالقين في مصر. تتمثل مهمتنا الأساسية في خدمتهم وحمايتهم من الجوع، والتشرد، وانعدام المأوى، وغياب سبل العيش الآمنة، مع إيلاء اهتمام خاص لجرحى الحرب والمصابين وذوي الإعاقة. كجزء من عملنا الإنساني، نركّز حالياً على تقديم الدعم الشامل للحالات الفلسطينية التي تمر بظروف صعبة. نعمل كجسر حيوي لتقديم الإغاثة العاجلة، والدعم المالي، والرعاية الطبية المتخصصة. تتجسد قيمنا في كل عمل نقوم به، بهدف التخفيف من المعاناة، واستعادة الحق في الكرامة الإنسانية، وتمكين الأسر الفلسطينية من تجاوز هذه المحنة معاً، يدًا بيد، للنهوض من جديد.
                    </div>
                </div>
            </div>

            <h1 class="ad-title">{{ $ad->title ?? 'فرصة للمصورين الفلسطينيين الشباب في مصر' }}</h1>
            <div class="ad-description">
                {{ $ad->description ?? 'نبحث عن مصورين فلسطينيين موهوبين للانضمام إلى فريقنا في مصر. إذا كنت تمتلك الشغف والإبداع في التصوير، هذه فرصتك للانضمام إلينا والعمل على مشاريع متميزة.' }}
            </div>
            <div class="ad-meta">

                <div class="ad-meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    مصر
                </div>
                <div class="ad-meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    القاهرة فقط
                </div>
                <div class="ad-meta-item">
                    <i class="fas fa-camera"></i>
                    تصوير فوتوغرافي وفيديو
                </div>
                <div class="ad-meta-item">
                    فئة الشباب
                </div>
            </div>
        </div>

        <!-- بطاقة النموذج -->
        <div class="form-card">
            <div class="form-header">
                <h2>نموذج التسجيل</h2>
                <p>يرجى تعبئة جميع البيانات المطلوبة بدقة</p>
                <div class="steps">
                    <span class="step active"></span>
                    <span class="step"></span>
                    <span class="step"></span>
                </div>
            </div>
@if ($errors->any())
    <div class="alert-success" style="background: linear-gradient(135deg, #f56565 0%, #c53030 100%);">
        <i class="fas fa-exclamation-circle"></i>
        <div class="alert-success-content">
            <div class="alert-success-title">يوجد أخطاء في النموذج</div>
            <div class="alert-success-text">
                يرجى التأكد من تعبئة جميع الحقول المطلوبة بشكل صحيح
            </div>
        </div>
    </div>
@endif

            <form method="POST" action="{{ route('photographer.submit') }}" id="applicationForm" enctype="multipart/form-data">

                @csrf
                
                <div class="form-grid">
                    <!-- الاسم الكامل (مطلوب) -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-user"></i>
                            الاسم الرباعي
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="full_name" 
                                   class="form-control @error('full_name') error @enderror" 
                                   value="{{ old('full_name') }}"
                                   placeholder="أدخل اسمك رباعي بالعربية"
                                   required>
                            <i class="fas fa-pencil-alt input-icon"></i>
                        </div>
                        @error('full_name')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-camera-retro"></i>
                            الصورة الشخصية
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="file" name="personal_photo" 
                                class="form-control @error('personal_photo') error @enderror" 
                                accept="image/*" required>
                        </div>
                        @error('personal_photo')
                            <div class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }} </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i>
                            المحافظة (مكان الإقامة الحالي)
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <select name="governorate" class="form-control @error('governorate') error @enderror" required>
                                <option value="" disabled selected>اختر المحافظة...</option>
                                <option value="القاهرة">القاهرة</option>
                                <option value="الجيزة">الجيزة</option>
                                <option value="الإسكندرية">الإسكندرية</option>
                                <option value="القليوبية">القليوبية</option>
                                <option value="الشرقية">الشرقية</option>
                                <option value="أخرى">أخرى</option>
                            </select>
                        </div>
                        @error('governorate')
                            <div class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }} </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-id-card"></i>
                            رقم الهوية الفلسطينية
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="national_id" 
                                   class="form-control @error('national_id') error @enderror" 
                                   value="{{ old('national_id') }}"
                                   placeholder="رقم الهوية"
                                   required>
                            <i class="fas fa-hashtag input-icon"></i>
                        </div>
                        @error('national_id')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- جواز السفر -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-passport"></i>
                            صورة جواز السفر
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="file" 
                                name="passport" 
                                class="form-control @error('passport') error @enderror"
                                accept="image/*"
                                required>
                        </div>
                        @error('passport')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- العمر -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar"></i>
                            العمر
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="number" 
                                   name="age" 
                                   class="form-control @error('age') error @enderror"
                                   value="{{ old('age') }}"
                                   placeholder="العمر"
                                   min="18"
                                   max="100">
                            <i class="fas fa-birthday-cake input-icon"></i>
                        </div>
                    </div>

                    <!-- معدات التصوير -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-camera"></i>
                            معدات التصوير المتوفرة
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <select name="camera_equipment_select" id="equipmentSelect" class="form-control @error('camera_equipment') error @enderror" onchange="toggleOtherInput()">
                                <option value="" disabled selected>اختر المعدات الأساسية...</option>
                                <option value="كاميرا احترافية">كاميرا احترافية</option>
                                <option value="هاتف ايفون 17 pro max">هاتف ايفون 17 pro max</option>
                                <option value="هاتف ايفون 16 pro max">هاتف ايفون 16 pro max</option>
                                <option value="هاتف ايفون 15 pro max">هاتف ايفون 15 pro max</option>
                                <option value="هاتف سامسونج 25 الترا">هاتف سامسونج 25 الترا</option>
                                <option value="هاتف سامسونج 24 الترا">هاتف سامسونج 24 الترا</option>
                                <option value="هاتف سامسونج 23 الترا">هاتف سامسونج 23 الترا</option>
                                <option value="other">أخرى (كتابة يدوية)...</option>
                            </select>

                            <div id="otherInputWrapper" style="display: none; margin-top: 10px;">
                                <textarea name="camera_equipment" 
                                        id="camera_equipment_manual"
                                        class="form-control" 
                                        placeholder="اذكر معدات التصوير بالتفصيل...">{{ old('camera_equipment') }}</textarea>
                            </div>

                            @error('camera_equipment')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- رقم الهاتف مصر (مطلوب) -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone"></i>
                            رقم الهاتف (مصر)
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="tel" 
                                   name="phone_egypt" 
                                   class="form-control @error('phone_egypt') error @enderror" 
                                   value="{{ old('phone_egypt') }}"
                                   placeholder="01xxxxxxxxx"
                                   required>
                            <i class="fas fa-mobile-alt input-icon"></i>
                        </div>
                        @error('phone_egypt')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- رقم هاتف آخر مصر -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone-alt"></i>
                            رقم هاتف آخر (مصر)
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="tel" 
                                   name="phone_egypt_2" 
                                   class="form-control @error('phone_egypt_2') error @enderror" 
                                   value="{{ old('phone_egypt_2') }}"
                                   placeholder="رقم آخر اختياري">
                            <i class="fas fa-mobile-alt input-icon"></i>
                            @error('phone_egypt_2')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- واتساب فلسطين -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fab fa-whatsapp"></i>
                            واتساب (فلسطين)
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="tel" 
                                   name="whatsapp_palestine" 
                                   class="form-control @error('whatsapp_palestine') error @enderror" 
                                   value="{{ old('whatsapp_palestine') }}"
                                   placeholder="رقم واتساب">
                            <i class="fab fa-whatsapp input-icon"></i>
                            @error('whatsapp_palestine')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- وجود عائلة في مصر -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-home"></i>
                            كم عدد افراد عائلتك في مصر؟
                            <span class="required-star">*</span>
                        </label>
                        <input type="number" 
                               name="family_in_egypt" 
                               class="form-control @error('family_in_egypt') error @enderror" 
                               value="{{ old('family_in_egypt') }}"
                               placeholder="عدد افراد العائلة">
                        @error('family_in_egypt')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- وجود عائلة في غزة -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-home"></i>
                            كم عدد افراد عائلتك في غزة؟
                        </label>
                        <input type="number" 
                               name="family_in_gaza" 
                               class="form-control @error('family_in_gaza') error @enderror" 
                               value="{{ old('family_in_gaza') }}"
                               placeholder="عدد افراد العائلة">
                        @error('family_in_gaza')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        </div>

                    <!-- سبب الدخول -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-question-circle"></i>
                            سبب الدخول إلى مصر
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="entry_reason" 
                                   class="form-control @error('entry_reason') error @enderror" 
                                   value="{{ old('entry_reason') }}"
                                   placeholder="سبب القدوم إلى مصر">
                            <i class="fas fa-info-circle input-icon"></i>
                        </div>
                        @error('entry_reason')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- تاريخ الدخول -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar-check"></i>
                            تاريخ الدخول
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="date" 
                                   name="entry_date" 
                                   class="form-control @error('entry_date') error @enderror" 
                                   value="{{ old('entry_date') }}">
                            <i class="fas fa-calendar input-icon"></i>
                            @error('entry_date')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- العنوان في مصر -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-map-pin"></i>
                            العنوان الحالي في مصر
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="address" 
                                   class="form-control @error('address') error @enderror" 
                                   value="{{ old('address') }}"
                                   placeholder="المدينة - المنطقة - شارع">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                        </div>
                        @error('address')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- المؤهل التعليمي -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-graduation-cap"></i>
                            المؤهل التعليمي
                            <span class="required-star">*</span>
                            </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="education" 
                                   class="form-control @error('education') error @enderror" 
                                   value="{{ old('education') }}"
                                   placeholder="الشهادة - التخصص - الجامعة">
                            <i class="fas fa-university input-icon"></i>
                        </div>
                        @error('education')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- العمل في غزة -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-briefcase"></i>
                            العمل في غزة
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="work_in_gaza" 
                                   class="form-control @error('work_in_gaza') error @enderror" 
                                   value="{{ old('work_in_gaza') }}"
                                   placeholder="مجال العمل السابق">
                            <i class="fas fa-building input-icon"></i>
                        </div>
                        @error('work_in_gaza')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- خبرة سابقة -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-star"></i>
                            هل لديك خبرة في التصوير؟
                            <span class="required-star">*</span>
                        </label>
                        <select name="has_experience" class="form-control" onchange="toggleExperienceDetails(this)">
                            <option value="">-- اختر --</option>
                            <option value="1" {{ old('has_experience') == '1' ? 'selected' : '' }}>نعم</option>
                            <option value="0" {{ old('has_experience') == '0' ? 'selected' : '' }}>لا</option>
                        </select>
                    </div>

                    <!-- تفاصيل الخبرة -->
                    <div class="form-group full-width" id="experienceDetails" style="display: none;">
                        <label class="form-label">
                            <i class="fas fa-info-circle"></i>
                            تفاصيل الخبرة
                            <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <textarea name="experience_details" 
                                      class="form-control @error('experience_details') error @enderror" 
                                      placeholder="اذكر خبراتك السابقة في مجال التصوير">{{ old('experience_details') }}</textarea>
                        </div>
                        @error('experience_details')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- تفاصيل الحالة -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-file-alt"></i>
                            تفاصيل إضافية عن الحالة
                        </label>
                        <div class="input-wrapper">
                            <textarea name="case_details" 
                                      class="form-control @error('case_details') error @enderror" 
                                      placeholder="أي معلومات إضافية ترغب في إضافتها">{{ old('case_details') }}</textarea>
                        </div>
                        @error('case_details')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- زر الإرسال -->
                <div class="submit-wrapper">
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="btn-text">إرسال الطلب</span>
                        <span class="spinner"></span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>

                <!-- معلومات إضافية -->
                <div class="info-note">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <strong>ملاحظة هامة:</strong> جميع البيانات التي تقدمها محمية ولن يتم مشاركتها مع أي جهة خارجية. سيتم استخدامها فقط لأغراض التقييم والتواصل.
                    </div>
                </div>
            </form>
        </div>

        <!-- تذييل الصفحة -->
        <div class="footer">
            © 2026  جميع الحقوق محفوظة لجمعية | <a href="#">سياسة الخصوصية</a> | <a href="#">الشروط والأحكام</a>
        </div>
    </div>
<script>
    // 1. إظهار/إخفاء تفاصيل الخبرة
    function toggleExperienceDetails(select) {
        const detailsDiv = document.getElementById('experienceDetails');
        if (select.value === '1' || select.value === 'yes') {
            detailsDiv.style.display = 'block';
        } else {
            detailsDiv.style.display = 'none';
        }
    }

    // 2. دالة التحقق من النموذج (تم إصلاحها هنا)
    function validateForm(event) {
        let isValid = true;
        const submitBtn = document.querySelector('button[type="submit"]'); // تأكد من وجود الزر
        
        // حذف رسائل الخطأ القديمة
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

        // التحقق من رقم الهاتف المصري
        const phoneField = document.querySelector('[name="phone_egypt"]');
        if (phoneField && phoneField.value) {
            const phoneRegex = /^01[0-9]{9}$/;
            if (!phoneRegex.test(phoneField.value)) {
                isValid = false;
                phoneField.classList.add('error');
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.style.color = 'red'; // تنسيق سريع
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> رقم الهاتف غير صحيح (يجب أن يبدأ بـ 01 ويتكون من 11 رقم)';
                phoneField.parentElement.appendChild(errorDiv);
            }
        }

        if (!isValid) {
            event.preventDefault();
            // تمرير سلس إلى أول حقل خطأ
            const firstError = document.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return false;
        }

        // إظهار مؤشر التحميل إذا كان كل شيء سليم
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
        
        return true;
    }

    // 3. تحسينات للهواتف المحمولة عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        // ربط دالة التحقق بالنموذج
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', validateForm);
        }

        // ضبط حقول التاريخ للهواتف
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    if (typeof this.showPicker === 'function') this.showPicker();
                }
            });
        });

        // تنسيق الأرقام فقط
        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });

        // إظهار/إخفاء تفاصيل الخبرة إذا كانت القيمة محفوظة مسبقاً (Laravel old value)
        const experienceSelect = document.querySelector('[name="has_experience"]');
        if (experienceSelect && (experienceSelect.value === 'yes' || experienceSelect.value === '1')) {
            const expDiv = document.getElementById('experienceDetails');
            if (expDiv) expDiv.style.display = 'block';
        }

        // إضافة تأثيرات حركية (Intersection Observer)
        const observerOptions = { threshold: 0.1 };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.form-group').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.5s ease';
            observer.observe(el);
        });

        // معالجة أخطاء الـ Session (Laravel)
        @if(session('error'))
            showNotification('{{ session('error') }}', 'error');
        @endif

        @if($errors->any())
            const firstErrorField = document.querySelector('.error') || document.querySelector('.text-danger');
            if (firstErrorField) {
                setTimeout(() => {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        @endif
    });

    // 4. دالة إظهار الإشعارات
    function showNotification(message, type = 'success') {
        const notificationDiv = document.createElement('div');
        notificationDiv.className = `alert-${type}`;
        notificationDiv.style.padding = "15px"; // تنسيق إضافي لضمان الظهور
        notificationDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <div class="alert-success-content">
                <div class="alert-success-title">${type === 'success' ? 'تم بنجاح' : 'خطأ'}</div>
                <div class="alert-success-text">${message}</div>
            </div>
            <i class="fas fa-times" style="cursor:pointer; float:left;" onclick="this.parentElement.remove()"></i>
        `;
        
        const container = document.querySelector('.container') || document.body;
        container.insertBefore(notificationDiv, container.firstChild);
        
        setTimeout(() => { if(notificationDiv) notificationDiv.remove(); }, 5000);
    }
</script>
<script>
function toggleOtherInput() {
    const selectElement = document.getElementById('equipmentSelect');
    const otherWrapper = document.getElementById('otherInputWrapper');
    const manualInput = document.getElementById('camera_equipment_manual');

    if (selectElement.value === 'other') {
        // إظهار حقل الكتابة
        otherWrapper.style.display = 'block';
        manualInput.setAttribute('required', 'required'); // جعله مطلوباً
        manualInput.name = "camera_equipment"; // التأكد من إرسال هذا الحقل للسيرفر
    } else {
        // إخفاء حقل الكتابة
        otherWrapper.style.display = 'none';
        manualInput.removeAttribute('required');
        
        // إذا اختار المستخدم خياراً جاهزاً، يمكننا نقل القيمة للحقل المخفي ليتم إرسالها
        manualInput.value = selectElement.value;
    }
}
</script>
</body>
</html>