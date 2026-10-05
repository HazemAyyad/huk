<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلبات البتر - لوحة التحكم</title>

    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(145deg, #f0f7fa, #e4eef5);
            padding: 20px;
        }

        .main-container {
            max-width: 1200px;
            margin: auto;
        }

        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #1a2a3a);
            color: white;
            border-radius: 25px;
            padding: 1rem 2rem;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .admin-header h1 {
            font-weight: 800;
            font-size: 1.4rem;
        }

        .mini-stat {
            background: rgba(255,255,255,0.1);
            padding: 5px 15px;
            border-radius: 15px;
            text-align: center;
        }

        .mini-stat span {
            display: block;
        }

        .card {
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .card-header {
            background: linear-gradient(135deg, #0a4f6e, #0e6b8c);
            color: white;
            font-weight: bold;
            border-radius: 25px 25px 0 0;
        }

        .user-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            margin: auto;
        }

        .user-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
        }

        .pending { background: #fff3cd; color: #856404; }
        .approved { background: #d4edda; color: #155724; }
        .rejected { background: #f8d7da; color: #721c24; }

        tr {
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="main-container">

    <!-- HEADER -->
    <div class="admin-header">
        <h1><i class="fas fa-wheelchair"></i> طلبات البتر</h1>

        <div style="display:flex; gap:10px;">
            <div class="mini-stat">
                <span>{{ $total }}</span>
                <small>الكل</small>
            </div>
            <div class="mini-stat">
                <span>{{ $pending }}</span>
                <small>معلق</small>
            </div>
            <div class="mini-stat">
                <span>{{ $approved }}</span>
                <small>معتمد</small>
            </div>
            <div class="mini-stat">
                <span>{{ $rejected }}</span>
                <small>مرفوض</small>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-table"></i> قائمة الحالات
        </div>

        <div class="p-3">

            <!-- SEARCH -->
            <form method="GET" class="mb-3 d-flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control"
                       placeholder="ابحث بالاسم أو الرقم القومي...">
                <button class="btn btn-primary">بحث</button>
            </form>

            @if($cases->count())
            <div class="table-responsive">
                <table class="table table-hover text-center align-middle">
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>البيانات</th>
                            <th>نوع البتر</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($cases as $case)
                        <tr onclick="window.location='{{ route('admin.amputations.show',$case->id) }}'">

                            <!-- صورة -->
                            <td>
                                <div class="user-avatar-large">
                                    <img src="{{ $case->person_image ? asset($case->person_image) : asset('default.png') }}">
                                </div>
                            </td>

                            <!-- بيانات -->
                            <td>
                                <strong>{{ $case->full_name }}</strong><br>
                                <small>{{ $case->national_id }}</small><br>
                                <small>
                                    تاريخ الطلب: {{ optional($case->created_at)->format('Y-m-d') ?? '-' }}
                                </small>
                            </td>

                            <!-- نوع البتر -->
                            <td>
                                <span class="badge bg-dark">
                                    {{ $case->amputation_type ?? 'غير محدد' }}
                                </span>
                            </td>

                            <!-- الحالة -->
                            <td>
                                @if($case->status == 'pending')
                                    <span class="status-badge pending">قيد المراجعة</span>
                                @elseif($case->status == 'approved')
                                    <span class="status-badge approved">معتمد</span>
                                @else
                                    <span class="status-badge rejected">مرفوض</span>
                                @endif
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $cases->links() }}
            </div>
            @else
                <div class="text-center p-5">
                    <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i>
                    <h4>لا توجد طلبات</h4>
                </div>
            @endif

        </div>
    </div>

</div>

</body>
</html>