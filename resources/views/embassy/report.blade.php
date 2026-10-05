<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تقرير حضور فعالية السفارة</title>
<link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
        <circle cx='50' cy='50' r='48' fill='%234361ee'/>
        <rect x='20' y='25' width='60' height='50' rx='8' ry='8' fill='white'/>
        <line x1='25' y1='38' x2='75' y2='38' stroke='%234361ee' stroke-width='4' stroke-linecap='round'/>
        <line x1='25' y1='50' x2='75' y2='50' stroke='%234361ee' stroke-width='4' stroke-linecap='round'/>
        <line x1='25' y1='62' x2='75' y2='62' stroke='%234361ee' stroke-width='4' stroke-linecap='round'/>
    </svg>">
<style>
    * {margin:0;padding:0;box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;}
    body {background:#f5f7fa; padding:15px;}
    .container {max-width:1200px; margin:0 auto;}
    .page-header {background: linear-gradient(135deg,#2d5a27 0%,#1e3d1a 100%); color:white; padding:25px 20px; border-radius:20px; margin-bottom:25px; text-align:center; position:relative; overflow:hidden;}
    .page-header::before {content:"📊"; position:absolute; left:20px; top:50%; transform:translateY(-50%); font-size:60px; opacity:0.1;}
    .page-header h2 {font-size:28px; font-weight:700; margin-bottom:8px; text-shadow:2px 2px 4px rgba(0,0,0,0.2);}
    .total-card {background:linear-gradient(135deg,#2d5a27 0%,#1e3d1a 100%); color:white; border-radius:20px; padding:30px; margin-bottom:30px; text-align:center; position:relative; overflow:hidden;}
    .total-card::after {content:"👥"; position:absolute; left:20px; bottom:20px; font-size:80px; opacity:0.1;}
    .total-label {font-size:20px; opacity:0.95; margin-bottom:10px; display:block;}
    .total-number {font-size:64px; font-weight:800; line-height:1; text-shadow:4px 4px 8px rgba(0,0,0,0.2);}
    .total-unit {font-size:20px; opacity:0.9; margin-right:10px;}
    .report-section {background:white; border-radius:20px; padding:25px; margin-bottom:30px; box-shadow:0 5px 20px rgba(0,0,0,0.08);}
    .section-header {display:flex; align-items:center; gap:12px; margin-bottom:20px; padding-bottom:15px; border-bottom:3px solid #e9ecef;}
    .section-icon {background:#2d5a27; color:white; width:45px; height:45px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px;}
    .section-header h3 {font-size:22px; color:#1e3d1a; font-weight:700;}
    .section-count {background:#e9ecef; color:#1e3d1a; padding:5px 12px; border-radius:30px; font-size:14px; font-weight:600; margin-right:auto;}
    .beneficiaries-grid {display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:20px;}
    .beneficiary-card {background:#f8fafc; border-radius:16px; padding:18px; border:1px solid #e9ecef; transition: all 0.3s;}
    .beneficiary-card:hover {transform:translateY(-3px); box-shadow:0 10px 20px rgba(0,0,0,0.1); border-color:#2d5a27;}
    .beneficiary-name {font-size:18px; font-weight:700; color:#1e3d1a; margin-bottom:10px; display:flex; align-items:center; gap:8px;}
    .beneficiary-name::before {content:"👤"; font-size:18px;}
    .meals-count {background:white; padding:12px; border-radius:12px; display:flex; justify-content:space-between; align-items:center; margin-top:10px;}
    .meals-label {color:#6c757d; font-weight:500;}
    .meals-value {background:#2d5a27; color:white; padding:5px 15px; border-radius:30px; font-weight:600; font-size:16px;}
    .empty-state {text-align:center; padding:50px 20px;}
    .empty-state i {font-size:50px; color:#adb5bd; margin-bottom:15px; display:block;}
    .empty-state p {color:#6c757d; font-size:16px;}
    .action-buttons {display:flex; gap:15px; justify-content:space-between; align-items:center; margin-top:30px; flex-wrap:wrap;}
    .btn {padding:14px 30px; border-radius:30px; font-weight:600; text-decoration:none; transition: all 0.3s; display:inline-flex; align-items:center; gap:10px; border:none; cursor:pointer; font-size:16px;}
    .btn-primary {background:#2d5a27; color:white;}
    .btn-primary:hover {background:#1e3d1a; transform:translateY(-2px);}
    .btn-secondary {background:white; color:#2d5a27; border:2px solid #2d5a27;}
    .btn-secondary:hover {background:#f8fafc; transform:translateY(-2px);}
    .btn-print {background:#6c757d; color:white;}
    .btn-print:hover {background:#5a6268; transform:translateY(-2px);}
    @media print {.no-print{display:none;} body{background:white;padding:0;}}
</style>
</head>
<body>
<div class="container">
    <div class="page-header no-print">
        <h2>📊 تقرير فعالية السفارة</h2>
        <p>نظام متابعة الحضور والغياب</p>
    </div>

    <div class="total-card">
        <span class="total-label">إجمالي الحاضرين</span>
        <div class="total-number">
            {{ $attended->count() }}
            <span class="total-unit">شخص</span>
        </div>
    </div>

    <div class="report-section">
        <div class="section-header">
            <div class="section-icon">✅</div>
            <h3>الحاضرون</h3>
            <span class="section-count">{{ $attended->count() }} شخص</span>
        </div>

        @if($attended->count() > 0)
            <div class="beneficiaries-grid">
                @foreach($attended as $a)
                    <div class="beneficiary-card beneficiary-item">
                        <div class="beneficiary-name">{{ $a->full_name }}</div>
                        <div class="meals-count">
                            <span class="meals-label">الحالة</span>
                            <span class="meals-value">حاضر</span>
                        </div>
                        <div style="margin-top:8px;font-size:14px;color:#6c757d;">
                            الكود: <strong>{{ $a->code }}</strong> | الهوية: <strong>{{ $a->national_id }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i>😔</i>
                <p>لا يوجد حضور لهذا اليوم</p>
            </div>
        @endif
    </div>

    <div class="report-section">
        <div class="section-header">
            <div class="section-icon">⏳</div>
            <h3>الغائبون</h3>
            <span class="section-count">{{ $notAttended->count() }} شخص</span>
        </div>

        @if($notAttended->count() > 0)
            <div class="beneficiaries-grid">
                @foreach($notAttended as $n)
                    <div class="beneficiary-card" style="border-right:4px solid #dc3545;">
                        <div class="beneficiary-name">{{ $n->full_name }}</div>
                        <div class="meals-count">
                            <span class="meals-label">الحالة</span>
                            <span class="meals-value" style="background:#dc3545;">غائب</span>
                        </div>
                        <div style="margin-top:8px;font-size:14px;color:#6c757d;">
                            الكود: <strong>{{ $n->code }}</strong> | الهوية: <strong>{{ $n->national_id }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i>🎉</i>
                <p>جميع المسجلين حضروا اليوم</p>
            </div>
        @endif
    </div>

    <div class="action-buttons no-print">
        <a href="{{ route('embassy.index') }}" class="btn btn-secondary">🔙 العودة للرئيسية</a>
        <button onclick="window.print()" class="btn btn-print">🖨️ طباعة التقرير</button>
    </div>
</div>

<script>
function searchBeneficiaries() {
    let input = prompt("ابحث بالاسم أو الكود أو الهوية:");
    input = input ? input.toLowerCase() : "";
    let cards = document.querySelectorAll(".beneficiary-item");
    cards.forEach(function(card){
        let text = card.innerText.toLowerCase();
        card.style.display = text.includes(input) ? "" : "none";
    });
}
</script>
</body>
</html>