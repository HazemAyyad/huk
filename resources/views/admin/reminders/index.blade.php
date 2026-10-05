<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التذكيرات</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">
    <style>
        body {
            background: #f4f7fc;
            font-family: 'Segoe UI', 'Tahoma', system-ui, sans-serif;
        }
        .container-fluid {
            max-width: 1360px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        .page-header {
            background: white;
            padding: 0.75rem 1.25rem;
            border-radius: 1.25rem;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.03);
        }
        .page-header h3 {
            font-weight: 600;
            color: #1e293b;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-header h3 i {
            font-size: 1.6rem;
            color: #f59e0b;
            filter: drop-shadow(0 2px 4px rgba(245, 158, 11, 0.2));
        }
        .btn-primary {
            background: #2563eb;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            font-weight: 500;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.2);
            transition: 0.2s;
        }
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3);
        }
        .btn-primary i {
            margin-left: 8px;
        }
        .card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.04);
            background: #ffffff;
            overflow: hidden;
        }
        .card-body {
            padding: 1.5rem 1.5rem 1.8rem;
        }
        .table {
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        .table thead th {
            border-bottom: 1px solid #e9edf4;
            color: #334155;
            font-weight: 600;
            letter-spacing: 0.2px;
            padding: 1rem 0.75rem;
            background-color: #f8fafc;
        }
        .table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tbody tr:hover {
            background-color: #fafcff;
            transition: 0.15s;
        }
        .badge {
            font-weight: 500;
            padding: 0.5rem 0.9rem;
            border-radius: 40px;
            font-size: 0.8rem;
            letter-spacing: 0.2px;
        }
        .badge.bg-success {
            background: #22c55e !important;
            color: #fff;
        }
        .badge.bg-danger {
            background: #ef4444 !important;
            color: #fff;
        }
        .badge.bg-secondary {
            background: #94a3b8 !important;
            color: #fff;
        }
        .btn-sm {
            border-radius: 30px;
            padding: 0.3rem 0.7rem;
            margin-left: 0.2rem;
            font-size: 0.8rem;
        }
        .btn-success.btn-sm {
            background: #22c55e;
            border: none;
            box-shadow: 0 2px 6px rgba(34, 197, 94, 0.2);
        }
        .btn-success.btn-sm:hover {
            background: #16a34a;
        }
        .btn-warning.btn-sm {
            background: #facc15;
            border: none;
            color: #1e293b;
            box-shadow: 0 2px 6px rgba(250, 204, 21, 0.25);
        }
        .btn-warning.btn-sm:hover {
            background: #eab308;
            color: #0f172a;
        }
        .btn-danger.btn-sm {
            background: #ef4444;
            border: none;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.2);
        }
        .btn-danger.btn-sm:hover {
            background: #dc2626;
        }
        .text-muted.py-5 {
            font-size: 1.1rem;
            letter-spacing: 0.3px;
        }
        .modal-content {
            border-radius: 28px;
            border: none;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.08);
            padding: 0.2rem 0.2rem;
        }
        .modal-header {
            border-bottom: 1px solid #eef2f6;
            padding: 1.5rem 1.8rem;
        }
        .modal-header h5 {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.3rem;
        }
        .modal-body {
            padding: 1.8rem 1.8rem 1.2rem;
        }
        .modal-body label {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
        }
        .modal-body .form-control {
            border-radius: 16px;
            padding: 0.7rem 1rem;
            border: 1px solid #e2e8f0;
            background: #fcfcfd;
            transition: 0.2s;
        }
        .modal-body .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
        }
        .modal-footer {
            border-top: none;
            padding: 0.8rem 1.8rem 1.8rem;
            gap: 0.6rem;
        }
        .modal-footer .btn-secondary {
            background: #f1f5f9;
            border: none;
            color: #1e293b;
            border-radius: 40px;
            padding: 0.5rem 1.6rem;
            font-weight: 500;
        }
        .modal-footer .btn-secondary:hover {
            background: #e2e8f0;
        }
        .modal-footer .btn-primary {
            border-radius: 40px;
            padding: 0.5rem 2rem;
        }
        .alert-success {
            background: #e6f9ed;
            border: none;
            border-radius: 20px;
            padding: 0.9rem 1.5rem;
            color: #166534;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.05);
        }
        @media (max-width: 768px) {
            .page-header {
                flex-wrap: wrap;
                gap: 0.8rem;
            }
            .table-responsive {
                border-radius: 18px;
            }
            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: stretch !important;
            }
            .btn-primary {
                width: fit-content;
            }
        }
        .action-group {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }
        .action-group form {
            display: inline-block;
        }
        .fw-bold.text-primary {
            color: #2563eb;
        }
    </style>
