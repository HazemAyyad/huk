<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>📸 إدارة طلبات المصورين</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><defs><linearGradient id='grad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'><stop offset='0%25' style='stop-color:%23667eea;stop-opacity:1' /><stop offset='100%25' style='stop-color:%23764ba2;stop-opacity:1' /></linearGradient></defs><circle cx='50' cy='50' r='48' fill='url(%23grad)'/><rect x='25' y='35' width='50' height='35' rx='5' fill='white'/><circle cx='50' cy='52.5' r='12' fill='url(%23grad)'/><circle cx='50' cy='52.5' r='5' fill='white'/><rect x='30' y='30' width='15' height='5' rx='2' fill='white'/><circle cx='68' cy='42' r='3' fill='%23f6ad55'/></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 10px;
        }

        .container {
            max-width: 1400px;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            padding: 20px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h2 {
            color: #2d3748;
            font-size: clamp(20px, 5vw, 28px);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .search-box {
            background: white;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        .search-grid {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .input-group {
            flex: 1;
            min-width: 200px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #4a5568;
            font-weight: 600;
            font-size: 14px;
        }

        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            background: #f8fafd;
        }

        .input-group input:focus, .input-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .btn-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        /* تصميم البطاقات للهواتف */
        .cards-view {
            display: none;
        }

        .table-view {
            display: block;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        th {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            padding: 15px 12px;
            font-size: 14px;
            white-space: nowrap;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #edf2f7;
            color: #2d3748;
            font-size: 14px;
        }

        tr:hover {
            background: #f7fafc;
        }

        .img-thumbnail {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-action {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        }

        .btn-action:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-view {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        }

        .badge {
            padding: 5px 12px;
            border-radius: 25px;
            font-size: 11px;
            font-weight: bold;
            color: white;
            display: inline-block;
        }

        .badge-expert {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        }

        .badge-beginner {
            background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
        }

        .pagination {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 5px;
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 8px 15px;
            border-radius: 25px;
            background: white;
            color: #4a5568;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .pagination .active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        /* تصميم متجاوب للهواتف */
        @media screen and (max-width: 768px) {
            .table-view {
                display: none;
            }

            .cards-view {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            .applicant-card {
                background: white;
                border-radius: 16px;
                padding: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                border: 1px solid #edf2f7;
                transition: transform 0.3s;
            }

            .applicant-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
            }

            .card-header {
                display: flex;
                gap: 15px;
                margin-bottom: 15px;
                padding-bottom: 15px;
                border-bottom: 2px solid #f0f4f8;
            }

            .card-header .img-thumbnail {
                width: 70px;
                height: 70px;
                border-width: 3px;
            }

            .card-title {
                flex: 1;
            }

            .card-title h3 {
                font-size: 18px;
                color: #2d3748;
                margin-bottom: 5px;
            }

            .card-title .national-id {
                color: #718096;
                font-size: 13px;
                font-family: monospace;
                background: #f7fafc;
                padding: 3px 8px;
                border-radius: 5px;
                display: inline-block;
            }

            .card-details {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                margin-bottom: 15px;
            }

            .detail-item {
                display: flex;
                flex-direction: column;
            }

            .detail-label {
                font-size: 11px;
                color: #a0aec0;
                margin-bottom: 3px;
            }

            .detail-value {
                font-size: 14px;
                color: #2d3748;
                font-weight: 600;
                word-break: break-word;
            }

            .card-actions {
                display: flex;
                gap: 10px;
                justify-content: space-between;
            }

            .card-actions .btn-action {
                flex: 1;
                justify-content: center;
                padding: 10px;
                font-size: 13px;
            }

            .search-grid {
                flex-direction: column;
                gap: 10px;
            }

            .input-group {
                width: 100%;
            }

            .btn-search {
                width: 100%;
                justify-content: center;
            }
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background: #f8fafd;
            border-radius: 16px;
        }

        .empty-state i {
            font-size: 50px;
            color: #a0aec0;
            margin-bottom: 15px;
        }

        .empty-state p {
            color: #718096;
            font-size: 16px;
        }

        /* تحسين التمرير */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>
            <i class="fas fa-camera-retro"></i>
            إدارة طلبات المصورين
        </h2>
        <div class="stats-card">
            <i class="fas fa-users"></i>
           إجمالي الطلبات: {{ $applications->total() }}
        </div>
    </div>

    <form method="GET" class="search-box">
        <div class="search-grid">
            <div class="input-group">
                <label><i class="fas fa-id-card"></i> رقم الهوية</label>
                <input type="text" name="national_id" placeholder="أدخل رقم الهوية" value="{{ request('national_id') }}">
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-camera"></i> نوع المعدة</label>
                <select name="camera_equipment">
                    <option value="">جميع المعدات</option>
                    <option value="كاميرا احترافية" {{ request('camera_equipment') == 'كاميرا احترافية' ? 'selected' : '' }}>كاميرا احترافية</option>
                    <option value="هاتف ايفون 17 pro max" {{ request('camera_equipment') == 'هاتف ايفون 17 pro max' ? 'selected' : '' }}>هاتف ايفون 17 برو ماكس</option>
                    <option value="هاتف ايفون 16 pro max" {{ request('camera_equipment') == 'هاتف ايفون 16 pro max' ? 'selected' : '' }}>هاتف ايفون 16 برو ماكس</option>
                    <option value="هاتف ايفون 15 pro max" {{ request('camera_equipment') == 'هاتف ايفون 15 pro max' ? 'selected' : '' }}>هاتف ايفون 15 برو ماكس</option>
                    <option value="هاتف سامسونج 25 الترا" {{ request('camera_equipment') == 'هاتف سامسونج 25 الترا' ? 'selected' : '' }}>سامسونج S25 الترا</option>
                    <option value="هاتف سامسونج 24 الترا" {{ request('camera_equipment') == 'هاتف سامسونج 24 الترا' ? 'selected' : '' }}>سامسونج S24 الترا</option>
                    <option value="هاتف سامسونج 23 الترا" {{ request('camera_equipment') == 'هاتف سامسونج 23 الترا' ? 'selected' : '' }}>سامسونج S23 الترا</option>
                    <option value="other">أخرى</option>
                </select>
            </div>

            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i>
                بحث وتصفية
            </button>
        </div>
    </form>

    <!-- عرض الجدول للشاشات الكبيرة -->
    <div class="table-view">
        <table>
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>الاسم الكامل</th>
                    <th>رقم الهوية</th>
                    <th>العمر</th>
                    <th>نوع المعدة</th>
                    <th>الجوال</th>
                    <th>المحافظة</th>
                    <th>الجواز</th>
                    <th>الخبرة</th>
                    <th>التفاصيل</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications ?? [] as $app)
                <tr>
                    <td>
                        @if($app->personal_photo ?? false)
                            <img src="{{ asset($app->personal_photo) }}" class="img-thumbnail">
                        @else
                            <div class="img-thumbnail" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </td>
                    <td><strong>{{ $app->full_name ?? 'لا يوجد' }}</strong></td>
                    <td><code>{{ $app->national_id ?? 'لا يوجد' }}</code></td>
                    <td>{{ $app->age ?? 'لا يوجد' }} سنة</td>
                    <td>{{ $app->camera_equipment ?? 'لا يوجد' }}</td>
                    <td>{{ $app->phone_egypt ?? 'لا يوجد' }}</td>
                    <td>{{ $app->governorate ?? 'لا يوجد' }}</td>
                    <td>
                        <button class="btn-action" onclick='showPassport("{{ asset($app->passport ?? '') }}", "{{ $app->full_name ?? 'المتقدم' }}")'>
                            <i class="fas fa-passport"></i>
                            عرض
                        </button>
                    </td>
                    <td>
                        <span class="badge {{ ($app->has_experience ?? false) ? 'badge-expert' : 'badge-beginner' }}">
                            {{ ($app->has_experience ?? false) ? '✅ خبير' : '❌ مبتدئ' }}
                        </span>
                    </td>
                    <td>
                        <button class="btn-action btn-view" onclick='showDetails(
                            "{{ $app->full_name ?? 'لا يوجد' }}",
                            "{{ $app->national_id ?? 'لا يوجد' }}",
                            "{{ $app->age ?? 'لا يوجد' }}",
                            "{{ $app->camera_equipment ?? 'لا يوجد' }}",
                            "{{ $app->phone_egypt ?? 'لا يوجد' }}",
                            "{{ $app->phone_egypt_2 ?? 'لا يوجد' }}",
                            "{{ $app->whatsapp_palestine ?? 'لا يوجد' }}",
                            "{{ $app->governorate ?? 'لا يوجد' }}",
                            "{{ $app->address ?? 'لا يوجد' }}",
                            "{{ $app->education ?? 'لا يوجد' }}",
                            "{{ $app->work_in_gaza ?? 'لا يوجد' }}",
                            "{{ $app->family_in_egypt ?? 'لا يوجد' }}",
                            "{{ $app->family_in_gaza ?? 'لا يوجد' }}",
                            "{{ $app->entry_reason ?? 'لا يوجد' }}",
                            "{{ $app->entry_date ?? 'لا يوجد' }}",
                            "{{ $app->experience_details ?? 'لا يوجد' }}",
                            "{{ $app->case_details ?? 'لا يوجد' }}"
                        )'>
                            <i class="fas fa-eye"></i>
                            عرض
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>لا يوجد طلبات مطابقة للبحث حالياً</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- عرض البطاقات للهواتف -->
    <div class="cards-view">
        @forelse($applications ?? [] as $app)
        <div class="applicant-card">
            <div class="card-header">
                @if($app->personal_photo ?? false)
                    <img src="{{ asset($app->personal_photo) }}" class="img-thumbnail">
                @else
                    <div class="img-thumbnail" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-user fa-2x"></i>
                    </div>
                @endif
                <div class="card-title">
                    <h3>{{ $app->full_name ?? 'غير متوفر' }}</h3>
                    <span class="national-id">{{ $app->national_id ?? 'غير متوفر' }}</span>
                </div>
            </div>

            <div class="card-details">
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-calendar"></i> العمر</span>
                    <span class="detail-value">{{ $app->age ?? 'غير متوفر' }} سنة</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-camera"></i> المعدة</span>
                    <span class="detail-value">{{ $app->camera_equipment ?? 'غير متوفر' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-phone"></i> الجوال</span>
                    <span class="detail-value">{{ $app->phone_egypt ?? 'غير متوفر' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-map-marker-alt"></i> المحافظة</span>
                    <span class="detail-value">{{ $app->governorate ?? 'غير متوفر' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-star"></i> الخبرة</span>
                    <span class="detail-value">
                        <span class="badge {{ ($app->has_experience ?? false) ? 'badge-expert' : 'badge-beginner' }}">
                            {{ ($app->has_experience ?? false) ? 'خبير' : 'مبتدئ' }}
                        </span>
                    </span>
                </div>
            </div>

            <div class="card-actions">
                <button class="btn-action" onclick='showPassport("{{ asset($app->passport ?? '') }}", "{{ $app->full_name ?? 'المتقدم' }}")'>
                    <i class="fas fa-passport"></i>
                    الجواز
                </button>
                <button class="btn-action btn-view" onclick='showDetails(
                    "{{ $app->full_name ?? 'غير متوفر' }}",
                    "{{ $app->national_id ?? 'غير متوفر' }}",
                    "{{ $app->age ?? 'غير متوفر' }}",
                    "{{ $app->camera_equipment ?? 'غير متوفر' }}",
                    "{{ $app->phone_egypt ?? 'غير متوفر' }}",
                    "{{ $app->phone_egypt_2 ?? 'غير متوفر' }}",
                    "{{ $app->whatsapp_palestine ?? 'غير متوفر' }}",
                    "{{ $app->governorate ?? 'غير متوفر' }}",
                    "{{ $app->address ?? 'غير متوفر' }}",
                    "{{ $app->education ?? 'غير متوفر' }}",
                    "{{ $app->work_in_gaza ?? 'غير متوفر' }}",
                    "{{ $app->family_in_egypt ?? 'غير متوفر' }}",
                    "{{ $app->family_in_gaza ?? 'غير متوفر' }}",
                    "{{ $app->entry_reason ?? 'غير متوفر' }}",
                    "{{ $app->entry_date ?? 'غير متوفر' }}",
                    "{{ $app->experience_details ?? 'غير متوفر' }}",
                    "{{ $app->case_details ?? 'غير متوفر' }}"
                )'>
                    <i class="fas fa-eye"></i>
                    التفاصيل
                </button>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>لا يوجد طلبات مطابقة للبحث حالياً</p>
        </div>
        @endforelse
    </div>

    <!-- روابط التصفح -->
    <div class="pagination">
        @if(isset($applications))
            {{ $applications->appends(request()->query())->links() }}
        @else
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#">4</a>
            <a href="#">التالي</a>
        @endif
    </div>
</div>

<script>
// دالة عرض صورة الجواز
function showPassport(url, name) {
    Swal.fire({
        title: '📄 صورة جواز السفر',
        html: `<p style="font-size:16px; margin-bottom:15px;">صاحب الطلب: <strong>${name}</strong></p>`,
        imageUrl: url,
        imageWidth: 600,
        imageAlt: 'Passport Image',
        confirmButtonText: 'إغلاق',
        confirmButtonColor: '#667eea',
        showCloseButton: true,
        backdrop: `rgba(102, 126, 234, 0.4)`
    });
}

// دالة عرض التفاصيل الكاملة
function showDetails(
    fullName, nationalId, age, equipment, phoneEgypt, phoneEgypt2, whatsapp,
    governorate, address, education, workInGaza, familyEgypt, familyGaza,
    entryReason, entryDate, experienceDetails, caseDetails
) {
    Swal.fire({
        title: '📋 تفاصيل الطلب الكاملة',
        html: `
            <div style="text-align:right; font-size:14px; max-height: 70vh; overflow-y: auto;">
                <div style="background: linear-gradient(135deg, #667eea10 0%, #764ba210 100%); padding:15px; border-radius:12px; margin-bottom:15px;">
                    <h4 style="color:#667eea; margin-bottom:10px;">👤 معلومات شخصية</h4>
                    <p><b>الاسم:</b> ${fullName}</p>
                    <p><b>رقم الهوية:</b> ${nationalId}</p>
                    <p><b>العمر:</b> ${age} سنة</p>
                    <p><b>نوع المعدة:</b> ${equipment}</p>
                </div>

                <div style="background: #f8fafd; padding:15px; border-radius:12px; margin-bottom:15px;">
                    <h4 style="color:#764ba2; margin-bottom:10px;">📞 معلومات الاتصال</h4>
                    <p><b>هاتف مصر:</b> ${phoneEgypt}</p>
                    <p><b>هاتف احتياطي:</b> ${phoneEgypt2 || 'لا يوجد'}</p>
                    <p><b>واتساب فلسطين:</b> ${whatsapp}</p>
                </div>

                <div style="background: linear-gradient(135deg, #764ba210 0%, #667eea10 100%); padding:15px; border-radius:12px; margin-bottom:15px;">
                    <h4 style="color:#667eea; margin-bottom:10px;">📍 العنوان</h4>
                    <p><b>المحافظة:</b> ${governorate}</p>
                    <p><b>العنوان:</b> ${address}</p>
                    <p><b>التعليم:</b> ${education}</p>
                    <p><b>العمل في غزة:</b> ${workInGaza}</p>
                </div>

                <div style="background: #f8fafd; padding:15px; border-radius:12px; margin-bottom:15px;">
                    <h4 style="color:#764ba2; margin-bottom:10px;">👪 معلومات العائلة</h4>
                    <p><b>عدد العائلة في مصر:</b> ${familyEgypt}</p>
                    <p><b>عدد العائلة في غزة:</b> ${familyGaza}</p>
                </div>

                <div style="background: linear-gradient(135deg, #667eea10 0%, #764ba210 100%); padding:15px; border-radius:12px; margin-bottom:15px;">
                    <h4 style="color:#667eea; margin-bottom:10px;">📝 تفاصيل الدخول</h4>
                    <p><b>سبب الدخول:</b> ${entryReason}</p>
                    <p><b>تاريخ الدخول:</b> ${entryDate}</p>
                </div>

                <div style="background: #f8fafd; padding:15px; border-radius:12px; margin-bottom:15px;">
                    <h4 style="color:#764ba2; margin-bottom:10px;">💼 الخبرة</h4>
                    <p>${experienceDetails || 'لا توجد خبرة سابقة'}</p>
                </div>

                <div style="background: #fff; padding:15px; border-radius:12px; border:1px solid #e2e8f0;">
                    <h4 style="color:#667eea; margin-bottom:10px;">📋 تفاصيل الحالة</h4>
                    <p>${caseDetails || 'لا توجد تفاصيل إضافية'}</p>
                </div>
            </div>
        `,
        width: 800,
        confirmButtonText: 'إغلاق',
        confirmButtonColor: '#667eea',
        showCloseButton: true,
        showClass: {
            popup: 'animate__animated animate__fadeInUp'
        }
    });
}

// إضافة بيانات تجريبية للعرض (للتجربة فقط)
document.addEventListener('DOMContentLoaded', function() {
    // يمكن إضافة أي كود إضافي هنا إذا لزم الأمر
});
</script>

<!-- إضافة مكتبة للأنيميشن -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

</body>
</html>