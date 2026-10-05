<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->name ?? 'تسجيل الحضور' }} | لوحة التحكم</title>
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Tajawal', 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f0f4fa 0%, #e2e8f0 100%);
            min-height: 100vh;
            padding: 16px;
        }

        .attendance-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* بطاقة الحدث أنيقة */
        .event-card {
            background: linear-gradient(145deg, #0f2b3d, #1b4a6e);
            border-radius: 32px;
            padding: 1.4rem;
            margin-bottom: 1.8rem;
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .event-card h2 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            .event-card {
                padding: 1rem;
            }
            .event-card h2 {
                font-size: 1.2rem;
            }
        }

        /* شريط البحث والفلتر */
        .search-filter-bar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(4px);
            border-radius: 28px;
            padding: 0.9rem 1.2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 18px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }

        /* الجدول وحاوية البيانات */
        .table-responsive-custom {
            background: white;
            border-radius: 28px;
            padding: 0.3rem;
            box-shadow: 0 20px 35px -8px rgba(0,0,0,0.12);
            overflow-x: hidden;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.95rem;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: #1e2a3e;
            color: white;
            font-weight: 700;
            padding: 14px 12px;
            border-bottom: none;
            white-space: nowrap;
            text-align: center;
            font-size: 0.9rem;
        }

        .table tbody tr {
            transition: all 0.2s;
            border-bottom: 1px solid #eef2f6;
        }

        .table tbody tr:hover {
            background-color: #f9fbfd;
        }

        .table td {
            vertical-align: middle;
            padding: 12px 8px;
            text-align: center;
        }

        /* الأسماء بمحاذاة يمين */
        .table td:nth-child(2) {
            text-align: right;
            font-weight: 500;
        }

        /* ================================================== */
        /* 🔥 تصميم زر الحضور العملاق والواضح في الجوال 🔥 */
        /* ================================================== */
        .btn-attend-mobile {
            width: 100%;
            padding: 14px 12px;
            font-size: 1.2rem;
            font-weight: 800;
            border-radius: 60px;
            background: linear-gradient(95deg, #059669, #10b981);
            border: none;
            color: white;
            box-shadow: 0 6px 14px rgba(5,150,105,0.35);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            letter-spacing: 0.5px;
        }

        .btn-attend-mobile:hover {
            transform: scale(1.01);
            background: linear-gradient(95deg, #047857, #059669);
            box-shadow: 0 10px 20px rgba(5,150,105,0.45);
        }

        .btn-attend-mobile:active {
            transform: scale(0.98);
        }

        .btn-sm {
            padding: 6px 20px;
            border-radius: 40px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-sm:hover {
            transform: translateY(-2px);
        }

        /* عدادات احترافية */
        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            padding: 6px 16px;
            border-radius: 60px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* ======================================== */
        /* تصميم الجوال المتميز - مع زر حضور عملاق */
        /* ======================================== */
        @media (max-width: 576px) {
            /* تحويل الجدول لكروت مرنة */
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            
            .table thead {
                display: none;
            }
            
            .table tbody tr {
                border: 1px solid #e9edf2;
                border-radius: 28px;
                margin-bottom: 18px;
                padding: 12px 8px;
                background: white;
                box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            }
            
            .table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: center;
                padding: 12px 14px;
                border-bottom: 1px solid #f0f2f5;
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .table td:last-child {
                border-bottom: none;
            }
            
            /* التسمية التوضيحية */
            .table td:before {
                content: attr(data-label);
                font-weight: 800;
                font-size: 0.8rem;
                color: #1e293b;
                background: #eef2ff;
                padding: 6px 14px;
                border-radius: 50px;
                display: inline-block;
                min-width: 100px;
                text-align: center;
                letter-spacing: 0.3px;
            }
            
            .table td .content-value {
                flex: 1;
                text-align: center;
                font-weight: 600;
                font-size: 0.95rem;
                word-break: break-word;
            }
            
            /* الاسم محاذاة يمين مع التسمية */
            .table td[data-label="الاسم"] {
                justify-content: flex-start;
                text-align: right;
            }
            
            .table td[data-label="الاسم"]:before {
                margin-left: 12px;
            }
            
            /* مركزية باقي الحقول */
            .table td[data-label="#"],
            .table td[data-label="الهوية"],
            .table td[data-label="إجراء"] {
                justify-content: center;
            }
            
            /* 🔥 زر الحضور داخل الخلية يصبح عملاق وواضح جداً 🔥 */
            .table td[data-label="إجراء"] .btn-attend-mobile {
                width: 100%;
                font-size: 1.2rem;
                padding: 14px 12px;
                background: linear-gradient(95deg, #059669, #10b981);
                border-radius: 60px;
                box-shadow: 0 8px 20px rgba(5,150,105,0.4);
            }
            
            .table td[data-label="إجراء"] button:disabled {
                width: 100%;
                padding: 12px;
                font-size: 1rem;
                font-weight: bold;
                background: #94a3b8;
                box-shadow: none;
            }

        }

        /* لضمان عدم وجود عنوان كلمة "إجراء" بارز (أخفي النص غير المرغوب) */
        /* تم إزالة أي نصوص جانبية غير ضرورية */
        .btn-attend-mobile i, .btn-sm i {
            pointer-events: none;
        }

        button:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none !important;
        }

        footer {
            text-align: center;
            margin-top: 2rem;
            color: #475569;
            font-size: 0.75rem;
        }

        .toast-notification {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            animation: slideInUp 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }

        @keyframes slideInUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* تحسين البحث والمدخلات */
        .form-control, .form-select {
            border-radius: 60px;
            border: 1px solid #dee2e6;
            padding: 10px 16px;
        }
        
        .input-group-text {
            border-radius: 60px 0 0 60px;
            background: white;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(5,150,105,0.2);
            border-color: #10b981;
        }
        
        /* إزالة أي كلمة "إجراء" زائدة في تصميم الجوال، الزر هو المسيطر */
        .table td[data-label="إجراء"]:before {
            font-weight: 800;
            background: #eef2ff;
        }
    </style>
</head>
<body>

<div class="attendance-wrapper">
    
    <!-- بطاقة الفعالية الجذابة -->
    <div class="event-card text-white">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="fas fa-calendar-alt me-2"></i> 
                    {{ 'فعالية الحضور لتاريخ ' . \Carbon\Carbon::parse($event->event_date)->format('Y-m-d') }}
                </h2>
            </div>
            <div class="mt-2 mt-sm-0">
                <div class="d-flex gap-2 flex-wrap justify-content-start">
                    <span class="stats-badge bg-success bg-opacity-30"><i class="fas fa-check-circle"></i> حضر: <strong id="attendedCount">{{ $attendedCount }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- شريط البحث والفلترة -->
    <div class="search-filter-bar">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-12">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0" 
                           placeholder="🔍 ابحث بالاسم أو رقم الهوية" autocomplete="off">
                </div>
            </div>
        </div>
    </div>
<!-- زر فتح الكاميرا -->
<div class="mb-3 text-center">
    <button id="openScannerBtn" class="btn btn-success rounded-pill px-4 py-3 fw-bold">
        <i class="fas fa-camera"></i>
        فتح الكاميرا وقراءة الباركود
    </button>
</div>

<!-- مكان الكاميرا -->
<div id="reader" style="width:100%; max-width:500px; margin:auto; display:none;"></div>

    <!-- الجدول الرئيسي مع زر حضور عملاق في الجوال -->
    <div class="table-responsive-custom">
        <table class="table table-hover align-middle" id="attendanceTable">
            <thead>
                <tr>
                    <th style="width: 70px">#</th>
                    <th>الاسم الكامل</th>
                    <th>رقم الهوية</th>
                    <th style="min-width: 140px">الإجراء</th>
                </tr>
            </thead>
            <tbody>
                @php $index = 1; @endphp
                @forelse($logs as $log)
                <tr data-log-id="{{ $log->id }}" data-attended="{{ $log->attended_at ? '1' : '0' }}" data-excused="{{ $log->excused_at ? '1' : '0' }}">
                    <td data-label="#"> 
                        <span class="content-value">{{ $index++ }}</span>
                    </td>
                    <td data-label="الاسم">
                        <span class="content-value">
                            <i class="fas fa-user-circle text-primary me-1"></i> 
                            {{ $log->user->full_name ?? 'مستخدم غير معروف' }}
                        </span>
                    </td>
                    <td data-label="الهوية">
                        <span class="content-value">{{ $log->user->national_id ?? '—' }}</span>
                    </td>
                    <td data-label="إجراء" class="action-cell">
                        @if(!$log->attended_at && !$log->excused_at)
                            <form method="POST" action="{{ route('attendance.mark', $log->id) }}" style="display:inline-block; width:100%;" class="attendance-form">
                                @csrf
                                <!-- ✅ زر حضور كبير وواضح جدًا في جميع الأحجام، ومصمم للجوال بشكل عملاق -->
                                <button type="submit" class="btn-attend-mobile">
                                    <i class="fas fa-fingerprint fa-lg"></i> 
                                    <span>حضور</span>
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                        @elseif($log->attended_at)
                            <button class="btn btn-dark btn-sm w-100 rounded-pill" disabled style="font-weight:700; background:#334155; border:none;">
                                <i class="fas fa-check-double"></i> تم الحضور
                            </button>
                        @elseif($log->excused_at)
                            <button class="btn-excuse" data-id="{{ $log->id }}">
                                معتذر
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                        لا توجد بيانات حضور لهذه الفعالية
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <footer>
        <i class="fas fa-mobile-screen-button"></i> نظام حضور متقدم - زر الحضور واضح وكبير
    </footer>
</div>
<!-- السكربتات -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {

    // =========================
    // DataTables
    // =========================
    var table = $('#attendanceTable').DataTable({
        language: {
            search: "",
            searchPlaceholder: "بحث...",
            lengthMenu: "عرض _MENU_ سجل",
            info: "_START_ - _END_ من _TOTAL_ سجل",
            paginate: {
                first: "الأول",
                last: "الأخير",
                next: "التالي",
                previous: "السابق"
            },
            zeroRecords: "لا توجد نتائج",
            infoEmpty: "لا توجد بيانات",
            infoFiltered: "(تم التصفية من _MAX_ سجل)"
        },
        pageLength: 20,
        lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, "الكل"]],
        dom: '<"d-none"l>ftip',
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [2, 3] },
            { className: "dt-center", targets: "_all" }
        ]
    });

    // =========================
    // منع بحث الافتراضي
    // =========================
    $('.dataTables_filter').hide();

    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#clearSearchBtn').on('click', function() {
        $('#searchInput').val('');
        table.search('').draw();
        $('#statusFilter').val('all');
        $.fn.dataTable.ext.search.pop();
        table.draw();
    });

    // =========================
    // 🔥 تأكيد الحضور (مهم جداً)
    // =========================
    $(document).on('submit', '.attendance-form', function(e) {
        e.preventDefault();

        let form = $(this);
        let btn = form.find('button[type="submit"]');

        // منع التكرار
        if (btn.data('disabled')) return false;

        // اسم الشخص
        let row = form.closest('tr');
        let name = row.find('[data-label="الاسم"] .content-value')
                      .text()
                      .trim()
                      .replace(/[\uF000-\uFFFF]/g, '');

        // تأكيد واحد فقط
        let confirmMsg = "هل أنت متأكد من تسجيل الحضور لـ:\n" + name + " ؟";

        if (!confirm(confirmMsg)) {
            return false;
        }

        // تعطيل الزر
        btn.data('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحضور...').attr('disabled', true);

        // إرسال فعلي بدون loop
        form.off('submit');
        form[0].submit();
    });

});
</script>
<script>
window.markExcused = function(logId) {

    if (!confirm("هل أنت متأكد من تسجيل معتذر؟")) return;

    fetch("/attendance/" + logId + "/excuse", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert("فشل التحديث");
        }
    });

};
</script>

