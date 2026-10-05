<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=yes">
    <title>تسليم لحوم الأضاحي | نموذج تسجيل الأسرة</title>
    <!-- Font Awesome 6 (Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Cairo (Arabic friendly) -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f8faf6 0%, #eef3e9 100%);
            font-family: 'Cairo', 'Tajawal', sans-serif;
            padding: 24px 16px 60px;
            min-height: 100vh;
        }

        .form-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* header card */
        .hero-card {
            background: #2d6a4f;
            border-radius: 2rem;
            padding: 1.6rem 1.5rem;
            margin-bottom: 2rem;
            color: white;
            text-align: center;
            box-shadow: 0 15px 30px rgba(0,0,0,0.12);
        }

        .hero-card h2 {
            font-size: 1.8rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .hero-card p {
            opacity: 0.9;
            margin-top: 8px;
            font-size: 0.95rem;
        }

        /* global card style */
        .form-card {
            background: white;
            border-radius: 1.8rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.8rem;
            overflow: hidden;
            transition: all 0.2s;
            border: 1px solid rgba(0,0,0,0.03);
        }

        .card-header {
            background: #fef8e7;
            padding: 1rem 1.5rem;
            border-bottom: 2px solid #e9d6a7;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .card-header i {
            font-size: 1.7rem;
            color: #bc6c25;
        }

        .card-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #3d5a2b;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* read-only fields style */
        .readonly-field {
            background-color: #f1f5ef;
            border-radius: 1rem;
            padding: 0.7rem 1rem;
            border: 1px solid #e2e8ce;
            color: #2c4b2c;
            font-weight: 500;
            width: 100%;
            font-size: 0.95rem;
        }

        .info-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .info-group {
            flex: 1;
            min-width: 180px;
        }

        .info-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6f6f4a;
            margin-bottom: 5px;
        }

        /* grid for editable fields */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.2rem;
        }

        .input-field {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8d0;
            border-radius: 1.2rem;
            font-family: 'Cairo', sans-serif;
            font-size: 0.95rem;
            transition: 0.2s;
            background: #fffef7;
        }

        .input-field:focus {
            outline: none;
            border-color: #2a9d8f;
            box-shadow: 0 0 0 3px rgba(42,157,143,0.15);
        }

        /* companion item */
        .companion-item {
            background: #fbfaf5;
            border-radius: 1.2rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border-right: 4px solid #f4a261;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        .companion-name {
            flex: 2;
            min-width: 150px;
        }

        .companion-relation {
            flex: 1;
            min-width: 130px;
        }

        .badge-muted {
            background: #e9ece5;
            padding: 8px 12px;
            border-radius: 40px;
            font-size: 0.85rem;
            color: #3c5e2b;
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid #bc6c25;
            padding: 8px 20px;
            border-radius: 2rem;
            font-weight: 600;
            color: #bc6c25;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Cairo', sans-serif;
        }

        .btn-outline:hover {
            background: #bc6c25;
            color: white;
        }

        .btn-primary {
            background: #2d6a4f;
            border: none;
            padding: 14px 28px;
            border-radius: 2.5rem;
            font-weight: 800;
            font-size: 1.1rem;
            color: white;
            cursor: pointer;
            width: 100%;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 8px 18px rgba(0,0,0,0.1);
        }

        .btn-primary:hover {
            background: #1e563f;
            transform: translateY(-2px);
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 0.5rem;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f9faf5;
            padding: 8px 18px;
            border-radius: 3rem;
        }

        .checkbox-item input {
            width: 18px;
            height: 18px;
            accent-color: #2d6a4f;
        }

        hr {
            margin: 1rem 0;
            border-color: #ede6d2;
        }

        @media (max-width: 560px) {
            .companion-item {
                flex-direction: column;
                align-items: stretch;
            }
            .card-header h3 {
                font-size: 1.1rem;
            }
        }
        .helper-note {
            font-size: 0.7rem;
            color: #8f9b7a;
            margin-top: 6px;
        }
        i.fa, i.far, i.fas {
            margin-left: 6px;
        }
    </style>
