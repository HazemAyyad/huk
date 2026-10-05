<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>طلبات الكراسي الكهربائية</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E♿%3C/text%3E%3C/svg%3E">

</head>

<body class="bg-light">

<div class="container mt-4">

    <div class="card shadow">

        {{-- عنوان الصفحة --}}
        <div class="card-header bg-primary text-white">

            <h4 class="mb-0">
                طلبات الكراسي الكهربائية
            </h4>

        </div>


        <div class="card-body">

            {{-- جدول الطلبات --}}
            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle text-center">

                    <thead class="table-light">

                    <tr>

                        <th>الصورة</th>

                        <th>الاسم</th>

                        <th>رقم الهوية</th>

                        <th>الهاتف</th>

                        <th>العمر</th>

                        <th>الحالة</th>

                        <th>تاريخ دخول مصر</th>

                        <th>الإجراءات</th>

                    </tr>

                    </thead>


                    <tbody>

                    @forelse($requests as $request)

                        <tr>

                            {{-- الصورة الشخصية --}}
                            <td width="90">

                                @if($request->personal_photo)

                                    <img
                                        src="{{ asset($request->personal_photo) }}"
                                        alt="الصورة الشخصية"
                                        class="rounded-circle"
                                        width="70"
                                        height="70"
                                        style="object-fit: cover;"
                                    >

                                @else

                                    <span class="text-muted">
                                        لا توجد صورة
                                    </span>

                                @endif

                            </td>


                            {{-- الاسم --}}
                            <td>

                                {{ $request->full_name }}

                            </td>


                            {{-- رقم الهوية --}}
                            <td>

                                {{ $request->national_id }}

                            </td>


                            {{-- الهاتف --}}
                            <td>

                                {{ $request->phone }}

                            </td>


                            {{-- العمر --}}
                            <td>

                                {{ $request->age }}

                            </td>


                            {{-- الحالة --}}
                            <td>

                                @if($request->status === 'pending')

                                    <span class="badge bg-warning text-dark">
                                        قيد المراجعة
                                    </span>


                                @elseif($request->status === 'approved_first')

                                    <span class="badge bg-success">
                                        معتمد من الدرجة الأولى
                                    </span>


                                @elseif($request->status === 'approved_second')

                                    <span class="badge bg-success">
                                        معتمد من الدرجة الثانية
                                    </span>


                                @elseif($request->status === 'approved_third')

                                    <span class="badge bg-success">
                                        معتمد من الدرجة الثالثة
                                    </span>


                                @elseif($request->status === 'rejected_previous')

                                    <span class="badge bg-danger">
                                        مرفوض بسبب الاستلام المسبق
                                    </span>


                                @elseif($request->status === 'rejected_not_eligible')

                                    <span class="badge bg-danger">
                                        مرفوض بسبب عدم الاستحقاق
                                    </span>


                                @else

                                    <span class="badge bg-secondary">
                                        غير محدد
                                    </span>

                                @endif

                            </td>


                            {{-- تاريخ الطلب --}}
                            <td>

                                {{ $request->entry_date->format('Y-m-d') }}

                            </td>


                            {{-- الإجراءات --}}
                            <td>

                                <a
                                    href="{{ route('wheelchair.show', $request->id) }}"
                                    class="btn btn-sm btn-primary"
                                >

                                    عرض التفاصيل

                                </a>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="8" class="py-4 text-muted">

                                لا توجد طلبات مسجلة

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            <div class="mt-3">

                {{ $requests->links() }}

            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>