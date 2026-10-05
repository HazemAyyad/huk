<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تم استلام الطلب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- أيقونة الصفحة -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E✅%3C/text%3E%3C/svg%3E">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap');

        * {
            font-family: 'Tajawal', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 30%, #bfdbfe 70%, #93c5fd 100%);
            padding: 20px;
        }

        .main-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(30, 58, 138, 0.25);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .main-card:hover {
            transform: translateY(-5px);
        }

        .success-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            border-radius: 50%;
            font-size: 60px;
            color: white;
            box-shadow: 0 15px 40px -8px rgba(59, 130, 246, 0.4);
            margin-bottom: 20px;
            position: relative;
        }

        .success-icon::before {
            content: '';
            position: absolute;
            top: -5px;
            right: -5px;
            bottom: -5px;
            left: -5px;
            border-radius: 50%;
            border: 3px solid rgba(59, 130, 246, 0.2);
            animation: pulse-border 2s ease-in-out infinite;
        }

        @keyframes pulse-border {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.5;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-icon i {
            position: relative;
            z-index: 1;
        }

        .check-mark {
            position: absolute;
            bottom: -5px;
            right: -5px;
            background: #22c55e;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            border: 3px solid white;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3);
        }

        .card-body-custom {
            padding: 50px 40px;
        }

        .main-title {
            color: #1e3a8a;
            font-weight: 700;
            font-size: 32px;
            margin-bottom: 15px;
        }

        .main-title i {
            color: #3b82f6;
            margin-left: 12px;
        }

        .subtitle {
            color: #4b5563;
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 25px;
        }

        .info-text {
            color: #6b7280;
            font-size: 17px;
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .info-text i {
            color: #3b82f6;
            margin-left: 10px;
            width: 22px;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 30px 0 25px;
            color: #94a3b8;
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 2px dashed #dbeafe;
        }

        .divider::before {
            margin-left: 20px;
        }

        .divider::after {
            margin-right: 20px;
        }

        .divider i {
            color: #3b82f6;
            margin: 0 10px;
        }

        .steps {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0 10px;
            flex-wrap: wrap;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 14px;
        }

        .step-item .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: #dbeafe;
            color: #1e3a8a;
            border-radius: 50%;
            font-weight: 700;
            font-size: 13px;
        }

        .step-item.active .step-number {
            background: #3b82f6;
            color: white;
        }

        /* ===== قسم التواصل ===== */
        .contact-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 30px 25px;
            margin-top: 25px;
            border: 2px solid #dbeafe;
            transition: all 0.3s ease;
        }

        .contact-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.15);
        }

        .contact-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: contain;
            background: white;
            padding: 10px;
            border: 3px solid #dbeafe;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }

        .contact-avatar:hover {
            border-color: #3b82f6;
            transform: scale(1.05);
        }

        .contact-title {
            color: #1e3a8a;
            font-weight: 700;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .contact-sub {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 18px;
        }

        .section-title-contact {
            color: #1e3a8a;
            font-weight: 700;
            font-size: 16px;
            margin: 18px 0 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .section-title-contact i {
            color: #3b82f6;
        }

        .link-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s ease;
            min-width: 200px;
            border: none;
        }

        .link-btn:hover {
            transform: translateY(-3px);
            text-decoration: none;
        }

        .link-btn.telegram {
            background: linear-gradient(135deg, #0088cc 0%, #2aabee 100%);
            color: white;
            box-shadow: 0 6px 20px -5px rgba(0, 136, 204, 0.3);
        }

        .link-btn.telegram:hover {
            box-shadow: 0 10px 30px -5px rgba(0, 136, 204, 0.4);
            color: white;
        }

        .link-btn.telegram i {
            font-size: 22px;
        }

        .link-btn.telegram-channel {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: white;
            box-shadow: 0 6px 20px -5px rgba(26, 26, 46, 0.3);
        }

        .link-btn.telegram-channel:hover {
            box-shadow: 0 10px 30px -5px rgba(26, 26, 46, 0.4);
            color: white;
        }

        .link-btn.telegram-channel i {
            font-size: 22px;
        }

        .contact-divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 20px 0;
        }

        .copyright-text {
            color: #94a3b8;
            font-size: 13px;
            margin: 10px 0 0;
        }

        .copyright-text i {
            color: #ef4444;
        }

        .btn-custom {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            border: none;
            padding: 14px 40px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 18px;
            color: white;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px -5px rgba(59, 130, 246, 0.4);
            color: white;
        }

        .btn-custom:active {
            transform: scale(0.97);
        }

        .btn-custom i {
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .card-body-custom {
                padding: 30px 20px;
            }

            .main-title {
                font-size: 26px;
            }

            .subtitle {
                font-size: 16px;
            }

            .info-text {
                font-size: 15px;
            }

            .success-icon {
                width: 90px;
                height: 90px;
                font-size: 45px;
            }

            .check-mark {
                width: 28px;
                height: 28px;
                font-size: 14px;
            }

            .btn-custom {
                padding: 12px 30px;
                font-size: 16px;
            }

            .steps {
                gap: 15px;
            }

            .step-item {
                font-size: 12px;
            }

            .contact-card {
                padding: 20px 15px;
            }

            .link-btn {
                padding: 10px 20px;
                font-size: 14px;
                min-width: 160px;
            }

            .contact-avatar {
                width: 60px;
                height: 60px;
            }

            .contact-title {
                font-size: 20px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }

            .card-body-custom {
                padding: 25px 15px;
            }

            .main-title {
                font-size: 22px;
            }

            .success-icon {
                width: 75px;
                height: 75px;
                font-size: 35px;
            }

            .check-mark {
                width: 24px;
                height: 24px;
                font-size: 12px;
            }

            .link-btn {
                padding: 10px 16px;
                font-size: 13px;
                min-width: 140px;
            }

            .contact-title {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height:100vh;">
            <div class="col-md-8">
                <div class="main-card">
                    <div class="card-body-custom text-center">

                        <!-- أيقونة النجاح -->
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                            <span class="check-mark">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>

                        <!-- العنوان -->
                        <h2 class="main-title">
                            <i class="fas fa-circle-check"></i>
                            تم استلام طلبكم بنجاح
                        </h2>

                        <p class="subtitle">
                            <i class="fas fa-heart" style="color:#ef4444;"></i>
                            نشكركم على تعبئة نموذج طلب توفير كرسي كهربائي
                        </p>

                        <div class="divider">
                            <i class="fas fa-arrow-left"></i>
                            <span>تفاصيل الطلب</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>

                        <!-- النصوص -->
                        <div class="text-start">
                            <p class="info-text">
                                <i class="fas fa-clipboard-list"></i>
                                سيتم مراجعة جميع الطلبات ودراسة كل حالة بعناية من قبل الفريق المختص.
                            </p>

                            <p class="info-text">
                                <i class="fas fa-sort-amount-down"></i>
                                سيتم تحديد الأولويات وفقاً للحالة الصحية، ودرجة الحاجة، والمعايير المعتمدة لدى الجمعية.
                            </p>

                            <p class="info-text">
                                <i class="fas fa-boxes"></i>
                                ونود التنويه إلى أن توفير الكراسي الكهربائية يعتمد على الكميات المتوفرة لدينا، لذلك قد لا يكون بالإمكان تلبية جميع الطلبات بشكل فوري.
                            </p>
                        </div>

                        <!-- خطوات بسيطة -->
                        <div class="steps">
                            <div class="step-item active">
                                <span class="step-number">1</span>
                                تم الاستلام
                            </div>
                            <div class="step-item">
                                <span class="step-number">2</span>
                                قيد المراجعة
                            </div>
                            <div class="step-item">
                                <span class="step-number">3</span>
                                التقييم
                            </div>
                            <div class="step-item">
                                <span class="step-number">4</span>
                                التواصل
                            </div>
                        </div>

                        <!-- ===== قسم التواصل ===== -->
                        <div class="contact-card">
                            <img src="{{ asset('files/logo2.png') }}" class="contact-avatar" alt="شعار الجمعية">
                            <h3 class="contact-title">تواصل معنا</h3>
                            <p class="contact-sub">سيتم التواصل معكم عبر التيليجرام</p>

                            <h4 class="section-title-contact">
                                <i class="fa-solid fa-comments"></i>
                                تواصل مباشر
                            </h4>

                            <a href="https://t.me/hakinsan" target="_blank" class="link-btn telegram">
                                <i class="fab fa-telegram"></i> تيليجرام
                            </a>

                            <br>

                            <h4 class="section-title-contact">
                                <i class="fa-solid fa-broadcast-tower"></i>
                                القنوات وصفحات التواصل
                            </h4>

                            <a href="https://t.me/hakinsan2023" target="_blank" class="link-btn telegram-channel">
                                <i class="fab fa-telegram"></i> قناة تيليجرام
                            </a>

                            <hr class="contact-divider">

                            <p class="copyright-text">
                                <i class="fas fa-copyright"></i>
                                جميع الحقوق محفوظة &copy; {{ date('Y') }} جمعية حق الإنسانية
                            </p>
                        </div>

                        <!-- زر العودة -->
                        <a href="/wheelchair" class="btn-custom">
                            <i class="fas fa-arrow-right"></i>
                            العودة للصفحة الرئيسية
                            <i class="fas fa-home"></i>
                        </a>

                        <p style="color:#94a3b8; font-size:13px; margin-top:20px;">
                            <i class="fas fa-clock me-1"></i>
                            سيتم الرد على طلبكم في أقرب وقت
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>