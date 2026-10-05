<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة حالة جديدة - رمضان</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 800px;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #2d5a27 0%, #1e3d1a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .card-header h2 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .card-body {
            padding: 40px;
        }

        .error-box {
            background: #fee;
            border-right: 5px solid #dc3545;
            padding: 15px;
            border-radius: 14px;
            margin-bottom: 25px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media(max-width:640px){
            .form-row{
                grid-template-columns:1fr;
            }
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: #1e3d1a;
        }

        input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 14px;
            font-size: 16px;
            background: #f8fafc;
        }

        input:focus {
            outline: none;
            border-color: #2d5a27;
            background: white;
            box-shadow: 0 0 0 4px rgba(45,90,39,0.1);
        }

        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border-radius: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2d5a27 0%, #1e3d1a 100%);
            color: white;
        }

        .btn-secondary {
            background: white;
            border: 2px solid #2d5a27;
            color: #2d5a27;
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">

        <div class="card-header">
            <h2>➕ إضافة حالة جديدة</h2>
            <p>إضافة مستفيد جديد إلى برنامج توزيع رمضان</p>
        </div>

        <div class="card-body">

            {{-- عرض الأخطاء --}}
            @if($errors->any())
                <div class="error-box">
                    <strong>⚠️ يرجى تصحيح الأخطاء التالية:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('ramadan.store') }}">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label>👤 الاسم الكامل</label>
                        <input type="text" name="full_name"
                               value="{{ old('full_name') }}">
                    </div>

                    <div class="form-group">
                        <label>🆔 رقم الهوية</label>
                        <input type="text" name="national_id"
                               value="{{ old('national_id') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>🔑 الكود</label>
                        <input type="text" name="code"
                               value="{{ old('code') }}">
                    </div>

                    <div class="form-group">
                        <label>👪 عدد أفراد الأسرة</label>
                        <input type="number" name="family_members_count"
                               value="{{ old('family_members_count') }}"
                               min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label>🍽️ عدد الوجبات اليومية</label>
                    <input type="number" name="daily_meals"
                           value="{{ old('daily_meals') }}"
                           min="1">
                </div>

                <div class="buttons">
                    <button type="submit" class="btn btn-primary">
                        💾 حفظ الحالة
                    </button>

                    <a href="{{ route('ramadan.index') }}" class="btn btn-secondary">
                        🔙 إلغاء
                    </a>
                </div>

            </form>
        </div>
    </div>

    <div class="footer-note">
        © 2026 برنامج توزيع رمضان - إدارة الحالات
    </div>
</div>

</body>
</html>
