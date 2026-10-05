<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب كرسي كهربائي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>♿⚡</text></svg>">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap');

        * {
            font-family: 'Tajawal', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e0e7ff 0%, #f0f4ff 100%);
            padding: 20px;
            margin: 0;
        }

        .main-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            transition: transform 0.3s ease;
            max-width: 500px;
            width: 100%;
        }

        .main-card:hover {
            transform: translateY(-5px);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 30px 25px 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card-header-custom::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .card-header-custom::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 75px;
            height: 75px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin-bottom: 15px;
            font-size: 35px;
            color: white;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }

        .card-header-custom h4 {
            color: white;
            font-weight: 700;
            font-size: 24px;
            margin: 0;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
        }

        .card-header-custom p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            margin: 5px 0 0;
            position: relative;
            z-index: 1;
        }

        .card-body-custom {
            padding: 35px 30px 40px;
        }

        .form-label-custom {
            font-weight: 600;
            color: #1e293b;
            font-size: 15px;
            margin-bottom: 8px;
            display: block;
        }

        .form-label-custom i {
            color: #3b82f6;
            margin-left: 8px;
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 10;
            font-size: 18px;
        }

        .form-control-custom {
            padding: 14px 50px 14px 20px;
            border-radius: 15px !important;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            font-size: 16px;
            transition: all 0.3s ease;
            height: 58px;
        }

        .form-control-custom:focus {
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        .form-control-custom::placeholder {
            color: #94a3b8;
            font-size: 14px;
        }

        .btn-custom {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border: none;
            padding: 16px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 1px;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
            color: white;
        }

        .btn-custom:active {
            transform: scale(0.97);
        }

        .btn-custom i {
            margin-left: 10px;
        }

        .alert-custom {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-custom i {
            font-size: 20px;
        }

        .alert-danger-custom {
            background: #fef2f2;
            color: #dc2626;
            border-right: 4px solid #dc2626;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #94a3b8;
            font-size: 13px;
        }

        .footer-text i {
            color: #ef4444;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 10px 0 20px;
            color: #94a3b8;
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px dashed #e2e8f0;
        }

        .divider::before {
            margin-left: 15px;
        }

        .divider::after {
            margin-right: 15px;
        }

        @media (max-width: 576px) {
            .card-body-custom {
                padding: 25px 20px 30px;
            }

            .card-header-custom h4 {
                font-size: 20px;
            }

            .icon-circle {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

    <div class="main-card">
        <!-- Header -->
        <div class="card-header-custom">
            <div class="icon-circle">
                <i class="fas fa-wheelchair"></i>
            </div>
            <h4>طلب توفير كرسي كهربائي</h4>
            <p>خدمة سريعة وآمنة للمستحقين</p>
        </div>

        <!-- Body -->
        <div class="card-body-custom">

            @if(session('error'))
                <div class="alert alert-custom alert-danger-custom">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="/wheelchair/check-id">
                @csrf

                <label class="form-label-custom">
                    <i class="fas fa-id-card"></i>
                    رقم الهوية الوطنية
                </label>

                <div class="input-group-custom">
                    <span class="input-icon">
                        <i class="fas fa-user"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control form-control-custom"
                        name="national_id"
                        placeholder="أدخل رقم الهوية مكون من 9 أرقام"
                        required
                        pattern="[0-9]{9}"
                        maxlength="9"
                        title="يرجى إدخال 9 أرقام فقط"
                    >
                </div>

                <div class="divider">
                    <span>تأكد من صحة البيانات</span>
                </div>

                <button class="btn btn-custom w-100">
                    <i class="fas fa-arrow-left"></i>
                    متابعة الطلب
                </button>

            </form>

        </div>
    </div>

</body>

</html>