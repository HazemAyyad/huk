<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المستخدم</title>
    
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
        
        .btn-back {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-back:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            transform: translateX(-5px);
        }
        
        .form-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .form-card .card-header {
            border-radius: 15px 15px 0 0;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .avatar-large {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .section-title {
            font-weight: 700;
            color: #2d3748;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        
        .section-title i {
            color: #667eea;
        }
        
        .form-label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
        }
        
        .form-control.is-invalid {
            border-color: #f56565;
        }
        
        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(245, 101, 101, 0.15);
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 35px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-cancel {
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
        }
        
        .alert-danger-custom {
            border-radius: 15px;
            border: none;
            background: linear-gradient(135deg, #feb2b2 0%, #fc8181 100%);
            color: #742a2a;
            font-weight: 500;
            padding: 18px 25px;
        }
        
        .alert-danger-custom ul {
            padding-right: 20px;
        }
        
        .alert-danger-custom li {
            margin-bottom: 5px;
        }
        
        .alert-info-custom {
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #bee3f8 0%, #90cdf4 100%);
            color: #2a4365;
            font-weight: 500;
            padding: 15px 20px;
        }
        
        .input-group-custom .form-control {
            border-left: none;
            border-radius: 10px 0 0 10px;
        }
        
        .input-group-custom .btn {
            border-radius: 0 10px 10px 0;
            border: 2px solid #e2e8f0;
            border-left: none;
            background: white;
            color: #4a5568;
            transition: all 0.2s ease;
        }
        
        .input-group-custom .btn:hover {
            background: #f7fafc;
        }
        
        .input-group-custom .form-control:focus {
            border-color: #667eea;
        }
        
        .input-group-custom .form-control:focus + .btn {
            border-color: #667eea;
        }
        
        .user-id-badge {
            background: #edf2f7;
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
            color: #4a5568;
        }
        
        .input-hint {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 4px;
        }
        
        @media (max-width: 768px) {
            .header-gradient {
                padding: 20px;
            }
            .header-gradient h3 {
                font-size: 20px;
            }
            .btn-back {
                padding: 6px 15px;
                font-size: 14px;
            }
            .form-card .card-body {
                padding: 20px !important;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                
                {{-- البطاقة الرئيسية --}}
                <div class="main-card">
                    
                    {{-- الهيدر --}}
                    <div class="header-gradient">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h3 class="mb-1">
                                    <i class="bi bi-person-gear me-2"></i>
                                    تعديل المستخدم
                                </h3>
                                <p class="mb-0">
                                    <i class="bi bi-dash-circle me-1"></i>
                                    تعديل بيانات الحساب وكلمة المرور
                                </p>
                            </div>
                            <a href="{{ route('admin.useradmin') }}" class="btn-back btn mt-2 mt-md-0">
                                <i class="bi bi-arrow-right me-1"></i>
                                العودة للمستخدمين
                            </a>
                        </div>
                    </div>
                    
                    {{-- المحتوى --}}
                    <div class="p-4" style="background: white;">
                        
                        {{-- أخطاء التحقق --}}
                        @if($errors->any())
                            <div class="alert alert-danger-custom alert-dismissible fade show">
                                <strong>
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    يرجى تصحيح الأخطاء التالية:
                                </strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        {{-- بطاقة النموذج --}}
                        <div class="form-card card">
                            
                            {{-- Header المستخدم --}}
                            <div class="card-header bg-white py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-large me-3">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">{{ $user->name }}</h5>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="user-id-badge">
                                                <i class="bi bi-hash me-1"></i>
                                                ID: {{ $user->id }}
                                            </span>
                                            <span class="user-id-badge">
                                                <i class="bi bi-envelope me-1"></i>
                                                {{ $user->email }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Body النموذج --}}
                            <div class="card-body p-4">
                                
                                <form method="POST" action="{{ route('admin.useradmin.update', $user->id) }}">
                                    @csrf
                                    @method('PUT')
                                    
                                    {{-- البيانات الأساسية --}}
                                    <h6 class="section-title">
                                        <i class="bi bi-person me-2"></i>
                                        بيانات الحساب
                                    </h6>
                                    
                                    <div class="row g-4">
                                        
                                        {{-- الاسم --}}
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                الاسم <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                name="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                                value="{{ old('name', $user->name) }}"
                                                required
                                                placeholder="أدخل الاسم كاملاً"
                                            >
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        {{-- البريد الإلكتروني --}}
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                البريد الإلكتروني <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="email"
                                                name="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email', $user->email) }}"
                                                required
                                                placeholder="example@domain.com"
                                            >
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        {{-- المسمى الوظيفي --}}
                                        <div class="col-md-6">
                                            <label class="form-label">المسمى الوظيفي</label>
                                            <input
                                                type="text"
                                                name="title"
                                                class="form-control"
                                                value="{{ old('title', $user->title) }}"
                                                placeholder="مثال: مدير، موظف، محاسب..."
                                            >
                                            <div class="input-hint">
                                                <i class="bi bi-info-circle me-1"></i>
                                                اختر المسمى الوظيفي المناسب
                                            </div>
                                        </div>
                                        
                                        {{-- الراتب الأساسي --}}
                                        <div class="col-md-6">
                                            <label class="form-label">الراتب الأساسي</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    name="basic_salary"
                                                    class="form-control @error('basic_salary') is-invalid @enderror"
                                                    value="{{ old('basic_salary', $user->basic_salary) }}"
                                                    placeholder="0.00"
                                                >
                                            </div>
                                            @error('basic_salary')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <div class="input-hint">
                                                <i class="bi bi-info-circle me-1"></i>
                                                أدخل الراتب الشهري الأساسي
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                    {{-- تغيير كلمة المرور --}}
                                    <h6 class="section-title mt-5">
                                        <i class="bi bi-shield-lock me-2"></i>
                                        تغيير كلمة المرور
                                    </h6>
                                    
                                    <div class="alert alert-info-custom">
                                        <i class="bi bi-info-circle me-2"></i>
                                        اترك حقول كلمة المرور فارغة إذا كنت لا تريد تغيير كلمة المرور الحالية.
                                    </div>
                                    
                                    <div class="row g-4">
                                        
                                        {{-- كلمة المرور الجديدة --}}
                                        <div class="col-md-6">
                                            <label class="form-label">كلمة المرور الجديدة</label>
                                            <div class="input-group input-group-custom">
                                                <input
                                                    type="password"
                                                    name="password"
                                                    id="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    autocomplete="new-password"
                                                    placeholder="••••••••"
                                                >
                                                <button
                                                    type="button"
                                                    class="btn"
                                                    onclick="togglePassword('password', this)"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            @error('password')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="input-hint">
                                                <i class="bi bi-info-circle me-1"></i>
                                                يجب أن تكون 8 أحرف على الأقل
                                            </div>
                                        </div>
                                        
                                        {{-- تأكيد كلمة المرور --}}
                                        <div class="col-md-6">
                                            <label class="form-label">تأكيد كلمة المرور</label>
                                            <div class="input-group input-group-custom">
                                                <input
                                                    type="password"
                                                    name="password_confirmation"
                                                    id="password_confirmation"
                                                    class="form-control"
                                                    autocomplete="new-password"
                                                    placeholder="••••••••"
                                                >
                                                <button
                                                    type="button"
                                                    class="btn"
                                                    onclick="togglePassword('password_confirmation', this)"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div class="input-hint">
                                                <i class="bi bi-info-circle me-1"></i>
                                                أعد كتابة كلمة المرور للتأكيد
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                    {{-- أزرار الإجراءات --}}
                                    <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
                                        <a href="{{ route('admin.useradmin') }}" class="btn btn-cancel">
                                            <i class="bi bi-x-lg me-1"></i>
                                            إلغاء
                                        </a>
                                        <button type="submit" class="btn btn-primary-custom">
                                            <i class="bi bi-check-lg me-2"></i>
                                            حفظ التعديلات
                                        </button>
                                    </div>
                                    
                                </form>
                                
                            </div>
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>