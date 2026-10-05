<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>مستخدمو الفئة</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' fill='%23ef233c'/%3E%3Crect x='20' y='20' width='25' height='25' rx='4' fill='white'/%3E%3Crect x='55' y='20' width='25' height='25' rx='4' fill='white'/%3E%3Crect x='20' y='55' width='25' height='25' rx='4' fill='white'/%3E%3Crect x='55' y='55' width='25' height='25' rx='4' fill='white'/%3E%3C/svg%3E">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --telegram-color: #0088cc;
            --bg-body: #f8f9fa;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Tahoma', system-ui, sans-serif;
            background-color: var(--bg-body);
            padding: 1rem 0;
        }

        .container {
            max-width: 1440px;
            padding: 0 1rem;
        }

        /* رأس الصفحة */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 16px;
            padding: 1.25rem 1.75rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 8px 24px rgba(67, 97, 238, 0.2);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .page-header h3 {
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            font-size: 1.4rem;
        }

        .page-header .badge-count {
            font-size: 0.9rem;
            padding: 0.35rem 0.9rem;
            background: rgba(255,255,255,0.2);
            color: white;
            border-radius: 40px;
        }

        .page-header .header-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* فلتر البحث والترتيب */
        .filter-bar {
            background: white;
            border-radius: 16px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem 1.25rem;
        }

        .filter-bar .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-bar .filter-group label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #2d3748;
            margin: 0;
            white-space: nowrap;
        }

        .filter-bar select,
        .filter-bar input {
            border-radius: 30px;
            padding: 0.35rem 1rem;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            font-size: 0.9rem;
            transition: all 0.2s;
            min-width: 130px;
        }

        .filter-bar select:focus,
        .filter-bar input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .filter-bar .btn-filter {
            border-radius: 30px;
            padding: 0.35rem 1.25rem;
            background: var(--primary-color);
            color: white;
            border: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .filter-bar .btn-filter:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }

        .filter-bar .btn-reset {
            border-radius: 30px;
            padding: 0.35rem 1.25rem;
            background: #e9ecef;
            color: #2d3748;
            border: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .filter-bar .btn-reset:hover {
            background: #dee2e6;
        }

        /* الجدول */
        .data-table {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            background: white;
        }

.data-table .table {
    margin: 0;
    font-size: 0.85rem;
    table-layout: fixed;
    width: 100%;
}

.data-table tbody td {
    padding: 0.65rem 0.5rem;
    vertical-align: middle;
    border-top: 1px solid #f1f5f9;

    overflow: hidden;
    text-overflow: ellipsis;
    word-wrap: break-word;
}

        .data-table thead th {
    background: #f1f5f9;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 600;
    padding: 0.75rem 0.55rem;
    color: #1e293b;

    /* السماح للعنوان بالنزول لأكثر من سطر */
    white-space: normal;
    word-break: normal;
    overflow-wrap: break-word;

    text-align: center;
    vertical-align: middle;
    line-height: 1.35;

    cursor: pointer;
    user-select: none;
    transition: background 0.15s;
    position: relative;
}

.data-table thead th:nth-child(1) {
    width: 8%;
}

.data-table thead th:nth-child(2) {
    width: 14%;
}

.data-table thead th:nth-child(3) {
    width: 12%;
}

.data-table thead th:nth-child(4) {
    width: 11%;
}

.data-table thead th:nth-child(5) {
    width: 12%;
}

.data-table thead th:nth-child(6) {
    width: 8%;
}

.data-table thead th:nth-child(7) {
    width: 12%;
}

.data-table thead th:nth-child(8) {
    width: 7%;
}

.data-table thead th:nth-child(9) {
    width: 9%;
}

.data-table thead th:nth-child(10) {
    width: 12%;
}

        .data-table thead th:hover {
            background: #e8edf5;
        }

        .data-table thead th .sort-icon {
            margin-right: 0.4rem;
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .data-table thead th.active-sort .sort-icon {
            color: var(--primary-color);
        }

        .data-table tbody td {
            padding: 0.8rem 0.75rem;
            vertical-align: middle;
            border-top: 1px solid #f1f5f9;
        }

        .data-table tbody tr:hover {
            background-color: #fafbff;
        }

        /* شارات الحالة */
        .status-badge {
            padding: 0.25rem 0.85rem;
            border-radius: 40px;
            font-size: 0.78rem;
            font-weight: 500;
            display: inline-block;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-primary { background: #dbeafe; color: #1e4f9e; }

        /* أزرار الإجراءات */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
        }
        .action-buttons .btn {
            padding: 0.2rem 0.65rem;
            font-size: 0.78rem;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.15s;
            border: none;
            font-weight: 500;
            text-decoration: none;
        }
        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            text-decoration: none;
        }
        .btn-view { background: #4361ee; color: white; }
        .btn-view:hover { background: #2a4fd7; color: white; }
        .btn-edit { background: #ffc107; color: #1e293b; }
        .btn-edit:hover { background: #e0a800; color: #1e293b; }
        .btn-telegram { background: #0088cc; color: white; }
        .btn-telegram:hover { background: #006699; color: white; }

        /* حالة فارغة */
        .empty-state {
            padding: 3rem 1rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 0.75rem;
        }

        /* الترقيم */
        .pagination {
            gap: 0.2rem;
        }
        .pagination .page-link {
            border-radius: 30px;
            color: var(--primary-color);
            border: 1px solid #dee2e6;
            padding: 0.35rem 0.85rem;
        }
        .pagination .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        /* ===== التجاوب الكامل ===== */
        @media (max-width: 992px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-bar .filter-group {
                flex-wrap: wrap;
            }
            .filter-bar select,
            .filter-bar input {
                min-width: 100px;
                flex: 1;
            }
        }

        @media (max-width: 768px) {
            .container { padding: 0 0.75rem; }
            .page-header { padding: 1rem 1.25rem; }
            .page-header h3 { font-size: 1.2rem; }

            /* تحويل الجدول إلى بطاقات */
            .data-table .table thead {
                display: none;
            }
            .data-table .table,
            .data-table .table tbody,
            .data-table .table tr,
            .data-table .table td {
                display: block;
                width: 100%;
            }
            .data-table .table tr {
                background: white;
                border-radius: 14px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
                padding: 0.5rem 0.25rem;
                margin-bottom: 1rem;
                border: 1px solid #e9edf4;
            }
            .data-table .table td {
                border: none;
                padding: 0.4rem 0.75rem;
                position: relative;
                padding-right: 46%;
                text-align: right;
                border-bottom: 1px dashed #edf2f7;
                font-size: 0.85rem;
            }
            .data-table .table td:last-child {
                border-bottom: none;
            }
            .data-table .table td::before {
                content: attr(data-label);
                position: absolute;
                right: 0.75rem;
                top: 50%;
                transform: translateY(-50%);
                font-weight: 600;
                color: #2d3748;
                width: 42%;
                font-size: 0.8rem;
            }
            .action-buttons {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 0.3rem;
            }
            .action-buttons .btn {
                flex: 1 0 auto;
                justify-content: center;
                padding: 0.25rem 0.6rem;
                font-size: 0.75rem;
            }
            .empty-state td::before { display: none; }
        }

        @media (max-width: 480px) {
            .page-header h3 { font-size: 1rem; }
            .data-table .table td {
                padding-right: 40%;
                font-size: 0.8rem;
                padding-top: 0.35rem;
                padding-bottom: 0.35rem;
            }
            .data-table .table td::before {
                font-size: 0.75rem;
                width: 36%;
            }
            .action-buttons .btn {
                font-size: 0.7rem;
                padding: 0.15rem 0.5rem;
            }
            .filter-bar {
                padding: 0.75rem;
            }
            .filter-bar select,
            .filter-bar input {
                font-size: 0.8rem;
                padding: 0.25rem 0.75rem;
                min-width: 80px;
            }
        }

        /* دعم الوضع المظلم */
        @media (prefers-color-scheme: dark) {
            body { background: #121212; color: #f0f0f0; }
            .data-table { background: #1e1e1e; }
            .data-table thead th { background: #2a2a2a; color: #eee; border-bottom-color: #3a3a3a; }
            .data-table thead th:hover { background: #333; }
            .data-table tbody td { border-top-color: #2a2a2a; color: #e8e8e8; }
            .data-table tbody tr:hover { background: #262626; }
            .data-table .table td::before { color: #b0b0b0; }
            .page-header { background: linear-gradient(135deg, #1f2a6b, #19193a); }
            .badge-primary { background: #2d3748; color: #b3c6ff; }
            .badge-success { background: #1e3a2a; color: #8fdfa0; }
            .badge-warning { background: #3d3500; color: #ffd966; }
            .badge-danger { background: #3d1a1a; color: #f5a0a0; }
            .pagination .page-link { background: #2a2a2a; border-color: #444; color: #b0b0b0; }
            .pagination .page-item.active .page-link { background: #4361ee; border-color: #4361ee; color: white; }
            .filter-bar { background: #1e1e1e; }
            .filter-bar .filter-group label { color: #d0d0d0; }
            .filter-bar select,
            .filter-bar input { background: #2a2a2a; border-color: #444; color: #e0e0e0; }
            .filter-bar .btn-reset { background: #333; color: #ccc; }
            .filter-bar .btn-reset:hover { background: #444; }
            .btn-edit { background: #b8960f; color: #111; }
            .btn-view { background: #3b53d0; }
            .btn-telegram { background: #0077b3; }
            .empty-state i { color: #444; }
            .data-table .table tr { background: #1e1e1e; border-color: #2a2a2a; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- رأس الصفحة -->
        <div class="page-header">
            <h3>
                <i class="fas fa-folder-open me-2"></i>
                مستخدمو فئة: {{ $category->name }}
                <span class="badge-count">{{ $users->total() }}</span>
            </h3>
            <div class="header-actions">
                <span class="badge bg-light text-dark px-3 py-2">
                    <i class="fas fa-users me-1"></i> {{ $users->count() }} في هذه الصفحة
                </span>
            </div>
        </div>

        <!-- شريط الفلتر والترتيب -->
        <div class="filter-bar" id="filterBar">
            <div class="filter-group">
                    <form method="GET" action="{{ route('admin.categories.users', $category->id) }}" class="filter-bar">

                        <div class="filter-group">
                            <label for="sort_field">
                                <i class="fas fa-sort me-1"></i>
                                ترتيب حسب:
                            </label>

                            <select name="sort_field" id="sort_field">
                                <option value="access_code"
                                    {{ request('sort_field') === 'access_code' ? 'selected' : '' }}>
                                    الكود
                                </option>

                                <option value="full_name"
                                    {{ request('sort_field') === 'full_name' ? 'selected' : '' }}>
                                    الاسم
                                </option>

                                <option value="benefit_count"
                                    {{ request('sort_field', 'benefit_count') === 'benefit_count' ? 'selected' : '' }}>
                                    الاستفادة
                                </option>

                                <option value="cash_benefit_count"
                                    {{ request('sort_field') === 'cash_benefit_count' ? 'selected' : '' }}>
                                    الاستفادة النقدية
                                </option>

                                <option value="family_members"
                                    {{ request('sort_field') === 'family_members' ? 'selected' : '' }}>
                                    عدد الأفراد
                                </option>

                                <option value="national_id"
                                    {{ request('sort_field') === 'national_id' ? 'selected' : '' }}>
                                    رقم الهوية
                                </option>
                            </select>
                        </div>


                        <div class="filter-group">
                            <label for="sort_direction">
                                <i class="fas fa-arrow-up me-1"></i>
                                اتجاه:
                            </label>

                            <select name="sort_direction" id="sort_direction">

                                <option value="asc"
                                    {{ request('sort_direction') === 'asc' ? 'selected' : '' }}>
                                    تصاعدي
                                </option>

                                <option value="desc"
                                    {{ request('sort_direction', 'desc') === 'desc' ? 'selected' : '' }}>
                                    تنازلي
                                </option>

                            </select>
                        </div>

                        <div class="filter-group" style="flex:1; min-width:150px;">

                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="بحث باسم أو كود أو رقم هوية..."
                                class="form-control form-control-sm"
                                style="border-radius:30px;"
                            >

                        </div>


                        <div class="filter-group">

                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search me-1"></i>
                                تطبيق
                            </button>

                            <a
                                href="{{ route('admin.categories.users', $category->id) }}"
                                class="btn-reset"
                            >
                                <i class="fas fa-undo me-1"></i>
                                إعادة
                            </a>

                        </div>

                    </form>
            </div>

        <!-- الجدول -->
        <div class="card data-table">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="usersTable">
                        <thead>
                            <tr>
                                <th data-field="access_code" class="sortable">
                                    الكود <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                </th>
                                <th data-field="full_name" class="sortable">
                                    الاسم <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                </th>
                                <th data-field="national_id" class="sortable">
                                    رقم الهوية <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                </th>
                                <th data-field="egypt_number">رقم الجوال</th>
                                <th data-field="address">العنوان</th>
                                <th data-field="family_members" class="sortable">
                                    عدد الأفراد <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                </th>
                                <th data-field="lastMessage">آخر رسالة</th>
                                <th data-field="benefit_count" class="sortable active-sort">
                                    الاستفادة <span class="sort-icon"><i class="fas fa-sort-down"></i></span>
                                </th>
                                <th data-field="cash_benefit_count" class="sortable">
                                    الاستفادة<br>
                                    النقدية
                                    <span class="sort-icon">
                                        <i class="fas fa-sort"></i>
                                    </span>
                                </th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($users as $user)
                                <tr data-user-id="{{ $user->id }}">
                                    <td data-label="الكود">
                                        <span class="badge badge-primary">{{ $user->access_code }}</span>
                                    </td>
                                    <td data-label="الاسم">{{ $user->full_name }}</td>
                                    <td data-label="رقم الهوية">{{ $user->national_id }}</td>
                                    <td data-label="رقم الجوال">{{ $user->egypt_number ?? 'غير محدد' }}</td>
                                    <td data-label="العنوان">{{ $user->address ?? 'غير محدد' }}</td>
                                    <td data-label="عدد الأفراد">{{ $user->family_members ?? 'غير محدد' }}</td>
                                    <td data-label="آخر رسالة">
                                        {{ $user->last_received_event_name ?? '-' }}<br>

                                        <small class="text-muted">
                                            {{ $user->last_received_event_date ?? '-' }}
                                        </small>
                                    </td>
                                    <td data-label="الاستفادة">{{ $user->benefit_count ?? '-' }}</td>
                                    <td data-label="الاستفادة النقدية">{{ $user->cash_benefit_count ?? '-' }}</td>
                                    <td data-label="الإجراءات">
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.telegram.details', $user->id) }}"
                                               class="btn btn-view btn-sm" target="_blank">
                                                <i class="fas fa-eye"></i> عرض
                                            </a>
                                            <a href="{{ route('admin.telegram.edit', $user->id) }}"
                                               class="btn btn-edit btn-sm" target="_blank">
                                                <i class="fas fa-edit"></i> تعديل
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-users-slash"></i>
                                            <div class="mt-2">لا يوجد مستخدمون مرتبطون بهذه الفئة</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- الترقيم -->
        @if($users->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                <nav aria-label="تنقل بين الصفحات">
                    {{ $users->appends(request()->query())->links() }}
                </nav>
            </div>
        @endif

        <!-- إظهار معلومات إضافية -->
        <div class="mt-3 text-center text-muted small">
            <i class="fas fa-info-circle me-1"></i>
            عرض {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} من إجمالي {{ $users->total() }} مستخدم
            @if($users->count() > 0)
                · {{ $users->count() }} مستخدم في هذه الصفحة
            @endif
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        (function() {
            "use strict";
            let allUsers = [];

            tableRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length < 11) return;

                const user = {
                    id: parseInt(row.dataset.userId),
                    access_code: cells[0]?.textContent?.trim() || '',
                    full_name: cells[1]?.textContent?.trim() || '',
                    national_id: cells[2]?.textContent?.trim() || '',
                    egypt_number: cells[3]?.textContent?.trim() || 'غير محدد',
                    address: cells[4]?.textContent?.trim() || 'غير محدد',
                    family_members: parseInt(cells[5]?.textContent?.trim()) || 0,
                    lastReceivedEvent: {
                        event: {
                            name: cells[6]?.textContent?.trim().split('\n')[0] || '-'
                        },
                        event_date: cells[6]?.querySelector('small')?.textContent?.trim() || '-'
                    },
                    benefit_count: parseInt(cells[7]?.textContent?.trim()) || 0,
                    cash_benefit_count: parseInt(cells[8]?.textContent?.trim()) || 0,
                    is_blocked: cells[9]?.textContent?.includes('محظور') || false,
                    is_completed: cells[9]?.textContent?.includes('مكتمل') || false,
                    username: row.querySelector('.btn-telegram')?.href?.split('/').pop() || null,
                    _row: row
                };
                allUsers.push(user);
            });

            // --- عناصر DOM ---
            const tbody = document.getElementById('tableBody');
            const paginationContainer = document.querySelector('.pagination')?.parentElement;

            // عناصر الفلتر
            const sortFieldEl = document.getElementById('sortField');
            const sortDirectionEl = document.getElementById('sortDirection');
            const statusFilterEl = document.getElementById('statusFilter');
            const searchInputEl = document.getElementById('searchInput');
            const applyBtn = document.getElementById('applyFilterBtn');
            const resetBtn = document.getElementById('resetFilterBtn');

            // --- دوال مساعدة ---
            function getStatus(user) {
                if (user.is_blocked) return 'blocked';
                if (user.is_completed) return 'completed';
                return 'active';
            }

            function getStatusBadge(user) {
                if (user.is_blocked)
                    return '<span class="status-badge badge-danger">محظور</span>';
                if (user.is_completed)
                    return '<span class="status-badge badge-success">مكتمل</span>';
                return '<span class="status-badge badge-warning">قيد التسجيل</span>';
            }


            // --- أحداث الفلتر والترتيب ---
            resetBtn.addEventListener('click', function() {
                sortFieldEl.value = 'benefit_count';
                sortDirectionEl.value = 'desc';
                statusFilterEl.value = 'all';
                searchInputEl.value = '';
                
                // إعادة عرض البيانات الأصلية
                location.reload(); // إعادة تحميل الصفحة لعرض البيانات الأصلية مع الترقيم
            });

            // --- فرز عبر النقر على رأس الجدول ---
            document.querySelectorAll('.sortable').forEach(th => {
                th.addEventListener('click', function() {
                    const field = this.dataset.field;
                    if (!field) return;

                    // تحديث حقل الترتيب
                    sortFieldEl.value = field;

                    // تبديل الاتجاه
                    const currentDir = sortDirectionEl.value;
                    sortDirectionEl.value = currentDir === 'asc' ? 'desc' : 'asc';

                    // تحديث التظليل
                    document.querySelectorAll('.sortable').forEach(el => el.classList.remove('active-sort'));
                    this.classList.add('active-sort');

                    filterData();
                });
            });

            // --- عرض معلومات إضافية ---
            console.log('Total users loaded:', allUsers.length);
            console.log('Users per page (from server):', {{ $users->count() }});
            console.log('Total users (from server):', {{ $users->total() }});

        })();
    </script>

</body>
</html>