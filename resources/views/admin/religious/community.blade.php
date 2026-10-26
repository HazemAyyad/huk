<x-admin-layout>
<div class="container-fluid py-4" dir="rtl">
  <h2>مجتمع حق</h2>
  <p>الحلقات والمساهمات والمراجعة والقصص والرسائل. الخدمة العامة: {{ $enabled ? 'مفعلة' : 'غير مفعلة' }}. النصوص الدينية من مكتبة حق فقط؛ الهدف الجماعي تنظيمي.</p>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
  <h3>الحلقات</h3>
  @foreach([...$circles->items(),(object)['id'=>null,'title'=>'','content_id'=>'','goal'=>1000,'published'=>false]] as $circle)
    <form class="card p-3 my-2" method="post" action="{{ route('admin.religious.community.circles') }}">@csrf
      <input type="hidden" name="id" value="{{ $circle->id }}">
      <label>العنوان<input class="form-control" name="title" maxlength="100" value="{{ $circle->title }}" required></label>
      <label>المحتوى<select class="form-control" name="content_id" required>@foreach($items as $item)<option value="{{ $item['id'] }}" @selected($circle->content_id===$item['id'])>{{ $item['title'] }} — {{ $item['id'] }}</option>@endforeach</select></label>
      <label>هدف جماعي<input class="form-control" name="goal" type="number" min="1" max="1000000000000" value="{{ $circle->goal }}" required></label>
      <label>الحالة<select class="form-control" name="published"><option value="0" @selected(!$circle->published)>مسودة</option><option value="1" @selected($circle->published)>منشورة</option></select></label>
      <button class="btn btn-primary mt-2">حفظ الحلقة</button>
    </form>
  @endforeach
  {{ $circles->withQueryString()->links() }}
  <h3>المشاركات والتعليقات</h3>
  @forelse($entries as $entry)
    <div class="card p-3 my-2"><p>#{{ $entry->id }} • حساب {{ $entry->account_id }} • {{ $entry->parent_id ? 'تعليق على '.$entry->parent_id : 'دعاء' }} • {{ $entry->status }}</p><p style="white-space:pre-wrap">{{ $entry->body }}</p>
    @if($entry->status!=='deleted')<form method="post" action="{{ route('admin.religious.community.review',['entry'=>$entry->id]) }}">@csrf<button name="status" value="approved" class="btn btn-success">اعتماد</button> <button name="status" value="rejected" class="btn btn-danger">رفض / إخفاء</button></form>@endif</div>
  @empty<p>لا مشاركات.</p>@endforelse
  {{ $entries->withQueryString()->links() }}
  <h3>البلاغات</h3>
  @forelse($reports as $report)<div class="card p-3 my-2"><p>#{{ $report->id }} • {{ $report->target_type }} {{ $report->target_id }} • {{ $report->status }}</p><p>{{ $report->reason }}</p><form method="post" action="{{ route('admin.religious.community.reports',['report'=>$report->id]) }}">@csrf<select name="action" class="form-control"><option value="resolve">إغلاق البلاغ</option>@if($report->target_type==='entry')<option value="hide">إخفاء المشاركة</option>@endif<option value="suspend">إيقاف الحساب عن المشاركة</option><option value="restore">إعادة السماح للحساب</option></select><button class="btn btn-primary mt-2">تنفيذ</button></form></div>@empty<p>لا بلاغات.</p>@endforelse
  {{ $reports->withQueryString()->links() }}
  <h3>القصص والرسائل</h3>
  <p>المرفقات JPEG/PNG أو MP4 حتى20MB. روابط HTTPS فقط. YouTube بالمعرف ذي11 محرفًا. لا يُنشر قبل الموعد ولا بعد الانتهاء، ولا تتحول المسودة لمنشور تلقائيًا.</p>
  @foreach([...$publications->items(),(object)['id'=>null,'channel'=>'message','kind'=>'dua','title'=>'','body'=>'','content_id'=>'','youtube_id'=>'','link'=>'','status'=>'draft','publish_at'=>null,'expires_at'=>null,'media_path'=>null]] as $pub)
    <details class="card p-3 my-2"><summary>{{ $pub->title ?: 'منشور جديد' }} — {{ $pub->status }}</summary>
    <form method="post" enctype="multipart/form-data" action="{{ route('admin.religious.community.publications') }}">@csrf
    <input type="hidden" name="id" value="{{ $pub->id }}">
    <label>العنوان<input class="form-control" name="title" value="{{ $pub->title }}" required maxlength="100"></label>
    <label>القناة<select class="form-control" name="channel">@foreach(['message'=>'رسالة','story'=>'قصة'] as $key=>$label)<option value="{{ $key }}" @selected($pub->channel===$key)>{{ $label }}</option>@endforeach</select></label>
    <label>النوع<select class="form-control" name="kind">@foreach(['quran'=>'قرآن','hadith'=>'حديث','dua'=>'دعاء','dhikr'=>'ذكر','tasbih'=>'تسبيح','video'=>'فيديو','link'=>'رابط'] as $key=>$label)<option value="{{ $key }}" @selected($pub->kind===$key)>{{ $label }}</option>@endforeach</select></label>
    <label>محتوى موثق<select class="form-control" name="content_id"><option value="">بدون محتوى للمرفق/الرابط</option>@foreach($items as $item)<option value="{{ $item['id'] }}" @selected($pub->content_id===$item['id'])>{{ $item['title'] }} — {{ $item['id'] }}</option>@endforeach</select></label>
    <label>وصف فيديو أو رابط فقط<textarea class="form-control" name="body" maxlength="2000">{{ $pub->body }}</textarea></label>
    <label>ملف<input class="form-control" name="media" type="file" accept="image/jpeg,image/png,video/mp4"></label><small>{{ $pub->media_path }}</small>
    <label><input type="checkbox" name="clear_media" value="1"> إزالة المرفق السابق من هذا المنشور</label>
    <label>معرف YouTube<input class="form-control" name="youtube_id" value="{{ $pub->youtube_id }}" maxlength="11"></label>
    <label>رابط HTTPS<input class="form-control" name="link" value="{{ $pub->link }}" type="url"></label>
    <label>وقت النشر (توقيت الخادم {{ config('app.timezone') }})<input class="form-control" type="datetime-local" name="publish_at" value="{{ $pub->publish_at ? \Carbon\Carbon::parse($pub->publish_at)->format('Y-m-d\TH:i') : '' }}"></label>
    <label>انتهاء اختياري<input class="form-control" type="datetime-local" name="expires_at" value="{{ $pub->expires_at ? \Carbon\Carbon::parse($pub->expires_at)->format('Y-m-d\TH:i') : '' }}"></label>
    <label>الحالة<select class="form-control" name="status">@foreach(['draft'=>'مسودة','scheduled'=>'معتمد ومجدول','published'=>'منشور','hidden'=>'مخفي'] as $key=>$label)<option value="{{ $key }}" @selected($pub->status===$key)>{{ $label }}</option>@endforeach</select></label>
    <button class="btn btn-primary mt-2">حفظ المنشور</button></form></details>
  @endforeach
  {{ $publications->withQueryString()->links() }}
</div>
</x-admin-layout>