</head>
<body>

<div class="container-fluid">

    <!-- رأس الصفحة -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h3>
            <i class="fas fa-bell text-warning"></i>
            التذكيرات
        </h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReminder">
            <i class="fas fa-plus"></i> إضافة تذكير
        </button>
    </div>

    <!-- رسالة نجاح من الجلسة -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- بطاقة الجدول -->
    <div class="card shadow">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>اسم الشخص</th>
                        <th>الوصف</th>
                        <th>يوم التذكير</th>
                        <th>الحالة</th>
                        <th width="220">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reminders as $reminder)
                        @php
                            $isDone = $reminder->completed_for_month &&
                                \Carbon\Carbon::parse($reminder->completed_for_month)->month == now()->month &&
                                \Carbon\Carbon::parse($reminder->completed_for_month)->year == now()->year;

                            $isDue = now()->day >= $reminder->day_of_month;
                        @endphp
                        <tr>
                            <td><span class="fw-bold text-primary">{{ $loop->iteration }}</span></td>
                            <td><strong>{{ $reminder->title }}</strong></td>
                            <td>{{ $reminder->notes }}</td>
                            <td>يوم {{ $reminder->day_of_month }}</td>
                            <td>
                                @if($isDone)
                                    <span class="badge bg-success">تم لهذا الشهر</span>
                                @elseif($isDue)
                                    <span class="badge bg-danger">مستحق</span>
                                @else
                                    <span class="badge bg-secondary">بانتظار الموعد</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-group">
                                    @if(!$isDone && $isDue)
                                        <form action="{{ route('reminders.complete', $reminder->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> تم
                                            </button>
                                        </form>
                                    @endif

                                    <!-- زر التعديل المعدل لفتح المودال -->
                                    <button
                                        class="btn btn-warning btn-sm edit-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editReminder"
                                        data-id="{{ $reminder->id }}"
                                        data-title="{{ $reminder->title }}"
                                        data-notes="{{ $reminder->notes }}"
                                        data-day="{{ $reminder->day_of_month }}"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form action="{{ route('reminders.destroy', $reminder->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('حذف التذكير؟')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                لا توجد تذكيرات
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- مودال إضافة تذكير -->
<div class="modal fade" id="addReminder" tabindex="-1" aria-labelledby="addReminderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('reminders.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReminderLabel">
                        <i class="fas fa-plus-circle text-primary me-2"></i>إضافة تذكير
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="titleInput" class="form-label"><i class="far fa-user me-1"></i> اسم الشخص</label>
                        <input type="text" name="title" id="titleInput" class="form-control" placeholder="أدخل الاسم" required>
                    </div>
                    <div class="mb-3">
                        <label for="notesTextarea" class="form-label"><i class="far fa-sticky-note me-1"></i> الوصف</label>
                        <textarea name="notes" id="notesTextarea" rows="3" class="form-control" placeholder="تفاصيل التذكير ..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="dayInput" class="form-label"><i class="far fa-calendar-alt me-1"></i> يوم التذكير من الشهر</label>
                        <input type="number" name="day_of_month" id="dayInput" min="1" max="31" class="form-control" placeholder="مثال: 15" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- مودال تعديل التذكير -->
<div class="modal fade" id="editReminder" tabindex="-1" aria-labelledby="editReminderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editReminderForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editReminderLabel">
                        <i class="fas fa-edit text-warning me-2"></i>تعديل التذكير
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editTitle" class="form-label"><i class="far fa-user me-1"></i> اسم الشخص</label>
                        <input type="text" name="title" id="editTitle" class="form-control" value="" required>
                    </div>
                    <div class="mb-3">
                        <label for="editNotes" class="form-label"><i class="far fa-sticky-note me-1"></i> الوصف</label>
                        <textarea name="notes" id="editNotes" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editDay" class="form-label"><i class="far fa-calendar-alt me-1"></i> يوم التذكير من الشهر</label>
                        <input type="number" name="day_of_month" id="editDay" min="1" max="31" class="form-control" value="" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> تحديث
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.edit-btn').forEach(button => {

    button.addEventListener('click', function () {

        document.getElementById('editTitle').value = this.dataset.title;
        document.getElementById('editNotes').value = this.dataset.notes;
        document.getElementById('editDay').value = this.dataset.day;

        document.getElementById('editReminderForm').action =
            "/reminders/" + this.dataset.id;
    });

});
</script>
</body>
</html>