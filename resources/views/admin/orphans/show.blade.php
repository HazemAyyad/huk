<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض بيانات الأيتام - مراجعة الأدمن</title>
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Google Fonts - Tajawal -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

        .review-form-container {
            background: white;
            border-radius: 30px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 2px solid #0e6b8c;
        }

        .review-form-title {
            font-weight: 800;
            color: #0e6b8c;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .info-item {
            background: #f8fafc;
            border-radius: 20px;
            padding: 1.2rem 1.5rem;
            border: 1px solid rgba(14,107,140,0.1);
            transition: all 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(14,107,140,0.1);
            border-color: #0e6b8c;
        }

        .info-label {
            color: #5f7d95;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label i {
            color: #0e6b8c;
            width: 20px;
        }

        .info-value {
            font-weight: 800;
            font-size: 1.2rem;
            color: #1a3b4e;
            padding-right: 28px;
        }

        .file-download {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            border: 2px solid #0e6b8c;
            color: #0e6b8c;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .file-download:hover {
            background: #0e6b8c;
            color: white;
        }

        .map-container {
            height: 400px;
            border-radius: 20px;
            overflow: hidden;
            margin: 1.5rem;
            border: 3px solid white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        #map {
            height: 100%;
            width: 100%;
            pointer-events: none;
        }

        .coordinates-box {
            background: #0a1a1f;
            color: #e2f0f9;
            padding: 1rem 1.5rem;
            border-radius: 20px;
            margin: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            direction: ltr;
            font-family: monospace;
        }

        .member-card {
            background: #fff9f2;
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 2px solid rgba(180,95,42,0.2);
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

        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .photo-item {
            text-align: center;
        }

        .photo-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 15px;
            border: 3px solid white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .photo-item span {
            display: block;
            margin-top: 0.5rem;
            font-weight: 600;
            color: #1a3b4e;
        }

        .admin-notes {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 1.5rem;
            margin: 1.5rem;
            border-right: 5px solid #0e6b8c;
        }

        .admin-notes h5 {
            font-weight: 800;
            color: #0e6b8c;
            margin-bottom: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-buttons form {
            margin: 0;
        }

        .btn-approve {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 1rem 3rem;
            font-weight: 800;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
        }

        .btn-approve:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(46, 204, 113, 0.3);
            color: white;
        }

        .btn-reject {
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 1rem 3rem;
            font-weight: 800;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
        }

        .btn-reject:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(231, 76, 60, 0.3);
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
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(127,140,141,0.3);
            color: white;
        }

        @media print {
            .admin-banner, .review-form-container, .action-buttons, .btn-back {
                display: none !important;
            }
            body { background: white; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }

        .btn-delete {
    background: linear-gradient(135deg, #8e0e00, #c0392b);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 1rem 3rem;
    font-weight: 800;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s;
}

.btn-delete:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(192, 57, 43, 0.3);
    color: white;
}

.coord-item, .address-item {
    display: flex;
    align-items: center;
    background: #fdfdfd;
    justify-content: center;
    padding: 10px;
    border-radius: 8px;
    border: 1px dashed #d1d8e0;
}

/* لجعل العنوان يأخذ السطر كاملاً */
.address-item {
    grid-column: span 2; 
    border-style: solid;
    background: #f0f7ff;
    border-color: #bcd9ff;
    margin-bottom: 40px;
}

.single-photo {
    display: flex;
    justify-content: center; /* بالنص أفقي */
    align-items: center;
    margin: 2rem 0;
}

.single-photo img {
    width: 100%;
    max-width: 400px; /* حجم مناسب على اللابتوب */
    height: auto;
    border-radius: 20px;
    object-fit: cover;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    border: 4px solid white;
}

/* للجوال */
@media (max-width: 768px) {
    .single-photo img {
        max-width: 90%; /* أكبر على الجوال */
    }
}

.photo-card {
    background: white;
    border-radius: 25px;
    padding: 1.5rem;
    margin: 1.5rem auto;
    max-width: 450px;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    border: 2px solid rgba(14,107,140,0.1);
    transition: 0.3s;
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 45px rgba(0,0,0,0.15);
}

.photo-card img {
    width: 100%;
    max-width: 350px;
    border-radius: 20px;
    object-fit: cover;
    margin-bottom: 10px;
}

.photo-card span {
    font-weight: 700;
    color: #0e6b8c;
}

    </style>
</head>
<body>
    <div class="main-container">

        <!-- شريط تحكم الأدمن -->
        <div class="admin-banner">
            <h2>
                <i class="fas fa-shield-alt"></i>
                لوحة تحكم الأدمن - مراجعة بيانات
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
            $guardianDetail = optional($case->telegramUser)->guardianDetail;
        @endphp

        <!-- معلومات الطلب -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-shield"></i>
                بيانات ولي الأمر
            </div>

            <!-- صورة ولي الأمر -->
            @if($guardianDetail && $guardianDetail->personal_photo)
            <div class="photo-card">

                <img src="{{ asset($guardianDetail->personal_photo) }}" alt="صورة ولي الأمر">

                <span><i class="fas fa-user"></i> صورة ولي الأمر</span>

                <!-- زر التعديل -->
                <form action="{{ route('admin.orphans.updateImage', $case->id) }}" 
                    method="POST" 
                    enctype="multipart/form-data"
                    style="margin-top:10px;">
                    @csrf

                    <input type="hidden" name="type" value="guardian_photo">

                    <input type="file" name="image" class="form-control mb-2" required>

                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> تعديل الصورة
                    </button>
                </form>

            </div>
            @endif

            <!-- المعلومات -->
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-qrcode"></i> الكود</div>
                    <div class="info-value">{{ optional($case->telegramUser)->access_code ?? 'غير محدد' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-user"></i> الاسم الكامل</div>
                    <div class="info-value">{{ optional($case->telegramUser)->full_name ?? 'غير محدد' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-id-card"></i> رقم الهوية</div>
                    <div class="info-value">{{ optional($case->telegramUser)->national_id ?? 'غير محدد' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-phone-alt"></i> رقم الهاتف</div>
                    <div class="info-value">{{ optional($case->telegramUser)->egypt_number ?? 'غير محدد' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-calendar-alt"></i> تاريخ الميلاد</div>
                    <div class="info-value">
                        {{ $guardianDetail && $guardianDetail->birth_date ? \Carbon\Carbon::parse($guardianDetail->birth_date)->format('Y-m-d') : 'غير محدد' }}
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-hourglass-half"></i> العمر</div>
                    <div class="info-value">
                        {{ $guardianDetail && $guardianDetail->age ? $guardianDetail->age . ' سنة' : 'غير محدد' }}
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-venus-mars"></i> الجنس</div>
                    <div class="info-value">
                        {{ $guardianDetail && $guardianDetail->gender == 'male' ? 'ذكر' : ($guardianDetail && $guardianDetail->gender == 'female' ? 'أنثى' : 'غير محدد') }}
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-hand-holding-heart"></i> صلة القرابة</div>
                    <div class="info-value">
                        {{ $guardianDetail->relationship_to_case ?? 'غير محدد' }}
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-home"></i> الحالة السكنية</div>
                    <div class="info-value">
                        {{ $guardianDetail->housing_status ?? 'غير محدد' }}
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-money-bill-wave"></i> الدخل الشهري</div>
                    <div class="info-value">
                        {{ $guardianDetail && $guardianDetail->monthly_income ? number_format($guardianDetail->monthly_income, 2) . ' ج.م' : 'غير محدد' }}
                    </div>
                </div>

                <div class="info-item full-width">
                    <div class="info-label"><i class="fas fa-map-pin"></i> العنوان</div>
                    <div class="info-value">{{ optional($case->telegramUser)->address ?? 'غير محدد' }}</div>
                </div>

            </div>
        </div>

        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-map-marker-alt"></i>
                الموقع على الخريطة
            </div>

            <!-- الموقع على الخريطة -->
            @if($guardianDetail && $guardianDetail->latitude && $guardianDetail->longitude)
                <div class="map-container">
                    <div class="text-center mb-3">
    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $guardianDetail->latitude }},{{ $guardianDetail->longitude }}"
       target="_blank"
       class="btn btn-success">
        <i class="fas fa-route"></i>
        فتح الاتجاهات
    </a>
</div>

<div id="map"></div>
                </div>
                <div class="address-item">
                    <i class="fas fa-home"></i> 
                    <span class="label">العنوان:</span>
                    <span class="value">{{ optional($case->telegramUser)->address ?? 'غير محدد' }}</span>
                </div>
            @endif
        
        </div>    
        <div class="card">
            <div class="card-header">
                <i class="fas fa-paperclip"></i>
                المرفقات
            </div>
            <!-- الملفات المرفوعة -->
            @php
                $attachments = [
                    'id_document' => ['label' => 'الهوية', 'icon' => 'fa-id-card'],
                    'passport' => ['label' => 'جواز السفر', 'icon' => 'fa-passport'],
                    'entry_stamp' => ['label' => 'ختم الدخول', 'icon' => 'fa-stamp'],
                    'medical_reports' => ['label' => 'التقارير الطبية', 'icon' => 'fa-file-medical'],
                    'rent_contract' => ['label' => 'عقد الإيجار', 'icon' => 'fa-file-contract'],
                ];
            @endphp

            <div class="info-grid">

            @foreach($attachments as $key => $data)

                <div class="info-item">

                    <div class="info-label">
                        <i class="fas {{ $data['icon'] }}"></i>
                        {{ $data['label'] }}
                    </div>

                    {{-- عرض الملف إذا موجود --}}
                    @if(optional($guardianDetail)->$key)
                        <a href="{{ asset($guardianDetail->$key) }}" target="_blank" class="file-download mb-2">
                            <i class="fas fa-eye"></i> عرض الملف
                        </a>
                    @else
                        <div class="text-muted mb-2">غير مرفق</div>
                    @endif

                    {{-- فورم رفع / تعديل --}}
                    <form action="{{ route('admin.orphans.attachment', $case->id) }}" 
                        method="POST" 
                        enctype="multipart/form-data">

                        @csrf

                        <input type="hidden" name="type" value="{{ $key }}">

                        <input type="file" name="file" class="form-control mb-2" required>

                        <button class="btn btn-sm btn-primary w-100">
                            {{ optional($guardianDetail)->$key ? 'تحديث الملف' : 'رفع الملف' }}
                        </button>
                    </form>

                </div>

            @endforeach

            </div>
        </div>

        <!-- حالة اليتيم -->
        <div class="card">
            <div class="card-header bg-info">
                <i class="fas fa-heart-broken"></i>
                حالة اليتم
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-user-slash"></i> حالة اليتيم</div>
                    <div class="info-value">
                        @if($case->orphan_status == 'father')
                            <span class="badge bg-danger">وفاة الأب</span>
                        @elseif($case->orphan_status == 'mother')
                            <span class="badge bg-danger">وفاة الأم</span>
                        @elseif($case->orphan_status == 'both')
                            <span class="badge bg-danger">وفاة الأب والأم</span>
                        @else
                            غير محدد
                        @endif
                    </div>
                </div>
                @if($case->death_certificate)
                    <a href="{{ asset($case->death_certificate) }}" target="_blank" class="file-download mb-2">
                        <i class="fas fa-eye"></i> عرض الملف
                    </a>
                @else
                    <div class="text-muted mb-2">غير مرفق</div>
                @endif

                <form action="{{ route('admin.orphans.attachment', $case->id) }}" 
                    method="POST" 
                    enctype="multipart/form-data">

                    @csrf
                    <input type="hidden" name="type" value="death_certificate">

                    <input type="file" name="file" class="form-control mb-2" required>

                    <button class="btn btn-sm btn-primary w-100">
                        {{ $case->death_certificate ? 'تحديث الملف' : 'رفع الملف' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- بيانات الأيتام -->
        <div class="card">
            <div class="card-header bg-warning">
                <i class="fas fa-children"></i>
                بيانات الأيتام
            </div>
            <div class="card-body">
                @php
                    $familyMembers = optional($case->telegramUser)->familyMembers ?? collect();
                @endphp
                
                @forelse($familyMembers as $index => $member)
                    @php
                        $orphanChild = $member->orphanChild;
                    @endphp
                    
                    @if($orphanChild)
                    <div class="member-card">
                        <div class="member-title">
                            <i class="fas fa-user"></i>
                            مرافق رقم {{ $loop->iteration }}: {{ $member->full_name ?? 'غير محدد' }}
                            <span class="orphan-badge">
                                <i class="fas fa-heart"></i> يتيم
                            </span>
                        </div>

                        <div class="photo-card">

                            @if($orphanChild->personal_photo)
                                <img src="{{ asset($orphanChild->personal_photo) }}" alt="صورة اليتيم">
                            @else
                                <div class="py-5 text-center text-muted border rounded mb-3">
                                    <i class="fas fa-image fa-3x mb-2"></i><br>
                                    لا توجد صورة لليتيم
                                </div>
                            @endif

                            <span>
                                <i class="fas fa-child"></i> صورة اليتيم
                            </span>

                            <form action="{{ route('admin.orphans.updateImage', $case->id) }}"
                                method="POST"
                                enctype="multipart/form-data"
                                style="margin-top:15px;">
                                @csrf

                                <input type="hidden" name="type" value="child_photo">
                                <input type="hidden" name="child_id" value="{{ $orphanChild->id }}">

                                <input type="file" name="image" class="form-control mb-2" required>

                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="fas fa-upload"></i>
                                    {{ $orphanChild->personal_photo ? 'تعديل الصورة' : 'رفع الصورة' }}
                                </button>
                            </form>

                        </div>

                        <div class="info-item">
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-calendar-alt"></i> تاريخ الميلاد</div>
                                <div class="info-value">{{ $orphanChild->birth_date ? \Carbon\Carbon::parse($orphanChild->birth_date)->format('Y-m-d') : 'غير محدد' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-birthday-cake"></i> العمر</div>
                                <div class="info-value">{{ $orphanChild->age ?? 'غير محدد' }} سنة</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-venus-mars"></i> الجنس</div>
                                <div class="info-value">{{ $orphanChild->gender == 'male' ? 'ذكر' : 'أنثى' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-graduation-cap"></i> المستوى التعليمي</div>
                                <div class="info-value">
                                    @switch($orphanChild->education_level)
                                        @case('kindergarten') حضانة / رياض أطفال @break
                                        @case('primary') ابتدائي @break
                                        @case('middle') إعدادي / متوسط @break
                                        @case('secondary') ثانوي @break
                                        @case('diploma') دبلوم @break
                                        @case('bachelor') بكالوريوس / ليسانس @break
                                        @case('master') ماجستير @break
                                        @case('phd') دكتوراه @break
                                        @default {{ $orphanChild->education_level ?? 'غير محدد' }}
                                    @endswitch
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-home"></i> يعيش مع ولي الأمر</div>
                                <div class="info-value">{{ isset($orphanChild->lives_with_guardian) && $orphanChild->lives_with_guardian ? 'نعم' : 'لا' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-thermometer-half"></i> مرض مزمن</div>
                                <div class="info-value">{{ isset($orphanChild->has_chronic_disease) && $orphanChild->has_chronic_disease ? 'نعم' : 'لا' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-wheelchair"></i> إعاقة</div>
                                <div class="info-value">{{ isset($orphanChild->has_disability) && $orphanChild->has_disability ? 'نعم' : 'لا' }}</div>
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
<!-- نموذج المراجعة -->
        <div class="review-form-container">
            <h4 class="review-form-title">
                <i class="fas fa-gavel"></i>
                إجراءات المراجعة
            </h4>
            
            <form action="{{ route('admin.orphans.review', $case->id) }}" method="POST" id="reviewForm">
                @csrf
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-sticky-note"></i>
                            ملاحظات الأدمن (اختياري)
                        </label>
                        <textarea 
                            name="admin_note" 
                            class="form-control @error('admin_note') is-invalid @enderror" 
                            rows="3"
                            placeholder="أضف أي ملاحظات حول الطلب..."
                            style="border-radius: 15px; padding: 1rem;"
                        >{{ old('admin_note', $case->admin_note) }}</textarea>
                        @error('admin_note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="{{ route('admin.orphans.edit',$case->id) }}"
                       class="btn btn-warning">
                        <i class="fas fa-plus"></i>
                        اضافة يتيم
                    </a>
                    <!-- فورم المراجعة -->
                    <form action="{{ route('admin.orphans.review', $case->id) }}" method="POST" id="reviewForm">
                        @csrf
                        <input type="hidden" name="status" value="approved">

                        <button type="submit" class="btn-approve"
                            onclick="return confirm('هل أنت متأكد من اعتماد هذا الطلب؟')">
                            <i class="fas fa-check-circle"></i>
                            اعتماد الطلب
                        </button>
                    </form>

                    <!-- فورم الحذف -->
                    <form action="{{ route('admin.orphans.destroy', $case->id) }}" method="POST"
                        onsubmit="return confirm('⚠️ هل أنت متأكد من حذف الطلب؟ لا يمكن التراجع!');">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn-delete">
                            <i class="fas fa-trash"></i>
                            حذف الطلب
                        </button>
                    </form>

                </div>
        </div>
        <!-- أزرار الإجراءات -->
        <div class="action-buttons">
            <button onclick="window.print()" class="btn-approve" style="background: linear-gradient(135deg, #2c3e50, #34495e);">
                <i class="fas fa-print"></i>
                طباعة البيانات
            </button>
            <button class="btn-approve"
                style="background:#198754"
                onclick="copyCaseSummary(event)">
                <i class="fas fa-copy"></i>
                نسخ ملخص الحالة
            </button>
            <a href="{{ route('admin.orphans.index') }}" class="btn-back">
                <i class="fas fa-arrow-right"></i>
                العودة للقائمة
            </a>
        </div>

    </div>
    
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($guardianDetail && $guardianDetail->latitude && $guardianDetail->longitude)
                // تهيئة الخريطة للعرض فقط
                var map = L.map('map', {
                    zoomControl: false,
                    dragging: false,
                    touchZoom: false,
                    scrollWheelZoom: false,
                    doubleClickZoom: false,
                    boxZoom: false,
                    keyboard: false,
                    attributionControl: false
                }).setView([{{ $guardianDetail->latitude }}, {{ $guardianDetail->longitude }}], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(map);

                L.marker([{{ $guardianDetail->latitude }}, {{ $guardianDetail->longitude }}], {
                    interactive: false
                }).addTo(map);
            @endif
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
function copyCaseSummary(event) {

const text = `📋 ملخص حالة الأيتام

👤 ولي الأمر
الاسم: {{ optional($case->telegramUser)->full_name ?? 'غير محدد' }}
رقم الهوية: {{ optional($case->telegramUser)->national_id ?? 'غير محدد' }}
رقم الهاتف: {{ optional($case->telegramUser)->egypt_number ?? 'غير محدد' }}
العنوان:
{{ optional($case->telegramUser)->address ?? 'غير محدد' }}

👨‍👩‍👧 بيانات ولي الأمر
صلة القرابة: {{ $guardianDetail->relationship_to_case ?? 'غير محدد' }}
العمر: {{ $guardianDetail->age ?? 'غير محدد' }}
الحالة السكنية: {{ $guardianDetail->housing_status ?? 'غير محدد' }}
الدخل الشهري: {{ $guardianDetail && $guardianDetail->monthly_income ? number_format($guardianDetail->monthly_income,2).' ج.م' : 'غير محدد' }}

🧒 عدد الأيتام:
{{ optional($case->telegramUser)->familyMembers->count() }}

💔 حالة اليتم:
@if($case->orphan_status == 'father')
وفاة الأب
@elseif($case->orphan_status == 'mother')
وفاة الأم
@elseif($case->orphan_status == 'both')
وفاة الأب والأم
@else
غير محدد
@endif

📌 بيانات الأيتام:
@foreach(optional($case->telegramUser)->familyMembers as $loopIndex => $member)
@if($member->orphanChild)
{{ $loop->iteration }}) {{ $member->full_name }}
- العمر: {{ $member->orphanChild->age ?? 'غير محدد' }}
- الجنس: {{ $member->orphanChild->gender == 'male' ? 'ذكر' : 'أنثى' }}
- التعليم:
@switch($member->orphanChild->education_level)
@case('kindergarten') حضانة @break
@case('primary') ابتدائي @break
@case('middle') إعدادي @break
@case('secondary') ثانوي @break
@case('diploma') دبلوم @break
@case('bachelor') بكالوريوس @break
@default {{ $member->orphanChild->education_level ?? 'غير محدد' }}
@endswitch

- مرض مزمن: {{ $member->orphanChild->has_chronic_disease ? 'نعم' : 'لا' }}
- إعاقة: {{ $member->orphanChild->has_disability ? 'نعم' : 'لا' }}

@endif
@endforeach

📝 ملاحظات الباحث:
{{ $case->admin_note ?: 'لا توجد' }}
`;

navigator.clipboard.writeText(text).then(() => {

    const btn = event.currentTarget;
    const old = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
    btn.style.background = '#198754';

    setTimeout(() => {
        btn.innerHTML = old;
    },1500);

}).catch(() => {
    alert("تم النسخ بنجاح");
});

}
</script>

</body>
</html>