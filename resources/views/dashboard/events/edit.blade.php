<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الفاعلية: {{ $event->name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
    <circle cx='50' cy='50' r='48' fill='%23f8961e'/>
    <circle cx='50' cy='50' r='28' fill='white'/>
    <path d='M35 50 L45 60 L65 40' stroke='%23f8961e' stroke-width='6' fill='none'/>
    </svg>">

    <style>
        /* سأحتفظ بنفس التنسيقات الأصلية مع تغيير طفيف في ألوان الهيدر لتمييز صفحة التعديل */
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', 'Tahoma', Geneva, Verdana, sans-serif; }
        body { background-color: #f5f7fb; color: var(--dark-color); line-height: 1.6; padding: 20px; min-height: 100vh; }
        .container { max-width: 900px; margin: 0 auto; }
        
        /* تغيير التدرج اللوني لتمييز التعديل (برتقالي إلى أرجواني) */
        .header {
            text-align: center; margin-bottom: 30px; padding: 20px;
            background: linear-gradient(135deg, var(--warning-color), var(--secondary-color));
            color: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow);
            position: relative; overflow: hidden;
        }
        .header h1 { font-size: 2.2rem; margin-bottom: 10px; position: relative; }
        .header i { margin-left: 15px; }

        .alert { padding: 18px; border-radius: var(--border-radius); margin-bottom: 30px; border-right: 5px solid; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .alert-danger { background-color: #ffe3e6; border-color: var(--danger-color); color: #b00020; }

        .form-container { background-color: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow); padding: 35px; margin-bottom: 40px; }
        .form-group { margin-bottom: 25px; position: relative; }
        .form-group label { display: block; margin-bottom: 10px; font-weight: 600; color: var(--primary-color); font-size: 1.05rem; }
        .form-control { width: 100%; padding: 15px; border: 2px solid var(--light-gray); border-radius: var(--border-radius); font-size: 1rem; transition: var(--transition); }
        .form-control:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2); }
        
        .form-row { display: flex; flex-wrap: wrap; margin: 0 -10px; }
        .form-col { flex: 1; padding: 0 10px; min-width: 250px; }

        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 16px 32px; background-color: var(--warning-color); color: white; border: none; border-radius: var(--border-radius); font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: var(--transition); box-shadow: 0 4px 12px rgba(248, 150, 30, 0.3); }
        .btn:hover { background-color: #e68a1a; transform: translateY(-2px); }
        
        .btn-secondary { background-color: var(--gray-color); margin-right: 10px; box-shadow: none; }
        .btn-secondary:hover { background-color: #5a6268; }

        .required::after { content: " *"; color: var(--danger-color); }
        .form-note { font-size: 0.9rem; color: var(--gray-color); margin-top: 5px; }
        
        /* ستايل مفتاح التبديل */
        .value-type-toggle { display: flex; margin-bottom: 15px; border-radius: var(--border-radius); overflow: hidden; border: 2px solid var(--light-gray); width: fit-content; }
        .toggle-option { padding: 10px 20px; background-color: #fff; cursor: pointer; transition: var(--transition); display: flex; align-items: center; font-weight: 500; }
        .toggle-option.active { background-color: var(--primary-color); color: white; }
        .value-text-group, .value-number-group { display: none; }
        .currency-symbol { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--gray-color); }
        
        @media (max-width: 768px) { .form-col { min-width: 100%; } .btn { width: 100%; margin-bottom: 10px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-edit"></i>تعديل الفاعلية</h1>
            <p>تعديل بيانات: <strong>{{ $event->name }}</strong></p>
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
            <form action="{{ route('events.update', $event->id) }}" method="POST" id="eventForm">
                @csrf
                @method('PUT')
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name" class="required"><i class="fas fa-heading"></i>اسم الفاعلية</label>
                            <input type="text" id="name" name="name" class="form-control" required value="{{ old('name', $event->name) }}">
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="location" class="required"><i class="fas fa-map-marker-alt"></i>مكان الفاعلية</label>
                            <input type="text" id="location" name="location" class="form-control" required value="{{ old('location', $event->location) }}">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="event_date" class="required"><i class="fas fa-calendar-alt"></i>تاريخ الفاعلية</label>
                            <input type="date" id="event_date" name="event_date" class="form-control" required value="{{ old('event_date', $event->event_date) }}">
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="event_time"><i class="fas fa-clock"></i>زمن الفاعلية</label>
                            <input type="time" id="event_time" name="event_time" class="form-control" value="{{ old('event_time', $event->event_time) }}">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="aid_type" class="required"><i class="fas fa-hand-holding-heart"></i>نوع المساعدة</label>
                            <select id="aid_type" name="aid_type" class="form-control" required>
                                <option value="in_kind" {{ old('aid_type', $event->aid_type) == 'in_kind' ? 'selected' : '' }}>عينية</option>
                                <option value="cash" {{ old('aid_type', $event->aid_type) == 'cash' ? 'selected' : '' }}>نقدية</option>
                                <option value="other" {{ old('aid_type', $event->aid_type) == 'other' ? 'selected' : '' }}>أخرى</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="value"><i class="fas fa-money-bill-wave"></i>القيمة</label>
                            <div class="value-type-toggle" id="valueTypeToggle">
                                <div class="toggle-option" data-type="number" id="opt-number">
                                    <i class="fas fa-hashtag"></i> رقم
                                </div>
                                <div class="toggle-option" data-type="text" id="opt-text">
                                    <i class="fas fa-font"></i> نص
                                </div>
                            </div>
                            
                            <div class="value-number-group" id="valueNumberGroup">
                                <div style="position: relative;">
                                    <span class="currency-symbol">جنيه</span>
                                    <input type="number" id="value_number" name="value_number"
                                           class="form-control"
                                           step="0.01"
                                           value="{{ old('value_number', $event->value) }}">
                                </div>
                            </div>
                            
                            <div class="value-text-group" id="valueTextGroup">
                                <input type="text" id="value_text" name="value_text"
                                       class="form-control"
                                       value="{{ old('value_text', $event->value_text) }}">
                            </div>
                            <input type="hidden" id="value_type" name="value_type" value="{{ old('value_type', $event->value_type) }}">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="extras"><i class="fas fa-sticky-note"></i>الإضافات</label>
                    <textarea id="extras" name="extras" class="form-control">{{ old('extras', $event->extras) }}</textarea>
                </div>
                
                <div class="form-group">
                    <label for="invitation_message"><i class="fas fa-comment-alt"></i>نص رسالة الدعوة</label>
                    <textarea id="invitation_message" name="invitation_message" class="form-control">{{ old('invitation_message', $event->invitation_message) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="default_message"><i class="fas fa-comment-alt"></i>نص الاستلام</label>
                    <textarea id="default_message" name="default_message" class="form-control">{{ old('default_message', $event->default_message) }}</textarea>
                </div>
                
                <div class="form-group">
                    <label for="is_archived"><i class="fas fa-archive"></i>أرشفة الفعالية</label>
                    <input type="checkbox" id="is_archived" name="is_archived" value="1" {{ old('is_archived', $event->is_archived) ? 'checked' : '' }}>
                </div>
                
                <div class="form-group">
                    <label for="is_fixed"><i class="fas fa-lock"></i>تثبيت الفعالية</label>
                    <input type="checkbox" id="is_fixed" name="is_fixed" value="1" {{ old('is_fixed', $event->is_fixed) ? 'checked' : '' }}>
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
                        {{ old('without_notification', $event->without_notification) ? 'checked' : '' }}>

                    <div class="form-note">
                        ضع علامة هنا إذا كانت الفاعلية لا تحتاج إلى إرسال إشعارات للمستفيدين
                    </div>
                </div>
                <div class="form-group" style="text-align: center; margin-top: 40px;">
                    <button type="submit" class="btn">
                        <i class="fas fa-sync-alt"></i> تحديث البيانات
                    </button>
                    <a href="{{ route('events.index') }}" class="btn btn-secondary" style="text-decoration: none;">
                        <i class="fas fa-times"></i> إلغاء
                    </a>
                </div>
            </form>
        </div>
        
        <div class="footer">
            <p>جميع الحقوق محفوظة © 2026 | نظام إدارة الفاعليات</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const valueNumberGroup = document.getElementById('valueNumberGroup');
            const valueTextGroup = document.getElementById('valueTextGroup');
            const valueTypeInput = document.getElementById('value_type');
            const toggleOptions = document.querySelectorAll('.toggle-option');
            
            function switchValueType(type) {
                toggleOptions.forEach(opt => {
                    opt.classList.toggle('active', opt.dataset.type === type);
                });
                
                if (type === 'number') {
                    valueNumberGroup.style.display = 'block';
                    valueTextGroup.style.display = 'none';
                } else {
                    valueNumberGroup.style.display = 'none';
                    valueTextGroup.style.display = 'block';
                }
                valueTypeInput.value = type;
            }

            toggleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    switchValueType(this.dataset.type);
                });
            });

            // تفعيل النوع المخزن مسبقاً عند تحميل الصفحة
            switchValueType(valueTypeInput.value);
        });
    </script>
</body>
</html>