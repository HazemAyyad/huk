<div class="info-card" style="border:2px solid #ef4444">
    <h3>
        <i class="fas fa-unlock"></i>
        إدارة فك الحظر
    </h3>

    <div style="padding:15px;background:#fef2f2;border-radius:10px;margin-bottom:15px">
        <strong>المستخدم الحالي:</strong> {{ $user->full_name }}
        <br>
        <strong>الحالة:</strong>
        @if($user->is_blocked)
            <span style="color:#ef4444">محظور</span>
        @else
            <span style="color:#10b981">غير محظور</span>
        @endif
    </div>

    <form action="{{ route('admin.telegram.unblock', $user->id) }}" method="POST">
        @csrf

        <div class="info-item">
            <label>المرحلة التي سيعود إليها المستخدم بعد فك الحظر</label>

            <select name="step" class="form-control" required>
                <option value="check_national_id">التحقق من رقم الهوية</option>
                <option value="name">الاسم</option>
                <option value="whatsapp">واتساب</option>
                <option value="telegram">تيليجرام</option>
                <option value="egypt_number">رقم مصر</option>
                <option value="family">عدد أفراد الأسرة</option>
                <option value="family_member_name">اسم المرافق</option>
                <option value="family_member_national_id">رقم هوية المرافق</option>
                <option value="governorate">المحافظة</option>
                <option value="city">المدينة</option>
                <option value="address">العنوان</option>
                <option value="benefited">هل استفاد من قبل</option>
                <option value="case">الحالة</option>
                <option value="entry_to_egypt">تاريخ الدخول لمصر</option>
                <option value="rent_amount">قيمة الإيجار</option>
                <option value="landlord_phone">رقم صاحب الشقة</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-unlock"></i>
            فك الحظر وإعادة المستخدم للمرحلة المحددة
        </button>
    </form>
</div>