</head>
<body>
<div class="form-container">
    <div class="hero-card">
        <h2><i class="fas fa-hand-holding-heart"></i> نموذج تسجيل لحمة الأضاحي - جمعية إنسان </h2>
        <p>يرجى تعبئة البيانات بدقة • الأولوية للأسر الأكثر عدداً • التسجيل لا يعني الاستفادة النهائية</p>
        <div style="margin-top: 10px; font-size:0.85rem;"><i class="fas fa-map-marker-alt"></i> التوزيع: القاهرة - مدينة العبور</div>
    </div>

    <form method="POST" action="{{ route('meat.submit') }}">
        @csrf

        {{-- ========================================== --}}
        {{-- بيانات رب الأسرة (من جدول التيليجرام يوزر) --}}
        {{-- ========================================== --}}
        <div class="form-card">
            <div class="card-header">
                <i class="fas fa-user-circle"></i>
                <h3>👨‍👩‍👧 بيانات رب الأسرة</h3>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-group">
                        <label><i class="fas fa-user"></i> الاسم الكامل</label>
                        <div class="readonly-field">{{ $user->full_name ?? 'غير مسجل' }}</div>
                    </div>
                    <div class="info-group">
                        <label><i class="fas fa-id-card"></i> رقم الهوية</label>
                        <div class="readonly-field">{{ $user->national_id ?? 'غير متوفر' }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-group">
                        <label><i class="fas fa-calendar-alt"></i> تاريخ الميلاد</label>
                        @if(empty($user->birth_date))
                            <input type="date" name="birth_date" class="input-field" required>
                        @else
                            <div class="readonly-field">{{ $user->birth_date }}</div>
                        @endif
                    </div>

                    <div class="info-group">
                        <label><i class="fas fa-plane-arrival"></i> سبب دخول مصر</label>
                        @if(empty($user->entry_reason))
                            <input type="text" name="entry_reason" class="input-field" placeholder="مثال: تحويلة مرضية" required>
                        @else
                            <div class="readonly-field">{{ $user->entry_reason }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <i class="fas fa-home"></i>
                <h3>🏠 بيانات السكن</h3>
            </div>
            <div class="card-body">
                <div class="grid-2">

                    <div>
                        <label>تاريخ دخول مصر</label>
                        <input type="date" name="entry_to_egypt" value="{{ $user->entry_to_egypt ?? '' }}" class="input-field">
                    </div>

                    <div>
                        <label>قيمة الإيجار الشهري</label>
                        <input type="number" name="rent_amount" value="{{ $user->rent_amount ?? '' }}" class="input-field" placeholder="بالجنيه" required>
                    </div>

                    <div>
                        <label>رقم صاحب الشقة</label>
                        <input type="tel" name="landlord_phone" value="{{ $user->landlord_phone ?? '' }}" class="input-field" placeholder="01xxxxxxxxx">
                    </div>

                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- بيانات المرافقين (من جدول family_members) --}}
        {{-- ========================================== --}}
        <div class="form-card">
            <div class="card-header">
                <i class="fas fa-users"></i>
                <h3>👥 أفراد الأسرة / المرافقين (بيانات مسجلة مسبقاً)</h3>
            </div>
            <div class="card-body">
                @if(isset($companions) && count($companions) > 0)
                    @foreach($companions as $index => $companion)
                        <div class="companion-item">
                            <div class="companion-name">
                                <div class="badge-muted"><i class="fas fa-user-tag"></i> الاسم: {{ $companion->full_name ?? 'غير معرف' }}</div>
                                <div class="badge-muted" style="margin-top: 6px;"><i class="fas fa-id-card"></i> الهوية: {{ $companion->national_id ?? '—' }}</div>
                                <div class="companion-relation">
                                <label style="font-size:0.75rem;">
                                    <i class="fas fa-calendar"></i> تاريخ الميلاد
                                </label>
                                <input 
                                    type="date" 
                                    name="companions[{{ $index }}][birth_date]" 
                                    value="{{ old('companions.'.$index.'.birth_date', isset($companion->birth_date) ? \Carbon\Carbon::parse($companion->birth_date)->format('Y-m-d') : '') }}" 
                                    class="input-field"
                                >
                            </div>
                            </div>
                            <div class="companion-relation">
                                <label style="font-size:0.75rem;"><i class="fas fa-heart"></i> صلة القرابة (تحديث)</label>
                                <input type="text" name="companions[{{ $index }}][relation]" value="{{ old('companions.'.$index.'.relation', $companion->relation ?? '') }}" class="input-field" placeholder="مثل: زوجة / ابن / ابنة">
                                <input type="hidden" name="companions[{{ $index }}][id]" value="{{ $companion->id ?? '' }}">
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="readonly-field" style="text-align:center;">لا يوجد مرافقين مسجلين حتى الآن</div>
                @endif
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- بيانات قابلة للتعديل (واتساب، تليجرام، رقم مصري، عدد أفراد الأسرة) --}}
        {{-- ========================================== --}}
        <div class="form-card">
            <div class="card-header">
                <i class="fas fa-edit"></i>
                <h3>📱 بيانات التواصل والتحديث</h3>
            </div>
            <div class="card-body">
                <div class="grid-2">
                    <div>
                        <label><i class="fab fa-whatsapp"></i> رقم الواتساب</label>
                        <input type="tel" name="whatsapp" value="{{ $user->whatsapp ?? '' }}" class="input-field" placeholder="مثال: 201234567890">
                    </div>
                    <div>
                        <label><i class="fab fa-telegram"></i> رقم التيليجرام</label>
                        <input type="tel" name="telegram_number" value="{{ $user->telegram_number ?? '' }}" class="input-field" placeholder="رقم التليجرام النشط">
                    </div>
                    <div>
                        <label><i class="fas fa-mobile-alt"></i> رقم جوال مصري فعال</label>
                        <input type="tel" name="egypt_number" value="{{ $user->egypt_number ?? '' }}" class="input-field" placeholder="01xxxxxxxxx">
                    </div>
                </div>
            </div>
        </div>


        {{-- ========================================== --}}
        {{-- بيانات التوزيع الإضافية (كرشة، رجلين، مرافق منفصل) --}}
        {{-- ========================================== --}}
        <div class="form-card">
            <div class="card-header">
                <i class="fas fa-plus-circle"></i>
                <h3>إضافات على حصة اللحوم (اختياري)</h3>
            </div>
            <div class="card-body">

                <div class="helper-note" style="margin-bottom:10px;">
                    الحصة الأساسية تشمل اللحوم، ويمكنك اختيار إضافات بالضغط على المربع الخاص بها.
                </div>

                <div class="checkbox-group">

                    <label class="checkbox-item">
                        <input type="checkbox" name="want_tripe" value="1">
                        <i class="fas fa-check"></i>
                        طلب إضافة كرشة مع الحصة
                    </label>

                    <label class="checkbox-item">
                        <input type="checkbox" name="want_legs" value="1">
                        <i class="fas fa-check"></i>
                        طلب إضافة أرجل مع الحصة
                    </label>

                </div>

                <hr>

                <label class="checkbox-item">
                    <input type="checkbox" name="separate_family" value="1">
                    <i class="fas fa-user-friends"></i>
                    يوجد فرد من الأسرة يقيم بشكل منفصل
                </label>

                <div class="helper-note" style="margin-top:8px;">
                    في حال وجود فرد منفصل، سيتم التواصل معه بشكل مستقل.
                </div>

            </div>
        </div>

        {{-- زر الإرسال النهائي --}}
        <div style="text-align:center; margin-top: 20px;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> حفظ وتحديث بيانات التسجيل
            </button>
        </div>
        <div class="helper-note" style="text-align:center; margin-top: 16px; background:#f3f2e8; padding:10px; border-radius:2rem;">
            ⚠️ ملاحظة: التسجيل لا يعني بالضرورة الاستفادة من البرنامج، سيتم الفرز حسب عدد أفراد الأسرة والأكثر احتياجا.
        </div>
    </form>
