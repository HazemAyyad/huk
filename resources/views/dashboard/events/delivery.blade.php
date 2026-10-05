<!-- resources/views/dashboard/events/delivery.blade.php -->

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->name ?? 'تسجيل التسليم' }} | لوحة التحكم</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        * {
            font-family: 'Tajawal', sans-serif;
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

        .event-card {
            background: linear-gradient(145deg, #0f2b3d, #1b4a6e);
            border-radius: 32px;
            padding: 1.4rem;
            margin-bottom: 1.8rem;
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.3);
        }

        .table-responsive-custom {
            background: white;
            border-radius: 28px;
            padding: .3rem;
            overflow-x: hidden;
            box-shadow: 0 20px 35px -8px rgba(0,0,0,0.12);
        }

        .table thead th {
            background: #1e2a3e;
            color: white;
            text-align: center;
            padding: 14px;
        }

        .table td {
            text-align: center;
            vertical-align: middle;
        }

        .btn-delivery-mobile {
            width: 100%;
            padding: 14px 12px;
            font-size: 1.15rem;
            font-weight: 800;
            border-radius: 60px;
            background: linear-gradient(95deg, #2563eb, #3b82f6);
            border: none;
            color: white;
            box-shadow: 0 6px 14px rgba(37,99,235,.35);
            transition: .2s;
        }

        .btn-delivery-mobile:hover {
            transform: scale(1.01);
            background: linear-gradient(95deg, #1d4ed8, #2563eb);
        }

        .search-filter-bar {
            background: rgba(255,255,255,0.95);
            border-radius: 28px;
            padding: .9rem 1.2rem;
            margin-bottom: 1.5rem;
        }

        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            padding: 6px 16px;
            border-radius: 60px;
            font-size: .85rem;
            font-weight: 600;
        }

        .toast-notification {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
        }

        @media (max-width:576px) {

            .table,
            .table tbody,
            .table tr,
            .table td {
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
            }

            .table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 14px;
                border-bottom: 1px solid #f0f2f5;
            }

            .table td:before {
                content: attr(data-label);
                font-weight: 800;
                background: #eef2ff;
                padding: 6px 14px;
                border-radius: 50px;
                min-width: 100px;
                text-align: center;
            }

            .content-value {
                flex: 1;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<div class="attendance-wrapper">

    <!-- Header -->
    <div class="event-card text-white">
        <div class="d-flex flex-wrap justify-content-between align-items-center">

            <div>
                <h2 class="mb-1">
                    <i class="fas fa-box-open me-2"></i>
                    {{ 'فعالية التسليم لتاريخ ' . \Carbon\Carbon::parse($event->event_date)->format('Y-m-d') }}
                </h2>
            </div>

            <div>
                <span class="stats-badge bg-primary bg-opacity-30">
                    <i class="fas fa-check-circle"></i>
                    تم التسليم:
                    <strong id="receivedCount">{{ $receivedCount }}</strong>
                </span>
            </div>

        </div>
    </div>

    <!-- Search -->
    <div class="search-filter-bar">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <i class="fas fa-search text-muted"></i>
            </span>

            <input type="text"
                   id="searchInput"
                   class="form-control border-start-0"
                   placeholder="🔍 ابحث بالاسم أو الهوية">
        </div>
    </div>

    <!-- Scanner -->
    <div class="mb-3 text-center">
        <button id="openScannerBtn" class="btn btn-primary rounded-pill px-4 py-3 fw-bold">
            <i class="fas fa-camera"></i>
            فتح الكاميرا وقراءة الباركود
        </button>
    </div>

    <div id="reader"
         style="width:100%; max-width:500px; margin:auto; display:none;">
    </div>

    <!-- Table -->
    <div class="table-responsive-custom">

        <table class="table table-hover align-middle" id="deliveryTable">

            <thead>
            <tr>
                <th>#</th>
                <th>الاسم الكامل</th>
                <th>رقم الهوية</th>
                <th>الإجراء</th>
            </tr>
            </thead>

            <tbody>

                @php $index = 1; @endphp

                @forelse($logs as $log)

                <tr>

                    <td data-label="#">
                        <span class="content-value">
                            {{ $index++ }}
                        </span>
                    </td>

                    <td data-label="الاسم">
                        <span class="content-value">
                            {{ $log->user->full_name ?? 'غير معروف' }}
                        </span>
                    </td>

                    <td data-label="الهوية">
                        <span class="content-value">
                            {{ $log->user->national_id ?? '---' }}
                        </span>
                    </td>

                    <td data-label="إجراء">

                        <button
                            class="btn-delivery-mobile mark-delivery-btn"
                            data-id="{{ $log->id }}"
                            data-name="{{ $log->user->full_name }}"
                        >
                            <i class="fas fa-box"></i>
                            تم التسليم
                        </button>

                    </td>

                </tr>

                @empty

                <tr>

                    <td data-label="#" class="text-center">—</td>

                    <td data-label="الاسم" class="text-center">
                        لا توجد بيانات
                    </td>

                    <td data-label="الهوية" class="text-center">—</td>

                    <td data-label="إجراء" class="text-center">—</td>

                </tr>

                @endforelse

                </tbody>

        </table>

    </div>

</div>

<!-- Sounds -->
<audio id="successSound">
    <source src="{{ asset('sounds/success.wav') }}" type="audio/wav">
</audio>

<audio id="errorSound">
    <source src="{{ asset('sounds/error.wav') }}" type="audio/wav">
</audio>

<audio id="warningSound">
    <source src="{{ asset('sounds/warning.wav') }}" type="audio/wav">
</audio>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://unpkg.com/html5-qrcode"></script>

<script>

let table = $('#deliveryTable').DataTable({
    language: {
        search: "",
        zeroRecords: "لا توجد نتائج"
    },
    pageLength: 20,
    dom: '<"d-none"l>ftip'
});

$('.dataTables_filter').hide();

$('#searchInput').on('keyup', function () {
    table.search(this.value).draw();
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

function showToast(message) {

    let toast = document.createElement('div');

    toast.className = 'toast-notification alert alert-dark shadow-lg';

    toast.innerHTML = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// =====================================
// رابط التسليم الأساسي
// =====================================
const deliveryMarkUrlTemplate =
    @json(route('delivery.mark', ['id' => 'LOG_ID']));

// =====================================
// تسجيل التسليم
// =====================================
$(document).on('click', '.mark-delivery-btn', function () {

    let btn = $(this);

    if (btn.data('disabled')) return;

    let logId = btn.data('id');
    let name = btn.data('name');

    if (!confirm("هل أنت متأكد من تسجيل التسليم لـ:\n" + name + " ؟")) {
        return;
    }

    btn.data('disabled', true);

    btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التسليم...');

    // توليد الرابط الصحيح
    let markUrl = deliveryMarkUrlTemplate.replace('LOG_ID', logId);

    fetch(markUrl, {

        method: "POST",

        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content')
        },

        body: JSON.stringify({
            send_method: 'with_notification'
        })

    })

    .then(async res => {

        // إذا رجع HTML أو خطأ
        if (!res.ok) {

            let text = await res.text();

            console.error("SERVER RESPONSE:", text);

            throw new Error("HTTP ERROR " + res.status);
        }

        return res.json();
    })

    .then(data => {

        // نجاح
        if (data.status === 'success') {

            playSuccessSound();

            btn.removeClass('btn-delivery-mobile');

            btn.addClass('btn btn-dark rounded-pill w-100');

            btn.html('<i class="fas fa-check-double"></i> تم التسليم');

            showToast(data.message);

            let count = parseInt($('#receivedCount').text());

            $('#receivedCount').text(count + 1);

            return;
        }

        // فشل تيليجرام
        if (data.status === 'telegram_failed') {

            playWarningSound();

            let forceManual = confirm(
                "فشل إرسال تيليجرام\nهل تريد التسجيل بدون إشعار؟"
            );

            if (!forceManual) {

                btn.data('disabled', false);

                btn.html('<i class="fas fa-box"></i> تم التسليم');

                return;
            }

            // رابط التسجيل اليدوي
            let manualUrl =
                deliveryMarkUrlTemplate.replace('LOG_ID', logId);

            fetch(manualUrl, {

                method: "POST",

                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                },

                body: JSON.stringify({
                    send_method: 'without_notification'
                })

            })

            .then(async res => {

                if (!res.ok) {

                    let text = await res.text();

                    console.error("MANUAL RESPONSE:", text);

                    throw new Error("HTTP ERROR " + res.status);
                }

                return res.json();
            })

            .then(manualData => {

                if (manualData.status === 'success') {

                    playSuccessSound();

                    btn.removeClass('btn-delivery-mobile');

                    btn.addClass('btn btn-dark rounded-pill w-100');

                    btn.html('<i class="fas fa-check-double"></i> تم التسليم');

                    showToast(manualData.message);

                    let count = parseInt($('#receivedCount').text());

                    $('#receivedCount').text(count + 1);

                } else {

                    playErrorSound();

                    showToast(manualData.message || 'فشل التسجيل اليدوي');

                    btn.data('disabled', false);

                    btn.html('<i class="fas fa-box"></i> تم التسليم');
                }

            })

            .catch(err => {

                playErrorSound();

                console.error(err);

                showToast('فشل الاتصال أثناء التسجيل اليدوي');

                btn.data('disabled', false);

                btn.html('<i class="fas fa-box"></i> تم التسليم');

            });

            return;
        }

        // أي خطأ آخر
        playErrorSound();

        showToast(data.message || 'حدث خطأ');

        btn.data('disabled', false);

        btn.html('<i class="fas fa-box"></i> تم التسليم');

    })

    .catch(async err => {

        playErrorSound();

        console.error(err);

        showToast('خطأ اتصال بالخادم');

        btn.data('disabled', false);

        btn.html('<i class="fas fa-box"></i> تم التسليم');

    });

});

// =====================================
// Scanner
// =====================================

let html5QrCode;
let scannerRunning = false;
let scanLocked = false;

document.getElementById('openScannerBtn').addEventListener('click', function () {

    const readerDiv = document.getElementById('reader');

    if (scannerRunning) {

        html5QrCode.stop().then(() => {

            scannerRunning = false;

            readerDiv.style.display = 'none';

            this.innerHTML =
                '<i class="fas fa-camera"></i> فتح الكاميرا وقراءة الباركود';

        });

        return;
    }

    readerDiv.style.display = 'block';

    html5QrCode = new Html5Qrcode("reader");

    Html5Qrcode.getCameras().then(devices => {

        if (devices.length) {

            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: 250
                },
                onScanSuccess
            );

            scannerRunning = true;

            this.innerHTML =
                '<i class="fas fa-times"></i> إغلاق الكاميرا';
        }

    }).catch(err => {

        console.error(err);

        alert("تعذر فتح الكاميرا");

    });

});

function onScanSuccess(decodedText) {

    if (scanLocked) return;

    scanLocked = true;

    html5QrCode.pause(true);

    let nationalId = decodedText.trim();

    $('#searchInput').val(nationalId);

    $('#deliveryTable').DataTable().search(nationalId).draw();

    setTimeout(() => {

        let visibleRows = $('#deliveryTable tbody tr:visible');

        if (visibleRows.length > 0) {

            let row = visibleRows.first();

            let btn = row.find('.mark-delivery-btn');

            if (btn.length > 0) {

                btn.click();

                playSuccessSound();

            } else {

                playWarningSound();

                showToast('تم التسليم مسبقاً');

            }

        } else {

            playErrorSound();

            showToast('لا توجد بيانات مطابقة');

        }

        setTimeout(() => {

            scanLocked = false;

            try {
                html5QrCode.resume();
            } catch (e) {
                console.log(e);
            }

        }, 500);

    }, 300);
}

</script>

</body>
</html>