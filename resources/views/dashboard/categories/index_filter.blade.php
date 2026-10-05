<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الاستعلام والفلترة المتقدمة</title>
    <link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
    <circle cx='50' cy='50' r='48' fill='%234361ee'/>
    <circle cx='45' cy='45' r='15' fill='none' stroke='white' stroke-width='6'/>
    <line x1='56' y1='56' x2='68' y2='68' stroke='white' stroke-width='8' stroke-linecap='round'/>
    <line x1='30' y1='75' x2='70' y2='75' stroke='white' stroke-width='4' stroke-linecap='round' opacity='0.8'/>
    <line x1='40' y1='85' x2='60' y2='85' stroke='white' stroke-width='4' stroke-linecap='round' opacity='0.6'/>
    </svg>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
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

        /* كارد الفلاتر */
        .filter-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e1e5eb;
            border-right: 4px solid var(--primary-color);
        }

        .filter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eaeaea;
        }

        .filter-title {
            color: var(--secondary-color);
            font-weight: 600;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-title i {
            color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* تصميم الفلاتر */
        .filter-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 20px;
            align-items: end;
        }

        .filter-group {
            margin-bottom: 0;
        }

        .filter-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: block;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #d1d9e6;
            padding: 12px 15px;
            transition: var(--transition);
            background-color: #fff;
            font-size: 1rem;
            height: 48px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
            background-color: #fff;
        }

        /* أزرار التحكم */
        .btn-search {
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

        .btn-search:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
            color: white;
        }

        .btn-clear {
            background-color: #6c757d;
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

        .btn-clear:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.25);
            color: white;
        }

        /* إحصائيات النتائج */
        .stats-card {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            transition: var(--transition);
        }

        .stat-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* جدول النتائج */
        .results-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 25px;
            transition: var(--transition);
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
            justify-content: space-between;
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

        /* تصميم بطاقات المستخدمين */
        .user-card {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4361ee, #4cc9f0);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .user-info {
            flex-grow: 1;
            min-width: 0;
        }

        .user-name {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 3px;
            font-size: 1rem;
        }

        .user-details {
            display: flex;
            gap: 15px;
            font-size: 0.85rem;
            color: #7f8c8d;
        }

        .user-category {
            background-color: #eef2ff;
            color: var(--primary-color);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* الرموز */
        .badge {
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 6px;
        }

        /* حالة عدم وجود نتائج */
        .no-results {
            text-align: center;
            padding: 60px 20px;
        }

        .no-results-icon {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        /* التصميم المتجاوب */
        @media (max-width: 992px) {
            .filter-form {
                grid-template-columns: 1fr 1fr;
                gap: 15px;
            }
            
            .btn-search, .btn-clear {
                grid-column: span 1;
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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

            .filter-card {
                padding: 20px;
                margin-bottom: 25px;
            }

            .filter-form {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .btn-search, .btn-clear {
                grid-column: 1;
                width: 100%;
            }

            .filter-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .card-header {
                padding: 15px 20px;
                font-size: 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .table thead th, .table tbody td {
                padding: 12px 10px;
                font-size: 0.9rem;
            }
            
            .user-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .user-details {
                flex-direction: column;
                gap: 5px;
            }
        }

        @media (max-width: 576px) {
            .filter-card {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .table {
                font-size: 0.9rem;
            }

            .badge {
                padding: 5px 10px;
                font-size: 0.8rem;
            }
            
            .form-control, .form-select {
                padding: 10px 12px;
                font-size: 0.95rem;
            }
            
            .btn-search, .btn-clear {
                padding: 12px 20px;
                font-size: 0.95rem;
            }
        }

        /* تخصيصات للوضع الداكن */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1a1d28;
                color: #e4e6eb;
            }

            .filter-card, .results-card {
                background-color: #252a38;
                border-color: #374151;
                border-right-color: var(--primary-color);
            }

            .card-header {
                background-color: #2d3748;
                border-color: #374151;
                color: #e4e6eb;
            }

            .form-control, .form-select {
                background-color: #2d3748;
                border-color: #4a5568;
                color: #e4e6eb;
            }

            .form-control:focus, .form-select:focus {
                background-color: #2d3748;
                color: #e4e6eb;
                border-color: var(--primary-color);
            }

            .filter-label {
                color: #d1d5db;
            }

            .filter-title {
                color: #e4e6eb;
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
            
            .user-category {
                background-color: rgba(67, 97, 238, 0.2);
                color: var(--accent-color);
            }
            
            .user-name {
                color: #bb86fc;
            }
            
            .user-details {
                color: #b0b7c3;
            }
        }

        /* تأثيرات تفاعلية */
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
        <h2 class="page-title">
            <i class="fas fa-filter me-2"></i>الاستعلام والفلترة المتقدمة
        </h2>

        <!-- كارد الفلاتر -->
        <div class="filter-card">
            <div class="filter-header">
                <div class="filter-title">
                    <i class="fas fa-sliders-h"></i>
                    <span>تصفية النتائج</span>
                </div>
                <div class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    <small>اختر التصنيف و/أو الفئة لعرض الأشخاص المرتبطين بها</small>
                </div>
            </div>
            
            <form id="filterForm" method="GET" action="{{ route('admin.categories.indexfilter') }}">
                <div class="filter-form">
                    <div class="filter-group">
                        <label for="categoryFilter" class="filter-label">
                            <i class="fas fa-sitemap me-1"></i>التصنيف
                        </label>
                        <select name="category" id="categoryFilter" class="form-select" onchange="updateCategoryFilters()">
                            <option value="">كل التصنيفات</option>
                            @foreach($caseProfiles as $profile)
                                <option value="{{ $profile->id }}" {{ request('category') == $profile->id ? 'selected' : '' }}>
                                    {{ $profile->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="subcategoryFilter" class="filter-label">
                            <i class="fas fa-folder me-1"></i>الفئة
                        </label>
                        <select name="subcategory" id="subcategoryFilter" class="form-select">
                            <option value="">كل الفئات</option>
                            @if(request('category'))
                                @foreach($categories->where('case_profile_id', request('category')) as $cat)
                                    <option value="{{ $cat->id }}" {{ request('subcategory') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            @else
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('subcategory') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="filter-group d-flex gap-2">
                        <button type="submit" class="btn btn-search flex-grow-1">
                            <i class="fas fa-search me-2"></i>بحث
                        </button>
                        <button type="button" class="btn btn-clear" onclick="clearFilters()">
                            <i class="fas fa-times me-2"></i>مسح
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- إحصائيات النتائج -->
        @if(request()->has('category') || request()->has('subcategory'))
        <div class="stats-card">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">{{ $totalUsers }}</div>
                    <div class="stat-label">إجمالي الأشخاص</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $selectedCategory ? 1 : 0 }}</div>
                    <div class="stat-label">التصنيف المحدد</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $selectedSubcategory ? 1 : 0 }}</div>
                    <div class="stat-label">الفئة المحددة</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $uniqueCategories->count() }}</div>
                    <div class="stat-label">عدد الفئات</div>
                </div>
            </div>
        </div>
        @endif

        <!-- النتائج -->
        <div class="results-card">
            <div class="card-header">
                <div>
                    <i class="fas fa-users me-2"></i>
                    <span>نتائج البحث</span>
                    @if(request()->has('category') || request()->has('subcategory'))
                    <span class="badge bg-primary ms-3">{{ $users->count() }} نتيجة</span>
                    @endif
                </div>
                
                @if(request()->has('category') || request()->has('subcategory'))
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    @if(request('subcategory'))
                        عرض الأشخاص في الفئة: <strong>{{ $selectedSubcategory->name ?? 'غير معروف' }}</strong>
                    @elseif(request('category'))
                        عرض الأشخاص في التصنيف: <strong>{{ $selectedCategory->name ?? 'غير معروف' }}</strong>
                    @endif
                </div>
                @endif
            </div>
            
            <div class="table-container">
                @if($users->count() > 0)
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="30%">
                                <i class="fas fa-user me-1"></i>الشخص
                            </th>
                            <th width="20%">
                                <i class="fas fa-sitemap me-1"></i>التصنيف
                            </th>
                            <th width="20%">
                                <i class="fas fa-folder me-1"></i>الفئة
                            </th>
                            <th width="15%">
                                <i class="fas fa-hashtag me-1"></i>الرقم التسلسلي
                            </th>
                            <th width="15%">
                                <i class="fas fa-calendar me-1"></i>تاريخ التسجيل
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="user-card">
                                    <div class="user-avatar">
                                        {{ substr($user->full_name, 0, 1) }}
                                    </div>
                                    <div class="user-info">
                                        <div class="user-name">{{ $user->full_name }}</div>
                                        <div class="user-details">
                                            <span><i class="fas fa-phone me-1"></i>{{ $user->telegram_number ?? 'غير متوفر' }}</span>
                                            <span><i class="fas fa-id-card me-1"></i>{{ $user->national_id ?? 'غير متوفر' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->category && $user->category->caseProfile)
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 30px; height: 30px; background-color: #e8f5e9; color: #2e7d32; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                        <i class="fas fa-sitemap"></i>
                                    </div>
                                    <span>{{ $user->category->caseProfile->name }}</span>
                                </div>
                                @else
                                <span class="text-muted">غير محدد</span>
                                @endif
                            </td>
                            <td>
                                <span class="user-category">
                                    <i class="fas fa-folder me-1"></i>{{ $user->category->name ?? 'غير محدد' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border border-primary">
                                    {{ $user->access_code ?? 'غير متوفر' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">{{ $user->created_at ? $user->created_at->format('Y-m-d') : 'غير محدد' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h5 class="text-muted mb-3">لا توجد نتائج للعرض</h5>
                    <p class="text-muted mb-4">
                        @if(request()->has('category') || request()->has('subcategory'))
                        لم يتم العثور على أشخاص يتطابقون مع معايير البحث المحددة
                        @else
                        استخدم الفلاتر أعلاه للبحث عن الأشخاص
                        @endif
                    </p>
                    @if(!request()->has('category') && !request()->has('subcategory'))
                    <button class="btn btn-primary" onclick="document.getElementById('categoryFilter').focus()">
                        <i class="fas fa-sliders-h me-2"></i>ابدأ التصفية
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- الترحيل -->
        @if($users->count() > 0 && $users->hasPages())
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="ترحيل النتائج">
                <ul class="pagination">
                    @if($users->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">السابق</span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $users->previousPageUrl() }}">السابق</a>
                    </li>
                    @endif

                    @foreach(range(1, $users->lastPage()) as $page)
                    <li class="page-item {{ $users->currentPage() == $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ $users->url($page) }}">{{ $page }}</a>
                    </li>
                    @endforeach

                    @if($users->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $users->nextPageUrl() }}">التالي</a>
                    </li>
                    @else
                    <li class="page-item disabled">
                        <span class="page-link">التالي</span>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // مصفوفة الفئات مرتبطة بالتصنيفات
        const categoriesData = {
            @foreach($caseProfiles as $profile)
            "{{ $profile->id }}": [
                @foreach($categories->where('case_profile_id', $profile->id) as $cat)
                {
                    id: "{{ $cat->id }}",
                    name: "{{ $cat->name }}"
                },
                @endforeach
            ],
            @endforeach
        };

        // تحديث قائمة الفئات بناءً على التصنيف المحدد
        function updateCategoryFilters() {
            const categorySelect = document.getElementById('categoryFilter');
            const subcategorySelect = document.getElementById('subcategoryFilter');
            const selectedCategoryId = categorySelect.value;
            
            // مسح الخيارات الحالية
            subcategorySelect.innerHTML = '<option value="">كل الفئات</option>';
            
            if (selectedCategoryId && categoriesData[selectedCategoryId]) {
                // إضافة الفئات الخاصة بالتصنيف المحدد
                categoriesData[selectedCategoryId].forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    
                    // الاحتفاظ بالقيمة المحددة سابقاً إن وجدت
                    const currentSubcategory = "{{ request('subcategory') }}";
                    if (currentSubcategory == category.id) {
                        option.selected = true;
                    }
                    
                    subcategorySelect.appendChild(option);
                });
            } else {
                // إضافة كل الفئات إذا لم يتم اختيار تصنيف
                @foreach($categories as $cat)
                const option = document.createElement('option');
                option.value = "{{ $cat->id }}";
                option.textContent = "{{ $cat->name }}";
                
                const currentSubcategory = "{{ request('subcategory') }}";
                if (currentSubcategory == "{{ $cat->id }}") {
                    option.selected = true;
                }
                
                subcategorySelect.appendChild(option);
                @endforeach
            }
        }

        // مسح الفلاتر
        function clearFilters() {
            document.getElementById('categoryFilter').value = '';
            document.getElementById('subcategoryFilter').value = '';
            document.getElementById('filterForm').submit();
        }

        // تهيئة الفلاتر عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            updateCategoryFilters();
            
            // تأثير عند تحميل النتائج
            const resultsCard = document.querySelector('.results-card');
            if (resultsCard) {
                resultsCard.style.opacity = '0';
                resultsCard.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    resultsCard.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    resultsCard.style.opacity = '1';
                    resultsCard.style.transform = 'translateY(0)';
                }, 300);
            }
            
            // إضافة تأثيرات للصفوف
            const tableRows = document.querySelectorAll('.table tbody tr');
            tableRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                
                setTimeout(() => {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, 100 + (index * 50));
            });
        });
    </script>
</body>
</html>