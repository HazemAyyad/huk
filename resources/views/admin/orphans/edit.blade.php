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

      
            <!-- ========================================================== -->
            <!-- 5. إضافة يتيم جديد -->
            <!-- ========================================================== -->
            <div class="card">
                <div class="card-header bg-danger">
                    <i class="fas fa-plus-circle"></i>
                    إضافة يتيم جديد
                </div>
                <div class="form-section">

                    <form action="{{ route('admin.orphans.storeChild', $case->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="col-md-4 form-group">
                            <label>
                                <i class="fas fa-image"></i>
                                الصورة الشخصية
                            </label>

                            <input type="file"
                                name="personal_photo"
                                class="form-control"
                                accept="image/*">
                        </div>
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