<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الرسالة - لوحة التحكم</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
            --info-color: #7209b7;
            --warning-color: #f72585;
        }
        
        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #333;
            min-height: 100vh;
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(67, 97, 238, 0.2);
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            overflow: hidden;
            margin-bottom: 25px;
        }
        
        .card:hover {
            transform: translateY(-3px);
        }
        
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
            font-weight: 600;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(58, 12, 163, 0.1));
            border-bottom: 2px solid rgba(67, 97, 238, 0.2);
        }
        
        .card-header.bg-soft-primary {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.15), rgba(58, 12, 163, 0.15));
            border-bottom: 2px solid rgba(67, 97, 238, 0.3);
        }
        
        .card-header.bg-soft-success {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.15), rgba(72, 149, 239, 0.15));
            border-bottom: 2px solid rgba(76, 201, 240, 0.3);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
            transform: translateY(-2px);
        }
        
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        
        .required-star {
            color: #dc3545;
            margin-right: 4px;
        }
        
        .btn {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #4cc9f0, #4895ef);
        }
        
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(76, 201, 240, 0.3);
        }
        
        .btn-light {
            background: #fff;
            border: 2px solid #e0e0e0;
            color: #555;
        }
        
        .btn-light:hover {
            background: #f8f9fa;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .input-group .form-control {
            border-radius: 10px 0 0 10px;
        }
        
        .input-group-text {
            border-radius: 0 10px 10px 0;
            background-color: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-left: none;
        }
        
        .select2-container--default .select2-selection--single {
            border: 2px solid #e0e0e0;
            height: 48px;
            border-radius: 10px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 48px;
            padding-right: 15px;
            padding-left: 30px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 48px;
            left: 10px;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .alert-info {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.1), rgba(72, 149, 239, 0.1));
            border-left: 4px solid #4cc9f0;
        }
        
        .preview-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 2px dashed #dee2e6;
        }
        
        .preview-card:hover {
            background: linear-gradient(135deg, #f1f3f5, #dee2e6);
        }
        
        .char-count {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 20px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
        }
        
        .char-count.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        
        .char-count.danger {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .badge {
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-left: 5px;
        }
        
        .status-sent { background-color: #28a745; }
        .status-delivered { background-color: #007bff; }
        .status-failed { background-color: #dc3545; }
        .status-pending { background-color: #ffc107; }
        
        @media (max-width: 768px) {
            .card {
                margin-bottom: 15px;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-2">
                                @if(isset($message) && $message->id)
                                ✏️ تعديل الرسالة
                                @else
                                📝 رسالة جديدة
                                @endif
                            </h1>
                            <p class="mb-0 opacity-75">
                                @if(isset($message) && $message->id)
                                تعديل بيانات الرسالة المرسلة عبر التليجرام
                                @else
                                إنشاء رسالة جديدة وإرسالها عبر التليجرام
                                @endif
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('admin.messages.index') }}" class="btn btn-light">
                                <i class="bi bi-arrow-right me-2"></i> رجوع للقائمة
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-danger">
                    <h6 class="alert-heading">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> هناك أخطاء يجب تصحيحها
                    </h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Main Form -->
        <form action="{{ isset($message) ? route('admin.messages.update', $message->id) : '#' }}" method="POST" id="messageForm">
            @csrf
            @if(isset($message) && $message->id)
                @method('PUT')
            @endif
            
            <div class="row">
                <!-- بيانات المستلم -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-soft-primary">
                            <h5 class="mb-0">
                                <i class="bi bi-person-circle me-2"></i> بيانات المستلم
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="form-label">
                                    <span class="required-star">*</span> اختيار المستخدم
                                </label>
                                <select name="telegram_user_id" class="form-select select2" required
                                        data-placeholder="ابحث واختر المستخدم">
                                    <option value=""></option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                                {{ (isset($message) && $message->telegram_user_id == $user->id) ? 'selected' : '' }}
                                                data-benefit="{{ $user->benefit_count ?? 0 }}"
                                                data-username="{{ $user->username ?? '' }}"
                                                data-phone="{{ $user->phone ?? '' }}">
                                            {{ $user->full_name }} 
                                            @if($user->username)
                                                ({{ '@' . $user->username }})
                                            @endif
                                            - {{ $user->phone ?? 'بدون رقم' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i> يمكنك البحث بالاسم أو المعرف أو رقم الهاتف
                                </small>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">عدد مرات الاستفادة</label>
                                <div class="input-group">
                                    <input type="number" 
                                           name="benefit_count" 
                                           class="form-control" 
                                           value="{{ isset($message) ? ($message->user->benefit_count ?? 0) : 0 }}"
                                           min="0"
                                           step="1"
                                           id="benefitCount">
                                    <span class="input-group-text">
                                        <i class="bi bi-trophy"></i>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <small class="form-text text-muted">
                                        سيتم تحديث هذا الرقم في جدول المستخدم مباشرة
                                    </small>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="incrementBenefit()">
                                        <i class="bi bi-plus"></i> زيادة
                                    </button>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="bi bi-lightbulb text-info" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading">ملاحظة هامة!</h6>
                                        <p class="mb-0">تغيير المستخدم سيؤدي إلى نقل الرسالة لهذا المستخدم الجديد مع تحديث عدد مرات الاستفادة.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User Info Preview -->
                            <div class="border rounded p-3 mt-3 bg-light" id="userInfoPreview" style="display: none;">
                                <h6 class="mb-2">معلومات المستخدم المحدد:</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">المعرف:</small>
                                        <p class="mb-1 fw-bold" id="previewUsername"></p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">رقم الهاتف:</small>
                                        <p class="mb-0 fw-bold" id="previewPhone"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- بيانات الفعالية والرسالة -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-soft-success">
                            <h5 class="mb-0">
                                <i class="bi bi-chat-left-text me-2"></i> بيانات الفعالية والرسالة
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="form-label">الفعالية التابعة لها</label>
                                <select name="event_id" class="form-select select2" data-placeholder="اختر الفعالية">
                                    <option value=""></option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" 
                                                {{ (isset($message) && $message->event_id == $event->id) ? 'selected' : '' }}>
                                            {{ $event->name }}
                                            @if($event->date)
                                                ({{ $event->date }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i> اختياري - ربط الرسالة بفعالية معينة
                                </small>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">تاريخ الرسالة</label>
                                <input type="datetime-local" name="message_date" class="form-control" 
                                    value="{{ $message->sent_at 
                                        ? \Carbon\Carbon::parse($message->sent_at)->format('Y-m-d\TH:i') 
                                        : now()->format('Y-m-d\TH:i') }}">
                            </div>
                            
                            <div class="mb-4">
                                <label for="message_type" class="form-label">نوع الرسالة</label>
                                <select name="message_type" id="message_type" class="form-control">
                                    <option value="invitation" {{ $message->message_type == 'invitation' ? 'selected' : '' }}>دعوة حضور</option>
                                    <option value="attended" {{ $message->message_type == 'attended' ? 'selected' : '' }}>تم الحضور</option>
                                    <option value="received" {{ $message->message_type == 'received' ? 'selected' : '' }}>تم الاستلام</option>
                                    <option value="general" {{ $message->message_type == 'general' ? 'selected' : '' }}>عامة</option>
                                </select>
                            </div>   

                            <div class="mb-4">
                                <label class="form-label">
                                    <span class="required-star">*</span> حالة التسليم
                                </label>
                                <select name="delivery_status" class="form-select" required>
                                    <option value="sent" {{ (isset($message) && $message->delivery_status == 'sent') ? 'selected' : '' }}>
                                        ✅ مرسلة
                                    </option>
                                    <option value="manual" {{ (isset($message) && $message->delivery_status == 'manual') ? 'selected' : '' }}>
                                        📬 مرسلة دون بوت
                                    </option>
                                    <option value="failed" {{ (isset($message) && $message->delivery_status == 'failed') ? 'selected' : '' }}>
                                        ❌ فشلت
                                    </option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">
                                    <span class="required-star">*</span> نص الرسالة
                                </label>
                                <textarea name="message" 
                                        class="form-control" 
                                        rows="8" 
                                        required
                                        placeholder="اكتب نص الرسالة هنا..."
                                        id="messageText"
                                        oninput="updateCharCount()">{{ old('message', isset($message) ? $message->message : '') }}</textarea>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div>
                                        <span class="char-count" id="charCount">
                                            عدد الأحرف: <span id="charCountNumber">0</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearMessage()">
                                        <i class="bi bi-eraser"></i> مسح
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Preview Section -->
            @if(isset($message) && $message->id)
            <div class="row">
                <div class="col-12">
                    <div class="card preview-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-eye me-2"></i> معاينة سريعة - البيانات الحالية
                                </h6>
                                <small class="text-muted">
                                    آخر تحديث: {{ isset($message) ? $message->updated_at->diffForHumans() : '' }}
                                </small>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="mb-1"><small class="text-muted">المستخدم الحالي:</small></p>
                                    <p class="fw-bold">
                                        <i class="bi bi-person me-1"></i>
                                        {{ isset($message) ? ($message->user->full_name ?? 'غير معروف') : 'غير محدد' }}
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><small class="text-muted">الفعالية الحالية:</small></p>
                                    <p class="fw-bold">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ isset($message) ? ($message->event->name ?? 'لا يوجد') : 'غير محدد' }}
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><small class="text-muted">مرات الاستفادة:</small></p>
                                    <p class="fw-bold">
                                        <i class="bi bi-trophy me-1"></i>
                                        {{ isset($message) ? ($message->user->benefit_count ?? 0) : 0 }}
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><small class="text-muted">الحالة الحالية:</small></p>
                                    <p>
                                        @if(isset($message))
                                            @php
                                                $statusClasses = [
                                                    'sent' => 'bg-success',
                                                    'delivered' => 'bg-primary',
                                                    'failed' => 'bg-danger',
                                                    'pending' => 'bg-warning'
                                                ];
                                                $statusText = [
                                                    'sent' => 'مرسلة',
                                                    'delivered' => 'مستلمة',
                                                    'failed' => 'فشلت',
                                                    'pending' => 'قيد الانتظار'
                                                ];
                                                $statusClass = $statusClasses[$message->delivery_status] ?? 'bg-secondary';
                                                $text = $statusText[$message->delivery_status] ?? $message->delivery_status;
                                            @endphp
                                            <span class="badge {{ $statusClass }}">
                                                {{ $text }}
                                                <span class="status-indicator"></span>
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">غير محدد</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if(isset($message) && $message->message)
                            <div class="mt-3">
                                <p class="mb-1"><small class="text-muted">نص الرسالة الحالي:</small></p>
                                <div class="border rounded p-3 bg-white">
                                    {{ Str::limit($message->message, 150) }}
                                    @if(strlen($message->message) > 150)
                                    <span class="text-muted">...</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Action Buttons -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="button" class="btn btn-light" onclick="window.history.back()">
                                        <i class="bi bi-x-circle me-2"></i> إلغاء
                                    </button>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-success" id="submitBtn">
                                        <i class="bi bi-check-circle me-2"></i> 
                                        {{ isset($message) && $message->id ? 'تحديث الرسالة' : 'إنشاء الرسالة' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "لم يتم العثور على نتائج";
                    }
                }
            });
            
            // Initialize character count
            updateCharCount();
            
            // Update benefit count when user changes
            const userSelect = $('select[name="telegram_user_id"]');
            const benefitInput = document.getElementById('benefitCount');
            const userInfoPreview = document.getElementById('userInfoPreview');
            const previewUsername = document.getElementById('previewUsername');
            const previewPhone = document.getElementById('previewPhone');
            
            userSelect.on('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const benefitCount = selectedOption.getAttribute('data-benefit') || 0;
                const username = selectedOption.getAttribute('data-username') || 'غير محدد';
                const phone = selectedOption.getAttribute('data-phone') || 'غير محدد';
                
                benefitInput.value = benefitCount;
                
                if (this.value) {
                    previewUsername.textContent = username ? '@' + username : 'بدون معرف';
                    previewPhone.textContent = phone;
                    userInfoPreview.style.display = 'block';
                } else {
                    userInfoPreview.style.display = 'none';
                }
            });
            
            // Trigger change event on page load if user is selected
            if (userSelect.val()) {
                userSelect.trigger('change');
            }
            
            // Form validation
            const form = document.getElementById('messageForm');
            const submitBtn = document.getElementById('submitBtn');
            
            form.addEventListener('submit', function(e) {
                const message = document.getElementById('messageText').value.trim();
                const user = userSelect.val();
                
                if (!user) {
                    e.preventDefault();
                    showAlert('يرجى اختيار المستخدم', 'danger');
                    userSelect.focus();
                    return;
                }
                
                if (!message) {
                    e.preventDefault();
                    showAlert('يرجى إدخال نص الرسالة', 'danger');
                    document.getElementById('messageText').focus();
                    return;
                }
                
                
                // Show loading state
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> جاري الحفظ...';
                submitBtn.disabled = true;
                
                // Re-enable after 5 seconds if submission fails
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            });
        });
        
        function updateCharCount() {
            const textarea = document.getElementById('messageText');
            const countElement = document.getElementById('charCountNumber');
            const charCountContainer = document.getElementById('charCount');
            const length = textarea.value.length;
            
            countElement.textContent = length;
            
            // Update color based on length
            charCountContainer.classList.remove('warning', 'danger');
            if (length > 3500 && length <= 4096) {
                charCountContainer.classList.add('warning');
            } else if (length > 4096) {
                charCountContainer.classList.add('danger');
            }
        }
        
        function incrementBenefit() {
            const benefitInput = document.getElementById('benefitCount');
            benefitInput.value = parseInt(benefitInput.value) + 1;
        }
        
        
        function clearMessage() {
            if (confirm('هل تريد مسح نص الرسالة بالكامل؟')) {
                document.getElementById('messageText').value = '';
                updateCharCount();
            }
        }
        
        function showAlert(message, type) {
            // Remove any existing alerts
            const existingAlert = document.querySelector('.alert-dismissible');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            // Insert after header
            const header = document.querySelector('.page-header');
            header.insertAdjacentHTML('afterend', alertHtml);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = document.querySelector('.alert-dismissible');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('submitBtn').click();
            }
            
            // Ctrl + R to reset
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                if (confirm('هل تريد إعادة تعيين النموذج؟')) {
                    form.reset();
                    updateCharCount();
                }
            }
        });
    </script>
</body>
</html>