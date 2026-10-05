<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تعديل بيانات المستخدم</title>
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
    body { font-family:'Tajawal', sans-serif; background:#f8fafc; padding:20px; }
    .container { max-width:1200px; margin:auto; }
    h1 { text-align:center; margin-bottom:30px; }
    .info-card { background:white; padding:20px; margin-bottom:20px; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.1); }
    .info-card h3 { margin-bottom:15px; color:#4361ee; }

    .info-item { display:flex; flex-direction:column; margin-bottom:12px; }
    .info-item label { font-weight:600; margin-bottom:5px; }
    .info-item input, .info-item textarea { padding:8px; border-radius:6px; border:1px solid #ccc; width:100%; }
    .family-card, .message-card { background:#f3f4f6; padding:15px; border-radius:12px; margin-bottom:10px; }
    .btn { padding:10px 20px; border:none; border-radius:12px; cursor:pointer; margin-top:15px; display:inline-block; text-decoration:none; }
    .btn-success { background:#10b981; color:white; }
    .btn-danger { background:#ef4444; color:white; }
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
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .info-item select.form-control {
            width: 100%;
            height: 45px; /* أو القيمة المستخدمة في الـ input فوقه */
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 8px; /* لتكون الحواف دائرية مثله */
            background-color: #fff;
            font-family: inherit;
            font-size: 14px;
            appearance: none; /* لإلغاء سهم المتصفح الافتراضي إذا أردت */
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); /* إضافة سهم أنيق */
            background-repeat: no-repeat;
            background-position: left 15px center; /* ليكون السهم على اليسار بما أن اللغة عربية */
            background-size: 15px;
        }

        /* تحسين شكل الـ focus عند الضغط */
        .info-item select.form-control:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <button class="back-btn" onclick="goBack()">
            <i class="fas fa-arrow-right"></i> رجوع
        </button>
        <h1><i class="fas fa-user-circle"></i> تعديل المستخدم</h1>
        <p>تعديل كامل بيانات المستخدم والمرافقين </p>
    </div>

    <div class="info-card" style="border: 2px solid var(--primary);">
    <h3><i class="fas fa-lock"></i> إدارة حساب الدخول للموقع</h3>
        <div class="card-body">
            @if(!$user->user)
                {{-- فورم مستقل تماماً --}}
                <form action="{{ route('admin.telegram.createLoginUser', $user->id) }}" method="POST">
                    @csrf
                    <p class="text-muted">
                        المستخدم لا يملك حساب دخول حالياً.
                        <br>
                        سيتم استخدام رقم الهوية <strong>({{ $user->national_id }})</strong> كـ "اسم مستخدم" و "كلمة مرور".
                    </p>
                    <button type="submit" class="btn btn-primary" style="background: var(--primary);">
                        🔐 إنشاء حساب دخول الآن
                    </button>
                </form>
            @else
                <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px;">
                    <i class="fas fa-check-circle"></i> <strong>الحساب مفعل وجاهز.</strong>
                    <br>
                    <strong>اسم المستخدم:</strong>
                    <span style="direction: ltr; display: inline-block;">
                        {{ $user->user->email ?? $user->national_id }}
                    </span>
                </div>
            @endif
        </div>
    </div>
<div class="info-card">
<h3><i class="fas fa-sticky-note"></i> ملاحظات المستخدم</h3>

<form action="{{ route('admin.telegram.notes.store',$user->id) }}" method="POST">
@csrf

<div class="info-item">
<label>إضافة ملاحظة جديدة</label>
<textarea name="note" required placeholder="اكتب الملاحظة هنا..."></textarea>
</div>

<button class="btn btn-success">
<i class="fas fa-plus"></i> إضافة الملاحظة
</button>

</form>

<hr style="margin:20px 0">

@forelse($user->notes->sortByDesc('created_at') as $note)

<div style="background:#fff8e1;padding:12px;border-radius:8px;margin-bottom:10px;position:relative;border:1px solid #facc15">

<form action="{{ route('admin.telegram.notes.delete',$note->id) }}" method="POST" style="position:absolute;left:10px;top:10px;">
@csrf
@method('DELETE')

<button onclick="return confirm('هل تريد حذف هذه الملاحظة؟')"
style="background:#ef4444;color:white;border:none;padding:4px 10px;border-radius:5px;cursor:pointer;">
<i class="fas fa-trash"></i>
</button>

</form>

<strong>{{ $note->creator->name ?? 'النظام' }}</strong>

<small style="color:#6b7280">
{{ $note->created_at->format('Y-m-d H:i') }}
</small>

<p style="margin-top:6px">
{{ $note->note }}
</p>

</div>

@empty

<p style="color:#6b7280">لا توجد ملاحظات لهذا المستخدم</p>

@endforelse

</div>

    <form action="{{ route('admin.telegram.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- المعلومات الأساسية -->
        <div class="info-card">
            <h3><i class="fas fa-info-circle"></i> المعلومات الأساسية</h3>
            <div class="info-item">
                <label for="categories">الفئات</label>
                <div class="form-check mt-2">
                    <input type="checkbox" name="change_main_category" value="1" class="form-check-input" id="changeMain">
                    <label class="form-check-label" for="changeMain">
                        🔄 أريد تغيير الفئة الأساسية وإعادة توليد الكود
                    </label>
                </div>
                <select name="categories[]" multiple class="form-control" style="height: 200px !important;">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ in_array($category->id, old('categories', $user->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $category->name }} ({{ $category->prefix }})
                        </option>
                    @endforeach
                </select>

                {{-- 🟡 إذا عنده كود --}}
                @if($user->access_code)
                    <div class="alert alert-warning mt-2 py-1 px-2 small">
                        ⚠️ الفئة الأساسية الحالية: ({{ $user->access_code }})<br>
                        ✔ يمكنك إضافة فئات أخرى<br>
                        🔄 لتغيير الفئة الأساسية يجب تفعيل خيار إعادة توليد الكود
                    </div>
                @endif
            </div>

            <div class="info-item">
                <label>الاسم الكامل</label>
                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}">
            </div>

            <div class="info-item">
                <label>الاسم الأول</label>
                <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}">
            </div>

            <div class="info-item">
                <label>اسم المستخدم</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}">
            </div>

            <div class="info-item">
                <label>رقم الهوية</label>
                <input type="text" name="national_id" value="{{ old('national_id', $user->national_id) }}">
            </div>

            <div class="info-item">
                <label>معرف المحادثة</label>
                <input type="text" name="chat_id" value="{{ old('chat_id', $user->chat_id) }}">
            </div>
            <div class="info-item">
                <label>الفئة المستهدفة</label>
                <input type="text" name="target_case" value="{{ old('target_case', $user->target_case) }}">
                
            </div>
            <div class="info-item" id="disease_type_wrapper">
                <label>نوع الحالة المرضية</label>
                <input type="text" name="disease_type" 
                    value="{{ old('disease_type', $user->disease_type) }}" 
                    placeholder="مثال: سرطان، فشل كلوي...">
            </div>
            <div class="info-item">
                <label class="required">حالة الحساب</label>
                <select name="status" class="form-control" required>
                    <option value="pending" {{ (old('status', $user->is_completed ? 'completed' : ($user->is_blocked ? 'blocked' : 'pending'))) == 'pending' ? 'selected' : '' }}>قيد التسجيل</option>
                    <option value="completed" {{ (old('status', $user->is_completed ? 'completed' : '')) == 'completed' ? 'selected' : '' }}>مكتمل</option>
                    <option value="blocked" {{ (old('status', $user->is_blocked ? 'blocked' : '')) == 'blocked' ? 'selected' : '' }}>محظور</option>
                </select>
                @error('status')
                    <div class="error-message"><i class="fas fa-exclamation-triangle"></i> {{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- معلومات الاتصال -->
        <div class="info-card">
            <h3><i class="fas fa-address-book"></i> معلومات الاتصال</h3>
            <div class="info-item">
                <label>واتساب</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}">
            </div>
            <div class="info-item">
                <label>تيليجرام</label>
                <input type="text" name="telegram_number" value="{{ old('telegram_number', $user->telegram_number) }}">
            </div>
            <div class="info-item">
                <label>رقم مصر</label>
                <input type="text" name="egypt_number" value="{{ old('egypt_number', $user->egypt_number) }}">
            </div>
        </div>

        <!-- الموقع -->
        <div class="info-card">
            <h3><i class="fas fa-map-marker-alt"></i> المعلومات الجغرافية</h3>
            <div class="info-item">
                <label>المحافظة</label>
                <input type="text" name="governorate" value="{{ old('governorate', $user->governorate) }}">
            </div>
            <div class="info-item">
                <label>المدينة</label>
                <input type="text" name="city" value="{{ old('city', $user->city) }}">
            </div>
            <div class="info-item">
                <label>العنوان</label>
                <textarea name="address">{{ old('address', $user->address) }}</textarea>
            </div>

        </div>

        <!-- أفراد الأسرة -->
        <div class="info-card">
            <h3><i class="fas fa-users"></i> المرافقين (أفراد الأسرة)</h3>

            <div id="family-list">
                @foreach($user->familyMembers as $member)
                <div class="family-card" id="member-row-{{ $member->id }}" style="position: relative; border: 1px solid #ddd; padding: 15px; margin-bottom: 10px;">
                    <button type="button" onclick="markForDeletion({{ $member->id }})"
                            style="position: absolute; left: 10px; top: 10px; background: #ff4d4d; color: white; border: none; border-radius: 5px; cursor: pointer; padding: 5px 10px;">
                        <i class="fas fa-trash"></i> حذف
                    </button>

                    <div class="info-item">
                        <label>الاسم الكامل</label>
                        <input type="text" name="family_members[{{ $member->id }}][full_name]" value="{{ $member->full_name }}">
                    </div>
                    <div class="info-item">
                        <label>رقم الهوية</label>
                        <input type="text" name="family_members[{{ $member->id }}][national_id]" value="{{ $member->national_id }}">
                    </div>
                </div>
                @endforeach
            </div>

            <div id="new-members-container"></div>

            <div id="delete-queue"></div>

            <hr>

            <button type="button" class="btn" style="background:#4361ee; color:white;" onclick="addNewMemberField()">
                <i class="fas fa-plus"></i> إضافة مرافق جديد
            </button>
        </div>

        <br>
        <div class="info-card">
            <div class="info-item">
                <h3><i class="fas fa-route"></i> المرحلة التي وصل إليها المستخدم </h3>
                <select name="step" class="form-control">
                    <option value="check_national_id" {{ $user->step == 'check_national_id' ? 'selected' : '' }}>التحقق من رقم الهوية</option>
                    <option value="name" {{ $user->step == 'name' ? 'selected' : '' }}>الاسم</option>
                    <option value="whatsapp" {{ $user->step == 'whatsapp' ? 'selected' : '' }}>واتساب</option>
                    <option value="telegram" {{ $user->step == 'telegram' ? 'selected' : '' }}>تيليجرام</option>
                    <option value="egypt_number" {{ $user->step == 'egypt_number' ? 'selected' : '' }}>رقم مصر</option>
                    <option value="family" {{ $user->step == 'family' ? 'selected' : '' }}>عدد أفراد الأسرة</option>
                    <option value="family_member_name" {{ $user->step == 'family_member_name' ? 'selected' : '' }}>اسم المرافق</option>
                    <option value="family_member_national_id" {{ $user->step == 'family_member_national_id' ? 'selected' : '' }}>رقم هوية المرافق</option>
                    <option value="governorate" {{ $user->step == 'governorate' ? 'selected' : '' }}>المحافظة</option>
                    <option value="city" {{ $user->step == 'city' ? 'selected' : '' }}>المدينة</option>
                    <option value="address" {{ $user->step == 'address' ? 'selected' : '' }}>العنوان</option>
                    <option value="benefited" {{ $user->step == 'benefited' ? 'selected' : '' }}>هل استفاد من قبل</option>
                    <option value="case" {{ $user->step == 'case' ? 'selected' : '' }}>الحالة</option>
                    <option value="entry_to_egypt" {{ $user->step == 'entry_to_egypt' ? 'selected' : '' }}>تاريخ الدخول إلى مصر</option>
                    <option value="rent_amount" {{ $user->step == 'rent_amount' ? 'selected' : '' }}>قيمة الإيجار</option>
                    <option value="landlord_phone" {{ $user->step == 'landlord_phone' ? 'selected' : '' }}>رقم هاتف صاحب الشقة</option>
                    <option value="done" {{ $user->step == 'done' ? 'selected' : '' }}>مكتمل</option>
                </select>
            </div>
        </div>
        <br>

        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> حفظ التعديلات</button>
        <a href="{{ route('admin.telegram.details', $user->id) }}" class="btn btn-danger"><i class="fas fa-times"></i> إلغاء</a>
    </form>
</div>
</body>


<script>
    let newMemberIndex = 0;

    // وظيفة إضافة فورم جديد في كل ضغطة
    function addNewMemberField() {
        const container = document.getElementById('new-members-container');

        const div = document.createElement('div');
        div.className = 'family-card';
        div.id = `new-member-row-${newMemberIndex}`;
        div.style = "border: 2px dashed #4361ee; background: #f0f7ff; position: relative; padding: 15px; margin-bottom: 10px;";

        div.innerHTML = `
            <button type="button" onclick="removeNewField(${newMemberIndex})"
                    style="position: absolute; left: 10px; top: 10px; background: #64748b; color: white; border: none; border-radius: 5px; cursor: pointer; padding: 5px 10px;">
                <i class="fas fa-times"></i> إلغاء
            </button>
            <h4>مرافق جديد مستعد للإضافة</h4>
            <div class="info-item">
                <label>الاسم الكامل للمرافق</label>
                <input type="text" name="new_family_members[${newMemberIndex}][full_name]" required>
            </div>
            <div class="info-item">
                <label>رقم هوية المرافق</label>
                <input type="text" name="new_family_members[${newMemberIndex}][national_id]" required>
            </div>
        `;

        container.appendChild(div);
        newMemberIndex++;
    }

    // حذف الفورم الجديد قبل الحفظ (إلغاء الإضافة)
    function removeNewField(index) {
        const element = document.getElementById(`new-member-row-${index}`);
        element.remove();
    }

    function markForDeletion(id) {
        if(confirm('هل أنت متأكد من حذف هذا المرافق؟ لن يتم الحذف نهائياً إلا بعد الضغط على "حفظ التعديلات".')) {
            document.getElementById('member-row-' + id).style.display = 'none';
            var queue = document.getElementById('delete-queue');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_members[]';
            input.value = id;
            queue.appendChild(input);
        }
    }

    function goBack() {
    if (document.referrer !== "") {
        window.location.href = document.referrer;
    } else {
        window.location.href = "{{ route('admin.telegram.index') }}";
    }
}
</script>

</html>
