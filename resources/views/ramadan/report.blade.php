<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>تقرير توزيع رمضان</title>
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

        /* الترويسة */
        .page-header {
            background: linear-gradient(135deg, #2d5a27 0%, #1e3d1a 100%);
            color: white;
            padding: 25px 20px;
            border-radius: 20px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: "📊";
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 60px;
            opacity: 0.1;
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

        .date-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 30px;
            display: inline-block;
            margin-top: 10px;
            font-size: 18px;
            font-weight: 600;
            backdrop-filter: blur(5px);
        }

        /* نموذج اختيار التاريخ */
        .date-section {
            background: white;
            border-radius: 18px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .date-form {
            display: flex;
            gap: 15px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        .date-input-wrapper {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .date-input-wrapper::before {
            content: "📅";
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #2d5a27;
        }

        .date-input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 14px;
            font-size: 16px;
            transition: all 0.3s;
            background: #f8fafc;
            font-family: inherit;
        }

        .date-input:focus {
            outline: none;
            border-color: #2d5a27;
            background: white;
            box-shadow: 0 0 0 4px rgba(45, 90, 39, 0.1);
        }

        .view-btn {
            background: #2d5a27;
            color: white;
            border: none;
            padding: 0 40px;
            height: 55px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(45, 90, 39, 0.3);
        }

        .view-btn:hover {
            background: #1e3d1a;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(45, 90, 39, 0.4);
        }

        .view-btn:active {
            transform: translateY(0);
        }

        /* بطاقة إجمالي الوجبات */
        .total-card {
            background: linear-gradient(135deg, #2d5a27 0%, #1e3d1a 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(45, 90, 39, 0.3);
            position: relative;
            overflow: hidden;
        }

        .total-card::after {
            content: "🍴";
            position: absolute;
            left: 20px;
            bottom: 20px;
            font-size: 80px;
            opacity: 0.1;
            transform: rotate(-10deg);
        }

        .total-label {
            font-size: 20px;
            opacity: 0.95;
            margin-bottom: 10px;
            display: block;
        }

        .total-number {
            font-size: 64px;
            font-weight: 800;
            line-height: 1;
            text-shadow: 4px 4px 8px rgba(0,0,0,0.2);
        }

        .total-unit {
            font-size: 20px;
            opacity: 0.9;
            margin-right: 10px;
        }

        /* أقسام التقرير */
        .report-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #e9ecef;
        }

        .section-icon {
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

        .section-header h3 {
            font-size: 22px;
            color: #1e3d1a;
            font-weight: 700;
        }

        .section-count {
            background: #e9ecef;
            color: #1e3d1a;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            margin-right: auto;
        }

        /* بطاقات المستفيدين */
        .beneficiaries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .beneficiary-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 18px;
            border: 1px solid #e9ecef;
            transition: all 0.3s;
        }

        .beneficiary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border-color: #2d5a27;
        }

        .beneficiary-name {
            font-size: 18px;
            font-weight: 700;
            color: #1e3d1a;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .beneficiary-name::before {
            content: "👤";
            font-size: 18px;
        }

        .meals-count {
            background: white;
            padding: 12px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .meals-label {
            color: #6c757d;
            font-weight: 500;
        }

        .meals-value {
            background: #2d5a27;
            color: white;
            padding: 5px 15px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 16px;
        }

        /* جدول للمستفيدين (عرض بديل) */
        .table-responsive {
            overflow-x: auto;
            border-radius: 14px;
        }

        .beneficiaries-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }

        .beneficiaries-table th {
            background: #f1f5f9;
            color: #1e3d1a;
            font-weight: 600;
            padding: 15px;
            text-align: right;
            font-size: 15px;
        }

        .beneficiaries-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .beneficiaries-table tr:last-child td {
            border-bottom: none;
        }

        .beneficiaries-table tr:hover td {
            background: #f8fafc;
        }

        .meal-badge {
            background: #2d5a27;
            color: white;
            padding: 5px 15px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            display: inline-block;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-state i {
            font-size: 50px;
            color: #adb5bd;
            margin-bottom: 15px;
            display: block;
        }

        .empty-state p {
            color: #6c757d;
            font-size: 16px;
        }

        /* أزرار التنقل */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 30px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-primary {
            background: #2d5a27;
            color: white;
            box-shadow: 0 4px 10px rgba(45, 90, 39, 0.2);
        }

        .btn-primary:hover {
            background: #1e3d1a;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(45, 90, 39, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #2d5a27;
            border: 2px solid #2d5a27;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            transform: translateY(-2px);
        }

        .btn-print {
            background: #6c757d;
            color: white;
        }

        .btn-print:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* تذييل */
        .footer-note {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        /* للهواتف */
        @media (max-width: 768px) {
            .total-number {
                font-size: 48px;
            }
            
            .section-header h3 {
                font-size: 18px;
            }
            
            .beneficiaries-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media print {
            .no-print {
                display: none;
            }
            body {
                background: white;
                padding: 0;
            }
            .page-header {
                background: #2d5a27;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .total-card {
                background: #2d5a27;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- الترويسة -->
        <div class="page-header no-print">
            <h2>📊 تقرير توزيع الوجبات</h2>
            <p>نظام متابعة توزيع وجبات رمضان اليومية</p>
            <div class="date-badge">
                {{ \Carbon\Carbon::parse($date)->format('l') }} - 
                {{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}
            </div>
        </div>

        <!-- نموذج اختيار التاريخ -->
        <div class="date-section no-print">
            <div style="margin-top:15px;text-align:center;">
    <input 
        type="text" 
        id="searchInput"
        placeholder="🔍 ابحث عن اسم المستفيد..."
        style="
            width:100%;
            max-width:400px;
            padding:12px 15px;
            border-radius:10px;
            border:2px solid #e9ecef;
            font-size:15px;
        "
        onkeyup="searchBeneficiaries()"
    >
</div>
            <form method="GET" class="date-form">
                <div class="date-input-wrapper">
                    <input 
                        type="date" 
                        name="date" 
                        class="date-input" 
                        value="{{ $date }}"
                        max="{{ now()->toDateString() }}"
                    >
                </div>
                <button type="submit" class="view-btn">
                    <span>عرض التقرير</span>
                    <span>🔍</span>
                </button>
            </form>
        </div>

        <!-- إجمالي الوجبات -->
        <div class="total-card">
            <span class="total-label">إجمالي الوجبات الموزعة لهذا اليوم</span>
            <div class="total-number">
                {{ $totalMeals }}
                <span class="total-unit">وجبة</span>
            </div>
        </div>

        <!-- المستفيدون الذين استلموا -->
        <div class="report-section">
            <div class="section-header">
                <div class="section-icon">✅</div>
                <h3>المستفيدون الذين استلموا اليوم</h3>
                <span class="section-count">{{ $distributed->count() }} مستفيد</span>
            </div>

            @if($distributed->count() > 0)
                <!-- عرض شبكي للهواتف -->
                <div class="beneficiaries-grid">
                    @foreach($distributed as $d)
                        <div class="beneficiary-card beneficiary-item">
                            <div class="beneficiary-name">
                                {{ $d->beneficiary->full_name }}
                            </div>
                            <div class="meals-count">
                                <span class="meals-label">عدد الوجبات المستلمة</span>
                                <span class="meals-value">{{ $d->meals_taken }}</span>
                            </div>

                            <div style="margin-top:8px;font-size:14px;color:#6c757d;">
                                👨‍💼 سجلها: <strong>{{ $d->creator->name ?? 'غير معروف' }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- عرض جدولي للشاشات الكبيرة (اختياري) -->
                <div class="table-responsive" style="margin-top: 20px; display: none;">
                    <table class="beneficiaries-table">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>عدد الوجبات</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($distributed as $d)
                                <tr>
                                    <td>{{ $d->beneficiary->full_name }}</td>
                                    <td>
                                        <span class="meal-badge">{{ $d->meals_taken }} وجبة</span>
                                    </td>
                                    <td>✅ مستلم</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i>😔</i>
                    <p>لا يوجد مستفيدون استلموا وجبات في هذا اليوم</p>
                </div>
            @endif
        </div>

        <!-- المستفيدون الذين لم يستلموا -->
        <div class="report-section">
            <div class="section-header">
                <div class="section-icon">⏳</div>
                <h3>المستفيدون الذين لم يستلموا اليوم</h3>
                <span class="section-count">{{ $notReceived->count() }} مستفيد</span>
            </div>

            @if($notReceived->count() > 0)
                <div class="beneficiaries-grid">
                    @foreach($notReceived as $n)
                        <div class="beneficiary-card" style="border-right: 4px solid #dc3545;">
                            <div class="beneficiary-name">
                                {{ $n->full_name }}
                            </div>
                            <div class="meals-count">
                                <span class="meals-label">الوجبات المتبقية</span>
                                <span class="meals-value" style="background: #dc3545;">{{ $n->daily_meals }} وجبة</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i>🎉</i>
                    <p>جميع المستفيدين استلموا وجباتهم اليوم! مبارك</p>
                </div>
            @endif
        </div>

        <!-- ملخص سريع -->
        <div class="report-section no-print">
            <div class="section-header">
                <div class="section-icon">📈</div>
                <h3>ملخص اليوم</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="background: #f8fafc; padding: 20px; border-radius: 14px; text-align: center;">
                    <div style="font-size: 30px; margin-bottom: 10px;">📦</div>
                    <div style="color: #6c757d; margin-bottom: 5px;">نسبة الاستلام</div>
                    @php
                        $totalBeneficiaries = $distributed->count() + $notReceived->count();
                        $percentage = $totalBeneficiaries > 0 ? round(($distributed->count() / $totalBeneficiaries) * 100) : 0;
                    @endphp
                    <div style="font-size: 28px; font-weight: 700; color: #2d5a27;">{{ $percentage }}%</div>
                </div>
                <div style="background: #f8fafc; padding: 20px; border-radius: 14px; text-align: center;">
                    <div style="font-size: 30px; margin-bottom: 10px;">👥</div>
                    <div style="color: #6c757d; margin-bottom: 5px;">إجمالي المستفيدين</div>
                    <div style="font-size: 28px; font-weight: 700; color: #2d5a27;">{{ $totalBeneficiaries }}</div>
                </div>
            </div>
        </div>

        <!-- أزرار الإجراءات -->
        <div class="action-buttons no-print">
            <a href="{{ route('ramadan.index') }}" class="btn btn-secondary">
                <span>🔙</span>
                <span>العودة للرئيسية</span>
            </a>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.print()" class="btn btn-print">
                    <span>🖨️</span>
                    <span>طباعة التقرير</span>
                </button>
                <a href="{{ route('ramadan.report', ['date' => now()->toDateString()]) }}" class="btn btn-primary">
                    <span>📅</span>
                    <span>تقرير اليوم</span>
                </a>
            </div>
        </div>

        <!-- تذييل -->
        <div class="footer-note no-print">
            <p>© 2024 برنامج توزيع رمضان - تقرير يومي آلي</p>
            <p style="margin-top: 5px; font-size: 12px;">شهر رمضان المبارك 🌙</p>
        </div>
    </div>

<script>
function searchBeneficiaries() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let cards = document.querySelectorAll(".beneficiary-item");

    cards.forEach(function(card){
        let name = card.innerText.toLowerCase();

        if(name.includes(input)){
            card.style.display = "";
        }else{
            card.style.display = "none";
        }
    });
}
</script>
</body>
</html>