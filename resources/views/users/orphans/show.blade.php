<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض بيانات الأيتام - قيد المراجعة</title>
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Google Fonts - Tajawal -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
        <circle cx='50' cy='50' r='48' fill='%232d5a27'/>
        <path d='M70 65c-20 0-35-15-35-35 0-5 1-10 3-15-15 5-25 20-25 35 0 20 15 35 35 35 15 0 30-10 35-25-5 3-10 5-13 5z' fill='white'/>
        <polygon points='65,25 68,33 77,33 70,38 73,47 65,42 57,47 60,38 53,33 62,33' fill='%23ffc107'/>
    </svg>">
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

        .status-banner {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            border-radius: 30px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 15px 35px rgba(230, 126, 34, 0.2);
        }

        .status-banner h2 {
            font-weight: 900;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.8rem;
        }

        .status-badge {
            background: rgba(255,255,255,0.25);
            padding: 0.7rem 2rem;
            border-radius: 50px;
            font-weight: 800;
            font-size: 1.2rem;
            border: 2px solid white;
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

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 2rem 0;
        }

        .btn-print {
            background: linear-gradient(135deg, #2c3e50, #34495e);
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

        .btn-print:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(44,62,80,0.3);
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
            .action-buttons, .status-banner, .btn-back {
                display: none !important;
            }
            body { background: white; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
</head>
<body>
    <div class="main-container">

        <!-- شريط حالة المراجعة -->
        <div class="status-banner">
            <h2>
                <i class="fas fa-clock"></i>
                البيانات قيد المراجعة
            </h2>
            <div class="status-badge">
                <i class="fas fa-eye"></i>
                تحت المراجعة
            </div>
        </div>

        <!-- رسالة توضيحية -->
        <div class="alert alert-info" style="border-radius: 20px; padding: 1.5rem;">
            <i class="fas fa-info-circle fa-2x float-end ms-3"></i>
            <h4 style="font-weight: 800;">تم استلام بياناتك بنجاح</h4>
            <p class="mb-0" style="font-size: 1.1rem;">بياناتك قيد المراجعة من قبل الفريق المختص. يمكنك مراجعة البيانات المدخلة أدناه. سيتم إشعارك عند اكتمال المراجعة.</p>
        </div>

        <!-- بيانات Telegram -->
        <div class="card">
            <div class="card-header" style="background: #0088cc;">
                <i class="fab fa-telegram-plane"></i>
                البيانات المسجلة من تيليجرام
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-user"></i> الاسم الكامل</div>
                    <div class="info-value">{{ $telegramUser->full_name ?? 'غير محدد' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-id-card"></i> رقم الهوية</div>
                    <div class="info-value">{{ $telegramUser->national_id ?? 'غير محدد' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-phone"></i> رقم الهاتف</div>
                    <div class="info-value">{{ $telegramUser->egypt_number ?? 'غير محدد' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-map-marker-alt"></i> العنوان</div>
                    <div class="info-value">{{ $telegramUser->address ?? 'غير محدد' }}</div>
                </div>
            </div>
        </div>

        <!-- بيانات ولي الأمر -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-shield"></i>
                بيانات ولي الأمر
            </div>
            
            <!-- صورة ولي الأمر -->
            @if($guardianDetail && $guardianDetail->personal_photo)
            <div class="photo-gallery" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                <div class="photo-item">
                    <img src="{{ asset($guardianDetail->personal_photo) }}" alt="صورة ولي الأمر">
                    <span><i class="fas fa-camera"></i> الصورة الشخصية</span>
                </div>
            </div>
            @endif

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-calendar-alt"></i> تاريخ الميلاد</div>
                    <div class="info-value">{{ $guardianDetail->birth_date ? \Carbon\Carbon::parse($guardianDetail->birth_date)->format('Y-m-d') : 'غير محدد' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-birthday-cake"></i> العمر</div>
                    <div class="info-value">{{ $guardianDetail->age ?? 'غير محدد' }} سنة</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-people-arrows"></i> صلة القرابة</div>
                    <div class="info-value">{{ $guardianDetail->relationship_to_case ?? 'غير محدد' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-venus-mars"></i> الجنس</div>
                    <div class="info-value">{{ $guardianDetail->gender == 'male' ? 'ذكر' : 'أنثى' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-home"></i> الحالة السكنية</div>
                    <div class="info-value">{{ $guardianDetail->housing_status ?? 'غير محدد' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-money-bill-wave"></i> الدخل الشهري</div>
                    <div class="info-value">{{ number_format($guardianDetail->monthly_income, 2) }} ج.م</div>
                </div>
            </div>

            <!-- الموقع على الخريطة -->
            @if($guardianDetail && $guardianDetail->latitude && $guardianDetail->longitude)
            <div class="coordinates-box">
                <span><i class="fas fa-latitude"></i> خط العرض: {{ $guardianDetail->latitude }}</span>
                <span><i class="fas fa-longitude"></i> خط الطول: {{ $guardianDetail->longitude }}</span>
            </div>
            <div class="map-container">
                <div id="map"></div>
            </div>
            @endif

            <!-- الملفات المرفوعة -->
            <div class="info-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                @if($guardianDetail && $guardianDetail->id_document)
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-id-card"></i> الهوية</div>
                    <a href="{{ asset($guardianDetail->id_document) }}" target="_blank" class="file-download">
                        <i class="fas fa-download"></i> عرض الملف
                    </a>
                </div>
                @endif

                @if($guardianDetail && $guardianDetail->passport)
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-passport"></i> جواز السفر</div>
                    <a href="{{ asset($guardianDetail->passport) }}" target="_blank" class="file-download">
                        <i class="fas fa-download"></i> عرض الملف
                    </a>
                </div>
                @endif

                @if($guardianDetail && $guardianDetail->entry_stamp)
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-stamp"></i> ختم الدخول</div>
                    <a href="{{ asset($guardianDetail->entry_stamp) }}" target="_blank" class="file-download">
                        <i class="fas fa-download"></i> عرض الملف
                    </a>
                </div>
                @endif

                @if($guardianDetail && $guardianDetail->medical_reports)
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-file-medical"></i> التقارير الطبية</div>
                    <a href="{{ asset($guardianDetail->medical_reports) }}" target="_blank" class="file-download">
                        <i class="fas fa-download"></i> عرض الملف
                    </a>
                </div>
                @endif

                @if($guardianDetail && $guardianDetail->rent_contract)
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-file-contract"></i> عقد الإيجار</div>
                    <a href="{{ asset($guardianDetail->rent_contract) }}" target="_blank" class="file-download">
                        <i class="fas fa-download"></i> عرض الملف
                    </a>
                </div>
                @endif
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
                        @if($orphanCase->orphan_status == 'father')
                            وفاة الأب
                        @elseif($orphanCase->orphan_status == 'mother')
                            وفاة الأم
                        @else
                            وفاة الأب والأم
                        @endif
                    </div>
                </div>
                @if($orphanCase->death_certificate)
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-file-certificate"></i> شهادة الوفاة</div>
                    <a href="{{ asset($orphanCase->death_certificate) }}" target="_blank" class="file-download">
                        <i class="fas fa-download"></i> عرض الملف
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- بيانات الأيتام -->
        <div class="card">
            <div class="card-header bg-warning">
                <i class="fas fa-children"></i>
                بيانات الأيتام
            </div>
            <div class="card-body">
                @forelse($familyMembers as $index => $member)
                    @php
                        $orphanChild = $member->orphanChild;
                    @endphp
                    
                    @if($orphanChild)
                    <div class="member-card">
                        <div class="member-title">
                            <i class="fas fa-user"></i>
                            مرافق رقم {{ $loop->iteration }}: {{ $member->full_name }}
                            <span class="orphan-badge">
                                <i class="fas fa-heart"></i> يتيم
                            </span>
                        </div>

                        @if($orphanChild->personal_photo)
                        <div class="photo-gallery" style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); margin-bottom: 1.5rem;">
                            <div class="photo-item">
                                <img src="{{ asset($orphanChild->personal_photo) }}" alt="صورة اليتيم">
                                <span><i class="fas fa-camera"></i> الصورة الشخصية</span>
                            </div>
                        </div>
                        @endif

                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-calendar-alt"></i> تاريخ الميلاد</div>
                                <div class="info-value">{{ $orphanChild->birth_date ? \Carbon\Carbon::parse($orphanChild->birth_date)->format('Y-m-d') : 'غير محدد' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-birthday-cake"></i> العمر</div>
                                <div class="info-value">{{ $orphanChild->age }} سنة</div>
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
                                        @default {{ $orphanChild->education_level }}
                                    @endswitch
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-home"></i> يعيش مع ولي الأمر</div>
                                <div class="info-value">{{ $orphanChild->lives_with_guardian ? 'نعم' : 'لا' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-thermometer-half"></i> مرض مزمن</div>
                                <div class="info-value">{{ $orphanChild->has_chronic_disease ? 'نعم' : 'لا' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-wheelchair"></i> إعاقة</div>
                                <div class="info-value">{{ $orphanChild->has_disability ? 'نعم' : 'لا' }}</div>
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

        <!-- أزرار الإجراءات -->
        <div class="action-buttons">
            <button onclick="window.print()" class="btn-print">
                <i class="fas fa-print"></i>
                طباعة البيانات
            </button>
            <a href="{{ route('index') }}" class="btn-back">
                <i class="fas fa-arrow-right"></i>
                العودة للرئيسية
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
</body>
</html>