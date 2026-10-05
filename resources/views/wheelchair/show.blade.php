<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بيانات الطلب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- أيقونة الصفحة -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E♿%3C/text%3E%3C/svg%3E">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap');

        * {
            font-family: 'Tajawal', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 30%, #bfdbfe 70%, #93c5fd 100%);
            padding: 20px;
        }

        .main-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(30, 58, 138, 0.25);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .main-card:hover {
            transform: translateY(-3px);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            padding: 25px 30px;
            position: relative;
            overflow: hidden;
        }

        .card-header-custom::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .card-header-custom::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .header-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-title .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            font-size: 24px;
            color: white;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .header-title h4 {
            color: white;
            font-weight: 700;
            font-size: 24px;
            margin: 0;
        }

        .header-title h4 small {
            font-weight: 400;
            font-size: 14px;
            opacity: 0.8;
            display: block;
        }

        .header-status {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .badge-status {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .badge-status.pending {
            background: rgba(251, 191, 36, 0.3);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .badge-status.approved {
            background: rgba(52, 211, 153, 0.3);
            color: #34d399;
            border: 1px solid rgba(52, 211, 153, 0.3);
        }

        .badge-status.rejected {
            background: rgba(251, 113, 133, 0.3);
            color: #fb7185;
            border: 1px solid rgba(251, 113, 133, 0.3);
        }

        .card-body-custom {
            padding: 30px;
        }

        /* ===== الصورة الشخصية ===== */
        .profile-section {
            background: #f8fafc;
            border-radius: 20px;
            padding: 25px;
            border: 2px solid #dbeafe;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .profile-section:hover {
            border-color: #3b82f6;
            box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.15);
        }

        .profile-img {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #dbeafe;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px -8px rgba(30, 58, 138, 0.2);
        }

        .profile-img:hover {
            border-color: #3b82f6;
            transform: scale(1.02);
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 5px;
        }

        .profile-id {
            font-size: 16px;
            color: #6b7280;
        }

        .profile-id i {
            color: #3b82f6;
            margin-left: 8px;
        }

        /* ===== الجداول ===== */
        .section-title-custom {
            font-weight: 700;
            color: #1e3a8a;
            font-size: 18px;
            margin: 25px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dbeafe;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title-custom i {
            color: #3b82f6;
        }

        .table-custom {
            border-radius: 16px;
            overflow: hidden;
            border-collapse: separate;
            border-spacing: 0;
            border: 2px solid #dbeafe;
        }

        .table-custom th {
            background: #eff6ff;
            color: #1e3a8a;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 18px;
            width: 30%;
            border: 1px solid #dbeafe;
        }

        .table-custom td {
            padding: 12px 18px;
            color: #1e293b;
            font-size: 14px;
            border: 1px solid #dbeafe;
            background: white;
        }

        .table-custom td i {
            color: #3b82f6;
            margin-left: 8px;
        }

        .table-custom tr:hover td {
            background: #f8fafc;
        }

        .table-custom .label-icon {
            width: 20px;
            color: #3b82f6;
            margin-left: 8px;
        }

        /* ===== المرفقات ===== */
        .gallery-section {
            background: #f8fafc;
            border-radius: 20px;
            padding: 25px;
            border: 2px solid #dbeafe;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .gallery-section:hover {
            border-color: #3b82f6;
        }

        .gallery-item {
            text-align: center;
            transition: all 0.3s ease;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
        }

        .gallery-item .img-wrapper {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            border: 3px solid #dbeafe;
            transition: all 0.3s ease;
            cursor: pointer;
            background: white;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gallery-item .img-wrapper:hover {
            border-color: #3b82f6;
            box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.3);
        }

        .gallery-item .img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .gallery-item .img-wrapper:hover img {
            transform: scale(1.05);
        }

        .gallery-item .img-wrapper .overlay-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(30, 58, 138, 0.8);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            opacity: 0;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .gallery-item .img-wrapper:hover .overlay-icon {
            opacity: 1;
        }

        .gallery-item .label {
            margin-top: 8px;
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
        }

        .gallery-item .label i {
            color: #3b82f6;
            margin-left: 6px;
        }

        .btn-file {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            border: none;
            width: 100%;
            margin-top: 5px;
        }

        .btn-file:hover {
            transform: translateY(-2px);
        }

        .btn-file.pdf {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: white;
            box-shadow: 0 6px 20px -5px rgba(220, 38, 38, 0.3);
        }

        .btn-file.pdf:hover {
            box-shadow: 0 10px 30px -5px rgba(220, 38, 38, 0.4);
            color: white;
        }

        /* ===== نموذج القرار ===== */
        .decision-section {
            background: #eff6ff;
            border-radius: 20px;
            padding: 30px;
            border: 2px solid #bfdbfe;
            margin-top: 25px;
        }

        .decision-section .section-title-custom {
            border-bottom-color: #bfdbfe;
        }

        .form-control-custom {
            border-radius: 12px;
            border: 2px solid #dbeafe;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control-custom:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-select-custom {
            border-radius: 12px;
            border: 2px solid #dbeafe;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
            height: 50px;
        }

        .form-select-custom:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn-save {
            background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
            border: none;
            padding: 14px 40px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 18px;
            color: white;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px -5px rgba(34, 197, 94, 0.4);
            color: white;
        }

        .btn-save i {
            font-size: 18px;
        }

        .btn-back {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px -5px rgba(59, 130, 246, 0.4);
            color: white;
        }

        /* ===== استجابة ===== */
        @media (max-width: 992px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .profile-section .row {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .profile-info {
                align-items: center;
                margin-top: 15px;
            }

            .profile-img {
                width: 140px;
                height: 140px;
            }

            .profile-name {
                font-size: 24px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .card-body-custom {
                padding: 15px;
            }

            .card-header-custom {
                padding: 18px 20px;
            }

            .header-title h4 {
                font-size: 20px;
            }

            .header-title h4 small {
                font-size: 12px;
            }

            .header-title .icon-circle {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }

            .profile-img {
                width: 120px;
                height: 120px;
            }

            .profile-name {
                font-size: 20px;
            }

            .table-custom {
                font-size: 13px;
            }

            .table-custom th,
            .table-custom td {
                padding: 8px 12px;
                font-size: 13px;
            }

            .section-title-custom {
                font-size: 16px;
            }

            .gallery-section .row {
                gap: 15px;
            }

            .decision-section {
                padding: 20px;
            }

            .btn-save {
                width: 100%;
                justify-content: center;
                padding: 14px 20px;
                font-size: 16px;
            }

            .btn-back {
                width: 100%;
                justify-content: center;
            }

            .header-status {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .profile-img {
                width: 100px;
                height: 100px;
            }

            .profile-name {
                font-size: 18px;
            }

            .table-custom {
                font-size: 12px;
            }

            .table-custom th,
            .table-custom td {
                padding: 6px 10px;
                font-size: 12px;
            }

            .table-custom th {
                width: 40%;
            }

            .gallery-item .img-wrapper {
                aspect-ratio: 4/3;
            }

            .decision-section {
                padding: 15px;
            }

            .badge-status {
                font-size: 12px;
                padding: 5px 14px;
            }
        }
    </style>
</head>

<body>

    <div class="container mt-3 mb-4">
        <div class="main-card">

            <!-- Header -->
            <div class="card-header-custom">
                <div class="header-content">
                    <div class="header-title">
                        <span class="icon-circle">
                            <i class="fas fa-wheelchair"></i>
                        </span>
                        <h4>
                            بيانات الطلب
                            <small>
                                <i class="fas fa-file-alt me-1"></i>
                                عرض وتقييم طلب الكرسي الكهربائي
                            </small>
                        </h4>
                    </div>
                    <div class="header-status">
                        @if($request->status == 'pending')
                            <span class="badge-status pending">
                                <i class="fas fa-clock"></i> بانتظار المراجعة
                            </span>
                        @elseif($request->status == 'approved_first')
                            <span class="badge-status approved">
                                <i class="fas fa-check-circle"></i> معتمد من الدرجة الاولى
                            </span>
                        @elseif($request->status == 'approved_second')
                            <span class="badge-status approved">
                                <i class="fas fa-check-circle"></i> معتمد من الدرجة الثانية
                            </span>
                        @elseif($request->status == 'approved_third')
                            <span class="badge-status approved">
                                <i class="fas fa-check-circle"></i> معتمد من الدرجة الثالثة
                            </span>
                        @elseif($request->status == 'rejected_not_eligible')
                            <span class="badge-status rejected">
                                <i class="fas fa-times-circle"></i> مرفوض بسبب عدم الاستحقاق
                            </span>
                        @elseif($request->status == 'rejected_previous')
                            <span class="badge-status rejected">
                                <i class="fas fa-times-circle"></i> مرفوض بسبب الاستلام المسبق 
                            </span>
                        @endif
                        <a href="{{ route('wheelchair.index') }}" class="btn-back" style="padding:8px 18px; font-size:14px;">
                            <i class="fas fa-arrow-right"></i> العودة
                        </a>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="card-body-custom">

                <!-- الصورة الشخصية والمعلومات الأساسية -->
                <div class="profile-section">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            @if($request->personal_photo && file_exists(public_path($request->personal_photo)))
                                <img src="{{ asset($request->personal_photo) }}" class="profile-img" alt="{{ $request->full_name }}">
                            @else
                                <div class="profile-img" style="background:#dbeafe; display:flex; align-items:center; justify-content:center; font-size:60px; color:#3b82f6;">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9 profile-info">
                            <div class="profile-name">{{ $request->full_name }}</div>
                            <div class="profile-id">
                                <i class="fas fa-id-card"></i>
                                رقم الهوية: {{ $request->national_id }}
                            </div>
                            <div class="profile-id" style="font-size:14px; margin-top:5px;">
                                <i class="fas fa-calendar-alt"></i>
                                تاريخ الطلب: {{ $request->created_at->format('Y-m-d') }}
                            </div>
                            <div class="mt-2">
                                <span class="badge-status pending" style="font-size:12px; padding:4px 14px; background:rgba(59,130,246,0.15); color:#3b82f6; border-color:rgba(59,130,246,0.2);">
                                    <i class="fas fa-hashtag"></i>
                                    رقم الطلب: #{{ $request->id }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- المعلومات الشخصية -->
                <div class="section-title-custom">
                    <i class="fas fa-user-circle"></i>
                    المعلومات الشخصية
                </div>

                <table class="table table-custom">
                    <tbody>
                        <tr>
                            <th><i class="fas fa-user label-icon"></i> الاسم</th>
                            <td>{{ $request->full_name }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-id-card label-icon"></i> رقم الهوية</th>
                            <td>{{ $request->national_id }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-calendar label-icon"></i> العمر</th>
                            <td>{{ $request->age }} سنة</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-phone label-icon"></i> الهاتف</th>
                            <td dir="ltr">{{ $request->phone }}</td>
                        </tr>
                        <tr>
                            <th><i class="fab fa-whatsapp label-icon" style="color:#25D366;"></i> الواتساب</th>
                            <td dir="ltr">{{ $request->whatsapp ?? 'غير متوفر' }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-map-marker-alt label-icon"></i> العنوان</th>
                            <td>{{ $request->address }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-calendar-alt label-icon"></i> تاريخ دخول مصر</th>
                            <td>{{ $request->entry_date }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- المعلومات الطبية -->
                <div class="section-title-custom">
                    <i class="fas fa-heartbeat"></i>
                    المعلومات الطبية
                </div>

                <table class="table table-custom">
                    <tbody>
                        <tr>
                            <th><i class="fas fa-notes-medical label-icon"></i> شرح الحالة</th>
                            <td>{{ $request->medical_condition }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-exclamation-triangle label-icon"></i> سبب الإصابة</th>
                            <td>{{ $request->injury_reason }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-wheelchair label-icon"></i> هل يمتلك كرسي؟</th>
                            <td>
                                @if($request->has_wheelchair)
                                    <span class="badge-custom badge-approved"><i class="fas fa-check-circle"></i> نعم</span>
                                @else
                                    <span class="badge-custom badge-rejected"><i class="fas fa-times-circle"></i> لا</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-tools label-icon"></i> حالة الكرسي</th>
                            <td>{{ $request->wheelchair_status ?? 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-hand-holding-heart label-icon"></i> استلم من جمعية أخرى؟</th>
                            <td>
                                @if($request->received_before)
                                    <span class="badge-custom badge-approved"><i class="fas fa-check-circle"></i> نعم</span>
                                @else
                                    <span class="badge-custom badge-rejected"><i class="fas fa-times-circle"></i> لا</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-building label-icon"></i> اسم الجمعية</th>
                            <td>{{ $request->organization_name ?? 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-pencil label-icon"></i> الملاحظات</th>
                            <td>{{ $request->notes ?? 'لا توجد ملاحظات' }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- المرفقات -->
                <div class="section-title-custom">
                    <i class="fas fa-upload"></i>
                    المرفقات
                </div>

                <div class="gallery-section">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="gallery-item">
                                <div class="img-wrapper">
                                    <img src="{{ asset($request->disability_photo) }}" alt="صورة الحالة">
                                    <div class="overlay-icon">
                                        <i class="fas fa-search-plus"></i>
                                    </div>
                                </div>
                                <div class="label">
                                    <i class="fas fa-file-medical"></i> الحالة المرضية
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="gallery-item">
                                <div class="img-wrapper">
                                    <img src="{{ asset($request->passport_photo) }}" alt="صورة الجواز">
                                    <div class="overlay-icon">
                                        <i class="fas fa-search-plus"></i>
                                    </div>
                                </div>
                                <div class="label">
                                    <i class="fas fa-passport"></i> الجواز
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="gallery-item">
                                <div class="img-wrapper">
                                    <img src="{{ asset($request->entry_stamp_photo) }}" alt="صورة ختم الدخول">
                                    <div class="overlay-icon">
                                        <i class="fas fa-search-plus"></i>
                                    </div>
                                </div>
                                <div class="label">
                                    <i class="fas fa-stamp"></i> ختم الدخول
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            @if($request->medical_report)
                                <div class="gallery-item">
                                    <div class="img-wrapper" style="background:#f1f5f9; border-color:#dbeafe;">
                                        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; color:#dc2626; gap:5px;">
                                            <i class="fas fa-file-pdf" style="font-size:48px;"></i>
                                            <span style="font-size:12px; color:#6b7280;">تقرير طبي</span>
                                        </div>
                                        <div class="overlay-icon">
                                            <i class="fas fa-download"></i>
                                        </div>
                                    </div>
                                    <div class="label">
                                        <i class="fas fa-file-pdf" style="color:#dc2626;"></i> التقرير الطبي
                                    </div>
                                    <a href="{{ asset($request->medical_report) }}" target="_blank" class="btn-file pdf">
                                        <i class="fas fa-external-link-alt"></i> فتح التقرير
                                    </a>
                                </div>
                            @else
                                <div class="gallery-item">
                                    <div class="img-wrapper" style="background:#f1f5f9; border-color:#e2e8f0; opacity:0.6;">
                                        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; color:#94a3b8; gap:5px;">
                                            <i class="fas fa-file" style="font-size:48px;"></i>
                                            <span style="font-size:12px;">لا يوجد</span>
                                        </div>
                                    </div>
                                    <div class="label" style="color:#94a3b8;">
                                        <i class="fas fa-file"></i> التقرير الطبي
                                    </div>
                                    <span style="font-size:13px; color:#94a3b8;">غير مرفق</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- نموذج القرار -->
                <div class="decision-section">
                    <div class="section-title-custom">
                        <i class="fas fa-gavel"></i>
                        قرار الإدارة
                    </div>

                    <form method="POST" action="{{ route('wheelchair.review', $request->id) }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">

                                <label class="form-label" style="font-weight:600; color:#1e293b;">
                                    <i class="fas fa-tag" style="color:#3b82f6;"></i>
                                    الحالة
                                </label>

                                <select name="status" class="form-select form-select-custom">

                                    <option value="pending"
                                        {{ $request->status == 'pending' ? 'selected' : '' }}>
                                        قيد الانتظار
                                    </option>

                                    <option value="approved_first"
                                        {{ $request->status == 'approved_first' ? 'selected' : '' }}>
                                        معتمد من الدرجة الأولى
                                    </option>

                                    <option value="approved_second"
                                        {{ $request->status == 'approved_second' ? 'selected' : '' }}>
                                        معتمد من الدرجة الثانية
                                    </option>

                                    <option value="approved_third"
                                        {{ $request->status == 'approved_third' ? 'selected' : '' }}>
                                        معتمد من الدرجة الثالثة
                                    </option>

                                    <option value="rejected_previous"
                                        {{ $request->status == 'rejected_previous' ? 'selected' : '' }}>
                                        مرفوض بسبب الاستلام المسبق
                                    </option>

                                    <option value="rejected_not_eligible"
                                        {{ $request->status == 'rejected_not_eligible' ? 'selected' : '' }}>
                                        مرفوض بسبب عدم الاستحقاق
                                    </option>

                                </select>

                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight:600; color:#1e293b;">
                                    <i class="fas fa-wheelchair" style="color:#3b82f6;"></i>
                                    هل تم تسليم كرسي؟
                                </label>
                                <select name="received_wheelchair" class="form-select form-select-custom">
                                    <option value="0" {{ $request->received_wheelchair == 0 ? 'selected' : '' }}>لا</option>
                                    <option value="1" {{ $request->received_wheelchair == 1 ? 'selected' : '' }}>نعم</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" style="font-weight:600; color:#1e293b;">
                                    <i class="fas fa-comment-dots" style="color:#3b82f6;"></i>
                                    سبب الرفض (اختياري)
                                </label>
                                <textarea name="rejection_reason" class="form-control form-control-custom" rows="3" placeholder="اكتب سبب الرفض إن وجد...">{{ $request->rejection_reason }}</textarea>
                            </div>

                            <div class="col-md-12 text-center mt-3">
                                <button class="btn-save">
                                    <i class="fas fa-save"></i>
                                    حفظ القرار
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>

</body>

</html>