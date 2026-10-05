<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>QR Code</title>
</head>
<body>

<h2>QR Code للمستخدم: {{ $user->full_name }}</h2>

<div>
    {!! QrCode::size(250)->generate($nationalId) !!}
</div>

<p>رقم الهوية: {{ $nationalId }}</p>

</body>
</html>