<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الفئات</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome للرموز -->
    <link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
        <rect width='100' height='100' rx='20' fill='%232ec4b6'/>
        <rect x='20' y='20' width='25' height='25' rx='4' fill='white'/>
        <rect x='55' y='20' width='25' height='25' rx='4' fill='white'/>
        <rect x='20' y='55' width='25' height='25' rx='4' fill='white'/>
        <rect x='55' y='55' width='25' height='25' rx='4' fill='white'/>
    </svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
        }

        .container {
            padding-top: 30px;
            padding-bottom: 50px;
        }

        .page-title {
            color: var(--secondary-color);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eaeaea;
            font-weight: 700;
            position: relative;
        }

        .page-title:after {
            content: '';
            position: absolute;
            bottom: -2px;
            right: 0;
            width: 100px;
            height: 2px;
            background-color: var(--primary-color);
        }

        /* كارد إنشاء الفئة - تصميم بسيط ونظيف */
        .create-category-container {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e1e5eb;
            border-right: 4px solid var(--primary-color);
        }

        .section-title {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* تحسين تصميم النموذج */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 20px;
            align-items: end;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: block;
            font-size: 0.95rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d9e6;
            padding: 12px 15px;
            transition: var(--transition);
            background-color: #fff;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
            background-color: #fff;
        }

        .input-group {
            border-radius: 8px;
            overflow: hidden;
        }

        .input-group-text {
            background-color: #f8fafc;
            border-color: #d1d9e6;
            color: var(--primary-color);
            font-weight: 500;
        }

        /* زر الإنشاء - تصميم بسيط وفعال */
        .btn-create {
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 13px 25px;
            font-weight: 600;
            transition: var(--transition);
            color: white;
            font-size: 1rem;
            height: 48px;
            min-width: 120px;
            white-space: nowrap;
        }

        .btn-create:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
            color: white;
        }

        .btn-create:active {
            transform: translateY(0);
        }

        /* كارد الفئات الحالية */
        .main-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 25px;
            transition: var(--transition);
            background-color: #fff;
        }

        .main-card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background-color: #f8fafc;
            color: var(--secondary-color);
            padding: 18px 25px;
            font-weight: 600;
            font-size: 1.1rem;
            border-bottom: 1px solid #eaeaea;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 0;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table thead {
            background-color: #f8fafc;
        }

        .table thead th {
            border-bottom: 2px solid #eaeaea;
            padding: 16px 12px;
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 0.95rem;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: #f8faff;
        }

        .table tbody td {
            padding: 16px 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
            color: #495057;
        }

        /* تصميم عمود التصنيف الجديد */
        .category-parent {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .parent-icon {
            background-color: #e8f5e9;
            color: #2e7d32;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .parent-info {
            flex-grow: 1;
            min-width: 0; /* لتجنب تجاوز النص للخارج */
        }

        .parent-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .parent-type {
            font-size: 0.85rem;
            color: #7f8c8d;
        }

        /* تصميم عمود اسم الفئة */
        .category-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .category-icon {
            background-color: #eef2ff;
            color: var(--primary-color);
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .category-details {
            flex-grow: 1;
            min-width: 0;
        }

        .category-name {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .category-date {
            font-size: 0.85rem;
            color: #7f8c8d;
        }

        .badge {
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 6px;
        }

        .stat-number {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-color);
        }
        
        .btn-filter-hero {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white !important;
            padding: 8px 20px 8px 8px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            transition: all 0.3s ease;
        }

        .btn-filter-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-filter-hero .icon-circle {
            background: rgba(255, 255, 255, 0.2);
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-left: 12px; /* للمحاذاة العربية */
        }

        .btn-filter-hero .text-label {
            font-size: 0.95rem;
        }

        /* التصميم المتجاوب */
        @media (max-width: 1200px) {
            .table thead th:nth-child(5),
            .table tbody td:nth-child(5) {
                display: none;
            }
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
                gap: 15px;
            }
            
            .btn-create {
                grid-column: span 2;
                justify-self: start;
                width: 100%;
            }
            
            .table thead th:nth-child(4),
            .table tbody td:nth-child(4) {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding-top: 20px;
                padding-bottom: 30px;
            }

            .page-title {
                font-size: 1.4rem;
                text-align: center;
                margin-bottom: 25px;
            }

            .page-title:after {
                right: 50%;
                transform: translateX(50%);
            }

            .create-category-container {
                padding: 20px;
                margin-bottom: 25px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .btn-create {
                grid-column: 1;
                width: 100%;
                margin-top: 5px;
            }

            .section-title {
                font-size: 1.1rem;
                margin-bottom: 15px;
            }

            .card-header {
                padding: 15px 20px;
                font-size: 1rem;
            }

            .table thead th, .table tbody td {
                padding: 12px 10px;
                font-size: 0.9rem;
            }
            
            .category-parent,
            .category-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .parent-icon,
            .category-icon {
                width: 36px;
                height: 36px;
            }
        }

        @media (max-width: 576px) {
            .create-category-container {
                padding: 15px;
            }
            
            .table {
                font-size: 0.9rem;
            }

            .badge {
                padding: 5px 10px;
                font-size: 0.8rem;
            }

            .stat-number {
                font-size: 1rem;
            }
            
            .form-control {
                padding: 10px 12px;
                font-size: 0.95rem;
            }
            
            .btn-create {
                padding: 12px 20px;
                font-size: 0.95rem;
            }
            
            .table thead th:nth-child(3),
            .table tbody td:nth-child(3) {
                display: none;
            }
        }

        /* تخصيصات للوضع الداكن */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1a1d28;
                color: #e4e6eb;
            }

            .create-category-container {
                background-color: #252a38;
                border-color: #374151;
                border-right-color: var(--primary-color);
            }

            .main-card {
                background-color: #252a38;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            }

            .card-header {
                background-color: #2d3748;
                border-color: #374151;
                color: #e4e6eb;
            }

            .form-control {
                background-color: #2d3748;
                border-color: #4a5568;
                color: #e4e6eb;
            }

            .form-control:focus {
                background-color: #2d3748;
                color: #e4e6eb;
                border-color: var(--primary-color);
            }

            .input-group-text {
                background-color: #374151;
                border-color: #4a5568;
                color: #e4e6eb;
            }

            .form-label {
                color: #d1d5db;
            }

            .section-title {
                color: #e4e6eb;
            }

            .section-title i {
                background-color: rgba(67, 97, 238, 0.2);
            }

            .table-container {
                background-color: #252a38;
            }

            .table thead {
                background-color: #2d3748;
            }

            .table thead th {
                color: #e4e6eb;
                border-color: #374151;
            }

            .table tbody tr:hover {
                background-color: #2d3748;
            }

            .table tbody td {
                border-color: #374151;
                color: #d1d5db;
            }
            
            .category-icon {
                background-color: rgba(67, 97, 238, 0.2);
                color: var(--accent-color);
            }
            
            .parent-icon {
                background-color: rgba(46, 125, 50, 0.2);
                color: #66bb6a;
            }
            
            .parent-name {
                color: #e4e6eb;
            }
            
            .parent-type,
            .category-date {
                color: #b0b7c3;
            }
            
            .category-name {
                color: #bb86fc;
            }
        }

        /* تأثيرات بسيطة */
        .highlight-box {
            transition: var(--transition);
        }

        .highlight-box:hover {
            border-color: var(--accent-color);
        }
    
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title mb-0">
                <i class="fas fa-layer-group me-2"></i>إدارة الفئات
            </h2>
            
            <a href="{{ route('admin.categories.indexfilter') }}" class="btn-filter-hero">
                <span class="icon-circle">
                    <i class="fas fa-filter"></i>
                </span>
                <span class="text-label">الاستعلام والفلترة المتقدمة</span>
            </a>
        </div>

        <!-- كارد إنشاء فئة جديد - تصميم بسيط ونظيف -->
        <div class="create-category-container highlight-box">
            <div class="section-title">
                <i class="fas fa-plus-circle"></i>
                <span>إضافة فئة جديدة</span>
            </div>
            
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label for="caseProfileId" class="form-label">التصنيف الأساسي</label>
                        <select name="case_profile_id" id="caseProfileId" class="form-control" required>
                            <option value="" selected disabled>اختر التصنيف...</option>
                            @foreach($caseProfiles as $profile)
                                <option value="{{ $profile->id }}">{{ $profile->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="categoryName" class="form-label">اسم الفئة</label>
                        <input type="text" name="name" id="categoryName" class="form-control" placeholder="أدخل اسم الفئة" required>
                    </div>

                    <div class="form-group">
                        <label for="categoryPrefix" class="form-label">الرمز (Prefix)</label>
                        <input type="text" name="prefix" id="categoryPrefix" class="form-control" placeholder="مثال: A" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-create">
                            <i class="fas fa-plus-circle me-2"></i>إنشاء
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- الفئات الحالية -->
        <div class="card main-card">
            <div class="card-header">
                <i class="fas fa-list-alt"></i>
                <span>الفئات الحالية</span>
                <span class="badge bg-primary me-auto">{{ count($categories) }} فئة</span>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="20%">
                                    <i class="fas fa-sitemap me-1"></i>التصنيف
                                </th>
                                <th width="20%">
                                    <i class="fas fa-folder me-1"></i>اسم الفئة
                                </th>
                                <th width="15%">
                                    <i class="fas fa-hashtag me-1"></i>الرمز
                                </th>
                                <th width="15%">
                                    <i class="fas fa-sort-numeric-up me-1"></i>آخر رقم
                                </th>
                                <th width="15%">
                                    <i class="fas fa-users me-1"></i>المسجلين
                                </th>
                                <th width="15%">
                                    <i class="fas fa-eye me-1"></i>عرض
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $cat)
                            <tr>
                                <!-- العمود الأول: التصنيف التابع له -->
                                <td>
                                    <div class="category-parent">
                                        <div class="parent-icon">
                                            <i class="fas fa-sitemap"></i>
                                        </div>
                                        <div class="parent-info">
                                            <div class="parent-name">
                                                {{ $cat->caseProfile->name ?? 'غير محدد' }}
                                            </div>
                                            <div class="parent-type">تصنيف أساسي</div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- العمود الثاني: اسم الفئة -->
                                <td>
                                    <div class="category-info">
                                        <div class="category-icon">
                                            <i class="fas fa-folder"></i>
                                        </div>
                                        <div class="category-details">
                                            <div class="category-name">{{ $cat->name }}</div>
                                            <div class="category-date">تم الإنشاء: {{ $cat->created_at ? $cat->created_at->format('Y-m-d') : date('Y-m-d') }}</div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="badge bg-light text-primary border border-primary">{{ $cat->prefix }}</span>
                                </td>
                                <td>
                                    <span class="stat-number">{{ $cat->last_number }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="stat-number me-2">{{ $cat->total_assigned }}</span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ min($cat->total_assigned * 5, 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.categories.users', $cat->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-users me-1"></i>
                                        عرض
                                    </a>
                                </td>
                            </tr>
                            @endforeach

                            @if(count($categories) == 0)
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                    <h5 class="text-muted">لا توجد فئات مضافة</h5>
                                    <p class="text-muted">استخدم النموذج أعلاه لإضافة فئة جديدة</p>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center text-muted small">
            <p><i class="fas fa-info-circle me-1"></i> يتم إنشاء أرقام تسلسلية تلقائياً بدمج الرمز مع آخر رقم مستخدم</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // جعل الحروف كبيرة في حقل الرمز
            const prefixInput = document.getElementById('categoryPrefix');
            if (prefixInput) {
                prefixInput.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            }

            // تأثير بسيط عند التركيز على الحقول
            const formInputs = document.querySelectorAll('.form-control');
            formInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('highlight-box');
                });

                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('highlight-box');
                });
            });

            // تأثير عند تحميل الصفحة
            const createContainer = document.querySelector('.create-category-container');
            if (createContainer) {
                createContainer.style.opacity = '0';
                createContainer.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    createContainer.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    createContainer.style.opacity = '1';
                    createContainer.style.transform = 'translateY(0)';
                }, 100);
            }
            
            // إضافة تأثير تفاعلي للصفوف
            const tableRows = document.querySelectorAll('.table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(-5px)';
                    this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
</body>
</html>