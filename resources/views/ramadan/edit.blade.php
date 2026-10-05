<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>تعديل بيانات مستفيد - رمضان</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
        }

        /* بطاقة التعديل */
        .edit-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* الترويسة */
        .card-header {
            background: linear-gradient(135deg, #2d5a27 0%, #1e3d1a 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: "✏️";
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 80px;
            opacity: 0.1;
        }

        .card-header h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .card-header p {
            font-size: 16px;
            opacity: 0.95;
            color: #f0f7ee;
        }

        /* رمز المستفيد */
        .beneficiary-code {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 30px;
            display: inline-block;
            margin-top: 15px;
            font-size: 18px;
            font-weight: 600;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        /* جسم البطاقة */
        .card-body {
            padding: 40px;
        }

        /* رسائل الخطأ */
        .error-messages {
            background: #fee;
            border-right: 5px solid #dc3545;
            padding: 15px 20px;
            border-radius: 14px;
            margin-bottom: 30px;
        }

        .error-messages h4 {
            color: #dc3545;
            margin-bottom: 10px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-messages ul {
            list-style: none;
            padding-right: 20px;
        }

        .error-messages li {
            color: #721c24;
            margin-bottom: 5px;
            font-size: 14px;
        }

        /* مجموعات الحقول */
        .form-group {
            margin-bottom: 25px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1e3d1a;
            font-size: 15px;
        }

        .label-icon {
            margin-left: 6px;
            font-size: 16px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #2d5a27;
            z-index: 1;
        }

        input {
            width: 100%;
            padding: 15px 50px 15px 15px;
            border: 2px solid #e9ecef;
            border-radius: 14px;
            font-size: 16px;
            transition: all 0.3s;
            background: #f8fafc;
            font-family: inherit;
        }

        input:focus {
            outline: none;
            border-color: #2d5a27;
            background: white;
            box-shadow: 0 0 0 4px rgba(45, 90, 39, 0.1);
        }

        input:hover:not(:focus) {
            border-color: #adb5bd;
            background: #f1f5f9;
        }

        /* المساعدة */
        .input-help {
            font-size: 13px;
            color: #6c757d;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .input-help i {
            font-size: 14px;
            color: #2d5a27;
        }

        /* معلومات إضافية */
        .info-box {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            margin: 30px 0;
            border: 1px dashed #2d5a27;
        }

        .info-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1e3d1a;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6c757d;
        }

        .info-value {
            font-weight: 600;
            color: #2d5a27;
        }

        /* أزرار الإجراءات */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 30px;
            border-radius: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            flex: 1;
            min-width: 180px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2d5a27 0%, #1e3d1a 100%);
            color: white;
            box-shadow: 0 4px 10px rgba(45, 90, 39, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 90, 39, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: white;
            color: #2d5a27;
            border: 2px solid #2d5a27;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(45, 90, 39, 0.1);
        }

        .btn-danger {
            background: white;
            color: #dc3545;
            border: 2px solid #dc3545;
        }

        .btn-danger:hover {
            background: #fff5f5;
            transform: translateY(-2px);
        }

        /* تأكيد الحذف */
        .delete-confirmation {
            display: none;
            background: #fff5f5;
            border: 2px solid #dc3545;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .delete-confirmation.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .delete-confirmation p {
            color: #721c24;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .delete-confirmation-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-confirm-delete {
            background: #dc3545;
            color: white;
            padding: 10px 25px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-confirm-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-cancel-delete {
            background: white;
            color: #6c757d;
            padding: 10px 25px;
            border-radius: 30px;
            border: 2px solid #6c757d;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-cancel-delete:hover {
            background: #f8fafc;
        }

        /* تذييل */
        .footer-note {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            color: #6c757d;
            font-size: 13px;
        }

        /* للهواتف الصغيرة */
        @media (max-width: 480px) {
            .card-body {
                padding: 25px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .card-header h2 {
                font-size: 24px;
            }
        }

        /* تجاوب مع لوحة المفاتيح */
        input:focus-visible {
            outline: 2px solid #2d5a27;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="edit-card">
            <!-- الترويسة -->
            <div class="card-header">
                <h2>✏️ تعديل بيانات مستفيد</h2>
                <p>تحديث معلومات المستفيد من برنامج توزيع رمضان</p>
                <div class="beneficiary-code">
                    <span>الكود: {{ $beneficiary->code }}</span>
                </div>
            </div>

            <!-- جسم البطاقة -->
            <div class="card-body">
                <!-- عرض أخطاء التحقق إن وجدت -->
                @if($errors->any())
                    <div class="error-messages">
                        <h4>
                            <span>⚠️</span>
                            يرجى تصحيح الأخطاء التالية:
                        </h4>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- نموذج التعديل -->
                <form method="POST" action="{{ route('ramadan.update', $beneficiary->id) }}">
                    @csrf

                    <!-- صفين من الحقول -->
                    <div class="form-row">
                        <!-- الاسم الكامل -->
                        <div class="form-group">
                            <label>
                                <span class="label-icon">👤</span>
                                الاسم الكامل
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon">📝</span>
                                <input 
                                    type="text" 
                                    name="full_name" 
                                    value="{{ old('full_name', $beneficiary->full_name) }}"
                                    placeholder="أدخل الاسم الرباعي"
                                >
                            </div>
                            <div class="input-help">
                                <i>ℹ️</i>
                                <span>الاسم كما في الهوية الوطنية</span>
                            </div>
                        </div>

                        <!-- رقم الهوية -->
                        <div class="form-group">
                            <label>
                                <span class="label-icon">🆔</span>
                                رقم الهوية
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon">🔢</span>
                                <input 
                                    type="text" 
                                    name="national_id" 
                                    value="{{ old('national_id', $beneficiary->national_id) }}"
                                    placeholder="أدخل رقم الهوية"
                                >
                            </div>
                            <div class="input-help">
                                <i>ℹ️</i>
                                <span>10 أرقام بدون شرطات</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <!-- الكود -->
                        <div class="form-group">
                            <label>
                                <span class="label-icon">🔑</span>
                                الكود
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon">🔐</span>
                                <input 
                                    type="text" 
                                    name="code" 
                                    value="{{ old('code', $beneficiary->code) }}"
                                    placeholder="أدخل الكود"
                                >
                            </div>
                            <div class="input-help">
                                <i>ℹ️</i>
                                <span>كود فريد للمستفيد</span>
                            </div>
                        </div>

                        <!-- عدد أفراد الأسرة -->
                        <div class="form-group">
                            <label>
                                <span class="label-icon">👪</span>
                                عدد أفراد الأسرة
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon">🧑‍🧑‍🧒</span>
                                <input 
                                    type="number" 
                                    name="family_members_count" 
                                    value="{{ old('family_members_count', $beneficiary->family_members_count) }}"
                                    placeholder="عدد الأفراد"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- الوجبات اليومية (صف كامل) -->
                    <div class="form-group">
                        <label>
                            <span class="label-icon">🍽️</span>
                            عدد الوجبات اليومية
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">🍴</span>
                            <input 
                                type="number" 
                                name="daily_meals" 
                                value="{{ old('daily_meals', $beneficiary->daily_meals) }}"
                                placeholder="عدد الوجبات المخصصة يومياً"
                            >
                        </div>
                        <div class="input-help">
                            <i>💡</i>
                            <span>إجمالي الوجبات التي يستحقها المستفيد يومياً</span>
                        </div>
                    </div>

                    <!-- معلومات إضافية -->
                    <div class="info-box">
                        <div class="info-title">
                            <span>📊</span>
                            <span>معلومات إضافية</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">تاريخ التسجيل</span>
                            <span class="info-value">{{ $beneficiary->created_at ? $beneficiary->created_at->format('Y/m/d') : 'غير محدد' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">مسجل بواسطة</span>
                            <span class="info-value">{{ $beneficiary->creator->name ?? 'غير معروف' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">آخر تحديث</span>
                            <span class="info-value">{{ $beneficiary->updated_at ? $beneficiary->updated_at->format('Y/m/d h:i A') : 'لم يسبق التحديث' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">إجمالي مرات الاستلام</span>
                            <span class="info-value">{{ $beneficiary->distributions->count() ?? 0 }} مرة</span>
                        </div>
                    </div>

                    <!-- أزرار الإجراءات -->
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">
                            <span>💾</span>
                            <span>حفظ التعديلات</span>
                        </button>
                        
                        <a href="{{ route('ramadan.index') }}" class="btn btn-secondary">
                            <span>🔙</span>
                            <span>إلغاء والعودة</span>
                        </a>
                    </div>
                </form>

                <!-- قسم الحذف (اختياري) -->
                <hr style="margin: 30px 0; border: none; border-top: 2px dashed #e9ecef;">

                <div style="text-align: center;">
                    <button onclick="toggleDeleteConfirmation()" class="btn btn-danger" style="width: auto; min-width: auto;">
                        <span>🗑️</span>
                        <span>حذف المستفيد</span>
                    </button>

                    <!-- تأكيد الحذف -->
                    <div id="deleteConfirmation" class="delete-confirmation">
                        <p>
                            <span>⚠️</span>
                            هل أنت متأكد من حذف المستفيد "{{ $beneficiary->full_name }}"؟
                            <br>
                            <small style="color: #dc3545;">لا يمكن التراجع عن هذا الإجراء</small>
                        </p>
                        <div class="delete-confirmation-buttons">
                            <form method="POST" action="{{ route('ramadan.delete', $beneficiary->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-confirm-delete">
                                    نعم، تأكيد الحذف
                                </button>
                            </form>
                            <button onclick="toggleDeleteConfirmation()" class="btn-cancel-delete">
                                إلغاء
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- تذييل الصفحة -->
        <div class="footer-note">
            <p>© 2024 برنامج توزيع رمضان - إدارة المستفيدين</p>
        </div>
    </div>

    <script>
        // إظهار/إخفاء تأكيد الحذف
        function toggleDeleteConfirmation() {
            const confirmation = document.getElementById('deleteConfirmation');
            confirmation.classList.toggle('show');
        }

        // إغلاق تأكيد الحذف عند الضغط خارجها
        document.addEventListener('click', function(event) {
            const confirmation = document.getElementById('deleteConfirmation');
            const deleteBtn = event.target.closest('.btn-danger');
            const isInsideConfirmation = confirmation.contains(event.target);
            const isDeleteBtn = deleteBtn && deleteBtn.textContent.includes('حذف المستفيد');
            
            if (!isInsideConfirmation && !isDeleteBtn && confirmation.classList.contains('show')) {
                confirmation.classList.remove('show');
            }
        });

        // منع إغلاق النافذة إذا تم الضغط داخل confirmation
        document.getElementById('deleteConfirmation').addEventListener('click', function(event) {
            event.stopPropagation();
        });
    </script>
</body>
</html>