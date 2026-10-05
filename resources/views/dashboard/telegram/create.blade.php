<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إنشاء مستخدم جديد</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml"
  href="data:image/svg+xml,
  <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
    <circle cx='50' cy='50' r='48' fill='%23ff9f1c'/>
    <path d='M50 20c-15 0-20 10-20 20v20l-10 10h60l-10-10V40c0-10-5-20-20-20z' fill='white'/>
    <circle cx='50' cy='80' r='7' fill='white'/>
  </svg>">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Tajawal', sans-serif;
    }

    :root {
        --primary: #4361ee;
        --primary-dark: #3a56d4;
        --secondary: #7209b7;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --dark: #1f2937;
        --gray: #6b7280;
        --light-gray: #f3f4f6;
        --border-gray: #e5e7eb;
        --border-radius: 12px;
        --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        color: var(--dark);
        line-height: 1.6;
        min-height: 100vh;
        padding: 20px;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .header {
        text-align: center;
        margin-bottom: 30px;
        padding: 25px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        border-radius: var(--border-radius);
        color: white;
        box-shadow: var(--box-shadow);
        position: relative;
        overflow: hidden;
    }

    .header h1 {
        font-size: 2rem;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .header p {
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .back-btn {
        position: absolute;
        right: 25px;
        top: 25px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: var(--border-radius);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .back-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .info-card {
        background: white;
        padding: 25px;
        margin-bottom: 25px;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        border-left: 5px solid var(--primary);
    }

    .info-card h3 {
        margin-bottom: 20px;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.3rem;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--light-gray);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .info-item {
        margin-bottom: 18px;
    }

    .info-item label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--dark);
        font-size: 0.95rem;
    }

    .info-item input,
    .info-item textarea,
    .info-item select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--border-gray);
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s;
        background: white;
    }

    .info-item input:focus,
    .info-item textarea:focus,
    .info-item select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    .info-item textarea {
        min-height: 100px;
        resize: vertical;
    }

    .checkbox-group {
        display: flex;
        gap: 25px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .family-card {
        background: var(--light-gray);
        padding: 20px;
        border-radius: var(--border-radius);
        margin-bottom: 15px;
        border: 2px dashed var(--border-gray);
        position: relative;
    }

    .family-card h4 {
        color: var(--secondary);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn {
        padding: 12px 25px;
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--box-shadow);
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-success:hover {
        background: #0da271;
        transform: translateY(-2px);
        box-shadow: var(--box-shadow);
    }

    .btn-danger {
        background: var(--danger);
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: var(--box-shadow);
    }

    .btn-secondary {
        background: var(--gray);
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
        transform: translateY(-2px);
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 2px solid var(--light-gray);
    }

    .required::after {
        content: " *";
        color: var(--danger);
    }

    .error-message {
        color: var(--danger);
        font-size: 0.9rem;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .success-message {
        color: var(--success);
        font-size: 0.9rem;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .alert {
        padding: 15px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <a href="{{ route('admin.telegram.index') }}" class="back-btn">
            <i class="fas fa-arrow-right"></i> رجوع للقائمة
        </a>
        <h1><i class="fas fa-user-plus"></i> إنشاء مستخدم جديد</h1>
        <p>إضافة مستخدم جديد إلى النظام يدوياً</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <strong>حدثت الأخطاء التالية:</strong>
            <ul style="margin-top: 5px; margin-right: 20px;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.telegram.store') }}" method="POST" id="createUserForm">
        @csrf

        <!-- المعلومات الأساسية -->
        <div class="info-card">
            <h3><i class="fas fa-info-circle"></i> المعلومات الأساسية</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label class="required">الاسم الكامل</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required>
                    @error('full_name')
                        <div class="error-message"><i class="fas fa-exclamation-triangle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="info-item">
                    <label class="required">رقم الهوية</label>
                    <input type="text" name="national_id" value="{{ old('national_id') }}"required>
                    @error('national_id')
                        <div class="error-message"><i class="fas fa-exclamation-triangle"></i> {{ $message }}</div>
                    @enderror
                </div>
                <div class="info-item">
                    <label class="required">الفئة</label>
                    <select name="category_id" class="form-control">
                        <option value="">-- اختر فئة لإنشاء كود تلقائي --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->prefix }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="info-item">
                    <label class="required">حالة الحساب</label>
                    <select name="status" class="form-control" required>
                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>قيد التسجيل</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                        <option value="blocked" {{ old('status') == 'blocked' ? 'selected' : '' }}>محظور</option>
                    </select>
                    @error('status')
                        <div class="error-message"><i class="fas fa-exclamation-triangle"></i> {{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- معلومات الاتصال -->
        <div class="info-card">
            <h3><i class="fas fa-address-book"></i> معلومات الاتصال</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>رقم الواتساب</label>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp') }}">
                </div>

                <div class="info-item">
                    <label>رقم التليجرام</label>
                    <input type="text" name="telegram_number" value="{{ old('telegram_number') }}">
                </div>

                <div class="info-item">
                    <label>رقم مصر</label>
                    <input type="text" name="egypt_number" value="{{ old('egypt_number') }}">
                </div>
            </div>
        </div>

        <!-- الموقع الجغرافي -->
        <div class="info-card">
            <h3><i class="fas fa-map-marker-alt"></i> المعلومات الجغرافية</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>المحافظة</label>
                    <input type="text" name="governorate" value="{{ old('governorate') }}">
                </div>

                <div class="info-item">
                    <label>المدينة</label>
                    <input type="text" name="city" value="{{ old('city') }}">
                </div>

                <div class="info-item">
                    <label>العنوان التفصيلي</label>
                    <textarea name="address">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        <!-- الحالات -->
        <div class="info-card">
            <h3><i class="fas fa-user-check"></i> حالات المستخدم</h3>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" name="benefited_before" value="1" id="benefited_before" {{ old('benefited_before') ? 'checked' : '' }}>
                    <label for="benefited_before">استفاد من قبل</label>
                </div>

                <div class="checkbox-item">
                    <input type="checkbox" name="is_blocked" value="1" id="is_blocked" {{ old('is_blocked') ? 'checked' : '' }}>
                    <label for="is_blocked">حظر المستخدم</label>
                </div>
            </div>
        </div>

        <!-- المرافقين (أفراد الأسرة) -->
        <div class="info-card">
            <h3><i class="fas fa-users"></i> المرافقين (أفراد الأسرة)</h3>
            <div id="family-members-container">
                <!-- سيتم إضافة حقول المرافقين هنا ديناميكياً -->
            </div>

            <button type="button" class="btn btn-primary" onclick="addFamilyMember()" style="margin-top: 15px;">
                <i class="fas fa-plus"></i> إضافة مرافق جديد
            </button>
        </div>
        <!-- الفاعلية ونوع الإرسال -->
        <div class="info-card">
            <h3><i class="fas fa-calendar-check"></i> ربط المستخدم بفاعلية</h3>

            <div class="info-item">
                <label>اختر الفاعلية (اختياري)</label>
                <select name="event_id">
                    <option value="">-- بدون فاعلية --</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}">
                            {{ $event->name }} - {{ $event->event_date }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="info-item">
                <label>نوع الإرسال</label>
                <select name="message_type">
                    <option value="invitation">✉️ دعوة</option>
                    <option value="received">✅ تم الاستلام</option>
                    <option value="attended">📊 تم الحضور</option>
                    <option value="general">📢 عام</option>
                </select>
            </div>

            <small style="color:#6b7280">
                ⚠️ لن يتم إرسال أي رسالة تيليجرام، هذا الربط لأغراض التوثيق فقط.
            </small>
        </div>

        <!-- أزرار الإجراءات -->
        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> حفظ المستخدم
            </button>

            <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                <i class="fas fa-redo"></i> تفريغ الحقول
            </button>

            <a href="{{ route('admin.telegram.index') }}" class="btn btn-danger">
                <i class="fas fa-times"></i> إلغاء
            </a>
        </div>
    </form>
</div>
<script>
    let familyMemberCount = 0;

    /**
     * إضافة مرافق جديد
     * سيتم استدعاء هذه الدالة فقط عند الضغط على الزر
     */
    function addFamilyMember() {
        const container = document.getElementById('family-members-container');
        const memberId = familyMemberCount;

        const memberDiv = document.createElement('div');
        memberDiv.className = 'family-card';
        memberDiv.id = `family-member-${memberId}`;

        memberDiv.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4><i class="fas fa-user-friends"></i> المرافق</h4>
                <button type="button" class="btn btn-danger" onclick="removeFamilyMember(${memberId})" style="padding: 5px 10px; font-size: 0.9rem;">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <label class="required">الاسم الكامل للمرافق</label>
                    <input type="text" name="family_members[${memberId}][full_name]" required>
                </div>
                <div class="info-item">
                    <label>رقم هوية المرافق</label>
                    <input type="text" name="family_members[${memberId}][national_id]">
                </div>
            </div>
            <hr style="margin: 20px 0; border-color: var(--border-gray);">
        `;

        container.appendChild(memberDiv);
        familyMemberCount++;

        // تحديث الأرقام التسلسلية بصرياً
        reorderMemberTitles();
    }

    /**
     * حذف المرافق مع تحديث العناوين
     */
    function removeFamilyMember(id) {
        if (confirm('هل تريد حذف هذا المرافق؟')) {
            const element = document.getElementById(`family-member-${id}`);
            if (element) {
                element.remove();
                reorderMemberTitles();
            }
        }
    }

    /**
     * تحديث عناوين المرافقين (المرافق 1، المرافق 2...)
     */
    function reorderMemberTitles() {
        const cards = document.querySelectorAll('.family-card');
        cards.forEach((card, index) => {
            const header = card.querySelector('h4');
            if (header) {
                header.innerHTML = `<i class="fas fa-user-friends"></i> المرافق ${index + 1}`;
            }
        });
    }

    /**
     * تفريغ الحقول وإفراغ حاوية المرافقين تماماً
     */
    function resetForm() {
        if (confirm('هل تريد تفريغ جميع الحقول؟ سيتم فقدان جميع البيانات المدخلة.')) {
            document.getElementById('createUserForm').reset();
            document.getElementById('family-members-container').innerHTML = '';
            familyMemberCount = 0;
        }
    }

    /**
     * عند تحميل الصفحة
     */
    document.addEventListener('DOMContentLoaded', function() {
        // يتم استرجاع المرافقين فقط في حال وجود بيانات سابقة (مثل فشل التحقق Validation)
        @if(old('family_members'))
            const oldMembers = @json(old('family_members'));
            Object.values(oldMembers).forEach((member) => {
                if (member && member.full_name) {
                    addFamilyMember();
                    const cards = document.querySelectorAll('.family-card');
                    const lastCard = cards[cards.length - 1];
                    lastCard.querySelector('[name*="[full_name]"]').value = member.full_name;
                    lastCard.querySelector('[name*="[national_id]"]').value = member.national_id || '';
                }
            });
        @endif

        // ملاحظة: تم حذف الكود الذي كان يضيف مرافقاً فارغاً تلقائياً هنا
    });

    /**
     * معالجة البيانات قبل الإرسال لضمان ترتيب المصفوفة
     */
    document.getElementById('createUserForm').addEventListener('submit', function(e) {
        const remainingMembers = document.querySelectorAll('.family-card');

        remainingMembers.forEach((card, index) => {
            const inputs = card.querySelectorAll('input');
            inputs.forEach(input => {
                const name = input.name;
                // إعادة بناء الاسم لضمان تسلسل الإندكس (0, 1, 2...) في السيرفر
                const newName = name.replace(/family_members\[\d+\]/, `family_members[${index}]`);
                input.name = newName;
            });
        });
    });
</script>
</body>
</html>
