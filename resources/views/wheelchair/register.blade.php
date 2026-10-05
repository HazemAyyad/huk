<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الطلب</title>
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
            padding: 30px 30px 25px;
            position: relative;
            overflow: hidden;
        }

        .card-header-custom::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -10%;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .card-header-custom::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -5%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .header-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 65px;
            height: 65px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            font-size: 30px;
            color: white;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            flex-shrink: 0;
        }

        .header-text h4 {
            color: white;
            font-weight: 700;
            font-size: 26px;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .header-text p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            margin: 5px 0 0;
        }

        .card-body-custom {
            padding: 35px 30px 40px;
        }

        .section-title {
            font-weight: 700;
            color: #1e3a8a;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dbeafe;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #3b82f6;
        }

        .form-label-custom {
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
            margin-bottom: 6px;
            display: block;
        }

        .form-label-custom .required {
            color: #ef4444;
            margin-right: 3px;
        }

        .form-label-custom i {
            color: #3b82f6;
            margin-left: 8px;
            width: 18px;
        }

        .form-control-custom,
        .form-select-custom {
            border-radius: 12px !important;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.3s ease;
            height: 48px;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        textarea.form-control-custom {
            height: auto;
            min-height: 100px;
            resize: vertical;
        }

        .form-control-custom[readonly] {
            background: #dbeafe;
            cursor: not-allowed;
            color: #1e3a8a;
            font-weight: 600;
        }

        .form-control-custom[type="file"] {
            padding: 10px;
            height: auto;
            background: white;
        }

        .form-control-custom[type="file"]::file-selector-button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            margin-left: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .form-control-custom[type="file"]::file-selector-button:hover {
            background: #1e3a8a;
            transform: scale(1.02);
        }

        .file-upload-wrapper {
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #bfdbfe;
            padding: 15px;
            transition: all 0.3s ease;
        }

        .file-upload-wrapper:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .btn-custom {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            border: none;
            padding: 16px 30px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 18px;
            color: white;
            transition: all 0.3s ease;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px -5px rgba(59, 130, 246, 0.4);
            color: white;
        }

        .btn-custom:active {
            transform: scale(0.97);
        }

        .btn-custom i {
            font-size: 20px;
        }

        .hr-divider {
            border: none;
            height: 2px;
            background: linear-gradient(to right, transparent, #3b82f6, transparent);
            margin: 25px 0;
            opacity: 0.3;
        }

        .info-badge {
            background: #dbeafe;
            color: #1e3a8a;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .text-muted-custom {
            color: #6b7280;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .card-body-custom {
                padding: 20px 15px 30px;
            }

            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .card-header-custom {
                padding: 20px;
            }

            .header-text h4 {
                font-size: 22px;
            }

            .form-control-custom,
            .form-select-custom {
                font-size: 14px;
                padding: 10px 14px;
            }

            .btn-custom {
                font-size: 16px;
                padding: 14px 20px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }

            .card-body-custom {
                padding: 15px 10px 25px;
            }

            .section-title {
                font-size: 16px;
            }

            .icon-circle {
                width: 50px;
                height: 50px;
                font-size: 24px;
            }

            .form-control-custom[type="file"]::file-selector-button {
                padding: 6px 15px;
                font-size: 13px;
            }
        }

        /* تلوين الحقول الإلزامية */
        .required-field .form-label-custom::after {
            content: ' *';
            color: #ef4444;
            font-weight: 700;
        }

        /* تحسين ظهور القيم المقروءة فقط */
        .form-control-custom[readonly] {
            border-color: #93c5fd;
            background: #eff6ff;
        }
    </style>
</head>

<body>

    <div class="container mt-3 mb-4">
        <div class="main-card">

            <!-- Header -->
            <div class="card-header-custom">
                <div class="header-content">
                    <div class="icon-circle">
                        <i class="fas fa-wheelchair"></i>
                    </div>
                    <div class="header-text">
                        <h4>
                            <i class="fas fa-pen-to-square me-2"></i>
                            تسجيل طلب كرسي كهربائي
                        </h4>
                        <p>
                            <i class="fas fa-info-circle me-1"></i>
                            يرجى تعبئة جميع البيانات بدقة
                            <span class="info-badge ms-2">
                                <i class="fas fa-asterisk text-danger"></i>
                                الحقول الإلزامية
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="card-body-custom">

                <form method="POST"
                      action="{{ route('wheelchair.register.submit') }}"
                      enctype="multipart/form-data">

                    @csrf

                    <!-- البيانات الشخصية -->
                    <div class="section-title">
                        <i class="fas fa-user-circle"></i>
                        البيانات الشخصية
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6 required-field">
                            <label class="form-label-custom">
                                <i class="fas fa-user"></i>
                                الاسم رباعي
                                <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control form-control-custom"
                                name="full_name"
                                placeholder="أدخل الاسم كاملاً"
                                required>
                        </div>

                        <div class="col-md-6 required-field">
                            <label class="form-label-custom">
                                <i class="fas fa-id-card"></i>
                                رقم الهوية
                                <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control form-control-custom"
                                name="national_id"
                                value="{{ $national_id ?? '' }}"
                                readonly
                                style="background:#eff6ff; font-weight:600; color:#1e3a8a; border-color:#93c5fd;">
                        </div>

                        <div class="col-md-4 required-field">
                            <label class="form-label-custom">
                                <i class="fas fa-calendar"></i>
                                العمر
                                <span class="required">*</span>
                            </label>
                            <input
                                type="number"
                                class="form-control form-control-custom"
                                name="age"
                                placeholder="مثال: 35"
                                min="1"
                                max="120"
                                required>
                        </div>

                        <div class="col-md-4 required-field">
                            <label class="form-label-custom">
                                <i class="fas fa-phone"></i>
                                رقم الهاتف المصري
                                <span class="required">*</span>
                            </label>
                            <input
                                type="tel"
                                class="form-control form-control-custom"
                                name="phone"
                                placeholder="مثال: 01012345678"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-custom">
                                <i class="fab fa-whatsapp"></i>
                                رقم الواتساب
                            </label>
                            <input
                                type="tel"
                                class="form-control form-control-custom"
                                name="whatsapp"
                                placeholder="مثال: 01012345678">
                        </div>

                        <div class="col-md-6 required-field">
                            <label class="form-label-custom">
                                <i class="fas fa-map-marker-alt"></i>
                                عنوان السكن
                                <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control form-control-custom"
                                name="address"
                                placeholder="المحافظة - المدينة - المنطقة"
                                required>
                        </div>

                        <div class="col-md-6 required-field">
                            <label class="form-label-custom">
                                <i class="fas fa-calendar-alt"></i>
                                تاريخ دخول مصر
                                <span class="required">*</span>
                            </label>
                            <input
                                type="date"
                                class="form-control form-control-custom"
                                name="entry_date"
                                required>
                        </div>

                    </div>

                    <hr class="hr-divider">

                    <!-- البيانات الطبية -->
                    <div class="section-title">
                        <i class="fas fa-heartbeat"></i>
                        البيانات الطبية
                    </div>

                    <div class="row g-3">

                        <div class="col-md-12 required-field">
                            <label class="form-label-custom">
                                <i class="fas fa-notes-medical"></i>
                                شرح الحالة المرضية
                                <span class="required">*</span>
                            </label>
                            <textarea
                                class="form-control form-control-custom"
                                rows="4"
                                name="medical_condition"
                                placeholder="اشرح بالتفصيل الحالة المرضية ..."
                                required></textarea>
                        </div>

                        <div class="col-md-12 required-field">
                            <label class="form-label-custom">
                                <i class="fas fa-exclamation-triangle"></i>
                                سبب الإصابة
                                <span class="required">*</span>
                            </label>
                            <textarea
                                class="form-control form-control-custom"
                                rows="4"
                                name="injury_reason"
                                placeholder="ما هو سبب الإصابة أو الإعاقة ..."
                                required></textarea>
                        </div>

                    </div>

                    <hr class="hr-divider">

                    <!-- معلومات الكرسي -->
                    <div class="section-title">
                        <i class="fas fa-wheelchair"></i>
                        معلومات الكرسي
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6 required-field">
                            <label class="form-label-custom">
                                <i class="fas fa-question-circle"></i>
                                هل يمتلك كرسي حالياً؟
                                <span class="required">*</span>
                            </label>
                            <select
                                class="form-select form-select-custom"
                                name="has_wheelchair"
                                id="has_wheelchair"
                                required>
                                <option value="0">لا</option>
                                <option value="1">نعم</option>
                            </select>
                        </div>

                        <div class="col-md-6 d-none" id="wheelchair_status_div">
                            <label class="form-label-custom">
                                <i class="fas fa-tools"></i>
                                حالة الكرسي
                            </label>
                            <textarea
                                class="form-control form-control-custom"
                                rows="2"
                                name="wheelchair_status"
                                placeholder="صف حالة الكرسي الحالي ..."></textarea>
                        </div>

                    </div>

                    <div class="row g-3 mt-1">

                        <div class="col-md-6 required-field">
                            <label class="form-label-custom">
                                <i class="fas fa-hand-holding-heart"></i>
                                هل سبق واستلمت كرسي من جمعية أخرى؟
                                <span class="required">*</span>
                            </label>
                            <select
                                class="form-select form-select-custom"
                                name="received_before"
                                id="received_before"
                                required>
                                <option value="0">لا</option>
                                <option value="1">نعم</option>
                            </select>
                        </div>

                        <div class="col-md-6 d-none" id="organization_div">
                            <label class="form-label-custom">
                                <i class="fas fa-building"></i>
                                اسم الجمعية
                            </label>
                            <input
                                type="text"
                                class="form-control form-control-custom"
                                name="organization_name"
                                placeholder="أدخل اسم الجمعية">
                        </div>

                    </div>

                    <hr class="hr-divider">

                    <!-- رفع الملفات -->
                    <div class="section-title">
                        <i class="fas fa-upload"></i>
                        رفع الملفات
                        <span style="font-size:13px; font-weight:400; color:#6b7280;">
                            (يفضل رفع الصور بصيغة JPG أو PNG)
                        </span>
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <div class="file-upload-wrapper">
                                <label class="form-label-custom">
                                    <i class="fas fa-user-circle"></i>
                                    الصورة الشخصية
                                </label>
                                <input
                                    type="file"
                                    class="form-control form-control-custom"
                                    name="personal_photo"
                                    accept="image/*">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="file-upload-wrapper">
                                <label class="form-label-custom">
                                    <i class="fas fa-file-medical"></i>
                                    صورة الحالة المرضية
                                </label>
                                <input
                                    type="file"
                                    class="form-control form-control-custom"
                                    name="disability_photo"
                                    accept="image/*">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="file-upload-wrapper">
                                <label class="form-label-custom">
                                    <i class="fas fa-passport"></i>
                                    صورة الجواز
                                </label>
                                <input
                                    type="file"
                                    class="form-control form-control-custom"
                                    name="passport_photo"
                                    accept="image/*">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="file-upload-wrapper">
                                <label class="form-label-custom">
                                    <i class="fas fa-stamp"></i>
                                    صورة ختم الدخول
                                </label>
                                <input
                                    type="file"
                                    class="form-control form-control-custom"
                                    name="entry_stamp_photo"
                                    accept="image/*">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="file-upload-wrapper">
                                <label class="form-label-custom">
                                    <i class="fas fa-file-pdf"></i>
                                    التقرير الطبي (اختياري)
                                </label>
                                <input
                                    type="file"
                                    class="form-control form-control-custom"
                                    name="medical_report"
                                    accept=".pdf,.doc,.docx,image/*">
                                <small class="text-muted-custom mt-1 d-block">
                                    <i class="fas fa-info-circle"></i>
                                    يمكن رفع ملف PDF أو Word أو صورة
                                </small>
                            </div>
                        </div>

                    </div>

                    <hr class="hr-divider">

                    <!-- ملاحظات -->
                    <div class="row g-3">

                        <div class="col-md-12">
                            <label class="form-label-custom">
                                <i class="fas fa-pencil"></i>
                                ملاحظات
                            </label>
                            <textarea
                                class="form-control form-control-custom"
                                rows="3"
                                name="notes"
                                placeholder="أضف أي ملاحظات إضافية تريد إبلاغنا بها ..."></textarea>
                        </div>

                    </div>

                    <hr class="hr-divider">

                    <!-- زر الإرسال -->
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-custom w-100">
                                <i class="fas fa-paper-plane"></i>
                                إرسال الطلب
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <p class="text-center text-muted mt-3" style="font-size:13px;">
                                <i class="fas fa-lock me-1"></i>
                                جميع البيانات آمنة ومشفرة
                                <i class="fas fa-circle-check me-1 ms-2" style="color:#3b82f6;"></i>
                                سيتم مراجعة الطلب في أقرب وقت
                            </p>
                        </div>
                    </div>

                </form>

            </div>

        </div>
    </div>

    <script>
        // إظهار/إخفاء حقل حالة الكرسي
        document.getElementById('has_wheelchair').addEventListener('change', function() {
            const div = document.getElementById('wheelchair_status_div');
            if (this.value == 1) {
                div.classList.remove('d-none');
            } else {
                div.classList.add('d-none');
            }
        });

        // إظهار/إخفاء حقل اسم الجمعية
        document.getElementById('received_before').addEventListener('change', function() {
            const div = document.getElementById('organization_div');
            if (this.value == 1) {
                div.classList.remove('d-none');
            } else {
                div.classList.add('d-none');
            }
        });

        // التحقق من صحة رقم الهاتف المصري
        document.querySelector('input[name="phone"]').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
        });

        document.querySelector('input[name="whatsapp"]').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
        });

        // التحقق من صحة رقم الهوية (10 أرقام)
        document.querySelector('input[name="national_id"]').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    </script>

</body>

</html>