<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>الفعاليات | المنصة التفاعلية</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
    <circle cx='50' cy='50' r='48' fill='%234361ee'/>
    <rect x='20' y='28' width='60' height='50' rx='5' ry='5' fill='white'/>
    <line x1='20' y1='40' x2='80' y2='40' stroke='%234361ee' stroke-width='4'/>
    <line x1='35' y1='55' x2='35' y2='70' stroke='%234361ee' stroke-width='4'/>
    <line x1='50' y1='55' x2='50' y2='70' stroke='%234361ee' stroke-width='4'/>
    <line x1='65' y1='55' x2='65' y2='70' stroke='%234361ee' stroke-width='4'/>
    </svg>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background: linear-gradient(145deg, #f4f7fc 0%, #eef2f8 100%);
            font-family: system-ui, 'Segoe UI', 'Tajawal', 'Cairo', 'Helvetica Neue', sans-serif;
            padding: 1rem;
            min-height: 100vh;
            color: #1a2c3e;
        }

        /* container مرن */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* هيدر أنيق */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(8px);
            padding: 1rem 1.5rem;
            border-radius: 2rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.02), 0 2px 6px rgba(0,0,0,0.05);
        }

        .page-title {
            font-size: 1.9rem;
            font-weight: 800;
            background: linear-gradient(135deg, #2b3b8c, #4361ee);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.3px;
        }

        .page-title i {
            background: none;
            -webkit-background-clip: unset;
            background-clip: unset;
            color: #4361ee;
            font-size: 2rem;
        }

        .btn-add {
            background: linear-gradient(105deg, #4361ee, #3a56d4);
            color: white;
            padding: 0.8rem 1.6rem;
            border-radius: 2.5rem;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.25s ease;
            box-shadow: 0 6px 14px rgba(67,97,238,0.25);
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 22px rgba(67,97,238,0.35);
            background: linear-gradient(105deg, #3a56d4, #2b42b3);
        }

        /* alert نجاح متحرك */
        .alert-success {
            background: rgba(72, 187, 120, 0.12);
            backdrop-filter: blur(8px);
            border-radius: 1.5rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-right: 4px solid #48bb78;
            color: #276749;
            font-weight: 500;
            animation: slidePop 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }

        @keyframes slidePop {
            0% { opacity: 0; transform: translateY(-18px) scale(0.97);}
            100% { opacity: 1; transform: translateY(0) scale(1);}
        }

        /* فلتر كروت محسنة */
        .filters-section {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(4px);
            border-radius: 1.8rem;
            padding: 1.4rem 1.8rem;
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            border: 1px solid rgba(255,255,255,0.6);
        }

        .filter-group {
            flex: 1;
            min-width: 160px;
        }

        .filter-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #2c3e66;
            letter-spacing: 0.3px;
        }

        .filter-input, .filter-select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #e2edf7;
            border-radius: 1.2rem;
            font-size: 0.9rem;
            background: white;
            transition: 0.2s;
            font-family: inherit;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67,97,238,0.2);
        }

        .btn-filter {
            background: #2b3b8c;
            color: white;
            border: none;
            padding: 0.8rem 1.6rem;
            border-radius: 1.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: 0.2s;
            font-size: 0.9rem;
        }

        .btn-filter:hover {
            background: #1e2c6e;
            transform: scale(0.97);
        }

        /* حاوية الجدول */
        .events-table-container {
            background: white;
            border-radius: 1.8rem;
            overflow: hidden;
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.12);
            transition: 0.2s;
        }

        /* جدول مرن جدا */
        .events-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .events-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 860px;
            font-size: 0.9rem;
        }

        .events-table thead tr {
            background: linear-gradient(120deg, #1f2b4e, #2b3b64);
        }

        .events-table th {
            padding: 1rem 1rem;
            text-align: right;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.3px;
        }

        .events-table td {
            padding: 1rem 1rem;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }

        .events-table tbody tr {
            transition: 0.2s;
        }

        .events-table tbody tr:hover {
            background: #f8fafd;
        }

        /* badges انيقة */
        .type-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 1rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 700;
            gap: 6px;
            width: fit-content;
            white-space: nowrap;
        }

        .type-in_kind { background: #e0f2e9; color: #2c6e3c; }
        .type-cash { background: #e0f0ff; color: #1a5d9c; }
        .type-other { background: #ffe4e8; color: #b23c5c; }

        /* مجموعة الأزرار بشكل grid مرن */
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.45rem 0.9rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s;
            background: #f1f5f9;
            color: #2d3e5f;
        }

        .btn-edit { background: #fef6e0; color: #b45309; }
        .btn-delete { background: #ffe8ea; color: #c53030; }
        .btn-tasks { background: #e0f2fe; color: #0e6b7c; }
        .btn-send, .btn-received, .btn-attendance {
            background: #e9eef5;
            color: #2c5282;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            filter: brightness(0.96);
        }

        /* حالة فارغة جميلة */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            background: #ffffffdd;
        }

        .empty-state i {
            font-size: 3.8rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.6rem;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        /* الفوتر */
        .page-footer {
            margin-top: 2rem;
            text-align: center;
            color: #5a6e8a;
            font-size: 0.8rem;
            padding-top: 1rem;
            border-top: 1px solid #dee7ef;
        }

        /* راقب الجوالات */
        @media (max-width: 720px) {
            body {
                padding: 0.75rem;
            }
            .page-header {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
                padding: 1rem;
            }
            .page-title {
                justify-content: center;
                font-size: 1.6rem;
            }
            .btn-add {
                justify-content: center;
            }
            .filters-section {
                flex-direction: column;
                padding: 1.2rem;
            }
            .filter-group {
                width: 100%;
            }
            .btn-filter {
                justify-content: center;
            }
            .actions {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: flex-start;
            }
            .events-table th, .events-table td {
                padding: 0.75rem 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .btn-action {
                font-size: 0.7rem;
                padding: 0.4rem 0.7rem;
            }
            .type-badge {
                font-size: 0.7rem;
                padding: 0.2rem 0.8rem;
            }
            .page-title {
                font-size: 1.5rem;
            }
        }

        /* تأثير اختفاء التنبيه */
        .alert-fadeout {
            transition: opacity 0.3s ease, transform 0.2s ease;
        }
        
        /* counter جميل */
        .total-badge {
            display: inline-block;
            background: #eef2ff;
            border-radius: 3rem;
            padding: 0.3rem 1rem;
            font-weight: 600;
            margin-top: 1rem;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
<div class="container">
    <header class="page-header">
        <h1 class="page-title"><i class="fas fa-calendar-alt"></i> الفعاليات</h1>
        <a href="{{ route('events.create') }}" class="btn-add">
            <i class="fas fa-plus-circle"></i> إضافة فعالية جديدة
        </a>
    </header>

    @if(session('success'))
    <div class="alert-success" id="successAlert">
        <i class="fas fa-check-circle fa-lg"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <section class="filters-section">
        <div class="filter-group">
            <label for="search"><i class="fas fa-search"></i> بحث بالاسم</label>
            <input type="text" id="search" class="filter-input" placeholder="اكتب اسم الفعالية...">
        </div>
        <div class="filter-group">
            <label for="type-filter"><i class="fas fa-filter"></i> نوع المساعدة</label>
            <select id="type-filter" class="filter-select">
                <option value="all">جميع الأنواع</option>
                <option value="in_kind">عينية</option>
                <option value="cash">نقدية</option>
                <option value="other">أخرى</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="date-filter"><i class="fas fa-calendar-day"></i> التاريخ</label>
            <input type="date" id="date-filter" class="filter-input">
        </div>
        <button class="btn-filter" id="applyFilterBtn"><i class="fas fa-sliders-h"></i> تطبيق الفلتر</button>
    </section>

    <div class="events-table-container">
        @if($events->count() > 0)
        <div class="events-table-wrapper">
            <table class="events-table">
                <thead>
                    <tr>
                        <th>رقم الكشف</th>
                        <th>الاسم</th>
                        <th>التاريخ</th>
                        <th>الوقت</th>
                        <th>نوع المساعدة</th>
                        <th>القيمة</th>
                        <th>المكان</th>
                        <th>الرسالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="events-table-body">
                    @foreach($events as $event)
                    <tr data-event-id="{{ $event->id }}">
                        <td>{{ $event->id }}</td>
                        <td class="event-name">{{ $event->name }}</td>
                        <td class="event-date">{{ \Carbon\Carbon::parse($event->event_date)->format('Y-m-d') }}</td>
                        <td>{{ $event->event_time ?? 'غير محدد' }}</td>
                        <td class="event-type">
                            @php
                                $typeText = [
                                    'in_kind' => 'عينية',
                                    'cash' => 'نقدية',
                                    'other' => 'أخرى'
                                ][$event->aid_type] ?? $event->aid_type;
                            @endphp
                            <span class="type-badge type-{{ $event->aid_type }}">{{ $typeText }}</span>
                        </td>
                        <td>
                            @if($event->value_type === 'number' && $event->value !== null)
                                {{ number_format($event->value) }} جنيه
                            @elseif($event->value_type === 'text' && $event->value_text)
                                {{ $event->value_text }}
                            @else
                                غير محدد
                            @endif
                        </td>
                        <td>{{ $event->location ?? 'غير محدد' }}</td>
                        <td>{{ $event->default_message ?? 'لا توجد رسالة' }}</td>
                        <td class="actions">
                            <a href="{{ route('events.tasks', $event->id) }}" class="btn-action btn-tasks">
                                <i class="fas fa-tasks"></i> توزيع المهام
                            </a>
                            <a href="{{ route('events.edit', $event->id) }}" class="btn-action btn-edit">
                                <i class="fas fa-edit"></i> تعديل
                            </a>
                            <a href="{{ route('admin.telegram.index', ['event_id' => $event->id, 'message_type' => 'invitation']) }}" class="btn-action btn-send">
                                📩 دعوة حضور
                            </a>
                            <a href="{{ route('admin.telegram.index', ['event_id' => $event->id, 'message_type' => 'received']) }}" class="btn-action btn-received">
                                ✅ تم الاستلام
                            </a>
                            <a href="{{ route('events.attendance', $event->id) }}" class="btn-action btn-attendance">
                                <i class="fas fa-user-check"></i> تم الحضور
                            </a>
                            <a href="{{ route('events.delivery', $event->id) }}" class="btn-action btn-delivery">
                                <i class="fas fa-box-open"></i> مباشر تم الاستلام
                            </a>
                            <a href="{{ route('admin.events.import', $event->id) }}" 
                               class="btn-action btn-import">
                                📤 رفع كشف الاستفادة
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="text-align: center; margin-top: 1rem; padding-bottom: 1rem;">
            <span class="total-badge"><i class="fas fa-chart-simple"></i> إجمالي الفعاليات: <strong id="totalCountDisplay">{{ $events->count() }}</strong></span>
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>لا توجد فعاليات حالياً</h3>
            <p>لم يتم إضافة أي فعاليات بعد. يمكنك إضافة فعالية جديدة بالنقر على زر "إضافة فعالية جديدة".</p>
            <a href="{{ route('events.create') }}" class="btn-add">
                <i class="fas fa-plus-circle"></i> إضافة فعالية جديدة
            </a>
        </div>
        @endif
    </section>

    <footer class="page-footer">
        <p>جميع الحقوق محفوظة &copy; {{ date('Y') }} - نظام إدارة الفعاليات</p>
    </footer>
</div>

<script>
    (function() {
        // --- العناصر الرئيسية ---
        const searchInput = document.getElementById('search');
        const typeFilter = document.getElementById('type-filter');
        const dateFilter = document.getElementById('date-filter');
        const applyBtn = document.getElementById('applyFilterBtn');
        const tbody = document.getElementById('events-table-body');
        const totalSpan = document.getElementById('totalCountDisplay');
        
        // إذا لم يكن هناك عناصر نخرج من الفلترة مبكراً
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // تحديث العداد المرئي
        function updateVisibleCount() {
            if (!totalSpan) return;
            const visible = rows.filter(row => row.style.display !== 'none').length;
            totalSpan.innerText = visible;
        }

        // دالة التصفية الشاملة
        function performFilter() {
            const searchVal = searchInput ? searchInput.value.trim().toLowerCase() : '';
            const typeVal = typeFilter ? typeFilter.value : 'all';
            const dateVal = dateFilter ? dateFilter.value : '';
            
            let visibleCount = 0;
            
            rows.forEach(row => {
                // الاسم
                const nameCell = row.querySelector('.event-name');
                const name = nameCell ? nameCell.innerText.toLowerCase() : '';
                
                // التاريخ (نص من العمود الثالث)
                const dateCell = row.querySelector('.event-date');
                const rowDate = dateCell ? dateCell.innerText.trim() : '';
                
                // النوع: نبحث عن badge داخل عمود النوع
                const typeCell = row.querySelector('.event-type');
                let rowType = 'all';
                if (typeCell) {
                    const badge = typeCell.querySelector('.type-badge');
                    if (badge) {
                        for (let cls of badge.classList) {
                            if (cls.startsWith('type-')) {
                                rowType = cls.replace('type-', '');
                                break;
                            }
                        }
                    }
                }
                
                let matchSearch = true;
                let matchType = true;
                let matchDate = true;
                
                if (searchVal !== '') {
                    matchSearch = name.includes(searchVal);
                }
                if (typeVal !== 'all') {
                    matchType = (rowType === typeVal);
                }
                if (dateVal !== '') {
                    matchDate = (rowDate === dateVal);
                }
                
                const shouldShow = matchSearch && matchType && matchDate;
                row.style.display = shouldShow ? '' : 'none';
                if (shouldShow) visibleCount++;
            });
            
            if (totalSpan) totalSpan.innerText = visibleCount;
            
            // عرض رسالة ضمنية في حالة عدم وجود نتائج (اختياري: يمكن تعديل المحتوى)
            const container = document.querySelector('.events-table-container');
            let noResultMsg = document.getElementById('dynamicNoResult');
            if (visibleCount === 0 && rows.length > 0) {
                if (!noResultMsg) {
                    const msgDiv = document.createElement('div');
                    msgDiv.id = 'dynamicNoResult';
                    msgDiv.className = 'empty-state';
                    msgDiv.style.padding = '2rem';
                    msgDiv.innerHTML = `<i class="fas fa-filter-circle-xmark"></i><h4>لا توجد نتائج مطابقة</h4><p>حاول تغيير خيارات البحث أو التصفية</p>`;
                    // insert after the table wrapper or inside container
                    const wrapper = document.querySelector('.events-table-wrapper');
                    if (wrapper) {
                        wrapper.style.display = 'none';
                        container.appendChild(msgDiv);
                    } else {
                        // إذا لم يوجد wrapper لكن الجدول موجود
                        const table = document.querySelector('.events-table');
                        if (table) table.style.display = 'none';
                        container.appendChild(msgDiv);
                    }
                } else {
                    const wrapper = document.querySelector('.events-table-wrapper');
                    if (wrapper) wrapper.style.display = 'none';
                    else if(document.querySelector('.events-table')) document.querySelector('.events-table').style.display = 'none';
                    noResultMsg.style.display = 'block';
                }
            } else {
                const wrapper = document.querySelector('.events-table-wrapper');
                if (wrapper) wrapper.style.display = 'block';
                else if(document.querySelector('.events-table')) document.querySelector('.events-table').style.display = 'table';
                const noMsg = document.getElementById('dynamicNoResult');
                if (noMsg) noMsg.style.display = 'none';
            }
        }
        
        // ربط الأحداث
        if (searchInput) searchInput.addEventListener('input', performFilter);
        if (typeFilter) typeFilter.addEventListener('change', performFilter);
        if (dateFilter) dateFilter.addEventListener('change', performFilter);
        if (applyBtn) applyBtn.addEventListener('click', performFilter);
        
        // عند تحميل الصفحة، احتفظ بالحالة الأولية
        performFilter();
    })();

    // إخفاء رسالة النجاح تلقائياً مع أنيميشن
    (function handleAlert() {
        const alertDiv = document.getElementById('successAlert');
        if (alertDiv) {
            setTimeout(() => {
                alertDiv.style.transition = 'opacity 0.4s ease, transform 0.3s ease';
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateY(-12px)';
                setTimeout(() => {
                    if (alertDiv && alertDiv.remove) alertDiv.remove();
                }, 400);
            }, 4800);
        }
    })();
</script>
</body>
</html>