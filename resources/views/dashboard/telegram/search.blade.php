<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة البيانات والبحث المتقدم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .search-section { background: white; padding: 20px; border-radius: 8px; mb: 20px; }
    </style>
</head>
<body>

<div class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>🔍 البحث المتقدم</h3>
        
        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
            📤 رفع ملف Excel جديد
        </button>
    </div>

    <div class="search-section shadow-sm mb-4">
        <form method="GET" action="{{ route('search.advance') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="اسم أو هوية" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="location" class="form-control" placeholder="مكان السكن" value="{{ request('location') }}">
            </div>
            <div class="col-md-1">
                <input type="number" name="benefit_from" class="form-control" placeholder="من" value="{{ request('benefit_from') }}">
            </div>
            <div class="col-md-1">
                <input type="number" name="benefit_to" class="form-control" placeholder="إلى" value="{{ request('benefit_to') }}">
            </div>
            <div class="col-md-3">
                <select name="target_case" class="form-select">
                    <option value="">كل الفئات</option>
                    <option value="الأيتام" {{ request('target_case') == 'الأيتام' ? 'selected' : '' }}>الأيتام</option>
                    <option value="حالات البتر" {{ request('target_case') == 'حالات البتر' ? 'selected' : '' }}>حالات البتر</option>
                    <option value="الحالات المرضية" {{ request('target_case') == 'الحالات المرضية' ? 'selected' : '' }}>الحالات المرضية</option>
                    <option value="مساكن الجرحى والمرضى" {{ request('target_case') == 'مساكن الجرحى والمرضى' ? 'selected' : '' }}>مساكن الجرحى</option>
                    <option value="فئات أخرى" {{ request('target_case') == 'فئات أخرى' ? 'selected' : '' }}>فئات أخرى</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">كل التصنيفات</option>
                    @foreach($categories as $id => $name)
                        <option value="{{ $id }}" {{ request('category_id') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
                <!-- ⭐ هنا أضفنا الترتيب -->
            <div class="col-md-2">
                <select name="sort" class="form-select">
                    <option value="benefit_asc" {{ request('sort') == 'benefit_asc' ? 'selected' : '' }}>
                        الأقل استفادة
                    </option>
                    <option value="benefit_desc" {{ request('sort') == 'benefit_desc' ? 'selected' : '' }}>
                        الأكثر استفادة
                    </option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>
                        الاسم (أ-ي)
                    </option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>
                        الاسم (ي-أ)
                    </option>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">بحث</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('search.export', request()->all()) }}" class="btn btn-success w-100">
                    تحميل النتائج Excel
                </a>
            </div>
        </form>
    </div>

    <hr>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>الاسم</th>
                    <th>الهوية</th>
                    <th>عدد افراد الاسرة</th>
                    <th>المدينة</th>
                    <th>العنوان</th>
                    <th>رقم الهاتف</th>
                    <th>عدد الاستفادة</th>
                    <th>الفئة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->full_name }}</td>
                    <td>{{ $user->national_id }}</td>
                    <td>{{ $user->family_members }}</td>
                    <td>{{ $user->city }}</td>
                    <td>{{ $user->address }}</td>
                    <td>{{ $user->egypt_number }}</td>
                    <td><span class="badge bg-info text-dark">{{ $user->benefit_count }}</span></td>
                    <td>{{ $user->target_case }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">لا توجد نتائج مطابقة للبحث</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $users->withQueryString()->links() }}
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('excel.check') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">رفع ملف الأسماء والهويات</h5>
                    <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اختار ملف Excel (xlsx, csv)</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <div class="alert alert-info">
                        <small>يرجى التأكد من مطابقة أعمدة الملف للنظام لتجنب الأخطاء.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">بدء الرفع والمعالجة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>