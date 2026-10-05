<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل الرسائل - لوحة التحكم</title>
    <link rel="icon" type="image/svg+xml"
    href="data:image/svg+xml,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
    <circle cx='50' cy='50' r='48' fill='%234361ee'/>
    <path d='M25 35h50c3 0 5 2 5 5v20c0 3-2 5-5 5H25c-3 0-5-2-5-5V40c0-3 2-5 5-5z' fill='white'/>
    <path d='M20 40l30 15 30-15' fill='none' stroke='%234361ee' stroke-width='5' stroke-linecap='round' stroke-linejoin='round'/>
    </svg>">
    
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
            background-color: #f8f9fa;
            color: #333;
            padding-top: 20px;
            padding-bottom: 50px;
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
            font-weight: 600;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .table th {
            font-weight: 600;
            color: var(--primary-color);
            border-top: none;
            padding: 15px;
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f3f4;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
            transform: scale(1.002);
            transition: all 0.2s;
        }
        
        .avatar-xs {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 16px;
        }
        
        .badge {
            padding: 6px 12px;
            font-weight: 500;
            border-radius: 8px;
        }
        
        .btn {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #4cc9f0, #4895ef);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 201, 240, 0.4);
        }
        
        .btn-group .btn {
            border-radius: 8px;
            margin: 0 3px;
        }
        
        .pagination {
            justify-content: center;
        }
        
        .page-link {
            color: var(--primary-color);
            border: none;
            margin: 0 5px;
            border-radius: 8px !important;
        }
        
        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-color: var(--primary-color);
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 80px;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            padding-right: 45px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .search-box .bi-search {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-sent { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }
        .status-delivered { background-color: rgba(0, 123, 255, 0.1); color: #007bff; }
        .status-failed { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .status-pending { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
        
        .message-preview {
            line-height: 1.6;
            max-width: 300px;
        }
        
        .read-more {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }
        
        .read-more:hover {
            text-decoration: underline;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
        }
        
        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                border-radius: 10px;
                border: 1px solid #dee2e6;
            }
            
            .btn-group {
                display: flex;
                flex-direction: column;
            }
            
            .btn-group .btn {
                margin: 3px 0;
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
                            <h1 class="h3 mb-2">📨 سجل الرسائل المرسلة</h1>
                            <p class="mb-0 opacity-75">إدارة وعرض جميع الرسائل المرسلة عبر التليجرام</p>
                        </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="search-box">
                    <input type="text" class="form-control" placeholder="🔍 ابحث عن رسالة أو مستخدم...">
                    <i class="bi bi-search"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="bi bi-funnel me-1"></i> فلترة
                    </button>
                    <a href="{{ route('admin.telegram.index') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> رسالة جديدة
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-0">إجمالي الرسائل</h6>
                                <h3 class="mb-0">{{ $messages->total() }}</h3>
                            </div>
                            <div class="avatar-xs bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-envelope"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-0">الرسائل المرسلة</h6>
                                <h3 class="mb-0">{{ $sentCount ?? 0 }}</h3>
                            </div>
                            <div class="avatar-xs bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-0">قيد الانتظار</h6>
                                <h3 class="mb-0">{{ $pendingCount ?? 0 }}</h3>
                            </div>
                            <div class="avatar-xs bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-danger border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-0">الرسائل الفاشلة</h6>
                                <h3 class="mb-0">{{ $failedCount ?? 0 }}</h3>
                            </div>
                            <div class="avatar-xs bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-x-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ul me-2"></i> قائمة الرسائل
                        </h5>
                        <span class="badge bg-light text-dark">
                            عرض {{ $messages->firstItem() ?? 0 }} - {{ $messages->lastItem() ?? 0 }} من {{ $messages->total() }}
                        </span>
                    </div>
                    
                    <div class="card-body">
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div>{{ session('success') }}</div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        @if($messages->isEmpty())
                        <div class="empty-state">
                            <i class="bi bi-chat-left-text"></i>
                            <h4 class="text-muted mb-3">لا توجد رسائل لعرضها</h4>
                            <p class="text-muted mb-4">سيظهر هنا سجل الرسائل المرسلة عبر التليجرام</p>
                            <a href="{{ route('admin.messages.edit', 'new') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> إضافة رسالة جديدة
                            </a>
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="20%">👤 المستلم</th>
                                        <th width="20%">🎯 الفعالية</th>
                                        <th width="25%">💬 الرسالة</th>
                                        <th width="10%">📊 مرات الاستفادة</th>
                                        <th width="10%">نوع الرسالة</th>
                                        <th width="15%">🕒 التاريخ</th>
                                        <th width="10%">⚙️ العمليات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $index => $msg)
                                    <tr>
                                        <td>{{ ($messages->currentPage() - 1) * $messages->perPage() + $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                        {{ substr($msg->user->full_name ?? '?', 0, 1) }}
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0">{{ $msg->user->full_name ?? 'غير معروف' }}</h6>
                                                    <small class="text-muted">{{ '@' . ($msg->user->username ?? 'بدون معرف') }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($msg->event)
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    {{ Str::limit($msg->event->name, 20) }}
                                                </span>
                                            </div>
                                            @else
                                            <span class="badge bg-secondary">لا يوجد</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="message-preview">
                                                {{ Str::limit($msg->message, 60) }}
                                                @if(strlen($msg->message) > 60)
                                                <a href="javascript:void(0);" 
                                                    class="read-more view-message" 
                                                    data-message="{{ $msg->message }}"
                                                    data-user="{{ $msg->user->full_name ?? 'غير معروف' }}"
                                                    data-event="{{ $msg->event->name ?? 'لا يوجد' }}"
                                                    data-status="{{ $msg->delivery_status }}"
                                                    data-date="{{ 
                                                        ($msg->sent_at 
                                                            ? \Carbon\Carbon::parse($msg->sent_at) 
                                                            : $msg->created_at
                                                        )->format('Y-m-d h:i A') 
                                                    }}"

                                                    title="عرض الرسالة كاملة">
                                                    ... المزيد
                                                </a>
                                                @endif
                                            </div>
                                            <div class="mt-1">
                                                @php
                                                    $statusClasses = [
                                                        'sent' => 'status-sent',
                                                        'delivered' => 'status-delivered', 
                                                        'failed' => 'status-failed',
                                                        'pending' => 'status-pending'
                                                    ];
                                                    $statusText = [
                                                        'sent' => 'مرسلة',
                                                        'delivered' => 'مستلمة',
                                                        'failed' => 'فشلت',
                                                        'pending' => 'قيد الانتظار'
                                                    ];
                                                    $statusClass = $statusClasses[$msg->delivery_status] ?? 'status-pending';
                                                    $text = $statusText[$msg->delivery_status] ?? $msg->delivery_status;
                                                @endphp
                                                <span class="status-badge {{ $statusClass }}">
                                                    {{ $text }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <span class="badge bg-success rounded-pill p-2 d-inline-flex align-items-center">
                                                    <i class="bi bi-trophy me-1"></i>
                                                    <span>{{ $msg->user->benefit_count ?? 0 }}</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($msg->message_type == 'invitation')
                                            <span value="invitation">دعوة حضور</span>
                                            @elseif($msg->message_type == 'attended')
                                            <span value="attended">تم الحضور</span>
                                            @elseif($msg->message_type == 'received')
                                            <span value="received">تم الاستلام</span>
                                            @elseif($msg->message_type == 'general')
                                            <span value="general">عامة</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-muted">
                                                @php
                                                    $date = $msg->sent_at ?? $msg->created_at;
                                                @endphp

                                                <small>{{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}</small>
                                                <br>
                                                <small>{{ \Carbon\Carbon::parse($date)->format('h:i A') }}</small>

                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.messages.edit', $msg->id) }}" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   data-bs-toggle="tooltip"
                                                   title="تعديل الرسالة">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-info view-message"
                                                        data-message="{{ $msg->message }}"
                                                        data-user="{{ $msg->user->full_name ?? 'غير معروف' }}"
                                                        data-event="{{ $msg->event->name ?? 'لا يوجد' }}"
                                                        data-status="{{ $msg->delivery_status }}"
                                                        data-date="{{ 
                                                            ($msg->sent_at 
                                                                ? \Carbon\Carbon::parse($msg->sent_at) 
                                                                : $msg->created_at
                                                            )->format('Y-m-d h:i A') 
                                                        }}"

                                                        data-bs-toggle="tooltip"
                                                        title="عرض التفاصيل">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmDelete({{ $msg->id }})" 
                                                        data-bs-toggle="tooltip"
                                                        title="حذف الرسالة">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    عرض {{ $messages->firstItem() }} إلى {{ $messages->lastItem() }} من إجمالي {{ $messages->total() }} سجل
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $messages->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message View Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-envelope me-2"></i> تفاصيل الرسالة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">
                                    <i class="bi bi-person me-1"></i> المستلم:
                                </label>
                                <p class="fw-bold fs-5" id="modalUser"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">
                                    <i class="bi bi-calendar-event me-1"></i> الفعالية:
                                </label>
                                <p class="fw-bold" id="modalEvent"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">
                                    <i class="bi bi-clock me-1"></i> التاريخ:
                                </label>
                                <p class="fw-bold" id="modalDate"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">
                                    <i class="bi bi-info-circle me-1"></i>حالة الارسال:
                                </label>
                                <p><span class="badge" id="modalStatus"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">
                            <i class="bi bi-chat-text me-1"></i> نص الرسالة:
                        </label>
                        <div class="border rounded p-4 bg-light" style="min-height: 150px; max-height: 300px; overflow-y: auto;" id="modalMessage"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> إغلاق
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">فلترة الرسائل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="filterForm">
                        <div class="mb-3">
                            <label class="form-label">حالة التسليم</label>
                            <select class="form-select" name="status">
                                <option value="">الكل</option>
                                <option value="sent">مرسلة</option>
                                <option value="delivered">مستلمة</option>
                                <option value="failed">فشلت</option>
                                <option value="pending">قيد الانتظار</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الفترة الزمنية</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="date" class="form-control" placeholder="من تاريخ" name="start_date">
                                </div>
                                <div class="col-6">
                                    <input type="date" class="form-control" placeholder="إلى تاريخ" name="end_date">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ترتيب النتائج</label>
                            <select class="form-select" name="order">
                                <option value="newest">الأحدث أولاً</option>
                                <option value="oldest">الأقدم أولاً</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" onclick="applyFilters()">تطبيق الفلترة</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i> تأكيد الحذف
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من حذف هذه الرسالة؟</p>
                    <p class="text-muted">هذا الإجراء لا يمكن التراجع عنه.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-1"></i> نعم، احذف
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="delete-form" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "سيتم حذف هذه الرسالة نهائياً، وقد يتم تعديل عدد الاستفادة!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، احذفها!',
            cancelButtonText: 'إلغاء',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // تحديد رابط الحذف ديناميكياً
                let form = document.getElementById('delete-form');
                form.action = '/admin/messages/' + id; // تأكد أن هذا المسار يطابق تعريف الـ Route عندك
                form.submit();
            }
        })
    }
    </script>
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Global variables
        let messageModal = null;
        let filterModal = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize modals
            messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            filterModal = new bootstrap.Modal(document.getElementById('filterModal'));
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // View message functionality
            // وظيفية عرض التفاصيل (المزيد + زر العين)
            const viewButtons = document.querySelectorAll('.view-message');
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // جلب البيانات من الـ data attributes
                    const message = this.dataset.message;
                    const user = this.dataset.user;
                    const event = this.dataset.event;
                    const date = this.dataset.date;
                    const status = this.dataset.status;

                    // تعبئة المودال بالبيانات الصحيحة
                    document.getElementById('modalUser').textContent = user;
                    document.getElementById('modalEvent').textContent = event;
                    document.getElementById('modalMessage').textContent = message;
                    document.getElementById('modalDate').textContent = date; // هنا سيعرض التاريخ الصحيح

                    // تخصيص حالة الرسالة (Status) بلونها المناسب
                    const statusBadge = document.getElementById('modalStatus');
                    statusBadge.textContent = getStatusText(status);
                    statusBadge.className = 'badge ' + getStatusClass(status);
                    
                    // إظهار زر التعديل إذا كنت تريد (اختياري)
                    if(document.getElementById('modalEditBtn')) {
                        document.getElementById('modalEditBtn').style.display = 'inline-block';
                    }

                    messageModal.show();
                });
            });

            // دوال مساعدة لترجمة الحالة وتلوينها
            function getStatusText(status) {
                const translations = {
                    'sent': 'مرسلة',
                    'delivered': 'مستلمة',
                    'failed': 'فشلت',
                    'pending': 'قيد الانتظار'
                };
                return translations[status] || status;
            }

            function getStatusClass(status) {
                const classes = {
                    'sent': 'bg-success',
                    'delivered': 'bg-primary',
                    'failed': 'bg-danger',
                    'pending': 'bg-warning'
                };
                return classes[status] || 'bg-secondary';
            }
            
            // Search functionality
            const searchInput = document.querySelector('.search-box input');
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    performSearch(this.value);
                }
            });
            
            document.querySelector('.search-box .bi-search').addEventListener('click', function() {
                performSearch(searchInput.value);
            });
        });
        
        
        function applyFilters() {
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams();
            
            for (const [key, value] of formData) {
                if (value) params.append(key, value);
            }
            
            window.location.href = '{{ route("admin.messages.index") }}?' + params.toString();
        }
        
        function performSearch(query) {
            if (query.trim()) {
                window.location.href = '{{ route("admin.messages.index") }}?search=' + encodeURIComponent(query);
            }
        }
        
        function getStatusText(status) {
            const statusMap = {
                'sent': 'مرسلة',
                'delivered': 'مستلمة',
                'failed': 'فشلت',
                'pending': 'قيد الانتظار'
            };
            return statusMap[status] || status;
        }
        
        function getStatusClass(status) {
            const classMap = {
                'sent': 'bg-success text-white',
                'delivered': 'bg-primary text-white',
                'failed': 'bg-danger text-white',
                'pending': 'bg-warning text-dark'
            };
            return classMap[status] || 'bg-secondary text-white';
        }
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        
    </script>
</body>
</html>