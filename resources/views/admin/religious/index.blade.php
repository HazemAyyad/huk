<x-admin-layout>
    <div class="container-fluid py-4" dir="rtl">
        <h2>الأدعية والمحتوى الديني</h2>
        <p>إدارة النصوص والمراجع والتصنيفات والنشر في تطبيق حق. التعديلات تظهر للتطبيق بعد تحديث المحتوى.</p>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="d-flex flex-wrap mb-3" style="gap:12px">
            <a class="btn btn-primary" href="{{ route('admin.religious.create') }}">إضافة محتوى</a>
            <form method="get" class="d-flex" style="gap:8px">
                <input name="q" value="{{ request('q') }}" class="form-control" placeholder="ابحث بالعنوان أو المعرف">
                <button class="btn btn-outline-primary">بحث</button>
            </form>
        </div>
        <div class="table-responsive"><table class="table table-striped bg-white">
            <thead><tr><th>العنوان</th><th>النوع</th><th>المصدر</th><th>النشر</th><th></th></tr></thead>
            <tbody>@foreach($items as $item)<tr>
                <td>{{ $item['title'] }}<small class="d-block text-muted">{{ $item['id'] }}</small></td>
                <td>{{ ['quran'=>'آيات','dua'=>'دعاء','hadith'=>'حديث','sunnah'=>'سنة','name'=>'اسم','playlist'=>'قائمة استماع'][$item['type']] }}</td>
                <td>{{ $item['source'] }}</td><td>{{ $item['published'] ? 'منشور' : 'مسودة' }}</td>
                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.religious.edit',['id'=>$item['id']]) }}">تعديل</a></td>
            </tr>@endforeach</tbody>
        </table></div>
        <h3 class="mt-4">التصنيفات وترتيبها</h3>
        <p>ترتيب التصنيفات يتبع ترتيب القائمة. الأقسام الأساسية السبعة تبقى ظاهرة، ويمكن تعديل عنوانها ووصفها.</p>
        @foreach([...$catalog['categories'], ['id'=>'','title'=>'','description'=>'','parent'=>null,'image'=>'quran','header'=>'sunnah','kind'=>'list','published'=>false]] as $category)
            <details class="card mb-2 p-3">
                <summary>{{ $category['title'] ?: 'إضافة تصنيف جديد' }}</summary>
                <form method="post" action="{{ route('admin.religious.categories.save') }}" class="mt-3">
                    @csrf
                    <div class="row">
                        <label class="col-md-4">المعرف الثابت<input class="form-control" name="id" value="{{ $category['id'] }}" required @readonly($category['id'] !== '')></label>
                        <label class="col-md-4">العنوان<input class="form-control" name="title" value="{{ $category['title'] }}" required></label>
                        <label class="col-md-4">التصنيف الأب<select class="form-control" name="parent"><option value="">قسم رئيسي</option>
                            @foreach($catalog['categories'] as $parent) @if($parent['id']!==$category['id'])
                                <option value="{{ $parent['id'] }}" @selected(($category['parent']??null)===$parent['id'])>{{ $parent['title'] }}</option>
                            @endif @endforeach
                        </select></label>
                    </div>
                    <label class="d-block">الوصف<textarea class="form-control" name="description" required>{{ $category['description'] }}</textarea></label>
                    <label>الترتيب<input class="form-control" name="position" type="number" min="1" max="1000" value="{{ $loop->iteration }}"></label>
                    <div class="row">
                        <label class="col-md-4">الصورة<select class="form-control" name="image">@foreach(\App\Services\Religious\ReligiousCatalog::IMAGES as $image)<option @selected($image===$category['image'])>{{ $image }}</option>@endforeach</select></label>
                        <label class="col-md-4">الخلفية<select class="form-control" name="header">@foreach(['home'=>'المسجد الفاتح','ruqya'=>'المصحف والفانوس','sunnah'=>'المسجد والمصحف','quran'=>'القوس الأخضر','reading'=>'المصحف المفتوح والفانوس','medina'=>'المسجد عند الغروب','sleep'=>'أدعية النوم والاستيقاظ'] as $id=>$label)<option value="{{ $id }}" @selected($id===$category['header'])>{{ $label }}</option>@endforeach</select></label>
                        <label class="col-md-4">طريقة العرض<select class="form-control" name="kind">@foreach(['list'=>'قائمة','categories'=>'تصنيفات','names'=>'شبكة أسماء','ruqya'=>'رئيسية الرقية','verses'=>'قائمة آيات'] as $id=>$label)<option value="{{ $id }}" @selected($id===$category['kind'])>{{ $label }}</option>@endforeach</select></label>
                    </div>
                    <label><input type="checkbox" name="published" value="1" @checked($category['published'])> منشور</label>
                    <button class="btn btn-primary mx-3">حفظ التصنيف</button>
                </form>
            </details>
        @endforeach
    </div>
</x-admin-layout>
