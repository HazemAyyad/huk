
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>طلبات الكراسي الكهربائية</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E♿%3C/text%3E%3C/svg%3E">

    <style>

        body {
            background: #f4f7fb;
            font-family: "Tahoma", "Arial", sans-serif;
            color: #263238;
        }

        .page-header {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            border-radius: 20px;
            padding: 25px 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(13, 110, 253, 0.15);
        }

        .page-header h2 {
            font-weight: 700;
            margin-bottom: 6px;
        }

        .page-header p {
            margin: 0;
            opacity: .85;
        }

        .stat-card {
            border: none;
            border-radius: 18px;
            background: white;
            box-shadow: 0 5px 18px rgba(0, 0, 0, .06);
            transition: .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .filter-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .06);
            margin-bottom: 25px;
        }

        .filter-title {
            font-weight: 700;
            color: #263238;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 11px 14px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .1);
        }

        .btn {
            border-radius: 11px;
        }

        .table-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .06);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8fafc;
            color: #495057;
            font-size: 14px;
            font-weight: 700;
            padding: 17px 12px;
            border-bottom: 1px solid #e9ecef;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 15px 12px;
            vertical-align: middle;
            border-color: #f0f2f5;
        }

        .table tbody tr {
            transition: .15s;
        }

        .table tbody tr:hover {
            background: #f8fbff;
        }

        .person-image {
            width: 58px;
            height: 58px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e9ecef;
        }

        .person-name {
            font-weight: 700;
            color: #212529;
        }

        .person-id {
            font-size: 13px;
            color: #6c757d;
        }

        .status-badge {
            padding: 7px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-rejected {
            background: #f8d7da;
            color: #842029;
        }

        .btn-view {
            background: #e7f0ff;
            color: #0d6efd;
            border: none;
            padding: 8px 14px;
            font-weight: 600;
        }

        .btn-view:hover {
            background: #0d6efd;
            color: white;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin: auto;
            border-radius: 50%;
            background: #eef4ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
        }

        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            border-radius: 8px !important;
            margin: 0 3px;
            border: none;
            color: #0d6efd;
        }

        .pagination .active .page-link {
            background: #0d6efd;
            color: white;
        }

        @media (max-width: 768px) {

            .page-header {
                padding: 20px;
            }

            .page-header h2 {
                font-size: 20px;
            }

            .table-card {
                border-radius: 15px;
            }

            .table-responsive {
                overflow-x: auto;
            }

        }

    </style>

</head>

<body>

<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Header --}}
    <div class="page-header">

        <div class="d-flex flex-column flex-md-row
                    justify-content-between align-items-md-center gap-3">

            <div>

                <h2>
                    <i class="bi bi-wheelchair"></i>
                    طلبات الكراسي الكهربائية
                </h2>

                <p>
                    إدارة ومراجعة طلبات المستفيدين
                </p>

            </div>

            <div>

                <span class="badge bg-light text-primary fs-6 px-3 py-2">

                    <i class="bi bi-list-check"></i>

                    {{ $requests->total() }} طلب

                </span>

            </div>

        </div>

    </div>

{{-- الإحصائيات --}}
<div class="row g-3 mb-4">

    {{-- إجمالي الطلبات --}}
    <div class="col-6 col-lg-3">

        <div class="card stat-card h-100">

            <div class="card-body d-flex align-items-center gap-3">

                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-files"></i>
                </div>

                <div>

                    <div class="text-muted small">
                        إجمالي الطلبات
                    </div>

                    <h4 class="mb-0 fw-bold">
                        {{ \App\Models\WheelchairRequest::count() }}
                    </h4>

                </div>

            </div>

        </div>

    </div>


    {{-- قيد المراجعة --}}
    <div class="col-6 col-lg-3">

        <div class="card stat-card h-100">

            <div class="card-body d-flex align-items-center gap-3">

                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>

                <div>

                    <div class="text-muted small">
                        قيد المراجعة
                    </div>

                    <h4 class="mb-0 fw-bold">

                        {{ \App\Models\WheelchairRequest::where('status', 'pending')->count() }}

                    </h4>

                </div>

            </div>

        </div>

    </div>


    {{-- الطلبات المعتمدة --}}
    <div class="col-6 col-lg-3">

        <div class="card stat-card h-100">

            <div class="card-body d-flex align-items-center gap-3">

                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle"></i>
                </div>

                <div>

                    <div class="text-muted small">
                        الطلبات المعتمدة
                    </div>

                    <h4 class="mb-0 fw-bold">

                        {{
                            \App\Models\WheelchairRequest::whereIn('status', [
                                'approved_first',
                                'approved_second',
                                'approved_third'
                            ])->count()
                        }}

                    </h4>

                </div>

            </div>

        </div>

    </div>


    {{-- الطلبات المرفوضة --}}
    <div class="col-6 col-lg-3">

        <div class="card stat-card h-100">

            <div class="card-body d-flex align-items-center gap-3">

                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-x-circle"></i>
                </div>

                <div>

                    <div class="text-muted small">
                        الطلبات المرفوضة
                    </div>

                    <h4 class="mb-0 fw-bold">

                        {{
                            \App\Models\WheelchairRequest::whereIn('status', [
                                'rejected_previous',
                                'rejected_not_eligible'
                            ])->count()
                        }}

                    </h4>

                </div>

            </div>

        </div>

    </div>

