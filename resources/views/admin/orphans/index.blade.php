<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلبات الأيتام - لوحة التحكم</title>
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Google Fonts - Tajawal -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.rtl.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='48' fill='%232d5a27'/><path d='M70 65c-20 0-35-15-35-35 0-5 1-10 3-15-15 5-25 20-25 35 0 20 15 35 35 35 15 0 30-10 35-25-5 3-10 5-13 5z' fill='white'/><polygon points='65,25 68,33 77,33 70,38 73,47 65,42 57,47 60,38 53,33 62,33' fill='%23ffc107'/></svg>">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        * {
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(145deg, #f0f7fa 0%, #e4eef5 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #1a2a3a);
            color: white;
            border-radius: 30px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 15px 35px rgba(44, 62, 80, 0.3);
        }

        .admin-header h1 {
            font-weight: 900;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.8rem;
        }

        .admin-header h1 i {
            font-size: 2rem;
            color: #f39c12;
        }

        .stats-badge {
            background: rgba(255,255,255,0.1);
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .stats-badge i {
            color: #f39c12;
        }

        .stats-container {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 25px;
            padding: 1.5rem 2rem;
            flex: 1;
            min-width: 200px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid rgba(14,107,140,0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(14,107,140,0.15);
        }

        .stat-card.pending {
            border-right: 5px solid #f39c12;
        }

        .stat-card.approved {
            border-right: 5px solid #27ae60;
        }

        .stat-card.rejected {
            border-right: 5px solid #c0392b;
        }

        .stat-card.total {
            border-right: 5px solid #2980b9;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 900;
            color: #1a3b4e;
            line-height: 1.2;
        }

        .stat-label {
            color: #5f7d95;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card {
            border: none;
            border-radius: 30px;
            box-shadow: 0 15px 40px rgba(0, 30, 50, 0.08);
            background: white;
            overflow: hidden;
        }

        .card-header {
            border-bottom: none;
            font-weight: 800;
            font-size: 1.3rem;
            padding: 1.3rem 2rem;
            background: linear-gradient(135deg, #0a4f6e, #0e6b8c);
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card-header i {
            font-size: 1.8rem;
        }

        .table-container {
            padding: 1.5rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid #0e6b8c;
            color: #0a4f6e;
            font-weight: 800;
            font-size: 1rem;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
            color: #1a3b4e;
            font-weight: 500;
        }

        .status-badge-custom {
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .status-pending {
            background: rgba(243, 156, 18, 0.15);
            color: #f39c12;
            border: 1px solid #f39c12;
        }

        .status-approved {
            background: rgba(46, 204, 113, 0.15);
            color: #27ae60;
            border: 1px solid #27ae60;
        }

        .status-rejected {
            background: rgba(231, 76, 60, 0.15);
            color: #c0392b;
            border: 1px solid #c0392b;
        }

        .btn-view {
            background: linear-gradient(135deg, #0a4f6e, #0e6b8c);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(14,107,140,0.3);
            color: white;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
/* تنسيق الصورة الكبيرة في الجدول */
.user-avatar-large {
    width: 85px;      /* تم تكبير الحجم هنا */
    height: 85px;     /* تم تكبير الحجم هنا */
    margin: 0 auto;
    border-radius: 12px; /* زوايا منحنية بدلاً من دائرة كاملة لتعطي شكلاً عصرياً */
    overflow: hidden;
    border: 2px solid #eef2f5;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.user-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* لضمان توسيط النصوص عمودياً مع الصورة الكبيرة */
.table td {
    vertical-align: middle !important;
}

.user-name {
    font-weight: 700;
    color: #1a3b4e;
    margin-bottom: 2px;
}
        .user-avatar {
            width: 40px;
            height: 40px;
            min-width: 40px;   /* مهم جداً */
            min-height: 40px;  /* مهم جداً */
            border-radius: 50%;
            overflow: hidden;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            aspect-ratio: 1 / 1; /* هذا أهم سطر للجوال */
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 800;
            color: #1a3b4e;
        }

        .user-phone {
            font-size: 0.85rem;
            color: #5f7d95;
        }

        .date-badge {
            background: #f8fafc;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #5f7d95;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 5rem;
            color: #d0dee8;
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-weight: 800;
            color: #1a3b4e;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #5f7d95;
            font-size: 1.1rem;
        }

        .filter-section {
            background: #f8fafc;
            border-radius: 20px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-label {
            font-weight: 700;
            color: #1a3b4e;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            border: 1px solid #d0dee8;
            background: white;
            color: #1a3b4e;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-btn:hover {
            background: #0e6b8c;
            color: white;
            border-color: #0e6b8c;
        }

        .filter-btn.active {
            background: #0e6b8c;
            color: white;
            border-color: #0e6b8c;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 30px !important;
            margin: 0 3px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #0e6b8c !important;
            color: white !important;
            border-color: #0e6b8c !important;
        }

        @media print {
            .admin-header, .stats-container, .filter-section, .btn-view {
                display: none !important;
            }
        }
        .btn-delete {
        background: linear-gradient(135deg, #c0392b, #e74c3c);
        color: white;
        border: none;
        border-radius: 30px;
        padding: 0.5rem 1.3rem;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(231, 76, 60, 0.3);
            color: white;
        }

        /* Pagination Container */
.pagination {
    justify-content: center;
    gap: 6px;
    margin-top: 20px;
}

/* كل زر */
.page-item .page-link {
    border: none;
    border-radius: 50px;
    padding: 8px 16px;
    color: #0e6b8c;
    font-weight: 700;
    background: #f8fafc;
    transition: all 0.3s ease;
}

/* Hover */
.page-item .page-link:hover {
    background: #0e6b8c;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(14,107,140,0.3);
}

/* الصفحة الحالية */
.page-item.active .page-link {
    background: linear-gradient(135deg, #0a4f6e, #0e6b8c);
    color: white;
    box-shadow: 0 8px 20px rgba(14,107,140,0.4);
}

/* Disabled */
.page-item.disabled .page-link {
    background: #e9ecef;
    color: #aaa;
    cursor: not-allowed;
}

/* RTL ضبط */
.pagination .page-link {
    margin: 0;
}
.page-link {
    min-width: 40px;
    text-align: center;
}

.search-box {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* input wrapper */
.search-input-wrapper {
    position: relative;
    flex: 1;
}

/* input */
.search-input-wrapper input {
    width: 100%;
    padding: 12px 45px 12px 15px;
    border-radius: 30px;
    border: 1px solid #d0dee8;
    outline: none;
    font-size: 0.95rem;
    transition: all 0.3s;
}

.search-input-wrapper input:focus {
    border-color: #0e6b8c;
    box-shadow: 0 0 0 3px rgba(14,107,140,0.1);
}

/* icon */
.search-input-wrapper i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #0e6b8c;
}

/* زر البحث */
.btn-search {
    background: linear-gradient(135deg, #0a4f6e, #0e6b8c);
    color: white;
    border: none;
    border-radius: 30px;
    padding: 0 20px;
    font-weight: 700;
    min-width: 90px;
    transition: all 0.3s;
}

.btn-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(14,107,140,0.3);
}

@media (max-width: 576px) {
    /* جعل حاوية البحث تأخذ عرض الحاوية الأب بالكامل */
    .search-box {
        display: flex !important;
        flex-direction: row !important; /* لضمان بقاء الزر بجانب الحقل أو column إذا أردت فوق بعض */
        width: 100% !important;
        gap: 5px !important;
        margin-bottom: 15px !important;
    }

    /* جعل حقل الإدخال مرناً ليملأ المساحة */
    .search-input-wrapper {
        flex: 1 !important;
        width: auto !important;
    }

    .search-input-wrapper input {
        width: 100% !important;
        padding: 10px 35px 10px 10px !important; /* تقليل الحشو ليتناسب مع الجوال */
        font-size: 14px !important;
    }

    /* ضبط عرض زر البحث */
    .btn-search {
        min-width: 70px !important;
        padding: 0 15px !important;
        font-size: 14px !important;
    }
}

    #orphansTable tbody tr:hover {
        background-color: rgba(14, 107, 140, 0.05) !important;
        transition: background-color 0.2s;
    }

    /* لمنع تداخل روابط النصوص مع الرابط الأساسي */
    #orphansTable tbody tr td a:not(.btn-delete) {
        pointer-events: none; /* يجعل النقرة تمر من خلال النص إلى الصف */
        color: inherit;
        text-decoration: none;
    }

    /* تنسيق الرأس المدمج */
.header-compact {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem !important; /* تقليل الحشو */
    gap: 1.5rem;
}

.title-section h1 {
    font-size: 1.4rem !important; /* تصغير حجم الخط قليلاً */
    white-space: nowrap;
}

/* حاوية الإحصائيات الصغيرة */
.stats-grid-inline {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.mini-stat {
    background: rgba(255, 255, 255, 0.1);
    padding: 5px 15px;
    border-radius: 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 70px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.mini-stat:hover {
    background: rgba(255, 255, 255, 0.2);
}

.mini-stat .stat-val {
    font-size: 1.2rem;
    font-weight: 800;
    line-height: 1.2;
}

.mini-stat .stat-lab {
    font-size: 0.75rem;
    font-weight: 500;
    opacity: 0.9;
}

/* ألوان دلالية للإحصائيات الصغيرة */
.mini-stat.total { border-bottom: 3px solid #3498db; }
.mini-stat.pending { border-bottom: 3px solid #f39c12; }
.mini-stat.approved { border-bottom: 3px solid #27ae60; }
.mini-stat.rejected { border-bottom: 3px solid #e74c3c; }

/* ضبط التجاوب للموبايل */
@media (max-width: 768px) {
    .header-compact {
        flex-direction: column;
        text-align: center;
    }
    .stats-grid-inline {
        justify-content: center;
        width: 100%;
    }
    .mini-stat {
        flex: 1; /* لتأخذ البطاقات مساحة متساوية في الجوال */
    }
}

@media (max-width: 576px) {
    /* تصغير البادج في الجوال */
    .status-badge-custom {
        padding: 0.3rem 0.6rem !important;
        font-size: 0.8rem !important;
        gap: 4px !important;
        display: inline-flex !important;
        justify-content: center;
        min-width: 35px; /* يضمن شكل متناسق للأيقونة */
    }

    /* إخفاء النص داخل البادج وإبقاء الأيقونة فقط في الجوال */
    .status-badge-custom span.status-text {
        display: none !important;
    }
}
.status-filter-section {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 15px 0;
    flex-wrap: wrap;
}

.status-filter-section label {
    font-weight: 600;
    color: #444;
}

.status-filter-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-btn {
    border: 1px solid #ddd;
    background: #fff;
    color: #555;
    padding: 8px 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
}

.filter-btn:hover {
    background: #f5f5f5;
}

.filter-btn.active {
    background: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}

.filter-btn[data-filter="pending"].active {
    background: #f0ad4e;
    border-color: #f0ad4e;
}

.filter-btn[data-filter="approved"].active {
    background: #198754;
    border-color: #198754;
}

.filter-btn[data-filter="rejected"].active {
    background: #dc3545;
    border-color: #dc3545;
}
    </style>
</head>
<body>
    <div class="main-container">
        
        <div class="admin-header header-compact">
    <div class="title-section">
        <h1>
            <i class="fas fa-clipboard-list"></i>
            طلبات الأيتام
        </h1>
    </div>

    <div class="stats-grid-inline">
        <div class="mini-stat total">
            <span class="stat-val">{{ $total }}</span>
            <span class="stat-lab">الكل</span>
        </div>
        <div class="mini-stat pending">
            <span class="stat-val">{{ $pending }}</span>
            <span class="stat-lab">معلق</span>
        </div>
        <div class="mini-stat approved">
            <span class="stat-val">{{ $approved }}</span>
            <span class="stat-lab">معتمد</span>
        </div>
        <div class="mini-stat rejected">
            <span class="stat-val">{{ $rejected }}</span>
            <span class="stat-lab">مرفوض</span>
        </div>
    </div>
</div>

        <!-- بطاقة الجدول -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-table"></i>
                قائمة الطلبات
            </div>
            
            <div class="table-container">
                <form method="GET" class="search-box">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث بالاسم أو رقم الهاتف أو الكود...">
                    </div>
                    <button type="submit" class="btn-search">بحث</button>
                </form>
                <div class="filter-section">
                    <label for="category-select">الفئات:</label>
                    <select id="category-select" class="form-select" onchange="window.location.href=this.value">
                        <option value="{{ route('admin.orphans.index') }}">الكل</option>
                        @foreach($categories as $category)
                            <option value="{{ route('admin.orphans.index', ['category_id' => $category->id]) }}"
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="status-filter-section">
    <label>حالة الطلب:</label>

    <div class="status-filter-buttons">

        {{-- الكل --}}
        <a href="{{ route('admin.orphans.index', request()->except('status', 'page')) }}"
           class="filter-btn {{ !request('status') ? 'active' : '' }}">
            <i class="fas fa-list"></i>
            الكل
        </a>

        {{-- بانتظار المراجعة --}}
        <a href="{{ route('admin.orphans.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}"
           class="filter-btn filter-pending {{ request('status') == 'pending' ? 'active' : '' }}">
            <i class="fas fa-clock"></i>
            بانتظار المراجعة
        </a>

        {{-- المعتمدين --}}
        <a href="{{ route('admin.orphans.index', array_merge(request()->except('page'), ['status' => 'approved'])) }}"
           class="filter-btn filter-approved {{ request('status') == 'approved' ? 'active' : '' }}">
            <i class="fas fa-check-circle"></i>
            المعتمدين
        </a>

        {{-- المرفوضين --}}
        <a href="{{ route('admin.orphans.index', array_merge(request()->except('page'), ['status' => 'rejected'])) }}"
           class="filter-btn filter-rejected {{ request('status') == 'rejected' ? 'active' : '' }}">
            <i class="fas fa-times-circle"></i>
            المرفوضين
        </a>

    </div>
</div>
                <div class="table-responsive">

                @if($cases->count() > 0)
                <table id="orphansTable" class="table table-hover" style="text-align: center; vertical-align: middle;">
                    <thead>
                        <tr>
                            <th>الصورة</th> <th>بيانات الحالة</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cases as $index => $case)
                        <tr data-status="{{ $case->status }}" 
                            data-href="{{ route('admin.orphans.show', $case->id) }}" 
                            style="cursor: pointer;">
                            
                            <td>
                                <div class="user-avatar-large">
                                    <img 
                                        src="{{ $case->telegramUser && $case->telegramUser->guardianDetail && $case->telegramUser->guardianDetail->personal_photo 
                                            ? asset($case->telegramUser->guardianDetail->personal_photo) 
                                            : asset('default.png') }}"
                                        alt="صورة المستخدم">
                                </div>
                            </td>

                            <td>
                                <div class="user-details">
                                    <span class="user-name" style="font-size: 1.1rem; display: block;">{{ optional($case->telegramUser)->full_name ?? 'غير محدد' }}</span>
                                    <small class="text-muted">الكود: {{ optional($case->telegramUser)->access_code ?? 'غير محدد' }}</small>
                                    <small class="text-muted">العمر: {{ $case->telegramUser->guardianDetail->age ?? 'غير محدد' }}</small>
                                    <small class="text-muted">
                                        عدد الأطفال: {{ $case->telegramUser->familyMembers->filter(function ($member) {
                                            return $member->orphanChild !== null;
                                        })->count() }}
                                    </small>
                                    <small class="text-muted">العنوان: {{ optional($case->telegramUser)->address ?? 'غير محدد' }}</small>
                                </div>
                            </td>
<td>
    @if($case->status == 'pending')
        <span class="status-badge-custom status-pending">
            <i class="fas fa-clock"></i> 
            <span class="status-text">تحت المراجعة</span>
        </span>
    @elseif($case->status == 'approved')
        <span class="status-badge-custom status-approved">
            <i class="fas fa-check-circle"></i> 
            <span class="status-text">معتمد</span>
        </span>
    @else
        <span class="status-badge-custom status-rejected">
            <i class="fas fa-times-circle"></i> 
            <span class="status-text">مرفوض</span>
        </span>
    @endif
</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $cases->links('pagination::bootstrap-5') }}
                @else
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>لا توجد طلبات</h3>
                    <p>لم يتم تقديم أي طلبات أيتام حتى الآن</p>
                </div>
                @endif
            </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <!-- jQuery and DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.com/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.com/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
            $(document).ready(function() {
                // تهيئة DataTables
                var table = $('#orphansTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
                },
                order: [[2, 'desc']],
                pageLength: 10,
            });

            // فلتر الحالة
            // فلتر الحالة
$('.filter-btn').click(function() {

    var filter = $(this).data('filter');

    // إزالة active من جميع الأزرار
    $('.filter-btn').removeClass('active');

    // إضافة active للزر الحالي
    $(this).addClass('active');

    // فلترة عمود الحالة
    if (filter === 'all') {

        table.column(2).search('').draw();

    } else if (filter === 'pending') {

        table.column(2).search('تحت المراجعة').draw();

    } else if (filter === 'approved') {

        table.column(2).search('معتمد').draw();

    } else if (filter === 'rejected') {

        table.column(2).search('مرفوض').draw();

    }
});
        });

        $(document).ready(function() {
            // عند الضغط على أي خلية في الصف
            $('#orphansTable tbody').on('click', 'tr td', function() {
                // نتحقق أن الضغطة ليست على عمود الإجراءات (الذي يحتوي على الأزرار)
                if ($(this).index() !== 4) { 
                    var url = $(this).parent().data('href');
                    if(url) {
                        window.location = url;
                    }
                }
            });
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>