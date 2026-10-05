<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تفاصيل حالة البتر · صورة كاملة + إطار معتمد</title>
  <!-- Bootstrap RTL + Icons + Tajawal -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
  <!-- Leaflet for map -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Tajawal', 'Segoe UI', sans-serif;
      background: linear-gradient(145deg, #f0f6fc, #dce8f2);
      min-height: 100vh;
      padding: 1.5rem;
    }
    .main-container {
      max-width: 1400px;
      margin: 0 auto;
    }

    /* ======== إطار الحالة ======== */
    .case-wrapper {
      border-radius: 40px;
      padding: 6px;
      background: transparent;
      transition: all 0.2s ease;
    }
    .case-wrapper.approved {
      background: #2b8c5e;
      box-shadow: 0 0 0 3px #1f7a4a, 0 12px 30px rgba(33, 138, 80, 0.25);
    }
    .case-inner {
      background: #ffffffdd;
      backdrop-filter: blur(2px);
      border-radius: 36px;
      padding: 1.5rem 1.8rem 1.8rem 1.8rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.04);
      border: 1px solid rgba(255,255,255,0.7);
    }

    /* ======== BANNER مع الصورة الكاملة ======== */
    .hero-banner {
      display: flex;
      flex-wrap: wrap;
      align-items: stretch;
      gap: 1.8rem;
      background: linear-gradient(135deg, #1a3f54, #0e6a8a);
      border-radius: 32px;
      padding: 1.2rem 1.8rem;
      box-shadow: 0 12px 28px rgba(0, 40, 60, 0.25);
      border: 1px solid rgba(255,255,255,0.08);
    }
    .hero-info {
      flex: 2 1 280px;
      color: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .hero-info h2 {
      font-weight: 900;
      font-size: 1.8rem;
      letter-spacing: -0.5px;
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .hero-info h2 i { color: #b3e6ff; }
    .hero-badge {
      background: rgba(255,255,255,0.12);
      backdrop-filter: blur(4px);
      padding: 0.4rem 1.8rem;
      border-radius: 60px;
      font-weight: 700;
      display: inline-block;
      border: 1px solid rgba(255,255,255,0.15);
      margin-top: 8px;
      align-self: flex-start;
    }
    .hero-image {
      flex: 0 0 200px;
      max-width: 220px;
      min-width: 140px;
    }
    .hero-image img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 28px;
      border: 4px solid rgba(255,255,255,0.6);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      background: #d9e6f0;
    }
    .hero-image .empty-photo {
      width: 100%;
      height: 180px;
      background: #2f5b72;
      border-radius: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #b3d9ee;
      font-size: 3rem;
      border: 3px dashed rgba(255,255,255,0.3);
    }

    /* ======== بقية البطاقات ======== */
    .section-card {
      background: #ffffffdd;
      backdrop-filter: blur(2px);
      border-radius: 30px;
      margin-top: 28px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
      overflow: hidden;
      border: 1px solid rgba(255,255,255,0.7);
    }
    .card-header-custom {
      padding: 0.9rem 1.8rem;
      font-weight: 800;
      font-size: 1.15rem;
      display: flex;
      align-items: center;
      gap: 12px;
      border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    .card-header-primary { background: linear-gradient(135deg, #0f4b66, #147a9e); color: white; }
    .card-header-warning { background: linear-gradient(135deg, #b9690e, #d9941c); color: white; }
    .card-header-info { background: linear-gradient(135deg, #1f6f8f, #3498b9); color: white; }
    .card-header-secondary { background: #eef3f7; color: #1a3b4e; }
    .card-header-dark { background: #2d4055; color: white; }

    .grid-2col, .grid-3col {
      display: grid;
      gap: 14px;
      padding: 1.8rem;
    }
    .grid-2col { grid-template-columns: repeat(auto-fit, minmax(210px,1fr)); }
    .grid-3col { grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); }

    .info-item {
      background: #f8faff;
      padding: 0.8rem 1.2rem;
      border-radius: 18px;
      display: flex;
      align-items: center;
      gap: 12px;
      border: 1px solid #e9f0f6;
      transition: 0.1s;
    }
    .info-item i {
      width: 26px;
      color: #1f7b9e;
      font-size: 1.1rem;
      text-align: center;
    }
    .info-item .label { font-weight: 500; color: #2f4a5e; margin-left: 6px; }
    .info-item .value { font-weight: 700; color: #0a2b3b; word-break: break-word; }

    .photo-block, .file-box {
      background: #f8faff;
      border-radius: 24px;
      padding: 1.2rem;
      border: 1px solid #e6eff6;
      transition: 0.2s;
      height: 100%;
    }
.photo-block {
    background: #f8faff;
    border-radius: 24px;
    padding: 1.2rem;
    border: 1px solid #e6eff6;
    text-align: center;
}

.photo-block img {
    width: 100%;
    max-height: 700px;
    object-fit: contain;
    border-radius: 18px;
}
    .file-box .file-label {
      font-weight: 600;
      color: #1a4055;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }
    .map-box { padding: 1.8rem; }
    #map { height: 300px; border-radius: 28px; border: 3px solid white; box-shadow: 0 6px 18px rgba(0,0,0,0.04); }

    .action-area {
      background: #ffffffcc;
      backdrop-filter: blur(6px);
      border-radius: 40px;
      padding: 1.8rem 2.2rem;
      margin-top: 30px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.04);
      border: 1px solid rgba(255,255,255,0.8);
    }
    .btn-soft {
      border-radius: 60px;
      padding: 0.6rem 1.8rem;
      font-weight: 700;
      transition: 0.15s;
      border: none;
    }
    .btn-soft-success { background: #1f9d7a; color: white; }
    .btn-soft-success:hover { background: #158263; color: white; transform: scale(1.02); }
    .btn-soft-danger { background: #c74a4a; color: white; }
    .btn-soft-danger:hover { background: #a83333; color: white; transform: scale(1.02); }
    .btn-soft-dark { background: #2c3e50; color: white; }
    .btn-soft-dark:hover { background: #1b2b3a; color: white; }
    .btn-outline-secondary-custom { border: 2px solid #b0c8d9; color: #1f4a62; background: transparent; border-radius: 60px; padding: 0.5rem 1.8rem; }
    .btn-outline-secondary-custom:hover { background: #e1edf7; border-color: #7fa3bd; }

    .form-control-soft {
      border-radius: 60px;
      border: 1px solid #d7e3ed;
      padding: 0.7rem 1.5rem;
      background: #f7fcff;
    }
    .form-control-soft:focus { border-color: #3b8db0; box-shadow: 0 0 0 3px rgba(33, 133, 178, 0.1); }

    @media (max-width: 680px) {
      .hero-banner { flex-direction: column-reverse; align-items: center; text-align: center; }
      .hero-info h2 { justify-content: center; flex-wrap: wrap; }
      .hero-badge { align-self: center; }
      .hero-image { flex: 0 0 auto; max-width: 180px; }
      .case-inner { padding: 1rem; }
    }
  </style>
</head>
<body>
<div class="main-container">

  <!-- ====== الإطار الخارجي (أخضر للمعتمد) ====== -->
  @php
    $isApproved = isset($case->status) && $case->status === 'approved';
  @endphp
  <div class="case-wrapper {{ $isApproved ? 'approved' : '' }}">
  <div class="case-inner">

    <!-- ====== HERO: الصورة الكاملة + البيانات ====== -->
    <div class="hero-banner">
      <div class="hero-info">
        <h2><i class="fas fa-wheelchair"></i> تفاصيل حالة البتر</h2>
        <div style="display: flex; flex-wrap: wrap; gap: 8px 18px; margin-top: 6px;">
          <span><i class="fas fa-user"></i> {{ $case->full_name ?? 'غير محدد' }}</span>
          <span><i class="fas fa-id-card"></i> {{ $case->national_id ?? '—' }}</span>
          <span><i class="fas fa-calendar-alt"></i> العمر: {{ $case->age ?? '-' }}</span>
        </div>
        <div class="hero-badge">
          <i class="fas fa-circle" style="color: #a3d8f0; font-size: 0.6rem; vertical-align: middle;"></i>
          {{ $case->status ?? 'قيد المراجعة' }}
          @if($isApproved) <i class="fas fa-check-circle" style="color: #b3f0c0; margin-right: 6px;"></i> @endif
        </div>
      </div>
      <div class="hero-image">
        @if(isset($case->person_image) && $case->person_image)
          <img src="{{ asset($case->person_image) }}" alt="صورة الشخص">
        @else
          <div class="empty-photo"><i class="fas fa-user-circle"></i></div>
        @endif
        <!-- زر تحديث الصورة (يظهر أسفل الصورة) -->
        <form action="{{ route('admin.amputations.updateImage', $case->id) }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 4px; margin-top: 10px; flex-wrap: wrap; justify-content: center;">
          @csrf
          <input type="hidden" name="type" value="person_image">
          <input type="file" name="image" class="form-control form-control-sm" style="flex:1; min-width:100px; border-radius:40px;" required>
          <button type="submit" class="btn btn-light btn-sm" style="border-radius:40px;"><i class="fas fa-upload"></i> تغيير</button>
        </form>
      </div>
    </div>

    <!-- ======== البيانات الأساسية ======== -->
    <div class="section-card">
      <div class="card-header-custom card-header-primary">
        <i class="fas fa-user-circle"></i> البيانات الأساسية
      </div>
      <div class="grid-2col">
        <div class="info-item"><i class="fas fa-user"></i><span><span class="label">الاسم:</span> <span class="value">{{ $case->full_name ?? '—' }}</span></span></div>
        <div class="info-item"><i class="fas fa-id-card"></i><span><span class="label">الهوية:</span> <span class="value">{{ $case->national_id ?? '—' }}</span></span></div>
        <div class="info-item"><i class="fas fa-calendar-alt"></i><span><span class="label">العمر:</span> <span class="value">{{ $case->age ?? '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-venus-mars"></i><span><span class="label">الجنس:</span> <span class="value">{{ $case->gender ?? '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-ring"></i><span><span class="label">الحالة الاجتماعية:</span> <span class="value">{{ $case->marital_status ?? '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-home"></i><span><span class="label">السكن:</span> <span class="value">{{ $case->housing_status ?? '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-money-bill-wave"></i><span><span class="label">الدخل:</span> <span class="value">{{ $case->rent_value ?? '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-coins"></i><span><span class="label">يوجد دخل:</span> <span class="value">{{ isset($case->has_income) ? ($case->has_income ? 'نعم' : 'لا') : '-' }}</span></span></div>
      </div>
    </div>

    <!-- ======== تفاصيل البتر ======== -->
    <div class="section-card">
      <div class="card-header-custom card-header-warning">
        <i class="fas fa-cut"></i> تفاصيل البتر
      </div>
      <div class="grid-2col">
        <div class="info-item"><i class="fas fa-procedures"></i><span><span class="label">نوع البتر:</span> <span class="value">{{ $case->amputation_type ?? '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-calendar-day"></i><span><span class="label">تاريخ البتر:</span> <span class="value">{{ optional($case->amputation_date)->format('Y-m-d') ?? '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-exclamation-triangle"></i><span><span class="label">سبب الإصابة:</span> <span class="value">{{ $case->injury_reason ?? '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-robot"></i><span><span class="label">يحتاج طرف صناعي:</span> <span class="value">{{ isset($case->needs_prosthetic) ? ($case->needs_prosthetic ? 'نعم' : 'لا') : '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-hand-holding-heart"></i><span><span class="label">تمت مساعدته:</span> <span class="value">{{ isset($case->received_help) ? ($case->received_help ? 'نعم' : 'لا') : '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-gift"></i><span><span class="label">نوع المساعدة:</span> <span class="value">{{ $case->help_type ?? '-' }}</span></span></div>
        <div class="info-item" style="grid-column: 1 / -1;"><i class="fas fa-notes-medical"></i><span><span class="label">الوصف الصحي:</span> <span class="value">{{ $case->health_description ?? '-' }}</span></span></div>
      </div>
    </div>

    <!-- ======== المرافق ======== -->
    @if(isset($case->needs_companion) && $case->needs_companion)
    <div class="section-card">
      <div class="card-header-custom card-header-info">
        <i class="fas fa-user-friends"></i> بيانات المرافق
      </div>
      <div class="grid-2col">
        <div class="info-item"><i class="fas fa-user"></i><span><span class="label">الاسم:</span> <span class="value">{{ $case->companion_full_name ?? '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-id-card"></i><span><span class="label">الهوية:</span> <span class="value">{{ $case->companion_national_id ?? '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-handshake"></i><span><span class="label">العلاقة:</span> <span class="value">{{ $case->companion_relationship ?? '-' }}</span></span></div>
        <div class="info-item"><i class="fas fa-birthday-cake"></i><span><span class="label">تاريخ الميلاد:</span> <span class="value">{{ optional($case->companion_birth_date)->format('Y-m-d') ?? '-' }}</span></span></div>
      </div>
    </div>
    @endif

    <!-- ======== الصور الإضافية (صورة البتر) ======== -->
    <div class="section-card">
      <div class="card-header-custom card-header-secondary">
        <i class="fas fa-images"></i> صور إضافية
      </div>
      <div style="padding: 1.8rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
        @if(isset($case->amputation_image) && $case->amputation_image)
        <div class="photo-block">
          <img src="{{ asset($case->amputation_image) }}" alt="صورة البتر">
          <div class="file-label" style="justify-content:center;"><i class="fas fa-x-ray"></i> صورة البتر</div>
          <form action="{{ route('admin.amputations.updateImage', $case->id) }}" method="POST" enctype="multipart/form-data" style="display:flex; flex-wrap:wrap; gap:6px; justify-content:center;">
            @csrf
            <input type="hidden" name="type" value="amputation_image">
            <input type="file" name="image" class="form-control form-control-sm" style="flex:1; min-width:100px;" required>
            <button type="submit" class="btn btn-warning btn-sm" style="border-radius:40px;"><i class="fas fa-upload"></i> تغيير</button>
          </form>
        </div>
        @endif
      </div>
    </div>

    <!-- ======== المرفقات ======== -->
    <div class="section-card">
      <div class="card-header-custom card-header-dark">
        <i class="fas fa-paperclip"></i> المرفقات
      </div>
      <div style="padding: 1.8rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 20px;">
        @php
          $attachments = [
            'medical_reports' => ['label' => 'التقارير الطبية', 'icon' => 'fa-file-medical'],
            'rent_contract' => ['label' => 'عقد الإيجار', 'icon' => 'fa-file-contract'],
            'guardian_passport' => ['label' => 'جواز السفر', 'icon' => 'fa-passport'],
            'entry_stamp' => ['label' => 'ختم الدخول', 'icon' => 'fa-stamp']
          ];
        @endphp
        @foreach($attachments as $key => $attr)
          @if(isset($case->$key) && $case->$key)
          <div class="file-box">
            <div class="file-label"><i class="fas {{ $attr['icon'] }}"></i> {{ $attr['label'] }}</div>
            <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
              <a href="{{ asset($case->$key) }}" target="_blank" class="btn btn-info btn-sm" style="border-radius:40px;"><i class="fas fa-eye"></i> عرض</a>
              <form action="{{ route('admin.amputations.updateAttachment', $case->id) }}" method="POST" enctype="multipart/form-data" style="display:flex; flex-wrap:wrap; gap:6px; flex:1;">
                @csrf
                <input type="hidden" name="type" value="{{ $key }}">
                <input type="file" name="file" class="form-control form-control-sm" style="flex:1; min-width:100px;" required>
                <button type="submit" class="btn btn-primary btn-sm" style="border-radius:40px;"><i class="fas fa-sync-alt"></i></button>
              </form>
            </div>
          </div>
          @endif
        @endforeach
      </div>
    </div>
    
    <!-- ======== الموقع ======== -->
    @if(isset($case->latitude) && isset($case->longitude) && $case->latitude && $case->longitude)
    <div class="section-card">
      <div class="card-header-custom card-header-secondary">
        <i class="fas fa-map-pin"></i> الموقع الجغرافي
      </div>
      <div class="map-box">
        <div class="text-center mb-3">
    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $case->latitude }},{{ $case->longitude }}"
       target="_blank"
       class="btn btn-success">
        <i class="fas fa-route"></i>
        فتح الاتجاهات
    </a>
</div>

<div id="map"></div>
      </div>
    </div>
    @endif

    <!-- ======== أزرار الإجراءات ======== -->
    <div class="action-area">
      <form action="{{ route('admin.amputations.review', $case->id) }}" method="POST">
        @csrf
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="fw-bold" style="color: #1d435a;"><i class="fas fa-pen"></i> ملاحظات المشرف</label>
            <textarea name="admin_note" class="form-control form-control-soft" rows="2" placeholder="أضف ملاحظة...">{{ $case->admin_note ?? '' }}</textarea>
          </div>
          <div class="col-md-6 d-flex flex-wrap gap-2 justify-content-md-end">
            <button name="status" value="approved" class="btn btn-soft-success"><i class="fas fa-check-circle"></i> اعتماد</button>
            <button name="status" value="rejected" class="btn btn-soft-danger"><i class="fas fa-times-circle"></i> رفض</button>
          </div>
        </div>
      </form>

      <div class="d-flex flex-wrap gap-3 mt-4 align-items-center">
        <form action="{{ route('admin.amputations.destroy', $case->id) }}" method="POST" onsubmit="return confirm('حذف الحالة نهائياً؟')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-soft-dark"><i class="fas fa-trash-alt"></i> حذف</button>
        </form>
        <a href="{{ route('admin.amputations.index') }}" class="btn btn-outline-secondary-custom"><i class="fas fa-arrow-right"></i> رجوع</a>
      </div>
    </div>

  </div> <!-- end case-inner -->
  </div> <!-- end case-wrapper -->
</div> <!-- end main-container -->

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@if(isset($case->latitude) && isset($case->longitude) && $case->latitude && $case->longitude)
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var lat = {{ $case->latitude }};
    var lng = {{ $case->longitude }};
    if (isFinite(lat) && isFinite(lng)) {
      var map = L.map('map').setView([lat, lng], 14);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
      }).addTo(map);
      L.marker([lat, lng]).addTo(map).bindPopup('📍 موقع الحالة');
    }
  });
</script>
@endif
</body>
</html>