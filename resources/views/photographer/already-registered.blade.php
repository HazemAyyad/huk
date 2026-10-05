{{-- resources/views/photographer/already-registered.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم التسجيل مسبقاً</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .message-card {
            background: white;
            border-radius: 30px;
            padding: 50px 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.3);
            animation: popIn 0.6s ease-out;
        }

        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.8);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .icon {
            width: 100px;
            height: 100px;
            background: #2ca534ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white;
            font-size: 50px;
            box-shadow: 0 20px 40px rgba(197, 48, 48, 0.3);
        }

        h1 {
            color: #2d3748;
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        p {
            color: #4a5568;
            font-size: 18px;
            line-height: 1.8;
            margin-bottom: 30px;
        }


        .info-box i {
            color: #ecc94b;
            margin-left: 10px;
            font-size: 20px;
        }

        .btn-home {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        }

        .btn-home i {
            margin-right: 10px;
        }

        .footer {
            margin-top: 30px;
            color: #718096;
            font-size: 14px;
        }
/* ... الأكواد السابقة تبقى كما هي، عدل فقط ما يلي ... */

.info-box {
    background: #fffbe6;
    border-right: 5px solid #ecc94b;
    padding: 20px;
    border-radius: 15px;
    text-align: right;
    margin: 20px 0; /* تقليل الهامش قليلاً */
}

.info-box p {
    margin-bottom: 12px;
    font-size: 15px;
    color: #744210;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
}

.telegram-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #229ED9;
    color: white;
    padding: 12px 25px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(34, 158, 217, 0.3);
    margin: 0 auto 25px; /* وضع مسافة أسفل الزر */
    max-width: 280px;
}

.telegram-btn i {
    margin-left: 10px; /* المسافة من اليسار لأن النص عربي */
    font-size: 1.3rem;
}

.btn-home {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    padding: 15px 40px;
    border-radius: 50px;
    font-size: 18px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    gap: 10px;
}
    </style>
</head>
<body>
    <div class="message-card">
        <div class="icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>تم التسجيل بياناتك بنجاح</h1>
        
        <p>عذراً، لقد قمت بالتسجيل في البرنامج من قبل. لا يمكن التسجيل أكثر من مرة..</p>
        
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <p><strong>ملاحظة:</strong> إذا كان لديك أي استفسار حول طلبك السابق، يرجى التواصل معنا.</p>
            <p><i class="fas fa-envelope"></i> Email: hakinsaniyardimdernegi10@gmail.com</p>
            <p><i class="fas fa-phone"></i> Phone: +20 106 353 2055</p>
            <a href="https://t.me/hakinsan" target="_blank" class="telegram-btn" style="display: block; margin: 0 auto;">
                <i class="fab fa-telegram-plane"></i> تواصل معنا عبر تيليجرام
            </a>
        </div>
        
        <a href="{{ url('/') }}" class="btn-home">
            <i class="fas fa-home"></i>
            العودة للصفحة الرئيسية
        </a>
        
        <div class="footer">
            شكراً لاهتمامك بالانضمام إلينا
        </div>
    </div>
</body>
</html>