<!--@extends('layouts.app')-->

<!--@section('content')-->
<div class="container">
    <h3>📨 إرسال رسالة للمستخدم</h3>

    <p>
        <strong>الاسم:</strong> {{ $user->full_name ?? $user->first_name }} <br>
        <strong>Chat ID:</strong> {{ $user->chat_id }}
    </p>

    @if(session('success'))
        <div style="color: green">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ url('/dashboard/telegram/send') }}">
        @csrf

        <input type="hidden" name="user_id" value="{{ $user->id }}">

        <textarea name="message" rows="6" style="width:100%" required
                  placeholder="اكتب الرسالة التي ستصل للمستخدم..."></textarea>

        <br><br>
        <button type="submit">🚀 إرسال</button>
    </form>
</div>
<!--@endsection-->
