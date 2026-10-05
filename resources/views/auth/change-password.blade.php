<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>تغيير كلمة المرور</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>

body{
    font-family: 'Cairo', sans-serif;
    background: linear-gradient(135deg,#4361ee,#3a0ca3);
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}

.card-box{
    background:white;
    border-radius:20px;
    padding:35px;
    box-shadow:0 15px 35px rgba(0,0,0,.2);
    width:100%;
    max-width:420px;
}

.title{
    font-weight:700;
    text-align:center;
    margin-bottom:25px;
}

.btn-main{
    background:#4361ee;
    border:none;
    padding:12px;
    font-weight:600;
}

.btn-main:hover{
    background:#3a0ca3;
}

.logo{
    font-size:35px;
    text-align:center;
    margin-bottom:10px;
}

</style>

</head>

<body>


<div class="card-box">

<div class="logo">🔐</div>

<h4 class="title">تغيير كلمة المرور</h4>

@if(session('success'))
<div class="alert alert-success text-center">
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('password.change.submit') }}">
@csrf

<div class="mb-3">
<label class="form-label">كلمة المرور الجديدة</label>

<input
type="password"
name="password"
class="form-control @error('password') is-invalid @enderror"
placeholder="اكتب كلمة المرور الجديدة"
required
>

@error('password')
<div class="invalid-feedback">
{{ $message }}
</div>
@enderror
</div>

<div class="mb-3">
<label class="form-label">تأكيد كلمة المرور</label>

<input
type="password"
name="password_confirmation"
class="form-control"
placeholder="أعد كتابة كلمة المرور"
required
>
</div>

<button class="btn btn-main w-100">
تغيير كلمة المرور
</button>

</form>

</div>


</body>
</html>