<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>لوحة إدارة التسجيلات - بحث متقدم</title>

<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
* {
    font-family: 'Cairo', sans-serif;
}

body {
    background: #f5f7f3;
    padding: 20px;
}

h2 {
    margin-bottom: 20px;
    color: #2d6a4f;
}

/* فلتر كارد */
.filter-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.filter-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #2d6a4f;
    border-right: 4px solid #2d6a4f;
    padding-right: 12px;
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 180px;
}

.filter-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #555;
    margin-bottom: 5px;
}

.filter-group input, 
.filter-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}

.filter-group input:focus, 
.filter-group select:focus {
    outline: none;
    border-color: #2d6a4f;
    box-shadow: 0 0 0 2px rgba(45,106,79,0.1);
}

.date-range {
    display: flex;
    gap: 10px;
}

.date-range input {
    flex: 1;
}

.btn-filter {
    background: #2d6a4f;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-filter:hover {
    background: #1b4d3e;
    transform: translateY(-1px);
}

.btn-reset {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-reset:hover {
    background: #5a6268;
}

/* بحث سريع */
.quick-search {
    margin-bottom: 15px;
    position: relative;
}

.quick-search i {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
}

.quick-search input {
    width: 100%;
    padding: 10px 35px 10px 12px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 14px;
}

/* جدول */
.table-wrapper {
    overflow-x: auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #eee;
}

th {
    background: #2d6a4f;
    color: white;
    font-weight: 600;
    white-space: nowrap;
}

tr:hover {
    background: #f1f5ef;
}

.badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    display: inline-block;
}