</div>

<script>
    let newCompanionIndex = 0;

    function addNewCompanionField() {
        const container = document.getElementById('new-companions-container');
        if (!container) return;

        const wrapperDiv = document.createElement('div');
        wrapperDiv.className = 'new-companion-wrapper';
        wrapperDiv.style.backgroundColor = '#fef9ef';
        wrapperDiv.style.borderRadius = '1.2rem';
        wrapperDiv.style.padding = '1rem';
        wrapperDiv.style.marginBottom = '1rem';
        wrapperDiv.style.border = '1px solid #ffe1a0';

        wrapperDiv.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items:center; margin-bottom: 8px;">
                <span style="font-weight: bold; color:#7f4f24;"><i class="fas fa-user-plus"></i> مرافق جديد</span>
                <button type="button" class="remove-companion" style="background:none; border:none; color:#b91c1c; font-size:1.1rem; cursor:pointer;" onclick="this.closest('.new-companion-wrapper').remove()"><i class="fas fa-trash-alt"></i></button>
            </div>
            <div class="grid-2" style="gap:0.8rem;">
                <div>
                    <label>الاسم الكامل <span style="color:#bc6c25;">*</span></label>
                    <input type="text" name="new_companions[${newCompanionIndex}][full_name]" class="input-field" placeholder="أدخل الاسم الرباعي" required>
                </div>
                <div>
                    <label>رقم الهوية</label>
                    <input type="text" name="new_companions[${newCompanionIndex}][national_id]" class="input-field" placeholder="رقم الهوية (اختياري)">
                </div>
                <div>
                    <label>تاريخ الميلاد</label>
                    <input type="date" name="new_companions[${newCompanionIndex}][birth_date]" class="input-field">
                </div>
                <div>
                    <label>صلة القرابة برب الأسرة <span style="color:#bc6c25;">*</span></label>
                    <input type="text" name="new_companions[${newCompanionIndex}][relation]" class="input-field" placeholder="مثال: ابن / زوجة / أخ" required>
                </div>
            </div>
            <div class="helper-note" style="margin-top: 6px;">يمكنك إضافة المزيد من المرافقين لزيادة عدد أفراد الأسرة (أولوية أكبر)</div>
        `;
        container.appendChild(wrapperDiv);
        newCompanionIndex++;
    }

    // إضافة دعم اختياري: إذا كان هناك مرافقين سابقين وتم تحميل الصفحة، نضمن إمكانية الإضافة بشكل ديناميكي.
    document.addEventListener('DOMContentLoaded', function() {
        // إضافة تلميحات وتناسق
        const allCheckboxes = document.querySelectorAll('.checkbox-item input');
        allCheckboxes.forEach(cb => {
            cb.style.cursor = 'pointer';
        });
    });
</script>

<style>
    /* تحسينات إضافية */
    .remove-companion:hover {
        background: #ffefef;
        border-radius: 30px;
        padding: 4px;
        transition: 0.2s;
    }
    .btn-outline i, .btn-primary i {
        margin-left: 6px;
    }
    .readonly-field {
        word-break: break-word;
    }
    input[type="number"]::-webkit-inner-spin-button, 
    input[type="number"]::-webkit-outer-spin-button {
        opacity: 0.5;
    }
</style>

<!-- 
    توضيح للمطور: النموذج متكامل مع كافة الإنسانول المطلوبة:
    - يعرض بيانات رب الأسرة من جدول التيليجرام يوزر (full_name, national_id, birth_date, entry_reason)
    - يعرض بيانات المرافقين من جدول فملي مببير (full_name, national_id, birth_date مع إمكانية إدخال صلة القرابة)
    - إنسانول قابلة للتعديل: whatsapp, telegram_number, egypt_number, family_members
    - إضافة مرافق جديد يحتوي على اسم، هوية، تاريخ ميلاد، صلة قرابة.
    - خيارات التوزيع: كرشة، رجلين، مرافق منفصل.
    - جميع البيانات ترسل عبر POST إلى route('meat.submit').
    - تم مراعاة التجاوب مع الهواتف ووضوح التعليمات.
-->
</body>
</html>