<audio id="successSound" preload="auto">
    <source src="{{ asset('sounds/success.wav') }}" type="audio/wav">
</audio>

<audio id="errorSound" preload="auto">
    <source src="{{ asset('sounds/error.wav') }}" type="audio/wav">
</audio>

<audio id="warningSound" preload="auto">
    <source src="{{ asset('sounds/warning.wav') }}" type="audio/wav">
</audio>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let html5QrCode;
let scannerRunning = false;

let scanLocked = false;
let lastScannedCode = '';

// فتح الكاميرا
document.getElementById('openScannerBtn').addEventListener('click', function () {

    const readerDiv = document.getElementById('reader');

    // إذا الكاميرا شغالة بالفعل
    if (scannerRunning) {
        html5QrCode.stop().then(() => {
            scannerRunning = false;
            readerDiv.style.display = 'none';
            this.innerHTML = '<i class="fas fa-camera"></i> فتح الكاميرا وقراءة الباركود';
        });
        return;
    }

    readerDiv.style.display = 'block';

    html5QrCode = new Html5Qrcode("reader");

    Html5Qrcode.getCameras().then(devices => {

        if (devices && devices.length) {

            html5QrCode.start(
                { facingMode: "environment" }, // الكاميرا الخلفية
                {
                    fps: 10,
                    qrbox: 250
                },
                onScanSuccess
            );

            scannerRunning = true;

            this.innerHTML = '<i class="fas fa-times"></i> إغلاق الكاميرا';
        }

    }).catch(err => {
        alert("تعذر فتح الكاميرا");
        console.error(err);
    });

});

