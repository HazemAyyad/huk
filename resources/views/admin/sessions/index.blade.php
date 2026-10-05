<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الجلسات النشطة</title>
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .main-card {
            border-radius: 20px;
            overflow: hidden;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        
        .header-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px 30px;
            color: white;
        }
        
        .header-gradient h3 {
            font-weight: 800;
        }
        
        .header-gradient p {
            opacity: 0.9;
            font-weight: 300;
        }
        
        .btn-warning-custom {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-warning-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 87, 108, 0.4);
            color: white;
        }
        
        .btn-danger-custom {
            background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);
            border: none;
            color: white;
            padding: 5px 15px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 13px;
        }
        
        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 101, 101, 0.4);
            color: white;
        }
        
        .btn-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-reset {
            background: #e2e8f0;
            border: none;
            color: #4a5568;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-reset:hover {
            background: #cbd5e0;
            color: #2d3748;
        }
        
        .stats-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .stats-icon.purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stats-icon.green {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .stats-icon.orange {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }
        
        .stats-number {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 0;
        }
        
        .stats-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }
        
        .search-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .search-card:hover {
            box-shadow: 0 6px 25px rgba(0,0,0,0.08);
        }
        
        .search-card .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .search-card .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
        }
        
        .search-card .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e2e8f0;
            border-left: none;
            border-radius: 10px 0 0 10px;
        }
        
        .search-card .form-control {
            border-right: none;
            border-radius: 0 10px 10px 0;
        }
        
        .search-card .form-control:focus {
            border-color: #667eea;
        }
        
        .search-card .form-control:focus + .input-group-text {
            border-color: #667eea;
        }
        
        .table-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .table-card .card-header {
            border-radius: 15px 15px 0 0;
            background: white;
            border-bottom: 2px solid #f0f0f0;
            padding: 15px 20px;
        }
        
        .table-card .card-header strong {
            color: #2d3748;
            font-size: 16px;
        }
        
        .table thead th {
            background: #f8f9fa;
            font-weight: 700;
            border-bottom: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 13px;
            color: #495057;
        }
        
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            font-size: 14px;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .badge-current {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 12px;
            color: #155724;
        }
        
        .badge-active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 12px;
            color: white;
        }
        
        .alert-success-custom {
            border-radius: 15px;
            border: none;
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: #155724;
            font-weight: 500;
            padding: 18px 25px;
        }
        
        .empty-state i {
            font-size: 60px;
            color: #d1d5db;
        }
        
        .empty-state p {
            color: #6b7280;
            font-weight: 500;
            margin-top: 15px;
        }
        
        .empty-state .search-query {
            color: #667eea;
            font-weight: 700;
        }
        
        .ip-code {
            background: #f1f3f5;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 13px;
            color: #2d3748;
            font-weight: 600;
        }
        
        .user-agent-text {
            max-width: 250px;
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .time-info strong {
            font-size: 14px;
            color: #2d3748;
        }
        
        .time-info small {
            font-size: 12px;
        }
        
        .search-result-badge {
            background: #edf2f7;
            color: #4a5568;
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .highlight-match {
            background: #fef3c7;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
        }
        
        /* تنسيق الترقيم */
        .pagination-custom {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .pagination-custom .page-item {
            list-style: none;
        }
        
        .pagination-custom .page-link {
            border: none;
            color: #667eea;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: transparent;
        }
        
        .pagination-custom .page-link:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .pagination-custom .active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .pagination-custom .disabled .page-link {
            color: #cbd5e0;
            cursor: not-allowed;
        }
        
        .pagination-custom .page-link i {
            font-size: 14px;
        }
        
        .pagination-info {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
            padding: 10px 0;
        }
        
        @media (max-width: 768px) {
            .header-gradient {
                padding: 20px;
            }
            .header-gradient h3 {
                font-size: 20px;
            }
            .btn-warning-custom {
                padding: 8px 18px;
                font-size: 13px;
                margin-top: 10px;
            }
            .stats-number {
                font-size: 22px;
            }
            .table-responsive {
                font-size: 13px;
            }
            .user-agent-text {
                max-width: 120px;
            }
            .search-card .row > div {
                margin-bottom: 10px;
            }
            .pagination-custom .page-link {
                padding: 6px 12px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                
                {{-- البطاقة الرئيسية --}}
                <div class="main-card">
                    
                    {{-- الهيدر --}}
                    <div class="header-gradient">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h3 class="mb-1">
                                    <i class="fas fa-users me-2"></i>
                                    الجلسات النشطة
                                </h3>
                                <p class="mb-0">
                                    <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                    جميع جلسات المستخدمين النشطة حاليًا
                                </p>
                            </div>
                            <form action="{{ route('admin.sessions.destroy-others') }}"
                                  method="POST"
                                  onsubmit="return confirm('هل أنت متأكد من إنهاء جميع جلساتك الأخرى؟');"
                                  class="mt-2 mt-md-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-warning-custom">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    إنهاء جلساتي الأخرى
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    {{-- المحتوى --}}
                    <div class="p-4" style="background: white;">
                        
                        {{-- رسائل النجاح --}}
                        @if(session('success'))
                            <div class="alert alert-success-custom alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        {{-- إحصائيات الجلسات --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="stats-card card">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="stats-icon purple me-3">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <div class="stats-label">إجمالي الجلسات النشطة</div>
                                            <h3 class="stats-number">{{ $sessions->total() }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card card">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="stats-icon green me-3">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <div class="stats-label">مدة الجلسة</div>
                                            <h3 class="stats-number">{{ $lifetime }} <small style="font-size: 16px; font-weight: 500;">دقيقة</small></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card card">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="stats-icon orange me-3">
                                            <i class="fas fa-hourglass-half"></i>
                                        </div>
                                        <div>
                                            <div class="stats-label">مدة الخمول القصوى</div>
                                            <h3 class="stats-number">{{ round($lifetime / 60, 1) }} <small style="font-size: 16px; font-weight: 500;">ساعة</small></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- شريط البحث --}}
                        <div class="search-card card p-3 mb-4">
                            <form method="GET" action="{{ route('admin.sessions.index') }}" id="searchForm">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold text-secondary mb-1">
                                            <i class="fas fa-search me-1"></i>
                                            البحث عن جلسة
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-search text-primary"></i>
                                            </span>
                                            <input
                                                type="text"
                                                name="search"
                                                id="searchInput"
                                                class="form-control"
                                                value="{{ request('search') }}"
                                                placeholder="ابحث بالاسم، البريد الإلكتروني، IP، أو الجهاز..."
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-search w-100">
                                                <i class="fas fa-search me-1"></i>
                                                بحث
                                            </button>
                                            @if(request('search'))
                                                <a href="{{ route('admin.sessions.index') }}" class="btn btn-reset">
                                                    <i class="fas fa-times me-1"></i>
                                                    إلغاء
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                            {{-- نتائج البحث --}}
                            @if(request('search'))
                                <div class="mt-3 pt-3 border-top">
                                    <span class="search-result-badge">
                                        <i class="fas fa-filter me-1"></i>
                                        نتائج البحث عن: <strong>"{{ request('search') }}"</strong>
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-database me-1"></i>
                                        {{ $sessions->total() }} جلسة
                                    </span>
                                    @if($sessions->total() == 0)
                                        <span class="search-result-badge bg-warning text-dark ms-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            لا توجد نتائج
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        {{-- جدول الجلسات --}}
                        <div class="table-card card">
                            <div class="card-header">
                                <strong>
                                    <i class="fas fa-list me-2"></i>
                                    الجلسات
                                </strong>
                                <span class="badge bg-primary ms-2">
                                    {{ $sessions->total() }}
                                </span>
                                @if($sessions->total() > 50)
                                    <span class="badge bg-info ms-2">
                                        <i class="fas fa-layer-group me-1"></i>
                                        صفحة {{ $sessions->currentPage() }} من {{ $sessions->lastPage() }}
                                    </span>
                                @endif
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>المستخدم</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>IP</th>
                                                <th>الجهاز / المتصفح</th>
                                                <th>آخر نشاط</th>
                                                <th>الحالة</th>
                                                <th>الإجراء</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($sessions as $index => $session)
                                                @php
                                                    $lastActivity = \Carbon\Carbon::createFromTimestamp(
                                                        $session->last_activity
                                                    );
                                                    $currentSession =
                                                        $session->id === request()->session()->getId();
                                                    
                                                    // Highlight search matches
                                                    $search = request('search');
                                                    $name = $session->name;
                                                    $email = $session->email;
                                                    $ip = $session->ip_address;
                                                    $userAgent = $session->user_agent;
                                                    
                                                    if ($search) {
                                                        $name = str_ireplace($search, '<span class="highlight-match">'.$search.'</span>', $name);
                                                        $email = str_ireplace($search, '<span class="highlight-match">'.$search.'</span>', $email);
                                                        $ip = str_ireplace($search, '<span class="highlight-match">'.$search.'</span>', $ip);
                                                        $userAgent = str_ireplace($search, '<span class="highlight-match">'.$search.'</span>', $userAgent);
                                                    }
                                                    
                                                    // رقم الصف مع مراعاة الترقيم
                                                    $rowNumber = $sessions->firstItem() + $index;
                                                @endphp
                                                <tr>
                                                    <td>{{ $rowNumber }}</td>
                                                    
                                                    {{-- المستخدم --}}
                                                    <td>
                                                        @if($session->name)
                                                            <strong>{!! $name !!}</strong>
                                                        @else
                                                            <span class="text-muted">مستخدم غير معروف</span>
                                                        @endif
                                                    </td>
                                                    
                                                    {{-- البريد --}}
                                                    <td>{!! $email ?? '-' !!}</td>
                                                    
                                                    {{-- IP --}}
                                                    <td>
                                                        <span class="ip-code">{!! $ip ?? '-' !!}</span>
                                                    </td>
                                                    
                                                    {{-- الجهاز --}}
                                                    <td>
                                                        <small class="text-muted user-agent-text" title="{{ $session->user_agent }}">
                                                            <i class="fas fa-desktop me-1"></i>
                                                            {!! \Illuminate\Support\Str::limit($userAgent, 70) !!}
                                                        </small>
                                                    </td>
                                                    
                                                    {{-- آخر نشاط --}}
                                                    <td>
                                                        <div class="time-info">
                                                            <strong>{{ $lastActivity->format('Y-m-d H:i:s') }}</strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="far fa-clock me-1"></i>
                                                                {{ $lastActivity->diffForHumans() }}
                                                            </small>
                                                        </div>
                                                    </td>
                                                    
                                                    {{-- الحالة --}}
                                                    <td>
                                                        @if($currentSession)
                                                            <span class="badge-current">
                                                                <i class="fas fa-check-circle me-1"></i>
                                                                جلستك الحالية
                                                            </span>
                                                        @else
                                                            <span class="badge-active">
                                                                <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                                                نشطة
                                                            </span>
                                                        @endif
                                                    </td>
                                                    
                                                    {{-- الإجراء --}}
                                                    <td>
                                                        @if(!$currentSession)
                                                            <form
                                                                action="{{ route('admin.sessions.destroy', $session->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('هل أنت متأكد من إنهاء هذه الجلسة؟');"
                                                                class="d-inline"
                                                            >
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger-custom">
                                                                    <i class="fas fa-sign-out-alt me-1"></i>
                                                                    إنهاء
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="text-muted">
                                                                <i class="fas fa-lock me-1"></i>
                                                                الجلسة الحالية
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-5">
                                                        <div class="empty-state">
                                                            @if(request('search'))
                                                                <i class="fas fa-search"></i>
                                                                <p>
                                                                    لا توجد نتائج للبحث عن:
                                                                    <span class="search-query">"{{ request('search') }}"</span>
                                                                </p>
                                                                <small class="text-muted">
                                                                    حاول استخدام كلمات بحث مختلفة
                                                                </small>
                                                            @else
                                                                <i class="fas fa-users-slash"></i>
                                                                <p class="mb-0">لا توجد جلسات نشطة حاليًا.</p>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            {{-- الترقيم (Pagination) --}}
                            @if($sessions->hasPages())
                                <div class="card-footer bg-white border-0">
                                    <div class="pagination-info">
                                        عرض {{ $sessions->firstItem() }} - {{ $sessions->lastItem() }} 
                                        من أصل {{ $sessions->total() }} جلسة
                                    </div>
                                    <div class="pagination-custom">
                                        {{ $sessions->links() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // بحث فوري عند الضغط على Enter
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('searchForm').submit();
            }
        });
    </script>
</body>
</html>