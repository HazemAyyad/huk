<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>فعالية السفارة</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
        <circle cx='50' cy='50' r='48' fill='%234361ee'/>
        <rect x='20' y='25' width='60' height='50' rx='8' ry='8' fill='white'/>
        <line x1='25' y1='38' x2='75' y2='38' stroke='%234361ee' stroke-width='4' stroke-linecap='round'/>
        <line x1='25' y1='50' x2='75' y2='50' stroke='%234361ee' stroke-width='4' stroke-linecap='round'/>
        <line x1='25' y1='62' x2='75' y2='62' stroke='%234361ee' stroke-width='4' stroke-linecap='round'/>
    </svg>">
</head>
<body class="bg-light">

<div class="container mt-5">

<h3 class="mb-4">إدارة حضور فعالية السفارة</h3>

@if(session('success'))
<div class="alert alert-success">
{{ session('success') }}
</div>
@endif


<!-- رفع Excel -->
<div class="card mb-4">
<div class="card-body">

<form action="{{ route('embassy.import') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="row">

<div class="col-md-6">
<input type="file" name="file" accept=".xlsx,.xls" class="form-control" required>
</div>

<div class="col-md-2">
<button class="btn btn-primary">رفع Excel</button>
</div>

</div>

</form>

</div>
</div>



<!-- إضافة سجل -->
<div class="card mb-4">
<div class="card-body">

<h5 class="mb-3">إضافة شخص</h5>

<form method="POST" action="{{ route('embassy.store') }}">
@csrf

<div class="row">

<div class="col-md-2">
<select name="type" class="form-control" required>
<option value="مريض">مريض</option>
<option value="مرافق">مرافق</option>
</select>
</div>

<div class="col-md-2">
<input type="text" name="code" class="form-control" placeholder="الكود">
</div>

<div class="col-md-3">
<input type="text" name="full_name" class="form-control" placeholder="الاسم الرباعي" required>
</div>

<div class="col-md-2">
<input type="text" name="national_id" class="form-control" placeholder="رقم الهوية">
</div>

<div class="col-md-2">
<input type="text" name="notes" class="form-control" placeholder="ملاحظات">
</div>

<div class="col-md-1">
<button class="btn btn-success">إضافة</button>
</div>

</div>

</form>

</div>
</div>



<!-- الجدول -->
<div class="card mb-3">
<div class="card-body">

<form method="GET" action="{{ route('embassy.index') }}">

<div class="row mb-3">

<div class="col-md-3">
<input type="text" name="code" class="form-control" placeholder="بحث بالكود">
</div>

<div class="col-md-3">
<input type="text" name="full_name" class="form-control" placeholder="بحث بالاسم">
</div>

<div class="col-md-3">
<input type="text" name="national_id" class="form-control" placeholder="بحث بالهوية">
</div>

<div class="col-md-3">
<button class="btn btn-dark">بحث</button>
<a href="{{ route('embassy.index') }}" class="btn btn-secondary">إعادة</a>
</div>

</div>

</form>

</div>
</div>
<div class="row mb-3">

<div class="col-md-4">
<div class="alert alert-primary">
إجمالي المسجلين: {{ $stats['total'] }}
</div>
</div>

<div class="col-md-4">
<div class="alert alert-success">
الحضور: {{ $stats['attended'] }}
</div>
</div>

<div class="col-md-4">
<div class="alert alert-danger">
الغياب: {{ $stats['absent'] }}
</div>
</div>

</div>
<!-- زر التقرير -->
<div class="mb-3 text-end">
    <a href="{{ route('embassy.report') }}" class="btn btn-info">
        عرض التقرير
    </a>
</div>
<table class="table table-bordered table-striped">

<tr>
<th>النوع</th>
<th>الكود</th>
<th>الاسم</th>
<th>الهوية</th>
<th>ملاحظات</th>
<th>الحضور</th>
<th>إجراءات</th>
</tr>

@foreach($attendees as $row)

<tr>

<td>{{ $row->type }}</td>

<td>{{ $row->code }}</td>

<td>{{ $row->full_name }}</td>

<td>{{ $row->national_id }}</td>

<td>{{ $row->notes }}</td>

<td>
@if($row->attended)
<span class="badge bg-success">حاضر</span>
@else
<span class="badge bg-danger">غائب</span>
@endif
</td>


<td class="d-flex gap-1">

<!-- تسجيل حضور -->

<form method="POST" action="{{ route('embassy.attend',$row->id) }}">
@csrf
<button class="btn btn-success btn-sm">حضور</button>
</form>


<!-- تعديل -->

<button class="btn btn-warning btn-sm"
onclick="editPerson(
{{ $row->id }},
'{{ addslashes($row->type) }}',
'{{ addslashes($row->code) }}',
'{{ addslashes($row->full_name) }}',
'{{ addslashes($row->national_id) }}',
'{{ addslashes($row->notes) }}'
)">
تعديل
</button>


<!-- حذف -->

<form method="POST" action="{{ route('embassy.destroy',$row->id) }}">
@csrf
@method('DELETE')
<button class="btn btn-danger btn-sm">حذف</button>
</form>

</td>

</tr>

@endforeach

</table>

</div>


<!-- مودال التعديل -->

<div class="modal fade" id="editModal">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">تعديل البيانات</h5>
</div>

<form method="POST" action="{{ route('embassy.update') }}">
@csrf

<input type="hidden" name="id" id="edit_id">

<select name="type" id="edit_type" class="form-control mb-2">
<option value="مريض">مريض</option>
<option value="مرافق">مرافق</option>
</select>

<input type="text" name="code" id="edit_code" class="form-control mb-2">

<input type="text" name="full_name" id="edit_full_name" class="form-control mb-2">

<input type="text" name="national_id" id="edit_national_id" class="form-control mb-2">

<input type="text" name="notes" id="edit_notes" class="form-control mb-2">

<button class="btn btn-primary">حفظ</button>

</form>

</div>
</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    
function editPerson(id,type,code,name,national_id,notes){

document.getElementById('edit_id').value=id;
document.getElementById('edit_type').value=type;
document.getElementById('edit_code').value=code;
document.getElementById('edit_full_name').value=name;
document.getElementById('edit_national_id').value=national_id;
document.getElementById('edit_notes').value=notes;

new bootstrap.Modal(document.getElementById('editModal')).show();

}

</script>

</body>
</html>