</div>


    {{-- الفلاتر --}}
    <div class="card filter-card">

        <div class="card-body p-4">

            <div class="d-flex align-items-center gap-2 mb-4">

                <div class="stat-icon bg-primary bg-opacity-10 text-primary">

                    <i class="bi bi-funnel"></i>

                </div>

                <div>

                    <h5 class="filter-title mb-1">
                        البحث والتصفية
                    </h5>

                    <small class="text-muted">
                        استخدم الفلاتر للوصول إلى الطلب المطلوب
                    </small>

                </div>

            </div>


            <form method="GET" action="{{ route('wheelchair.index') }}">

                <div class="row g-3">

                    {{-- الاسم --}}
                    <div class="col-md-6 col-lg-3">

                        <label class="form-label fw-semibold">
                            اسم المستفيد
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-white">
                                <i class="bi bi-person"></i>
                            </span>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                placeholder="اكتب الاسم..."
                                value="{{ request('full_name') }}">

                        </div>

                    </div>


                    {{-- رقم الهوية --}}
                    <div class="col-md-6 col-lg-3">

                        <label class="form-label fw-semibold">
                            رقم الهوية
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-white">
                                <i class="bi bi-card-text"></i>
                            </span>

                            <input
                                type="text"
                                name="national_id"
                                class="form-control"
                                placeholder="رقم الهوية..."
                                value="{{ request('national_id') }}">

                        </div>

                    </div>


                    {{-- الهاتف --}}
                    <div class="col-md-6 col-lg-3">

                        <label class="form-label fw-semibold">
                            رقم الهاتف
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-white">
                                <i class="bi bi-telephone"></i>
                            </span>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                placeholder="رقم الهاتف..."
                                value="{{ request('phone') }}">

                        </div>

                    </div>


                    {{-- الحالة --}}
                    <div class="col-md-6 col-lg-3">

                        <label class="form-label fw-semibold">
                            حالة الطلب
                        </label>

                        <select name="status" class="form-select">

                            <option value="">
                                جميع الحالات
                            </option>

                            <option value="pending"
                                {{ request('status') == 'pending' ? 'selected' : '' }}>
                                بانتظار المراجعة
                            </option>

                            <option value="approved_first"
                                {{ request('status') == 'approved_first' ? 'selected' : '' }}>
                                معتمد من الدرجة الأولى
                            </option>

                            <option value="approved_second"
                                {{ request('status') == 'approved_second' ? 'selected' : '' }}>
                                معتمد من الدرجة الثانية
                            </option>

                            <option value="approved_third"
                                {{ request('status') == 'approved_third' ? 'selected' : '' }}>
                                معتمد من الدرجة الثالثة
                            </option>

                            <option value="rejected_not_eligible"
                                {{ request('status') == 'rejected_not_eligible' ? 'selected' : '' }}>
                                مرفوض بسبب عدم الاستحقاق
                            </option>

                            <option value="rejected_previous"
                                {{ request('status') == 'rejected_previous' ? 'selected' : '' }}>
                                مرفوض بسبب الاستلام المسبق
                            </option>

                        </select>

                    </div>


                    {{-- العمر من --}}
                    <div class="col-md-6 col-lg-2">

                        <label class="form-label fw-semibold">
                            العمر من
                        </label>

                        <input
                            type="number"
                            name="age_from"
                            class="form-control"
                            placeholder="مثال: 18"
                            value="{{ request('age_from') }}">

                    </div>


                    {{-- العمر إلى --}}
                    <div class="col-md-6 col-lg-2">

                        <label class="form-label fw-semibold">
                            العمر إلى
                        </label>

                        <input
                            type="number"
                            name="age_to"
                            class="form-control"
                            placeholder="مثال: 60"
                            value="{{ request('age_to') }}">

                    </div>


                    {{-- التاريخ من --}}
                    <div class="col-md-6 col-lg-3">

                        <label class="form-label fw-semibold">
                            تاريخ الطلب من
                        </label>

                        <input
                            type="date"
                            name="date_from"
                            class="form-control"
                            value="{{ request('date_from') }}">

                    </div>


                    {{-- التاريخ إلى --}}
                    <div class="col-md-6 col-lg-3">

                        <label class="form-label fw-semibold">
                            تاريخ الطلب إلى
                        </label>

                        <input
                            type="date"
                            name="date_to"
                            class="form-control"
                            value="{{ request('date_to') }}">

                    </div>


                    {{-- الأزرار --}}
                    <div class="col-md-12 col-lg-2 d-flex align-items-end gap-2">

                        <button type="submit"
                                class="btn btn-primary flex-grow-1">

                            <i class="bi bi-search"></i>
                            بحث

                        </button>

                        <a href="{{ route('wheelchair.index') }}"
                           class="btn btn-outline-secondary"
                           title="إعادة ضبط">

                            <i class="bi bi-arrow-clockwise"></i>

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- جدول الطلبات --}}
    <div class="card table-card">

        <div class="card-header bg-white border-0 p-4">

            <div class="d-flex flex-column flex-md-row
                        justify-content-between align-items-md-center gap-2">

                <div>

                    <h5 class="mb-1 fw-bold">

                        <i class="bi bi-table text-primary"></i>

                        قائمة الطلبات

                    </h5>

                    <small class="text-muted">

                        عرض نتائج البحث والطلبات المسجلة

                    </small>

                </div>

                <div class="text-muted small">

                    عرض {{ $requests->firstItem() ?? 0 }}
                    -
                    {{ $requests->lastItem() ?? 0 }}
                    من {{ $requests->total() }}

                </div>

            </div>

        </div>


        <div class="table-responsive">

            <table class="table text-center align-middle">

                <thead>

                <tr>

                    <th>#</th>

                    <th>الصورة</th>

                    <th>المستفيد</th>

                    <th>الهاتف</th>

                    <th>العمر</th>

                    <th>الحالة</th>

                    <th>تاريخ دخول مصر</th>

                    <th>الإجراء</th>

                </tr>

                </thead>


                <tbody>

                @forelse($requests as $request)

                    <tr>

                        {{-- الرقم --}}
                        <td class="text-muted fw-semibold">

                            {{ $requests->firstItem() + $loop->index }}

                        </td>


                        {{-- الصورة --}}
                        <td>

                            @if($request->personal_photo)

                                <img
                                    src="{{ asset($request->personal_photo) }}"
                                    class="person-image"
                                    alt="صورة {{ $request->full_name }}">

                            @else

                                <div class="person-image bg-light d-inline-flex
                                            align-items-center justify-content-center">

                                    <i class="bi bi-person text-secondary fs-4"></i>

                                </div>

                            @endif

                        </td>


                        {{-- الاسم والهوية --}}
                        <td class="text-end">

                            <div class="person-name">

                                {{ $request->full_name }}

                            </div>

                            <div class="person-id mt-1">

                                <i class="bi bi-card-text"></i>

                                {{ $request->national_id }}

                            </div>

                        </td>


                        {{-- الهاتف --}}
                        <td>

                            <span class="text-nowrap">

                                <i class="bi bi-telephone text-primary"></i>

                                {{ $request->phone }}

                            </span>

                        </td>


                        {{-- العمر --}}
                        <td>

                            <span class="badge bg-light text-dark px-3 py-2">

                                {{ $request->age }} سنة

                            </span>

                        </td>


                        {{-- الحالة --}}
                        <td>

                            @if($request->status == 'pending')

                                <span class="status-badge status-pending">

                                    <i class="bi bi-hourglass-split"></i>

                                    بانتظار المراجعة

                                </span>

                            @elseif($request->status == 'approved_first')

                                <span class="status-badge status-approved">

                                    <i class="bi bi-check-circle"></i>

                                    معتمد من الدرجة الأولى

                                </span>

                            @elseif($request->status == 'approved_second')

                                <span class="status-badge status-approved">

                                    <i class="bi bi-check-circle"></i>

                                    معتمد من الدرجة الثانية

                                </span>

                            @elseif($request->status == 'approved_third')

                                <span class="status-badge status-approved">

                                    <i class="bi bi-check-circle"></i>

                                    معتمد من الدرجة الثالثة

                                </span>

                            @elseif($request->status == 'rejected_not_eligible')

                                <span class="status-badge status-rejected">

                                    <i class="bi bi-x-circle"></i>

                                    مرفوض بسبب عدم الاستحقاق

                                </span>

                            @elseif($request->status == 'rejected_previous')

                                <span class="status-badge status-rejected">

                                    <i class="bi bi-x-circle"></i>

                                    مرفوض بسبب الاستلام المسبق

                                </span>

                            @endif

                        </td>


                        {{-- التاريخ --}}
                        <td>
                            <div class="fw-semibold">

                                {{ $request->entry_date }}

                            </div>
                        </td>


                        {{-- الإجراء --}}
                        <td>

                            <a href="{{ route('wheelchair.show', $request->id) }}"
                               class="btn btn-view btn-sm">

                                <i class="bi bi-eye"></i>

                                عرض

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="8">

                            <div class="empty-state">

                                <div class="empty-icon mb-3">

                                    <i class="bi bi-inbox"></i>

                                </div>

                                <h5 class="fw-bold">
                                    لا توجد طلبات
                                </h5>

                                <p class="text-muted mb-0">

                                    لم يتم العثور على طلبات مطابقة للفلاتر الحالية.

                                </p>

                            </div>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if($requests->hasPages())

            <div class="p-4 border-top">

                {{ $requests->withQueryString()->links() }}

            </div>

        @endif

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>