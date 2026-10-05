<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🗑️ سلة المحذوفات - إدارة المستخدمين</title>
    <link rel="icon" type="image/png" href="/files/telegram.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }

        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --primary-light: #eef2ff;
            --secondary: #7209b7;
            --success: #10b981;
            --success-light: #d1fae5;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --info: #3b82f6;
            --light: #f8f9fa;
            --dark: #1f2937;
            --gray: #6b7280;
            --light-gray: #f3f4f6;
            --border-gray: #e5e7eb;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --box-shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 35px 25px;
            background: linear-gradient(135deg, #dc2626 0%, #7f1d1d 100%);
            border-radius: var(--border-radius);
            color: white;
            box-shadow: var(--box-shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%);
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            position: relative;
            z-index: 1;
            font-weight: 700;
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .back-btn {
            position: fixed;
            top: 30px;
            right: 30px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: 16px;
            padding: 16px 28px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 1000;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            transition: var(--transition);
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
        }

        .back-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(114, 9, 183, 0.4);
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border: 1px solid var(--border-gray);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 6px;
            height: 100%;
        }

        .stat-card.total::before { background: var(--danger); }
        .stat-card.today::before { background: var(--warning); }
        .stat-card.week::before { background: var(--info); }
        .stat-card.month::before { background: var(--primary); }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-lg);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            flex-shrink: 0;
        }

        .stat-card.total .stat-icon {
            background: var(--danger-light);
            color: var(--danger);
        }
        .stat-card.today .stat-icon {
            background: var(--warning-light);
            color: var(--warning);
        }
        .stat-card.week .stat-icon {
            background: #dbeafe;
            color: var(--info);
        }
        .stat-card.month .stat-icon {
            background: var(--primary-light);
            color: var(--primary);
        }

        .stat-info h3 {
            font-size: 2rem;
            margin-bottom: 5px;
            font-weight: 700;
            line-height: 1;
        }

        .stat-info p {
            color: var(--gray);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Search Section */
        .search-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            border: 1px solid var(--border-gray);
            margin-bottom: 25px;
        }

        .search-box {
            display: flex;
            gap: 15px;
        }

        .search-box input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid var(--border-gray);
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: var(--transition);
            font-family: 'Tajawal', sans-serif;
        }

        .search-box input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            padding: 0 35px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            font-weight: 600;
            font-size: 1rem;
            justify-content: center;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .reset-btn {
            background: var(--light-gray);
            color: var(--dark);
            border: 2px solid var(--border-gray);
            border-radius: var(--border-radius-sm);
            padding: 0 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
        }

        .reset-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            border: 1px solid var(--border-gray);
            padding: 25px;
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table thead {
            background: linear-gradient(135deg, #dc2626 0%, #7f1d1d 100%);
            color: white;
        }

        .users-table th {
            padding: 16px 12px;
            font-size: 0.9rem;
            text-align: right;
            font-weight: 600;
            white-space: nowrap;
        }

        .users-table th:first-child {
            border-top-right-radius: var(--border-radius-sm);
        }

        .users-table th:last-child {
            border-top-left-radius: var(--border-radius-sm);
        }

        .users-table td {
            padding: 14px 12px;
            text-align: right;
            font-size: 0.9rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--light-gray);
            word-wrap: break-word;
        }

        .users-table tbody tr {
            transition: var(--transition);
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }

        .users-table tbody tr:hover {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        }

        .user-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 1rem;
        }

        .deleted-info {
            display: flex;
            flex-direction: column;
            gap: 3px;
            font-size: 0.85rem;
        }

        .deleted-info .date {
            color: var(--danger);
            font-weight: 600;
        }

        .deleted-info .by {
            color: var(--gray);
            font-size: 0.8rem;
        }

        .time-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            background: var(--danger-light);
            color: #b91c1c;
            margin-top: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: var(--transition);
            white-space: nowrap;
            font-family: 'Tajawal', sans-serif;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Empty State */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-radius: var(--border-radius);
            border: 3px dashed #86efac;
            margin: 20px;
        }

        .no-results i {
            font-size: 5rem;
            margin-bottom: 25px;
            color: var(--success);
            opacity: 0.7;
        }

        .no-results h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: var(--dark);
        }

        .no-results p {
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto;
            color: var(--gray);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
            gap: 8px;
            flex-wrap: wrap;
            padding: 20px;
        }

        .pagination .page-link,
        .pagination .page-item span {
            min-width: 44px;
            height: 44px;
            border: 2px solid var(--border-gray);
            background: white;
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--dark);
            text-decoration: none;
            transition: var(--transition);
        }

        .pagination .page-item.active span,
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
            border-color: var(--danger);
        }

        .pagination .page-link:hover {
            background: var(--danger-light);
            border-color: var(--danger);
            color: var(--danger);
            transform: translateY(-2px);
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 30px;
            left: 30px;
            padding: 18px 25px;
            border-radius: var(--border-radius);
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: var(--box-shadow-lg);
            z-index: 9999;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s, transform 0.3s;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.95) 0%, rgba(13, 162, 113, 0.95) 100%);
            border-right: 5px solid var(--success);
        }

        .toast-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.95) 0%, rgba(220, 38, 38, 0.95) 100%);
            border-right: 5px solid var(--danger);
        }

        .toast-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.95) 0%, rgba(217, 119, 6, 0.95) 100%);
            border-right: 5px solid var(--warning);
        }

        /* Responsive */
        @media (max-width: 768px) {
            body { padding: 10px; }
            
            .header h1 {
                font-size: 1.6rem;
                flex-direction: column;
                gap: 10px;
            }

            .back-btn {
                top: 15px;
                right: 15px;
                padding: 12px 18px;
                font-size: 0.9rem;
            }

            .back-btn span {
                display: none;
            }

            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .stat-card {
                padding: 15px;
                flex-direction: column;
                text-align: center;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.4rem;
            }

            .stat-info h3 { font-size: 1.5rem; }

            .search-box {
                flex-direction: column;
            }

            .search-btn, .reset-btn {
                padding: 15px;
                width: 100%;
            }

            /* Card Layout for Mobile */
            .users-table thead { display: none; }
            
            .users-table, .users-table tbody, .users-table tr, .users-table td {
                display: block;
                width: 100%;
            }

            .users-table tr {
                margin-bottom: 15px;
                border-radius: var(--border-radius-sm);
                box-shadow: var(--box-shadow);
                padding: 15px;
                border: 2px solid var(--danger-light);
            }

            .users-table td {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border: none;
                border-bottom: 1px solid #f0f0f0;
            }

            .users-table td:last-child {
                border-bottom: none;
            }

            .users-table td::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--danger);
                min-width: 100px;
                text-align: left;
            }

            .action-buttons {
                flex-direction: column;
                width: 100%;
            }

            .action-buttons .btn {
                width: 100%;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="{{ route('admin.telegram.index') }}" class="back-btn">
        <i class="fas fa-arrow-right"></i>
        <span>العودة للمستخدمين</span>
    </a>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>
                <i class="fas fa-trash-restore"></i>
                سلة المحذوفات
            </h1>
            <p>يمكنك استعادة المستخدمين المحذوفين أو حذفهم نهائياً</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ number_format($totalTrashed) }}</h3>
                    <p>إجمالي المحذوفين</p>
                </div>
            </div>

            <div class="stat-card today">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ number_format($trashedToday) }}</h3>
                    <p>محذوفين اليوم</p>
                </div>
            </div>

            <div class="stat-card week">
                <div class="stat-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ number_format($trashedThisWeek) }}</h3>
                    <p>هذا الأسبوع</p>
                </div>
            </div>

            <div class="stat-card month">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ number_format($trashedThisMonth) }}</h3>
                    <p>هذا الشهر</p>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="search-section">
            <form method="GET" action="{{ route('admin.telegram.trashed') }}">
                <div class="search-box">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="🔍 ابحث بالاسم، الهوية، رقم التليجرام...">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                        بحث
                    </button>
                    @if(request()->filled('search'))
                        <a href="{{ route('admin.telegram.trashed') }}" class="reset-btn">
                            <i class="fas fa-redo"></i>
                            مسح
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Users Table -->
        @if($users->isEmpty())
            <div class="no-results">
                <i class="fas fa-check-circle"></i>
                <h3>لا يوجد مستخدمون محذوفون</h3>
                <p>
                    @if(request()->filled('search'))
                        لم يتم العثور على نتائج للبحث "{{ request('search') }}"
                    @else
                        سلة المحذوفات فارغة، جميع المستخدمين نشطون ✅
                    @endif
                </p>
            </div>
        @else
            <div class="table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> #</th>
                            <th><i class="fas fa-user"></i> الاسم الكامل</th>
                            <th><i class="fas fa-id-card"></i> رقم الهوية</th>
                            <th><i class="fas fa-phone"></i> رقم مصر</th>
                            <th><i class="fas fa-telegram"></i> التليجرام</th>
                            <th><i class="fas fa-clock"></i> تاريخ الحذف</th>
                            <th><i class="fas fa-user-times"></i> حذف بواسطة</th>
                            <th><i class="fas fa-cogs"></i> الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                            <tr data-id="{{ $user->id }}">
                                <td data-label="#">
                                    {{ $users->firstItem() + $index }}
                                </td>
                                <td data-label="الاسم">
                                    <div class="user-name">
                                        {{ $user->full_name ?? $user->first_name ?? 'غير متوفر' }}
                                    </div>
                                </td>
                                <td data-label="رقم الهوية">
                                    {{ $user->national_id ?? 'غير متوفر' }}
                                </td>
                                <td data-label="رقم مصر">
                                    {{ $user->egypt_number ?? 'غير متوفر' }}
                                </td>
                                <td data-label="التليجرام">
                                    {{ $user->telegram_number ?? 'غير متوفر' }}
                                </td>
                                <td data-label="تاريخ الحذف">
                                    <div class="deleted-info">
                                        <span class="date">
                                            {{ $user->deleted_at ? $user->deleted_at->format('Y-m-d') : 'غير متوفر' }}
                                        </span>
                                        <span class="by">
                                            {{ $user->deleted_at ? $user->deleted_at->format('H:i') : '' }}
                                        </span>
                                        @if($user->deleted_at)
                                            <span class="time-badge">
                                                {{ $user->deleted_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="حذف بواسطة">
                                    @if($user->deleter)
                                        <span style="color: var(--primary); font-weight: 600;">
                                            <i class="fas fa-user-shield"></i>
                                            {{ $user->deleter->name }}
                                        </span>
                                    @else
                                        <span style="color: var(--gray);">غير معروف</span>
                                    @endif
                                </td>
                                <td data-label="الإجراءات">
                                    <div class="action-buttons">
                                        <button class="btn btn-success" 
                                                onclick="restoreUser({{ $user->id }}, '{{ addslashes($user->full_name ?? $user->first_name ?? 'المستخدم') }}')">
                                            <i class="fas fa-undo-alt"></i> استعادة
                                        </button>
                                        <button class="btn btn-danger" 
                                                onclick="forceDeleteUser({{ $user->id }}, '{{ addslashes($user->full_name ?? $user->first_name ?? 'المستخدم') }}')">
                                            <i class="fas fa-fire"></i> حذف نهائي
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Toast -->
    <div id="toast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">تم تنفيذ العملية بنجاح</span>
    </div>

    <script>
        const csrfToken = "{{ csrf_token() }}";

        // Toast function
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const icon = toast.querySelector('i');

            if (type === 'success') {
                icon.className = 'fas fa-check-circle';
                toast.className = 'toast toast-success show';
            } else if (type === 'error') {
                icon.className = 'fas fa-exclamation-circle';
                toast.className = 'toast toast-error show';
            } else if (type === 'warning') {
                icon.className = 'fas fa-exclamation-triangle';
                toast.className = 'toast toast-warning show';
            }

            toastMessage.textContent = message;

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // ♻️ استعادة مستخدم
        function restoreUser(userId, userName) {
            if (!confirm(`♻️ هل أنت متأكد من استعادة المستخدم "${userName}"؟`)) {
                return;
            }

            const btn = event.target.closest('button');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الاستعادة...';

            axios.post(`/ar/admin/telegram/restore/${userId}`, {}, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res.data.status === 'success') {
                    showToast(res.data.message, 'success');
                    
                    const row = document.querySelector(`tr[data-id="${userId}"]`);
                    if (row) {
                        row.style.transition = 'all 0.5s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-100%)';
                        
                        setTimeout(() => {
                            row.remove();
                            checkEmptyTable();
                        }, 500);
                    }
                } else {
                    showToast(res.data.message || 'حدث خطأ', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-undo-alt"></i> استعادة';
                }
            })
            .catch(err => {
                console.error('Restore Error:', err.response || err);
                let errorMsg = 'حدث خطأ في الاتصال';
                if (err.response?.data?.message) errorMsg = err.response.data.message;
                showToast(errorMsg, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-undo-alt"></i> استعادة';
            });
        }

        // 💥 حذف نهائي
        function forceDeleteUser(userId, userName) {
            if (!confirm(`💥 تحذير!\n\nهل أنت متأكد من الحذف النهائي للمستخدم "${userName}"؟\n\n⚠️ هذا الإجراء لا يمكن التراجع عنه نهائياً!`)) {
                return;
            }

            // تأكيد ثاني للحذف النهائي
            if (!confirm(`⚠️ تأكيد أخير!\n\nسيتم حذف جميع البيانات المرتبطة بـ "${userName}" نهائياً.\n\nهل أنت متأكد 100%؟`)) {
                return;
            }

            const btn = event.target.closest('button');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحذف...';

            axios.delete(`/ar/admin/telegram/force-delete/${userId}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res.data.status === 'success') {
                    showToast(res.data.message, 'success');
                    
                    const row = document.querySelector(`tr[data-id="${userId}"]`);
                    if (row) {
                        row.style.transition = 'all 0.5s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-100%)';
                        
                        setTimeout(() => {
                            row.remove();
                            checkEmptyTable();
                        }, 500);
                    }
                } else {
                    showToast(res.data.message || 'حدث خطأ', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-fire"></i> حذف نهائي';
                }
            })
            .catch(err => {
                console.error('Force Delete Error:', err.response || err);
                let errorMsg = 'حدث خطأ في الاتصال';
                if (err.response?.data?.message) errorMsg = err.response.data.message;
                showToast(errorMsg, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-fire"></i> حذف نهائي';
            });
        }

        // التحقق من الجدول الفارغ
        function checkEmptyTable() {
            const rows = document.querySelectorAll('.users-table tbody tr');
            if (rows.length === 0) {
                setTimeout(() => {
                    location.reload();
                }, 800);
            }
        }
    </script>
</body>
</html>