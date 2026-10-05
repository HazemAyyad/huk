@extends('layouts.adminss')

@push('styles')
<style>
    /* عزل كامل للستايل داخل هذه الصفحة فقط */
    .dashboard-page .custom-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        border-radius: 35px;
        margin-bottom: 20px !important;
    }

    .dashboard-page .custom-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .dashboard-page .icon-wrapper {
        background-color: #f8f9fa;
        border-radius: 50%;
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    @media (max-width: 576px) {
        .dashboard-page .custom-card {
            margin-bottom: 20px !important;
        }
    }

.text-decoration-none:hover {
        opacity: 0.8;
        cursor: pointer;
    }
    
    /* أرقام الإحصائيات - إجمالي */
    .dashboard-page .stat-number-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px dashed #e2e8f0;
        margin-bottom: 12px;
    }

    .dashboard-page .stat-number-value {
        font-size: 26px;
        font-weight: 700;
        color: #212529;      /* لون غامق مرئي */
        line-height: 1.2;
    }

    .dashboard-page .stat-number-label {
        font-size: 13px;
        color: #6c757d;
        font-weight: 500;
        margin-top: 4px;
    }

    /* تفاصيل الإحصائيات */
    .dashboard-page .stat-details-box {
        padding: 8px 4px;
    }

    .dashboard-page .stat-detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 6px 0;
        font-size: 13px;
        border-bottom: 1px solid #f1f3f5;
    }

    .dashboard-page .stat-detail-row:last-child {
        border-bottom: none;
    }

    .dashboard-page .stat-detail-row span {
        color: #6c757d;      /* لون رمادي للوصف */
    }

    .dashboard-page .stat-detail-row strong {
        color: #212529;      /* لون غامق للرقم - بدل الأبيض غير المرئي */
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')

<div class="container py-5 dashboard-page">
    <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <a href="{{ route('admin.telegram.index') }}" class="text-decoration-none" style="display: block; transition: opacity 0.3s;">
                        <div class="p-3 rounded border-0 hover-shadow"> 
                            <h3 class="fw-bold mb-2" style="color: #2d3748;">
                                <i class="fas fa-chart-line me-2" style="color: #667eea;"></i>
                                لوحة التحكم تيليجرام
                            </h3>
                            <p class="text-muted mb-0" style="font-size: 0.95rem;">
                               الانتقال الى لوحة التحكم تيليجرام
                            </p>
                        </div>
                    </a>
                    <div class="d-flex gap-2">
                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                            <i class="fas fa-calendar-alt me-1 text-primary"></i>
                            {{ now()->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
                <hr class="mt-3 mb-0" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); height: 3px; opacity: 0.3;">
            </div>
        </div>

    <div class="row g-4">

        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="card h-100 shadow-sm p-4 custom-card">

        {{-- الأيقونة والعنوان --}}
        <div class="text-center">
            <div class="icon-wrapper d-flex align-items-center justify-content-center">
                <img src="{{ asset('/files/orphan.png') }}" alt="Orphans" style="width: 50px;">
            </div>
            <h5 class="fw-bold mb-3">الأيتام</h5>
        </div>

        {{-- الإجمالي --}}
        <div class="stat-total-box">
            <span class="stat-total-value">{{ number_format($orphansCount) }}</span>
            <span class="stat-total-label">إجمالي الأيتام</span>
        </div>

        {{-- التفاصيل --}}
        <div class="stat-details-box">
            <div class="stat-detail-row">
                <span class="stat-detail-label">المسجلون عبر الرابط</span>
                <strong class="stat-detail-value">{{ number_format($orphansRegisteredInLink) }}</strong>
            </div>
            <div class="stat-detail-row">
                <span class="stat-detail-label">المتبقي غير المسجل</span>
                <strong class="stat-detail-value">{{ number_format($orphansRemaining) }}</strong>
            </div>
            <div class="stat-detail-row">
                <span class="stat-detail-label">الحاصلون على كود</span>
                <strong class="stat-detail-value">{{ number_format($orphansWithCode) }}</strong>
            </div>
            <div class="stat-detail-row">
                <span class="stat-detail-label">المتبقي بدون كود</span>
                <strong class="stat-detail-value">{{ number_format($orphansWithoutCode) }}</strong>
            </div>
        </div>

        {{-- الزر --}}
        <div class="mt-auto pt-3">
            <a href="{{ route('admin.orphans.index') }}" class="btn btn-outline-primary w-100 rounded-pill">
                عرض الطلبات
            </a>
        </div>
    </div>
</div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="card h-100 shadow-sm p-4 custom-card">

        <div class="text-center">
            <div class="icon-wrapper d-flex align-items-center justify-content-center">
                <img src="{{ asset('/files/amputation.png') }}" alt="Amputation" style="width: 50px;">
            </div>
            <h5 class="fw-bold mb-3">حالات البتر</h5>
        </div>

        <div class="stat-total-box">
            <span class="stat-total-value">{{ number_format($amputationsCount) }}</span>
            <span class="stat-total-label">إجمالي حالات البتر</span>
        </div>

        <div class="stat-details-box">
            <div class="stat-detail-row">
                <span class="stat-detail-label">المسجلون عبر الرابط</span>
                <strong class="stat-detail-value">{{ number_format($amputationsRegisteredInLink) }}</strong>
            </div>
            <div class="stat-detail-row">
                <span class="stat-detail-label">المتبقي غير المسجل</span>
                <strong class="stat-detail-value">{{ number_format($amputationsRemaining) }}</strong>
            </div>
            <div class="stat-detail-row">
                <span class="stat-detail-label">الحاصلون على كود</span>
                <strong class="stat-detail-value">{{ number_format($amputationsWithCode) }}</strong>
            </div>
            <div class="stat-detail-row">
                <span class="stat-detail-label">المتبقي بدون كود</span>
                <strong class="stat-detail-value">{{ number_format($amputationsWithoutCode) }}</strong>
            </div>
        </div>

        <div class="mt-auto pt-3">
            <a href="{{ route('admin.amputations.index') }}" class="btn btn-outline-primary w-100 rounded-pill">
                عرض التفاصيل
            </a>
        </div>
    </div>
</div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 text-center shadow-sm p-4 custom-card">
                <div class="icon-wrapper d-flex align-items-center justify-content-center">
                    <img src="{{ asset('/files/patient_case.png') }}" alt="Patient Case" style="width: 50px;">
                </div>
                <h5 class="fw-bold mb-3 text-center">حالات مرضى ومصابين</h5>
                <span class="text-center">الإجمالي: {{ $totalPatientCase }}</span>

                <div class="mt-auto text-center">
                    <a href="{{ route('admin.telegram.index') }}?search=الحالات المرضية&status=" class="btn btn-outline-primary w-100 rounded-pill text-center">
                        عرض التفاصيل
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 text-center shadow-sm p-4 custom-card">
                <div class="icon-wrapper d-flex align-items-center justify-content-center">
                    <img src="{{ asset('/files/family_breadwinner.png') }}" alt="Family Breadwinner" style="width: 50px;">
                </div>
                <h5 class="fw-bold mb-3 text-center">المساكن الفلسطينية</h5>
                <span class="text-center">الإجمالي: {{ $totalSolidarityHousing }}</span>

                <div class="mt-auto text-center">
                    <a href="{{ route('admin.categories.indexfilter') }}?category=3&subcategory=" class="btn btn-outline-primary w-100 rounded-pill text-center">
                        عرض التفاصيل
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 text-center shadow-sm p-4 custom-card">
                <div class="icon-wrapper d-flex align-items-center justify-content-center">
                    <img src="{{ asset('/files/family_breadwinner.png') }}" alt="Family Breadwinner" style="width: 50px;">
                </div>
                <h5 class="fw-bold mb-3 text-center">اسرة بدون معيل</h5>
                <span class="text-center">الإجمالي: {{ $totalFamilyBreadwinner }}</span>

                <div class="mt-auto text-center">
                    <a href="#" class="btn btn-outline-primary w-100 rounded-pill text-center">
                        عرض التفاصيل
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 text-center shadow-sm p-4 custom-card">
                <div class="icon-wrapper d-flex align-items-center justify-content-center">
                    <img src="{{ asset('/files/stranded_family.png') }}" alt="Stranded Family" style="width: 50px;">
                </div>
                <h5 class="fw-bold mb-3 text-center">فئات اخرى</h5>
                <span class="text-center">الإجمالي: {{ $totalOtherCategories }}</span>

                <div class="mt-auto text-center">
                    <a href="{{ route('admin.telegram.index') }}?search=فئات+أخرى&status=" class="btn btn-outline-primary w-100 rounded-pill text-center">
                        عرض التفاصيل
                    </a>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="card h-100 text-center shadow-sm p-4 custom-card">
        <div class="icon-wrapper d-flex align-items-center justify-content-center">
            <img src="{{ asset('/files/totalUsers.png') }}" alt="Total Users" style="width: 50px;">
        </div>

        <h5 class="fw-bold mb-3 text-center">إجمالي المستخدمين</h5>

        <span class="text-center">الإجمالي: {{ $totalUsers }}</span>

        <div class="mt-auto text-center">
            <a href="{{ route('admin.telegram.index') }}"
               class="btn btn-outline-success w-100 rounded-pill text-center">
                عرض المستخدمين
            </a>
        </div>
    </div>
</div>
    </div>
</div>

@endsection