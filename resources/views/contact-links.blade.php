<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>روابط التواصل</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
    <circle cx='50' cy='50' r='48' fill='%2310b981'/>
    <path d='M30 40 h40 v30 H45 L30 80 V40' fill='white'/>
    <circle cx='40' cy='50' r='3' fill='%2310b981'/>
    <circle cx='50' cy='50' r='3' fill='%2310b981'/>
    <circle cx='60' cy='50' r='3' fill='%2310b981'/>
</svg>"
    />
    <style>
        /* إعدادات أساسية ناعمة ومتجاوبة */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(145deg, #0b1120 0%, #1a2635 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
            color: #ffffff;
            line-height: 1.6;
        }

        /* البطاقة الرئيسية: زجاجية وأنيقة */
        .card {
            width: 100%;
            max-width: 480px;
            background: rgba(21, 32, 48, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 36px;
            padding: 2rem 1.8rem;
            text-align: center;
            box-shadow: 0 30px 50px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.02) inset;
            transition: all 0.3s ease;
        }

        /* تحسين الصورة الرمزية */
        .avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;          /* تمنع التمدد وتحافظ على النسب */
            border: 4px solid #2563eb;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);
            margin: 0 auto 1.2rem;
            display: block;
            background-color: #1e293b;   /* خلفية في حال لم يتم تحميل الصورة */
            transition: border-color 0.2s;
        }

        .avatar:hover {
            border-color: #60a5fa;
        }

        /* العنوان الرئيسي */
        h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.8rem;
            letter-spacing: -0.5px;
            background: linear-gradient(to left, #f0f9ff, #bae6fd);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }

        /* تصميم الأزرار (موحّد) */
        .link-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            text-decoration: none;
            padding: 1rem 0.8rem;
            margin: 1rem 0;
            border-radius: 60px;          /* شكل كبسولي ناعم */
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.3, 1.2);
            border: none;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.3);
            color: white;
            position: relative;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* ألوان العلامات التجارية مع تحسين التباين */
        .telegram {
            background: #1e96c8;
            background: linear-gradient(135deg, #229ED9, #1a7da0);
        }
        .whatsapp {
            background: #1faa59;
            background: linear-gradient(135deg, #25D366, #1b9e50);
        }
        .facebook {
            background: #1f5192;
            background: linear-gradient(135deg, #1877F2, #145dbb);
        }
        .instagram {
            background: linear-gradient(135deg, #f09b4a, #e4405f, #b534b4, #5e35b1);
            background-size: 200% 200%;
        }

        /* تأثير حركي أنيق */
        .link-btn:hover {
            transform: scale(1.05) translateY(-3px);
            box-shadow: 0 18px 30px -5px rgba(0, 0, 0, 0.5);
            border-color: rgba(255,255,255,0.3);
        }

        .link-btn:active {
            transform: scale(0.98);
            box-shadow: 0 5px 12px rgba(0,0,0,0.4);
        }

        /* أيقونة داخل الزر بحجم متناسق */
        .link-btn i {
            font-size: 1.8rem;
            filter: drop-shadow(0 2px 2px rgba(0,0,0,0.3));
            width: 1.8rem;
            text-align: center;
        }

        /* الخط الفاصل */
        hr {
            border: 0;
            border-top: 2px dashed #334155;
            margin: 1.8rem 0 1.2rem;
            opacity: 0.4;
        }

        /* نص حقوق الملكية */
        .copyright {
            font-size: 1rem;
            font-weight: 400;
            color: #9ca9b9;
            margin-top: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            flex-wrap: wrap;
            background: rgba(0, 0, 0, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 40px;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            backdrop-filter: blur(4px);
        }

        .copyright i {
            color: #f97316;
            font-size: 1rem;
        }

        /* تحسين التجاوب للشاشات الصغيرة */
        @media (max-width: 480px) {
            body {
                padding: 0.8rem;
            }

            .card {
                padding: 1.8rem 1.2rem;
                border-radius: 30px;
            }

            .avatar {
                width: 95px;
                height: 95px;
                border-width: 3px;
            }

            h2 {
                font-size: 1.8rem;
                margin-bottom: 1.5rem;
            }

            .link-btn {
                font-size: 1.1rem;
                padding: 0.9rem 0.5rem;
                margin: 0.9rem 0;
            }

            .link-btn i {
                font-size: 1.6rem;
            }

            .copyright {
                font-size: 0.9rem;
                padding: 0.4rem 0.8rem;
            }
        }
.section-title{
    font-size: 1.2rem;
    font-weight: 700;
    margin: 1.6rem 0 0.8rem;
    color: #cbd5f5;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    position: relative;
}

.section-title::before,
.section-title::after{
    content: "";
    flex: 1;
    height: 1px;
    background: linear-gradient(to left, transparent, #334155, transparent);
}

.section-title i{
    color: #60a5fa;
    font-size: 1rem;
}
        /* للشاشات الصغيرة جداً (أقل من 360px) */
        @media (max-width: 360px) {
            .link-btn {
                font-size: 1rem;
                padding: 0.75rem 0.5rem;
            }
            .link-btn i {
                font-size: 1.4rem;
            }
        }

        /* دعم الشاشات الطويلة: توسيط مثالي */
        @media (min-height: 700px) {
            body {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="card">

    <img src="{{ asset('files/logo2.png') }}" class="avatar" alt="Logo">
    <h2>تواصل معنا</h2>
    
    <h3 class="section-title">
        <i class="fa-solid fa-comments"></i>
        تواصل مباشر
    </h3>

    <a href="https://t.me/hakinsan" target="_blank" class="link-btn telegram">
        <i class="fab fa-telegram"></i> تيليجرام
    </a>

    <br>

    <h3 class="section-title">
        <i class="fa-solid fa-broadcast-tower"></i>
        القنوات وصفحات التواصل
    </h3>
    <a href="https://t.me/hakinsan2023" target="_blank" class="link-btn telegram">
        <i class="fab fa-telegram"></i> قناة تيليجرام
    </a>

<hr style="border: 0; border-top: 1px solid #334155; margin: 20px 0;">
<p class="copyright">جميع الحقوق محفوظة &copy; {{ date('Y') }} جمعية حق الإنسانية</p>
</div>


</body>
</html>