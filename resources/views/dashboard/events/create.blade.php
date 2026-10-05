<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة فاعلية جديدة</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
    <circle cx='50' cy='50' r='48' fill='%237209b7'/>
    <circle cx='50' cy='50' r='28' fill='white'/>
    <line x1='50' y1='35' x2='50' y2='65' stroke='%237209b7' stroke-width='6'/>
    <line x1='35' y1='50' x2='65' y2='50' stroke='%237209b7' stroke-width='6'/>
    </svg>">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a56d4;
            --secondary-color: #7209b7;
            --success-color: #4cc9f0;
            --warning-color: #f8961e;
            --danger-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --light-gray: #e9ecef;
            --border-radius: 12px;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Tahoma', Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark-color);
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.3;
        }

        .header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            position: relative;
        }

        .header i {
            margin-left: 15px;
        }

        .alert {
            padding: 18px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            border-right: 5px solid;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-danger {
            background-color: #ffe3e6;
            border-color: var(--danger-color);
            color: #b00020;
        }

        .alert-danger ul {
            list-style-position: inside;
            margin-top: 10px;
        }

        .alert-danger li {
            margin-bottom: 5px;
            padding-right: 10px;
        }

        .form-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 35px;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.05rem;
        }

        .form-group label i {
            margin-left: 8px;
            width: 20px;
            text-align: center;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: #fff;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .form-col {
            flex: 1;
            padding: 0 10px;
            min-width: 250px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 16px 32px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            margin-top: 10px;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(67, 97, 238, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn i {
            margin-right: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: var(--gray-color);
            font-size: 0.9rem;
        }

        /* تصميم متجاوب للهواتف */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .form-container {
                padding: 25px 20px;
            }
            
            .form-col {
                min-width: 100%;
                margin-bottom: 15px;
            }
            
            .btn {
                width: 100%;
                padding: 18px;
            }
            
            .form-row {
                margin: 0;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .form-container {
                padding: 20px 15px;
            }
            
            .form-group label {
                font-size: 1rem;
            }
            
            .form-control {
                padding: 12px;
            }
        }

        /* تحسينات إضافية */
        .required::after {
            content: " *";
            color: var(--danger-color);
        }

        .form-note {
            font-size: 0.9rem;
            color: var(--gray-color);
            margin-top: 5px;
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper::after {
            content: "\f078";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: var(--gray-color);
            pointer-events: none;
        }

        select.form-control {
            appearance: none;
            padding-left: 40px;
        }

        /* تصميم مفتاح تبديل نوع القيمة */
        .value-type-toggle {
            display: flex;
            margin-bottom: 15px;
            border-radius: var(--border-radius);
            overflow: hidden;
            border: 2px solid var(--light-gray);
            width: fit-content;
        }

        .toggle-option {
            padding: 10px 20px;
            background-color: #fff;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .toggle-option i {
            margin-left: 8px;
        }

        .toggle-option:first-child {
            border-left: 1px solid var(--light-gray);
        }

        .toggle-option:last-child {
            border-right: 1px solid var(--light-gray);
        }

        .toggle-option:hover {
            background-color: var(--light-gray);
        }

        .toggle-option.active {
            background-color: var(--primary-color);
            color: white;
        }

        /* تصميم خاص لحقل القيمة النصي */
        .value-text-group {
            display: none;
        }

        .value-number-group {
            display: block;
        }

        .currency-symbol {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-color);
            font-weight: 500;
        }

        .value-number-input {
            padding-left: 40px;
        }

        /* تنسيق خاص عند عرض النص */
        .value-text-input {
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-calendar-plus"></i>إضافة فاعلية جديدة</h1>
            <p>املأ النموذج التالي لإضافة فاعلية جديدة للمساعدة</p>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-triangle"></i> حدثت الأخطاء التالية:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="form-container">
            <form action="{{ route('events.store') }}" method="POST" id="eventForm">
                @csrf
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name" class="required"><i class="fas fa-heading"></i>اسم الفاعلية</label>
                            <input type="text" id="name" name="name" class="form-control" required placeholder="أدخل اسم الفاعلية" value="{{ old('name') }}">
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="location" class="required"><i class="fas fa-map-marker-alt"></i>مكان الفاعلية</label>
                            <input type="text" id="location" name="location" class="form-control" required placeholder="أدخل مكان الفاعلية" value="{{ old('location') }}">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="event_date" class="required"><i class="fas fa-calendar-alt"></i>تاريخ الفاعلية</label>
                            <input type="date" id="event_date" name="event_date" class="form-control" required value="{{ old('event_date') }}">
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="event_time"><i class="fas fa-clock"></i>زمن الفاعلية</label>
                            <input type="time" id="event_time" name="event_time" class="form-control" value="{{ old('event_time') }}">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="aid_type" class="required"><i class="fas fa-hand-holding-heart"></i>نوع المساعدة</label>
                            <div class="select-wrapper">
                                <select id="aid_type" name="aid_type" class="form-control" required>
                                    <option value="" disabled {{ old('aid_type') ? '' : 'selected' }}>اختر نوع المساعدة</option>
                                    <option value="in_kind" {{ old('aid_type') == 'in_kind' ? 'selected' : '' }}>عينية</option>
                                    <option value="cash" {{ old('aid_type') == 'cash' ? 'selected' : '' }}>نقدية</option>
                                    <option value="other" {{ old('aid_type') == 'other' ? 'selected' : '' }}>أخرى</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="value"><i class="fas fa-money-bill-wave"></i>القيمة</label>
                            
                            <!-- مفتاح التبديل بين النوعين -->
                            <div class="value-type-toggle" id="valueTypeToggle">
                                <div class="toggle-option active" data-type="number">
                                    <i class="fas fa-hashtag"></i> رقم
                                </div>
                                <div class="toggle-option" data-type="text">
                                    <i class="fas fa-font"></i> نص
                                </div>
                            </div>
                            
                            <!-- حقل القيمة الرقمية -->
                            <div class="value-number-group" id="valueNumberGroup">
                                <div style="position: relative;">
                                    <span class="currency-symbol">جنيه</span>
                                    <input type="number" id="value_number" name="value_number" class="form-control value-number-input" 
                                           placeholder="أدخل القيمة بالجنيه" step="0.01" min="0" value="{{ old('value_number') }}">
                                </div>
                                <div class="form-note">يمكنك ترك الحقل فارغًا إذا لم تكن هناك قيمة محددة</div>
                            </div>
                            
                            <!-- حقل القيمة النصية -->
                            <div class="value-text-group" id="valueTextGroup">
                                <input type="text" id="value_text" name="value_text" class="form-control value-text-input" 
                                       placeholder="مثال: جهاز كمبيوتر، مواد غذائية، هدايا..." value="{{ old('value_text') }}">
                                <div class="form-note">أدخل وصفًا نصيًا للقيمة مثل (مواد غذائية، أجهزة إلكترونية...)</div>
                            </div>
                            
                            <!-- حقل مخفي لتخزين النوع المختار -->
                            <input type="hidden" id="value_type" name="value_type" value="number">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="extras"><i class="fas fa-sticky-note"></i>الإضافات</label>
                    <textarea id="extras" name="extras" class="form-control" placeholder="أي معلومات إضافية تريد إضافتها...">{{ old('extras') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="invitation_message"><i class="fas fa-comment-alt"></i>نص الدعوة</label>
                    <textarea id="invitation_message" name="invitation_message" class="form-control" placeholder="نص الدعوة الذي سيتم إرساله للمستفيدين...">{{ old('invitation_message') }}</textarea>
                    <div class="form-note">نص رسالة دعوة الحضور</div>
                </div>
                
                <div class="form-group">
                    <label for="default_message"><i class="fas fa-comment-alt"></i>نص الرسالة الاستلام</label>
                    <textarea id="default_message" name="default_message" class="form-control" placeholder="نص الرسالة الذي سيتم إرساله للمستفيدين...">{{ old('default_message') }}</textarea>
                    <div class="form-note">نص رسالة تم الاستلام</div>
                </div>

                <div class="form-group">
                    <label for="is_archived"><i class="fas fa-archive"></i>أرشفة الفاعلية</label>
                    <input type="checkbox" id="is_archived" name="is_archived" value="1">
                    <div class="form-note">ضع علامة هنا لتأريخ الفاعلية</div>
                </div>
                
                <div class="form-group">
                    <label for="is_fixed"><i class="fas fa-lock"></i>تثبيت الفاعلية</label>
                    <input type="checkbox" id="is_fixed" name="is_fixed" value="1">
                    <div class="form-note">ضع علامة هنا لتثبيت الفاعلية</div>
                </div>
                <div class="form-group">
                    <label for="without_notification">
                        <i class="fas fa-bell-slash"></i>
                        فعالية بدون إشعار
                    </label>

                    <input type="checkbox"
                        id="without_notification"
                        name="without_notification"
                        value="1"
                        {{ old('without_notification') ? 'checked' : '' }}>

                    <div class="form-note">
                        ضع علامة هنا إذا كانت الفاعلية لا تحتاج إلى إرسال إشعارات للمستفيدين
                    </div>
                </div>
                <div class="form-group" style="text-align: center;">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> حفظ الفاعلية
                    </button>
                </div>
            </form>
        </div>
        
        <div class="footer">
            <p>جميع الحقوق محفوظة © 2023 | نظام إدارة الفاعليات</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تعيين التاريخ الحالي كقيمة افتراضية لتاريخ الفاعلية
            const today = new Date().toISOString().split('T')[0];
            const dateInput = document.getElementById('event_date');
            if (dateInput && !dateInput.value) {
                dateInput.value = today;
            }
            
            // إدارة تبديل نوع القيمة
            const valueTypeToggle = document.getElementById('valueTypeToggle');
            const valueNumberGroup = document.getElementById('valueNumberGroup');
            const valueTextGroup = document.getElementById('valueTextGroup');
            const valueTypeInput = document.getElementById('value_type');
            const valueNumberInput = document.getElementById('value_number');
            const valueTextInput = document.getElementById('value_text');
            const toggleOptions = document.querySelectorAll('.toggle-option');
            
            // دالة لتبديل نوع القيمة
            function switchValueType(type) {
                // تحديث حالة أزرار التبديل
                toggleOptions.forEach(option => {
                    if (option.dataset.type === type) {
                        option.classList.add('active');
                    } else {
                        option.classList.remove('active');
                    }
                });
                
                // إظهار/إخفاء الحقول المناسبة
                if (type === 'number') {
                    valueNumberGroup.style.display = 'block';
                    valueTextGroup.style.display = 'none';
                    valueTypeInput.value = 'number';
                    
                    // نسخ القيمة من النص إلى الرقم إذا كان هناك قيمة
                    if (valueTextInput.value && !valueNumberInput.value) {
                        // محاولة تحويل النص إلى رقم إذا كان ممكناً
                        const num = parseFloat(valueTextInput.value);
                        if (!isNaN(num)) {
                            valueNumberInput.value = num;
                        }
                    }
                } else {
                    valueNumberGroup.style.display = 'none';
                    valueTextGroup.style.display = 'block';
                    valueTypeInput.value = 'text';
                    
                    // نسخ القيمة من الرقم إلى النص إذا كان هناك قيمة
                    if (valueNumberInput.value && !valueTextInput.value) {
                        valueTextInput.value = valueNumberInput.value + ' ر.س';
                    }
                }
            }
            
            // إضافة أحداث النقر على أزرار التبديل
            toggleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const type = this.dataset.type;
                    switchValueType(type);
                });
            });
            
            // استعادة الحالة السابقة إذا كانت موجودة
            const savedValueType = "{{ old('value_type', 'number') }}";
            switchValueType(savedValueType);
            
            // عند تغيير نوع المساعدة، ضبط التلميحات المناسبة
            const aidTypeSelect = document.getElementById('aid_type');
            aidTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;
                
                // إذا كان النوع نقدي، افترض أن القيمة رقمية
                if (selectedType === 'cash') {
                    if (valueTypeInput.value !== 'number') {
                        switchValueType('number');
                    }
                }
                
                // تحديث التلميحات بناءً على نوع المساعدة
                if (selectedType === 'cash') {
                    document.querySelector('.value-number-input').placeholder = 'أدخل القيمة النقدية بالريال';
                    document.querySelector('#valueTextGroup .form-note').textContent = 'للمساعدة النقدية، استخدم القيمة الرقمية';
                } else if (selectedType === 'in_kind') {
                    document.querySelector('.value-number-input').placeholder = 'أدخل القيمة التقديرية بالريال (اختياري)';
                    document.querySelector('#valueTextGroup .form-note').textContent = 'أدخل وصفًا نصيًا للقيمة العينية (مثل: مواد غذائية، أجهزة...)';
                } else {
                    document.querySelector('.value-number-input').placeholder = 'أدخل القيمة بالريال (اختياري)';
                    document.querySelector('#valueTextGroup .form-note').textContent = 'أدخل وصفًا نصيًا للقيمة';
                }
            });
            
            // تغيير لون الحقول المطلوبة عند التركيز
            const requiredFields = document.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.addEventListener('focus', function() {
                    this.style.borderColor = 'var(--primary-color)';
                });
                
                field.addEventListener('blur', function() {
                    if (!this.value) {
                        this.style.borderColor = 'var(--danger-color)';
                    }
                });
            });
            
            // رسالة تأكيد عند تقديم النموذج
            const form = document.getElementById('eventForm');
            form.addEventListener('submit', function(e) {
                const eventName = document.getElementById('name').value;
                
                // التأكد من أن حقل القيمة مملوء بشكل صحيح
                const valueType = valueTypeInput.value;
                let valueValid = true;
                
                if (valueType === 'number' && valueNumberInput.value) {
                    const numValue = parseFloat(valueNumberInput.value);
                    if (isNaN(numValue) || numValue < 0) {
                        alert('يرجى إدخال قيمة رقمية صحيحة لحقل القيمة');
                        valueNumberInput.focus();
                        e.preventDefault();
                        return;
                    }
                }
                
                if (eventName) {
                    let confirmMessage = `هل أنت متأكد من إضافة الفاعلية "${eventName}"؟`;
                    
                    // إضافة معلومات القيمة لرسالة التأكيد
                    if (valueType === 'number' && valueNumberInput.value) {
                        confirmMessage += `\nالقيمة: ${valueNumberInput.value} ر.س`;
                    } else if (valueType === 'text' && valueTextInput.value) {
                        confirmMessage += `\nالقيمة: ${valueTextInput.value}`;
                    }
                    
                    if (!confirm(confirmMessage)) {
                        e.preventDefault();
                    }
                }
            });
            
            // استعادة القيم القديمة بعد إعادة تحميل الصفحة (في حالة أخطاء التحقق)
            const oldValueType = "{{ old('value_type') }}";
            const oldValueNumber = "{{ old('value_number') }}";
            const oldValueText = "{{ old('value_text') }}";
            
            if (oldValueType) {
                switchValueType(oldValueType);
                if (oldValueType === 'number' && oldValueNumber) {
                    valueNumberInput.value = oldValueNumber;
                } else if (oldValueType === 'text' && oldValueText) {
                    valueTextInput.value = oldValueText;
                }
            }
        });
    </script>
</body>
</html>