function playSuccessSound() {
    let sound = document.getElementById('successSound');
    sound.currentTime = 0;
    sound.play();
}

function playErrorSound() {
    let sound = document.getElementById('errorSound');
    sound.currentTime = 0;
    sound.play();
}

function playWarningSound() {
    let sound = document.getElementById('warningSound');
    sound.currentTime = 0;
    sound.play();
}

// عند نجاح القراءة
// عند نجاح القراءة
function onScanSuccess(decodedText, decodedResult) {

    // إذا السكانر متوقف مؤقتاً تجاهل
    if (scanLocked) return;

    // قفل فوري
    scanLocked = true;

    // إيقاف القراءة مؤقتاً فوراً
    html5QrCode.pause(true);

    console.log("QR Result:", decodedText);

    // تنظيف الرقم
    let nationalId = decodedText.trim();

    // تعبئة البحث
    $('#searchInput').val(nationalId);

    // تنفيذ البحث
    $('#attendanceTable').DataTable().search(nationalId).draw();

    setTimeout(() => {

        let visibleRows = $('#attendanceTable tbody tr:visible');

        if (visibleRows.length > 0) {

            let row = visibleRows.first();

            // زر الحضور
            let attendBtn = row.find('.attendance-form button[type="submit"]');

            if (attendBtn.length > 0) {

                attendBtn.click();

                playSuccessSound();

                showToast('✅ تم العثور على الشخص وتسجيل حضوره بنجاح');

            } else {

                playWarningSound();

                showToast('⚠️ الشخص مسجل مسبقاً أو معتذر');

            }

        } else {

            playErrorSound();

            showToast('❌ لا توجد بيانات مطابقة');

        }

        // بعد 4 ثواني ارجع فعل السكانر
        setTimeout(() => {

            scanLocked = false;

            try {
                html5QrCode.resume();
            } catch (e) {
                console.log("resume error", e);
            }

        }, 500);

    }, 300);
}

// Toast Notification
function showToast(message) {

    let toast = document.createElement('div');

    toast.className = 'toast-notification alert alert-dark shadow-lg';

    toast.innerHTML = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
</body>
</html>