<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل بيانات اليتيم - الأدمن</title>
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Google Fonts - Tajawal -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='48' fill='%232d5a27'/><path d='M70 65c-20 0-35-15-35-35 0-5 1-10 3-15-15 5-25 20-25 35 0 20 15 35 35 35 15 0 30-10 35-25-5 3-10 5-13 5z' fill='white'/><polygon points='65,25 68,33 77,33 70,38 73,47 65,42 57,47 60,38 53,33 62,33' fill='%23ffc107'/></svg>">
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
            max-width: 1400px;
            margin: 0 auto;
        }

        .admin-banner {
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

        .admin-banner h2 {
            font-weight: 900;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.8rem;
        }

        .status-badge {
            padding: 0.7rem 2rem;
            border-radius: 50px;
            font-weight: 800;
            font-size: 1.1rem;
        }

        .status-badge.pending {
            background: rgba(243, 156, 18, 0.2);
            color: #f39c12;
            border: 2px solid #f39c12;
        }

        .status-badge.approved {
            background: rgba(46, 204, 113, 0.2);
            color: #27ae60;
            border: 2px solid #27ae60;
        }

        .status-badge.rejected {
            background: rgba(231, 76, 60, 0.2);
            color: #c0392b;
            border: 2px solid #c0392b;
        }

        .card {
            border: none;
            border-radius: 30px;
            box-shadow: 0 15px 40px rgba(0, 30, 50, 0.08);
            background: white;
            margin-bottom: 2rem;
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

        .card-header.bg-warning {
            background: linear-gradient(135deg, #b45f2a, #d17d3c) !important;
        }

        .card-header.bg-info {
            background: linear-gradient(135deg, #1a6b5e, #218c74) !important;
        }

        .card-header.bg-secondary {
            background: linear-gradient(135deg, #7f8c8d, #95a5a6) !important;
        }

        .card-header.bg-danger {
            background: linear-gradient(135deg, #a93226, #e74c3c) !important;
        }

        .form-section {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 700;
            color: #1a3b4e;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0.5rem;
        }

        .form-group label i {
            color: #0e6b8c;
            width: 20px;
        }

        .form-control, .form-select {
            border-radius: 15px;
            padding: 0.8rem 1.2rem;
            border: 2px solid #e8f0f5;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0e6b8c;
            box-shadow: 0 0 0 0.25rem rgba(14, 107, 140, 0.15);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .member-card {
            background: #fff9f2;
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 2px solid rgba(180, 95, 42, 0.2);
            position: relative;
        }

        .member-title {
            font-weight: 900;
            font-size: 1.4rem;
            color: #b45f2a;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 3px solid #f0d5b8;
            padding-bottom: 0.8rem;
        }

        .orphan-badge {
            background: #b45f2a;
            color: white;
            padding: 0.3rem 1.2rem;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 700;
            margin-right: 1rem;
        }

        .btn-save {
            background: linear-gradient(135deg, #0e6b8c, #1a8bb0);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 1rem 3rem;
            font-weight: 800;
            font-size: 1.2rem;
            display: inline-flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
        }

        .btn-save:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(14, 107, 140, 0.3);
            color: white;
        }

        .btn-back {
            background: linear-gradient(135deg, #7f8c8d, #95a5a6);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 1rem 3rem;
            font-weight: 800;
            font-size: 1.2rem;
            display: inline-flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-back:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(127, 140, 141, 0.3);
            color: white;
        }

        .btn-delete-child {
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 0.5rem 1.5rem;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-delete-child:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(231, 76, 60, 0.3);
            color: white;
        }

        .btn-add-child {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-add-child:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(46, 204, 113, 0.3);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            padding: 1.5rem;
        }

        .required-star {
            color: #e74c3c;
            font-weight: 900;
            margin-right: 4px;
        }

        .child-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .toggle-collapse {
            cursor: pointer;
            transition: all 0.3s;
        }

        .toggle-collapse:hover {
            opacity: 0.7;
        }

        .btn-sm {
            border-radius: 30px;
            padding: 0.4rem 1.2rem;
        }

        .divider {
            border: none;
            border-top: 3px dashed #d1d8e0;
            margin: 1.5rem 0;
        }

        @media (max-width: 768px) {
            .admin-banner {
                flex-direction: column;
                text-align: center;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn-save, .btn-back {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">

        <!-- شريط تحكم الأدمن -->
        <div class="admin-banner">
            <h2>
                <i class="fas fa-edit"></i>
                تعديل بيانات الطلب
            </h2>
            <div class="status-badge {{ $case->status }}">
                @if($case->status == 'pending')
                    <i class="fas fa-clock"></i> في انتظار المراجعة
                @elseif($case->status == 'approved')
                    <i class="fas fa-check-circle"></i> تم الاعتماد
                @else
                    <i class="fas fa-times-circle"></i> مرفوض
                @endif
            </div>
        </div>

        @php
            $user = $case->telegramUser;
            $guardian = $user ? $user->guardianDetail : null;
            $familyMembers = $user ? $user->familyMembers : collect();
        @endphp

        <!-- عرض رسائل الخطأ والنجاح -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- ============================================================ -->
        <!-- نموذج التعديل الرئيسي -->
        <!-- ============================================================ -->
        <form action="{{ route('admin.orphans.update', $case->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- ========================================================== -->
            <!-- 1. بيانات المستخدم (ولي الأمر) -->
            <!-- ========================================================== -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-shield"></i>
                    بيانات ولي الأمر
                </div>
                <div class="form-section">

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label><i class="fas fa-user"></i> الاسم الكامل <span class="required-star">*</span></label>
                            <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                                value="{{ old('full_name', $user->full_name ?? '') }}" required>
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label><i class="fas fa-id-card"></i> رقم الهوية</label>
                            <input type="text" name="national_id" class="form-control @error('national_id') is-invalid @enderror"
                                value="{{ old('national_id', $user->national_id ?? '') }}">
                            @error('national_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-calendar-alt"></i> تاريخ الميلاد</label>
                            <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror"
                                value="{{ old('birth_date', $user->birth_date ?? '') }}">
                            @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-phone"></i> واتساب</label>
                            <input type="text" name="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror"
                                value="{{ old('whatsapp', $user->whatsapp ?? '') }}">
                            @error('whatsapp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-telegram"></i> رقم تيليجرام</label>
                            <input type="text" name="telegram_number" class="form-control @error('telegram_number') is-invalid @enderror"
                                value="{{ old('telegram_number', $user->telegram_number ?? '') }}">
                            @error('telegram_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-phone-alt"></i> رقم مصر <span class="required-star">*</span></label>
                            <input type="text" name="egypt_number" class="form-control @error('egypt_number') is-invalid @enderror"
                                value="{{ old('egypt_number', $user->egypt_number ?? '') }}" required>
                            @error('egypt_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-map-marker-alt"></i> المحافظة</label>
                            <input type="text" name="governorate" class="form-control @error('governorate') is-invalid @enderror"
                                value="{{ old('governorate', $user->governorate ?? '') }}">
                            @error('governorate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-city"></i> المدينة</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                value="{{ old('city', $user->city ?? '') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label><i class="fas fa-home"></i> العنوان التفصيلي <span class="required-star">*</span></label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                value="{{ old('address', $user->address ?? '') }}" required>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="divider">

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label><i class="fas fa-sign-in-alt"></i> سبب الدخول</label>
                            <input type="text" name="entry_reason" class="form-control @error('entry_reason') is-invalid @enderror"
                                value="{{ old('entry_reason', $user->entry_reason ?? '') }}">
                            @error('entry_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label><i class="fas fa-calendar-check"></i> تاريخ الدخول إلى مصر</label>
                            <input type="date" name="entry_to_egypt" class="form-control @error('entry_to_egypt') is-invalid @enderror"
                                value="{{ old('entry_to_egypt', $user->entry_to_egypt ?? '') }}">
                            @error('entry_to_egypt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label><i class="fas fa-money-bill-wave"></i> قيمة الإيجار</label>
                            <input type="number" step="0.01" name="rent_amount" class="form-control @error('rent_amount') is-invalid @enderror"
                                value="{{ old('rent_amount', $user->rent_amount ?? '') }}">
                            @error('rent_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label><i class="fas fa-phone"></i> تليفون صاحب العقار</label>
                            <input type="text" name="landlord_phone" class="form-control @error('landlord_phone') is-invalid @enderror"
                                value="{{ old('landlord_phone', $user->landlord_phone ?? '') }}">
                            @error('landlord_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================== -->
            <!-- 2. بيانات ولي الأمر التفصيلية -->
            <!-- ========================================================== -->
            <div class="card">
                <div class="card-header bg-info">
                    <i class="fas fa-user-tie"></i>
                    بيانات ولي الأمر التفصيلية
                </div>
                <div class="form-section">

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-calendar-alt"></i> تاريخ الميلاد</label>
                            <input type="date" name="guardian_birth_date" class="form-control @error('guardian_birth_date') is-invalid @enderror"
                                value="{{ old('guardian_birth_date', $guardian->birth_date ?? '') }}">
                            @error('guardian_birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-birthday-cake"></i> العمر</label>
                            <input type="number" name="guardian_age" class="form-control @error('guardian_age') is-invalid @enderror"
                                value="{{ old('guardian_age', $guardian->age ?? '') }}">
                            @error('guardian_age')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-venus-mars"></i> الجنس</label>
                            <select name="guardian_gender" class="form-select @error('guardian_gender') is-invalid @enderror">
                                <option value="">اختر</option>
                                <option value="male" {{ old('guardian_gender', $guardian->gender ?? '') == 'male' ? 'selected' : '' }}>ذكر</option>
                                <option value="female" {{ old('guardian_gender', $guardian->gender ?? '') == 'female' ? 'selected' : '' }}>أنثى</option>
                            </select>
                            @error('guardian_gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-handshake"></i> صلة القرابة</label>
                            <input type="text" name="relationship_to_case" class="form-control @error('relationship_to_case') is-invalid @enderror"
                                value="{{ old('relationship_to_case', $guardian->relationship_to_case ?? '') }}">
                            @error('relationship_to_case')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-home"></i> الحالة السكنية</label>
                            <select name="housing_status" class="form-select @error('housing_status') is-invalid @enderror">
                                <option value="">اختر</option>
                                <option value="owned" {{ old('housing_status', $guardian->housing_status ?? '') == 'owned' ? 'selected' : '' }}>تملك</option>
                                <option value="rented" {{ old('housing_status', $guardian->housing_status ?? '') == 'rented' ? 'selected' : '' }}>إيجار</option>
                                <option value="with_family" {{ old('housing_status', $guardian->housing_status ?? '') == 'with_family' ? 'selected' : '' }}>مع العائلة</option>
                                <option value="other" {{ old('housing_status', $guardian->housing_status ?? '') == 'other' ? 'selected' : '' }}>أخرى</option>
                            </select>
                            @error('housing_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-money-bill-wave"></i> الدخل الشهري</label>
                            <input type="number" step="0.01" name="monthly_income" class="form-control @error('monthly_income') is-invalid @enderror"
                                value="{{ old('monthly_income', $guardian->monthly_income ?? '') }}">
                            @error('monthly_income')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label><i class="fas fa-map-pin"></i> خط الطول (Latitude)</label>
                            <input type="text" step="any" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                value="{{ old('latitude', $guardian->latitude ?? '') }}" placeholder="مثال: 30.0444">
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label><i class="fas fa-map-pin"></i> خط العرض (Longitude)</label>
                            <input type="text" step="any" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                value="{{ old('longitude', $guardian->longitude ?? '') }}" placeholder="مثال: 31.2357">
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================== -->
            <!-- 3. بيانات الطلب -->
            <!-- ========================================================== -->
            <div class="card">
                <div class="card-header bg-secondary">
                    <i class="fas fa-folder-open"></i>
                    بيانات الطلب
                </div>
                <div class="form-section">

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-heart-broken"></i> حالة اليتيم</label>
                            <select name="orphan_status" class="form-select @error('orphan_status') is-invalid @enderror">
                                <option value="">اختر</option>
                                <option value="father" {{ old('orphan_status', $case->orphan_status ?? '') == 'father' ? 'selected' : '' }}>وفاة الأب</option>
                                <option value="mother" {{ old('orphan_status', $case->orphan_status ?? '') == 'mother' ? 'selected' : '' }}>وفاة الأم</option>
                                <option value="both" {{ old('orphan_status', $case->orphan_status ?? '') == 'both' ? 'selected' : '' }}>وفاة الأب والأم</option>
                            </select>
                            @error('orphan_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-check-circle"></i> حالة الطلب <span class="required-star">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="pending" {{ old('status', $case->status ?? '') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="approved" {{ old('status', $case->status ?? '') == 'approved' ? 'selected' : '' }}>مقبول</option>
                                <option value="rejected" {{ old('status', $case->status ?? '') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-sticky-note"></i> ملاحظات الأدمن</label>
                            <input type="text" name="admin_note" class="form-control @error('admin_note') is-invalid @enderror"
                                value="{{ old('admin_note', $case->admin_note ?? '') }}">
                            @error('admin_note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================== -->
            <!-- 4. بيانات الأيتام (عرض + تعديل + حذف) -->
            <!-- ========================================================== -->
            <div class="card">
                <div class="card-header bg-warning">
                    <i class="fas fa-children"></i>
                    بيانات الأيتام
                </div>
                <div class="form-section">

                    @forelse($familyMembers as $index => $member)
                        @php
                            $child = $member->orphanChild;
                        @endphp

                        @if($child)
                            <div class="member-card" id="child-{{ $child->id }}">
                                <div class="member-title">
                                    <i class="fas fa-user"></i>
                                    مرافق رقم {{ $loop->iteration }}: {{ $member->full_name ?? 'غير محدد' }}
                                    <span class="orphan-badge"><i class="fas fa-heart"></i> يتيم</span>

                                    <button type="button" class="btn btn-sm btn-secondary ms-auto toggle-collapse"
                                        data-bs-toggle="collapse" data-bs-target="#collapse-{{ $child->id }}">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>

                                <div class="collapse show" id="collapse-{{ $child->id }}">
                                    <!-- نموذج تعديل الطفل -->
                                    <form action="{{ route('admin.orphans.updateChild', $child->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label><i class="fas fa-user"></i> الاسم الكامل <span class="required-star">*</span></label>
                                                <input type="text" name="full_name" class="form-control"
                                                    value="{{ old('full_name', $member->full_name ?? '') }}" required>
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label><i class="fas fa-id-card"></i> رقم الهوية</label>
                                                <input type="text" name="national_id" class="form-control"
                                                    value="{{ old('national_id', $member->national_id ?? '') }}">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label><i class="fas fa-calendar-alt"></i> تاريخ الميلاد <span class="required-star">*</span></label>
                                                <input type="date" name="birth_date" class="form-control"
                                                    value="{{ old('birth_date', $child->birth_date ?? '') }}" required>
                                            </div>

                                            <div class="col-md-4 form-group">
                                                <label><i class="fas fa-birthday-cake"></i> العمر <span class="required-star">*</span></label>
                                                <input type="number" name="age" class="form-control"
                                                    value="{{ old('age', $child->age ?? '') }}" required>
                                            </div>

                                            <div class="col-md-4 form-group">
                                                <label><i class="fas fa-venus-mars"></i> الجنس <span class="required-star">*</span></label>
                                                <select name="gender" class="form-select" required>
                                                    <option value="male" {{ old('gender', $child->gender ?? '') == 'male' ? 'selected' : '' }}>ذكر</option>
                                                    <option value="female" {{ old('gender', $child->gender ?? '') == 'female' ? 'selected' : '' }}>أنثى</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label><i class="fas fa-handshake"></i> الصلة بالحالة</label>
                                                <input type="text" name="relation" class="form-control"
                                                    value="{{ old('relation', $member->relation ?? '') }}">
                                            </div>

                                            <div class="col-md-4 form-group">
                                                <label><i class="fas fa-home"></i> يعيش مع ولي الأمر</label>
                                                <select name="lives_with_guardian" class="form-select">
                                                    <option value="1" {{ old('lives_with_guardian', $child->lives_with_guardian ?? '') == '1' ? 'selected' : '' }}>نعم</option>
                                                    <option value="0" {{ old('lives_with_guardian', $child->lives_with_guardian ?? '') == '0' ? 'selected' : '' }}>لا</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4 form-group">
                                                <label><i class="fas fa-graduation-cap"></i> المستوى التعليمي</label>
                                                <select name="education_level" class="form-select">
                                                    <option value="">اختر</option>
                                                    <option value="none" {{ old('education_level', $child->education_level ?? '') == 'none' ? 'selected' : '' }}>لا يوجد</option>
                                                    <option value="kindergarten" {{ old('education_level', $child->education_level ?? '') == 'kindergarten' ? 'selected' : '' }}>حضانة / رياض أطفال</option>
                                                    <option value="primary" {{ old('education_level', $child->education_level ?? '') == 'primary' ? 'selected' : '' }}>ابتدائي</option>
                                                    <option value="middle" {{ old('education_level', $child->education_level ?? '') == 'middle' ? 'selected' : '' }}>إعدادي / متوسط</option>
                                                    <option value="secondary" {{ old('education_level', $child->education_level ?? '') == 'secondary' ? 'selected' : '' }}>ثانوي</option>
                                                    <option value="diploma" {{ old('education_level', $child->education_level ?? '') == 'diploma' ? 'selected' : '' }}>دبلوم</option>
                                                    <option value="bachelor" {{ old('education_level', $child->education_level ?? '') == 'bachelor' ? 'selected' : '' }}>بكالوريوس / ليسانس</option>
                                                    <option value="master" {{ old('education_level', $child->education_level ?? '') == 'master' ? 'selected' : '' }}>ماجستير</option>
                                                    <option value="phd" {{ old('education_level', $child->education_level ?? '') == 'phd' ? 'selected' : '' }}>دكتوراه</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label><i class="fas fa-book"></i> نوع الدراسة</label>
                                                <input type="text" name="study_type" class="form-control"
                                                    value="{{ old('study_type', $child->study_type ?? '') }}">
                                            </div>

                                            <div class="col-md-4 form-group">
                                                <label><i class="fas fa-thermometer-half"></i> مرض مزمن</label>
                                                <select name="has_chronic_disease" class="form-select">
                                                    <option value="0" {{ old('has_chronic_disease', $child->has_chronic_disease ?? '') == '0' ? 'selected' : '' }}>لا</option>
                                                    <option value="1" {{ old('has_chronic_disease', $child->has_chronic_disease ?? '') == '1' ? 'selected' : '' }}>نعم</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4 form-group">
                                                <label><i class="fas fa-wheelchair"></i> إعاقة</label>
                                                <select name="has_disability" class="form-select">
                                                    <option value="0" {{ old('has_disability', $child->has_disability ?? '') == '0' ? 'selected' : '' }}>لا</option>
                                                    <option value="1" {{ old('has_disability', $child->has_disability ?? '') == '1' ? 'selected' : '' }}>نعم</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="child-actions">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-save"></i> تحديث بيانات اليتيم
                                            </button>
                                    </form>

                                    <!-- حذف اليتيم -->
                                    <form action="{{ route('admin.orphans.destroyChild', $child->id) }}" method="POST"
                                        onsubmit="return confirm('⚠️ هل أنت متأكد من حذف هذا اليتيم؟ لا يمكن التراجع!');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete-child btn-sm">
                                            <i class="fas fa-trash"></i> حذف اليتيم
                                        </button>
                                    </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="text-center p-5">
                            <i class="fas fa-child" style="font-size: 4rem; color: #ccc;"></i>
                            <p class="mt-3">لا يوجد أيتام مضافين</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- ========================================================== -->
            <!-- 5. إضافة يتيم جديد -->
            <!-- ========================================================== -->
            <div class="card">
                <div class="card-header bg-danger">
                    <i class="fas fa-plus-circle"></i>
                    إضافة يتيم جديد
                </div>
                <div class="form-section">

                    <form action="{{ route('admin.orphans.storeChild', $case->id) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label><i class="fas fa-user"></i> الاسم الكامل <span class="required-star">*</span></label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>

                            <div class="col-md-6 form-group">
                                <label><i class="fas fa-id-card"></i> رقم الهوية</label>
                                <input type="text" name="national_id" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label><i class="fas fa-calendar-alt"></i> تاريخ الميلاد <span class="required-star">*</span></label>
                                <input type="date" name="birth_date" class="form-control" required>
                            </div>

                            <div class="col-md-4 form-group">
                                <label><i class="fas fa-birthday-cake"></i> العمر <span class="required-star">*</span></label>
                                <input type="number" name="age" class="form-control" required>
                            </div>

                            <div class="col-md-4 form-group">
                                <label><i class="fas fa-venus-mars"></i> الجنس <span class="required-star">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="male">ذكر</option>
                                    <option value="female">أنثى</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label><i class="fas fa-handshake"></i> الصلة بالحالة</label>
                                <input type="text" name="relation" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label><i class="fas fa-home"></i> يعيش مع ولي الأمر</label>
                                <select name="lives_with_guardian" class="form-select">
                                    <option value="1">نعم</option>
                                    <option value="0">لا</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label><i class="fas fa-graduation-cap"></i> المستوى التعليمي</label>
                                <select name="education_level" class="form-select">
                                    <option value="">اختر</option>
                                    <option value="none">لا يوجد</option>
                                    <option value="kindergarten">حضانة / رياض أطفال</option>
                                    <option value="primary">ابتدائي</option>
                                    <option value="middle">إعدادي / متوسط</option>
                                    <option value="secondary">ثانوي</option>
                                    <option value="diploma">دبلوم</option>
                                    <option value="bachelor">بكالوريوس / ليسانس</option>
                                    <option value="master">ماجستير</option>
                                    <option value="phd">دكتوراه</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label><i class="fas fa-book"></i> نوع الدراسة</label>
                                <input type="text" name="study_type" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label><i class="fas fa-thermometer-half"></i> مرض مزمن</label>
                                <select name="has_chronic_disease" class="form-select">
                                    <option value="0">لا</option>
                                    <option value="1">نعم</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label><i class="fas fa-wheelchair"></i> إعاقة</label>
                                <select name="has_disability" class="form-select">
                                    <option value="0">لا</option>
                                    <option value="1">نعم</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn-add-child">
                            <i class="fas fa-plus-circle"></i> إضافة يتيم جديد
                        </button>
                    </form>
                </div>
            </div>

            <!-- ========================================================== -->
            <!-- 6. أزرار الإجراءات -->
            <!-- ========================================================== -->
            <div class="card">
                <div class="card-body">
                    <div class="action-buttons">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i>
                            حفظ جميع التغييرات
                        </button>

                        <a href="{{ route('admin.orphans.show', $case->id) }}" class="btn-back">
                            <i class="fas fa-eye"></i>
                            العودة للعرض
                        </a>

                        <a href="{{ route('admin.orphans.index') }}" class="btn-back">
                            <i class="fas fa-arrow-right"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>

        </form>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // تفعيل Select2 على جميع الحقول التي تحتاجه
            $('.form-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'اختر...',
                allowClear: true
            });

            // تبديل أيقونة الـ collapse
            $('.toggle-collapse').on('click', function() {
                const icon = $(this).find('i');
                icon.toggleClass('fa-chevron-down fa-chevron-up');
            });
        });
    </script>
</body>
</html>