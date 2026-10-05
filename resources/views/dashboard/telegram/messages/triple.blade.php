

<h3>المستفيدون من الثلاث فعاليات</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>رقم الهوية</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $user->full_name }}</td>
            <td>{{ $user->national_id }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

