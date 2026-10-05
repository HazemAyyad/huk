<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اعتماد الحالة</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">اعتماد الحالة</h4>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('admin.telegram.approve', $user->id) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">اسم من قام بالزيارة</label>
                            <input
                                type="text"
                                name="approved_by"
                                class="form-control"
                                value="{{ old('approved_by') }}"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">شرح الحالة</label>
                            <textarea
                                name="approval_description"
                                class="form-control"
                                rows="7"
                                required
                            >{{ old('approval_description') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                اعتماد الحالة
                            </button>

                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                رجوع
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

</body>
</html>