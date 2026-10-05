<div class="container">

    <h3>📤 رفع كشف الاستفادة</h3>
    <p>الفعالية: <strong>{{ $event->name }}</strong></p>

    <form method="POST" action="{{ route('admin.events.import.store') }}" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="event_id" value="{{ $event->id }}">

        <div class="mb-3">
            <label>ملف Excel</label>
            <input type="file" name="file" class="form-control" required>
        </div>

        <button class="btn btn-success">🚀 رفع ومعالجة</button>
    </form>

    <hr>

    {{-- ✅ النتائج --}}
    @if(session('success_count') !== null)
        <div class="alert alert-success">
            ✅ تم تسجيل {{ session('success_count') }} شخص
        </div>
    @endif

    {{-- ❌ غير الموجودين --}}
    @if(session('not_found') && count(session('not_found')) > 0)
        <div class="alert alert-danger">
            <h5>❗ غير موجودين:</h5>
            <ul>
                @foreach(session('not_found') as $id)
                    <li>{{ $id }}</li>
                @endforeach
            </ul>
        </div>
    @endif

</div>