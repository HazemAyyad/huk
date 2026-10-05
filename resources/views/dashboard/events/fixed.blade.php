<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>الفعاليات الثابتة</title>
    <!-- Font Awesome 6 (Free CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Tajawal for modern Arabic readability -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(145deg, #f4f7fc 0%, #eef2f5 100%);
            font-family: 'Tajawal', sans-serif;
            padding: 2rem 1.5rem;
            min-height: 100vh;
        }

        /* الحاوية الرئيسية - Responsive Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* عنوان الصفحة مع أيقونة متحركة */
        .page-title {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            font-size: 1.9rem;
            font-weight: 800;
            color: #1e2a3e;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(4px);
            padding: 0.8rem 1.8rem;
            border-radius: 80px;
            width: fit-content;
            margin-bottom: 2.5rem;
            box-shadow: 0 6px 14px rgba(0,0,0,0.03);
            border: 1px solid rgba(255,255,255,0.7);
        }

        .page-title i {
            color: #2c7da0;
            font-size: 2rem;
            animation: subtleSpin 6s infinite ease-in-out;
        }

        @keyframes subtleSpin {
            0% { transform: rotate(0deg); }
            50% { transform: rotate(12deg); }
            100% { transform: rotate(0deg); }
        }

        /* شبكة البطاقات - متجاوبة تماماً */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 28px;
        }

        /* تصميم البطاقة بأناقة وتحسين اللمس للأجهزة */
        .event-card {
            background: #ffffff;
            border-radius: 28px;
            padding: 1.5rem;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0,0,0,0.02);
            transition: all 0.25s ease;
            border: 1px solid rgba(76, 201, 240, 0.2);
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(0px);
        }

        .event-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 36px -16px rgba(0, 0, 0, 0.18);
            border-color: rgba(76, 201, 240, 0.5);
        }

        /* رأس البطاقة */
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1rem;
            border-bottom: 2px dashed #e9edf2;
            padding-bottom: 0.75rem;
        }

        .event-header h3 {
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f2b3d;
            letter-spacing: -0.2px;
            margin: 0;
            line-height: 1.3;
            word-break: break-word;
        }

        .badge {
            background: linear-gradient(135deg, #4cc9f0, #3b9bcb);
            color: white;
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(59,155,203,0.3);
            letter-spacing: 0.3px;
        }

        /* محتوى البطاقة */
        .event-body {
            margin: 1rem 0 1.2rem 0;
            line-height: 1.85;
            flex: 1;
        }

        .event-body p {
            margin: 0.6rem 0;
            font-size: 1rem;
            color: #2c3e44;
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 6px;
        }

        .event-body p strong {
            font-weight: 700;
            color: #1f5068;
            min-width: 85px;
            display: inline-block;
        }

        /* تلوين خاص لأنواع المساعدات */
        .event-body p:nth-child(4) strong, 
        .event-body p:nth-child(5) strong {
            font-weight: 700;
        }

        /* زر الإجراء الرئيسي - تم التسليم */
        .event-actions {
            margin-top: 0.5rem;
        }

        .btn-received {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(105deg, #2ecc71, #27ae60);
            color: white;
            padding: 12px 12px;
            border-radius: 60px;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.95rem;
            transition: all 0.25s;
            box-shadow: 0 6px 12px -6px rgba(46,204,113,0.4);
            border: none;
            cursor: pointer;
            letter-spacing: 0.3px;
        }

        .btn-received i {
            font-size: 1.1rem;
        }

        .btn-received:hover {
            background: linear-gradient(105deg, #27ae60, #219653);
            transform: scale(0.98);
            box-shadow: 0 4px 10px rgba(39,174,96,0.4);
        }

        /* حالة فارغة - empty state جميلة */
        .empty-state {
            text-align: center;
            background: white;
            border-radius: 48px;
            padding: 3rem 2rem;
            margin: 2rem 0;
            box-shadow: 0 12px 28px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.03);
        }

        .empty-state i {
            font-size: 4.5rem;
            color: #b9d8ec;
            margin-bottom: 1.2rem;
        }

        .empty-state h3 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #597a8f;
        }

        /* تحسينات للأجهزة الصغيرة جداً */
        @media (max-width: 680px) {
            body {
                padding: 1.2rem 1rem;
            }
            .page-title {
                font-size: 1.4rem;
                padding: 0.5rem 1.2rem;
                gap: 8px;
                margin-bottom: 1.8rem;
            }
            .page-title i {
                font-size: 1.5rem;
            }
            .events-grid {
                gap: 20px;
                grid-template-columns: 1fr;
            }
            .event-card {
                padding: 1.2rem;
                border-radius: 24px;
            }
            .event-header h3 {
                font-size: 1.3rem;
            }
            .event-body p {
                font-size: 0.9rem;
                flex-direction: row;
                flex-wrap: wrap;
            }
            .event-body p strong {
                min-width: 75px;
            }
            .badge {
                font-size: 0.7rem;
                padding: 4px 12px;
            }
            .btn-received {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
        }

        /* تحسين للشاشات المتوسطة (تابلت) */
        @media (min-width: 681px) and (max-width: 1024px) {
            .events-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 24px;
            }
            .page-title {
                font-size: 1.7rem;
            }
        }

        /* إضافات جمالية للبطاقات عند التركيز لذوي الاحتياجات */
        .btn-received:focus-visible,
        .event-card:focus-within {
            outline: 3px solid #2c7da0;
            outline-offset: 2px;
        }
        
        /* تخصيص شريط التمرير لمتصفحات WebKit (أناقة) */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #dfe8ef;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #98c1d9;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="page-title">
        <i class="fas fa-sync-alt"></i> الفعاليات الثابتة
    </h1>
    <div class="events-grid">
        @foreach($events as $event)
            <div class="event-card">
                <div class="event-header">
                    <h3>{{ $event->name }}</h3>
                    <span class="badge"><i class="fas fa-calendar-check" style="margin-left: 6px;"></i> ثابتة</span>
                </div>
                <div class="event-body">
                    <p><strong><i class="fas fa-hashtag"></i> رقم الكشف:</strong> {{ $event->id }}</p>
                    <p><strong><i class="far fa-calendar-alt"></i> التاريخ:</strong> {{ now()->format('Y-m-d') }}</p>
                    <p><strong><i class="fas fa-tag"></i> النوع:</strong>
                        @if($event->aid_type == 'cash') 
                            <span style="background: #e9f7ef; padding: 2px 12px; border-radius: 50px; font-weight:500;">💰 نقدية</span>
                        @elseif($event->aid_type == 'in_kind') 
                            <span style="background: #e3f0fa; padding: 2px 12px; border-radius: 50px; font-weight:500;">🎁 عينية</span>
                        @else 
                            <span style="background:#f1f3f5;">📦 أخرى</span>
                        @endif
                    </p>
                    <p><strong><i class="fas fa-chart-line"></i> القيمة:</strong>
                        @if($event->value_type === 'number' && $event->value)
                            <span class="value-highlight">{{ number_format($event->value) }} جنيه</span>
                        @else
                            <span class="value-text">{{ $event->value_text ?? 'غير محدد' }}</span>
                        @endif
                    </p>
                </div>
                <div class="event-actions">
                    <!-- الرابط الموافق للمسار المطلوب في المشروع الأصلي مع event_id وmessage_type -->
                    <a href="{{ route('admin.telegram.index', ['event_id' => $event->id, 'message_type' => 'received']) }}" class="btn-received">
                        <i class="fas fa-check-circle"></i> ✅ تم التسليم
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- ملاحظة: في بيئة Laravel الحقيقية، يتم تعريف route() بشكل تلقائي، لكننا نعرض المعاينة بشكل آمن -->
<!-- لإظهار الروابط بشكل واضح وتفادي أي خطأ PHP في العرض المباشر نضيف شرط وجود route helper افتراضي -->
<?php
// هذا السطر للتأكد من دالة route لا تسبب خطأ في العرض التوضيحي المعزول.
// في Laravel الأصلي ستُحل بشكل طبيعي. نقوم بتعريف دالة route احتياطيًا إن لم تكن موجودة (فقط لتنفيذ المعاينة).
if (!function_exists('route')) {
    function route($name, $params = []) {
        // فقط لأغراض العرض التجميلي: نعيد رابط وهمي readable
        if ($name == 'admin.telegram.index' && isset($params['event_id'])) {
            return '#'; // أو رابط placeholder
        }
        return '#';
    }
}
?>
</body>
</html>