.green { background: #d1fae5; color: #065f46; }
.red { background: #fee2e2; color: #991b1b; }

/* تعداد النتائج */
.result-count {
    margin: 15px 0;
    padding: 10px;
    background: #e9ecef;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
}

/* أزرار التحكم */
.btn-toggle {
    background: #2d6a4f;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    margin-bottom: 15px;
}

.btn-toggle:hover {
    background: #1b4d3e;
}

.pagination {
    margin-top: 20px;
    display: flex;
    justify-content: center;
}

/* responsive */
@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .date-range {
        flex-direction: column;
    }
    
    th, td {
        padding: 8px;
        font-size: 12px;
    }
}
</style>
</head>

<body>

<h2><i class="fas fa-chart-line"></i> 📋 قائمة المسجلين في توزيع اللحوم</h2>

<!-- زر تشغيل/إيقاف الرابط -->
<form method="POST" action="{{ route('toggle.meat.check') }}" style="margin-bottom:15px;">
    @csrf
    @php
        $status = Cache::get('meat_check_status', 'open');
    @endphp
    @if($status === 'open')
        <button type="submit" class="btn-toggle" style="background:#dc3545;">
            <i class="fas fa-lock"></i> 🔴 إغلاق الرابط
        </button>
    @else
        <button type="submit" class="btn-toggle" style="background:#28a745;">
            <i class="fas fa-lock-open"></i> 🟢 تشغيل الرابط
        </button>
    @endif
</form>

@if(session('success'))
    <div style="background:#d4edda;padding:10px;border-radius:8px;margin-bottom:10px;border-right:4px solid #28a745;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<!-- فلتر متقدم -->
<div class="filter-card">
    <div class="filter-title">
        <i class="fas fa-filter"></i> فلتر متقدم
    </div>
    
    <form method="GET" action="{{ route('meat.adminindex') }}" id="filterForm">
        <div class="filter-row">
            <div class="filter-group" style="flex:2;">
                <label><i class="fas fa-search"></i> بحث سريع (الاسم - الهوية - الهاتف - العنوان)</label>
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="ابحث عن أي كلمة...">
            </div>
        </div>
        
        <div class="filter-row">
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> كرشة</label>
                <select name="want_tripe">
                    <option value="">الكل</option>
                    <option value="1" {{ request('want_tripe') == '1' ? 'selected' : '' }}>نعم</option>
                    <option value="0" {{ request('want_tripe') == '0' ? 'selected' : '' }}>لا</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> رجلين</label>
                <select name="want_legs">
                    <option value="">الكل</option>
                    <option value="1" {{ request('want_legs') == '1' ? 'selected' : '' }}>نعم</option>
                    <option value="0" {{ request('want_legs') == '0' ? 'selected' : '' }}>لا</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> مرافق منفصل</label>
                <select name="separate_family">
                    <option value="">الكل</option>
                    <option value="1" {{ request('separate_family') == '1' ? 'selected' : '' }}>نعم</option>
                    <option value="0" {{ request('separate_family') == '0' ? 'selected' : '' }}>لا</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-chart-line"></i> أقل عدد أفراد</label>
                <input type="number" name="min_members" value="{{ request('min_members') }}" placeholder="مثال: 3" min="0">
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-chart-line"></i> أعلى عدد أفراد</label>
                <input type="number" name="max_members" value="{{ request('max_members') }}" placeholder="مثال: 10" min="0">
            </div>
        </div>
        
        <div class="filter-row">
            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> نطاق التاريخ (من)</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> نطاق التاريخ (إلى)</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-dollar-sign"></i> الإيجار (أقل من)</label>
                <input type="number" name="max_rent" value="{{ request('max_rent') }}" placeholder="أقل من" step="0.01">
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i> تطبيق الفلتر
                </button>
                <button type="button" class="btn-reset" onclick="resetFilters()">
                    <i class="fas fa-undo"></i> إعادة تعيين
                </button>
            </div>
        </div>
        <div class="filter-group">
            <label>مواليد المرافقين من</label>
            <input type="date" name="child_birth_from"
                value="{{ request('child_birth_from') }}">
        </div>

        <div class="filter-group">
            <label>إلى</label>
            <input type="date" name="child_birth_to"
                value="{{ request('child_birth_to') }}">
        </div>
    </form>
</div>

<!-- تعداد النتائج -->
<div class="result-count">
    <i class="fas fa-chart-simple"></i>
    عدد النتائج: <strong>{{ $registrations->total() }}</strong> مسجل
    @if(request()->anyFilled(['search', 'want_tripe', 'want_legs', 'separate_family', 'min_members', 'max_members', 'date_from', 'date_to', 'max_rent']))
        <span style="color:#2d6a4f;"> (مفلتر)</span>
        <a href="{{ route('meat.adminindex') }}" style="color:#dc3545;margin-right:10px;">
            <i class="fas fa-times"></i> إلغاء الفلتر
        </a>
    @endif
</div>
<a href="{{ route('meat.export', request()->query()) }}" 
   class="btn-filter"
   style="display:inline-block;text-decoration:none;margin-bottom:15px;">
    <i class="fas fa-file-excel"></i> تحميل النتائج Excel
</a>
<!-- الجدول -->
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>رقم الهوية</th>
                <th>عدد الأفراد</th>
                <th>رقم الهاتف</th>
                <th>الفئة</th>
                <th>المحافظة</th>
                <th>العنوان</th>
                <th>الإيجار</th>
                <th>التفاصيل</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $index => $reg)
            <tr>
                <td>{{ $registrations->firstItem() + $index }}</td>
                <td>{{ $reg->telegramUser->full_name ?? '-' }}</td>
                <td>{{ $reg->telegramUser->national_id ?? '-' }}</td>
                <td>{{ $reg->telegramUser->family_members ?? '-' }}</td>
                <td>{{ $reg->telegramUser->egypt_number ?? '-' }}</td>
                <td>{{ $reg->telegramUser->target_case ?? '-' }}</td>
                <td>{{ $reg->telegramUser->governorate ?? '-' }}</td>
                <td>{{ $reg->telegramUser->address ?? '-' }}</td>
                <td>{{ $reg->telegramUser->rent_amount ?? '-' }}</td>
                <td>
                    <a href="{{ route('admin.telegram.details', $reg->telegram_user_id) }}"
                    class="btn-details">
                        <i class="fas fa-eye"></i> عرض
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align:center;padding:40px;">
                    <i class="fas fa-inbox"></i> لا توجد نتائج مطابقة للبحث
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $registrations->appends(request()->query())->links() }}
</div>

<script>
// إعادة تعيين جميع الفلاتر
function resetFilters() {
    window.location.href = "{{ route('meat.adminindex') }}";
}


</script>

</body>
</html>