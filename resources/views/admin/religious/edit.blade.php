<x-admin-layout>
    <div class="container py-4" dir="rtl">
        <h2>{{ $creating ? 'إضافة محتوى ديني' : 'تعديل المحتوى الديني' }}</h2>
        <a href="{{ route('admin.religious.index') }}">الرجوع إلى المحتوى والتصنيفات</a>
        @if(session('success'))<div class="alert alert-success mt-3">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger mt-3"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="post" action="{{ route('admin.religious.save') }}" class="mt-3">
            @csrf
            <div class="row">
                <label class="col-md-4">المعرف الثابت<input class="form-control" name="id" required value="{{ old('id',$item['id']) }}" @readonly(!$creating)><small>حروف إنجليزية وأرقام وشرطة، ويحافظ على روابط المفضلة.</small></label>
                <label class="col-md-4">نوع المادة<select name="type" class="form-control">@foreach(['quran'=>'آيات قرآنية','dua'=>'دعاء','hadith'=>'حديث','sunnah'=>'سنة','name'=>'اسم من أسماء الله','playlist'=>'قائمة استماع'] as $value=>$label)<option value="{{ $value }}" @selected(old('type',$item['type'])===$value)>{{ $label }}</option>@endforeach</select></label>
                <label class="col-md-4">الرقم<input class="form-control" type="number" min="0" name="number" value="{{ old('number',$item['number']??0) }}"></label>
            </div>
            <label class="d-block">العنوان<input class="form-control" name="title" required value="{{ old('title',$item['title']) }}"></label>
            <label class="d-block">النص الكامل<textarea class="form-control" name="text" required rows="10">{{ old('text',$item['text']) }}</textarea></label>
            <label class="d-block">ملخص للقائمة<textarea class="form-control" name="summary" rows="2">{{ old('summary',$item['summary']??'') }}</textarea></label>
            <div class="row">
                <label class="col-md-6">المصدر والتخريج<input class="form-control" name="source" required value="{{ old('source',$item['source']) }}"></label>
                <label class="col-md-6">رابط المصدر<input class="form-control" name="sourceUrl" type="url" required dir="ltr" value="{{ old('sourceUrl',$item['sourceUrl']) }}"></label>
            </div>
            <fieldset class="card p-3 my-3"><legend>التصنيفات</legend><div class="row">@foreach($catalog['categories'] as $category)
                <label class="col-md-4"><input type="checkbox" name="categories[]" value="{{ $category['id'] }}" @checked(in_array($category['id'],old('categories',$item['categories']),true))> {{ $category['title'] }}</label>
            @endforeach</div></fieldset>
            <div class="row">
                <label class="col-md-6">وسوم التصفية مفصولة بفاصلة<input class="form-control" name="tags" value="{{ old('tags',implode(',',$item['tags']??[])) }}"></label>
                <label class="col-md-6">الصورة<select name="image" class="form-control">@foreach(\App\Services\Religious\ReligiousCatalog::IMAGES as $image)<option @selected(old('image',$item['image'])===$image)>{{ $image }}</option>@endforeach</select></label>
            </div>
            <label class="d-block">الآيات المرتبطة مفصولة بفاصلة<input class="form-control" dir="ltr" name="verseKeys" value="{{ old('verseKeys',implode(',',$item['verseKeys']??[])) }}" placeholder="2:201"><small>للمادة القرآنية يجب أن يطابق النص هذه الآيات حرفيًا وبنفس الترتيب.</small></label>
            <div class="row">
                <label class="col-md-6">رابط التسجيل الصوتي<input class="form-control" type="url" dir="ltr" name="audioUrl" value="{{ old('audioUrl',$item['audioUrl']??'') }}"></label>
                <label class="col-md-6">القارئ أو المصدر الصوتي<input class="form-control" name="reciter" value="{{ old('reciter',$item['reciter']??'') }}"></label>
            </div>
            <label class="d-block">الاسم بالحروف اللاتينية<input class="form-control" name="transliteration" value="{{ old('transliteration',$item['transliteration']??'') }}"></label>
            <label class="d-block">معرفات المواد المرتبطة أو مقاطع الرقية مفصولة بفاصلة<input class="form-control" dir="ltr" name="related" value="{{ old('related',implode(',',$item['related']??[])) }}"></label>
            <h3 class="mt-4">الشرح والأدلة والفوائد</h3>
            @foreach([...old('sections',$item['sections']??[]),['title'=>'','text'=>'','source'=>''],['title'=>'','text'=>'','source'=>'']] as $index=>$section)
                <fieldset class="card p-3 mb-3">
                    <label>عنوان الفقرة<input class="form-control" name="sections[{{ $index }}][title]" value="{{ $section['title'] }}"></label>
                    <label>النص<textarea class="form-control" name="sections[{{ $index }}][text]" rows="5">{{ $section['text'] }}</textarea></label>
                    <label>مصدر الفقرة<input class="form-control" name="sections[{{ $index }}][source]" value="{{ $section['source']??'' }}"></label>
                </fieldset>
            @endforeach
            <p>تُترك الفقرات الفارغة دون حفظ. احفظ التغييرات لإضافة فقرات أخرى.</p>
            <label><input type="checkbox" name="published" value="1" @checked(old('published',$item['published']))> تمت مراجعة المادة والمراجع وتُعرض للمستخدمين</label>
            <div class="my-3"><button class="btn btn-primary btn-lg">حفظ المحتوى</button></div>
        </form>
    </div>
</x-admin-layout>
