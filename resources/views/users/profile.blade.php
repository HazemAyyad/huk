
<div class="container">
    <h3>👤 بياناتي</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('user.telegram.profile.update') }}">
        @csrf

        <div class="mb-3">
            <label>الاسم</label>
            <input type="text" class="form-control" value="{{ $user->full_name }}" disabled>
        </div>

        <div class="mb-3">
            <label>رقم الهوية</label>
            <input type="text" class="form-control" value="{{ $user->national_id }}" disabled>
        </div>

        <div class="mb-3">
            <label>رقم واتساب</label>
            <input type="text" name="whatsapp" class="form-control" value="{{ $user->whatsapp }}">
        </div>

        <div class="mb-3">
            <label>العنوان</label>
            <textarea name="address" class="form-control">{{ $user->address }}</textarea>
        </div>

        <button class="btn btn-primary">💾 حفظ</button>
    </form>
</div>
