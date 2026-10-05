<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين</title>
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
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
        
        .search-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .search-card:hover {
            box-shadow: 0 6px 25px rgba(0,0,0,0.08);
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-warning-custom {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            color: white;
            padding: 6px 18px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-warning-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 87, 108, 0.4);
            color: white;
        }
        
        .table-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .table thead th {
            background: #f8f9fa;
            font-weight: 700;
            border-bottom: 2px solid #e9ecef;
            padding: 15px 12px;
            font-size: 14px;
            color: #495057;
        }
        
        .table tbody td {
            padding: 15px 12px;
            vertical-align: middle;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.005);
        }
        
        .avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            color: white;
            flex-shrink: 0;
        }
        
        .avatar-1 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .avatar-2 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .avatar-3 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .avatar-4 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .avatar-5 { background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); }
        .avatar-6 { background: linear-gradient(135deg, #fccb90 0%, #d57eeb 100%); }
        .avatar-7 { background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%); }
        .avatar-8 { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }
        
        .badge-count {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
        }
        
        .empty-state i {
            font-size: 70px;
            color: #d1d5db;
        }
        
        .empty-state h5 {
            color: #6b7280;
            font-weight: 700;
            margin-top: 15px;
        }
        
        .pagination .page-link {
            border: none;
            color: #667eea;
            border-radius: 8px;
            margin: 0 3px;
            font-weight: 500;
        }
        
        .pagination .page-link:hover {
            background: #667eea;
            color: white;
        }
        
        .pagination .active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .alert-custom {
            border-radius: 15px;
            border: none;
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: #155724;
            font-weight: 500;
            padding: 18px 25px;
        }
        
        .input-group-custom .input-group-text {
            background: #f8f9fa;
            border-left: none;
            border-radius: 10px 0 0 10px;
        }
        
        .input-group-custom .form-control {
            border-right: none;
            border-radius: 0 10px 10px 0;
        }
        
        .input-group-custom .form-control:focus {
            border-color: #667eea;
            box-shadow: none;
        }
        
        .salary-badge {
            background: #e9ecef;
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                
                {{-- البطاقة الرئيسية --}}
                <div class="main-card">
                    
                    {{-- الهيدر --}}
                    <div class="header-gradient">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h3 class="mb-1">
                                    <i class="bi bi-people-fill me-2"></i>
                                    إدارة المستخدمين
                                </h3>
                                <p class="mb-0">
                                    <i class="bi bi-dash-circle me-1"></i>
                                    البحث وإدارة حسابات المستخدمين
                                </p>
                            </div>
                            <div class="mt-2 mt-md-0">
                                <span class="badge bg-light text-dark px-4 py-2 rounded-pill">
                                    <i class="bi bi-person me-2"></i>
                                    {{ $users->total() }} مستخدم
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- المحتوى --}}
                    <div class="p-4" style="background: white;">
                        
                        {{-- رسالة النجاح --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show alert-custom">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        {{-- البحث --}}
                        <div class="search-card card p-3 mb-4">
                            <form method="GET" action="{{ route('admin.useradmin') }}">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-10">
                                        <label class="form-label fw-bold text-secondary mb-1">
                                            <i class="bi bi-search me-1"></i>
                                            البحث عن مستخدم
                                        </label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text">
                                                <i class="bi bi-search text-primary"></i>
                                            </span>
                                            <input
                                                type="text"
                                                name="search"
                                                class="form-control"
                                                value="{{ request('search') }}"
                                                placeholder="ابحث بالاسم أو البريد الإلكتروني أو المسمى الوظيفي..."
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary-custom w-100">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        {{-- جدول المستخدمين --}}
                        <div class="table-card card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="60">#</th>
                                                <th>المستخدم</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>المسمى الوظيفي</th>
                                                <th>الراتب الأساسي</th>
                                                <th>آخر تحديث</th>
                                                <th class="text-center" width="130">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($users as $index => $user)
                                                <tr>
                                                    <td class="text-center fw-bold text-secondary">
                                                        {{ $users->firstItem() + $index }}
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-{{ ($index % 8) + 1 }} me-3">
                                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold">{{ $user->name }}</div>
                                                                <small class="text-muted">ID: {{ $user->id }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-break text-secondary">
                                                            <i class="bi bi-envelope me-1 text-muted"></i>
                                                            {{ $user->email }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($user->title)
                                                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                                                                <i class="bi bi-briefcase me-1"></i>
                                                                {{ $user->title }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($user->basic_salary !== null)
                                                            <span class="salary-badge">
                                                                <i class="bi bi-currency-dollar me-1"></i>
                                                                {{ number_format($user->basic_salary, 2) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="text-secondary" title="{{ $user->updated_at }}">
                                                            <i class="bi bi-clock me-1"></i>
                                                            {{ $user->updated_at?->diffForHumans() }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a
                                                            href="{{ route('admin.useradmin.edit', $user->id) }}"
                                                            class="btn btn-warning-custom btn-sm"
                                                            title="تعديل المستخدم"
                                                        >
                                                            <i class="bi bi-pencil-square me-1"></i>
                                                            تعديل
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-5">
                                                        <div class="empty-state">
                                                            <i class="bi bi-person-x"></i>
                                                            <h5>لا يوجد مستخدمون</h5>
                                                            @if(request('search'))
                                                                <p class="text-muted">
                                                                    لم يتم العثور على نتائج للبحث:
                                                                    <strong class="text-primary">{{ request('search') }}</strong>
                                                                </p>
                                                            @else
                                                                <p class="text-muted">لم يتم إضافة أي مستخدمين حتى الآن</p>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            {{-- Pagination --}}
                            @if($users->hasPages())
                                <div class="card-footer bg-transparent border-0 py-3">
                                    <div class="d-flex justify-content-center">
                                        {{ $users->links() }}
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
</body>
</html>