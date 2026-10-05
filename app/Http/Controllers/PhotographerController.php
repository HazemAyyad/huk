<?php

namespace App\Http\Controllers;

use App\Models\PhotographerAd;
use App\Models\PhotographerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PhotographerController extends Controller
{
    
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin|admin|user']);
    }

    public function show()
    {
        $ad = PhotographerAd::where('is_active', true)->firstOrFail();
        
        // التحقق إذا كان المستخدم سجل مسبقاً من نفس الجهاز
        $ip = request()->ip();
        $exists = PhotographerApplication::where('ip_address', $ip)->exists();
        
        // إذا كان مسجل مسبقاً، عرض رسالة منع
        if ($exists) {
            return view('photographer.already-registered');
        }
        
        return view('photographer.form', compact('ad'));
    }

    public function form(Request $request)
    {
        $ip = $request->ip();

        // تحقق هل هذا IP سجل مسبقًا
        $exists = PhotographerApplication::where('ip_address', $ip)->exists();

        if ($exists) {
            return redirect()->route('photographer.index')
                ->with('error', 'لا يمكن التسجيل أكثر من مرة من نفس الجهاز');
        }

        // قواعد التحقق الميدانية
        $rules = [
            'full_name' => 'required|string|max:255',
            'personal_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'governorate' => 'required|string|max:100', 
            'national_id' => 'required|string|max:20|unique:photographer_applications,national_id',
            'phone_egypt' => 'required|string|max:20|regex:/^01[0-9]{9}$/',
            'passport' => 'required|image|mimes:jpeg,png,jpg|max:2048', 
            'age' => 'required|integer|min:18|max:100',
            'camera_equipment' => 'required|string|max:255',
            'phone_egypt_2' => 'required|string|max:20',
            'whatsapp_palestine' => 'required|string|max:20',
            'family_in_egypt' => 'required|integer|min:0',
            'family_in_gaza' => 'nullable|integer|min:0',
            'entry_reason' => 'required|string|max:255',
            'entry_date' => 'required|date',
            'address' => 'required|string|max:255',
            'education' => 'required|string|max:255',
            'work_in_gaza' => 'required|string|max:255',
            'has_experience' => 'required|in:0,1',
            'experience_details' => 'required_if:has_experience,1|nullable|string|max:500',
            'case_details' => 'nullable|string|max:500',
        ];

        // رسائل الخطأ المخصصة
        $messages = [
            'personal_photo.required' => 'الصورة الشخصية مطلوبة',
            'personal_photo.image' => 'يجب أن يكون الملف المرفوع صورة',
            'governorate.required' => 'يرجى تحديد المحافظة',
            'passport.required' => 'صورة جواز السفر مطلوبة',
            'passport.image' => 'ملف الجواز يجب أن يكون صورة بصيغة (jpg, png)',
            'full_name.required' => 'الاسم الكامل مطلوب',
            'full_name.max' => 'الاسم الكامل طويل جداً',
            'national_id.required' => 'رقم الهوية مطلوب',
            'national_id.unique' => 'هذا رقم الهوية مسجل مسبقاً',
            'phone_egypt.required' => 'رقم الهاتف في مصر مطلوب',
            'phone_egypt.regex' => 'رقم الهاتف غير صحيح (يجب أن يبدأ بـ 01 ويتكون من 11 رقم)',
            'age.required' => 'العمر مطلوب',
            'age.min' => 'العمر يجب أن يكون 18 سنة على الأقل',
            'age.max' => 'العمر يجب أن يكون أقل من 100 سنة',
            'has_experience.required' => 'يرجى تحديد إذا كان لديك خبرة في التصوير',
            'experience_details.required_if' => 'يرجى كتابة تفاصيل الخبرة',
        ];

        $validated = $request->validate($rules, $messages);

        // 3. تجهيز البيانات واستثناء الملفات لرفعها يدوياً
        $data = $request->except(['_token', 'personal_photo', 'passport']);

        // رفع الصورة الشخصية
        if ($request->hasFile('personal_photo')) {
            $file = $request->file('personal_photo');
            $filename = time().'_photo_'.$file->getClientOriginalName();

            $destination = public_path('uploads/photographer/photos');

            $file->move($destination, $filename);

            $data['personal_photo'] = 'uploads/photographer/photos/'.$filename;
        }

        // رفع صورة الجواز
        if ($request->hasFile('passport')) {
            $file = $request->file('passport');
            $filename = time().'_passport_'.$file->getClientOriginalName();

            $destination = public_path('uploads/photographer/passports');

            $file->move($destination, $filename);

            $data['passport'] = 'uploads/photographer/passports/'.$filename;
        }



        $ad = PhotographerAd::where('is_active', true)->firstOrFail();

        // 4. الحفظ النهائي باستخدام المصفوفة المعدلة $data
        PhotographerApplication::create(array_merge($data, [
            'photographer_ad_id' => $ad->id,
            'ip_address' => $ip,
        ]));

        return redirect()->route('photographer.index')
            ->with('success', 'تم إرسال الطلب بنجاح');
    }

    public function applications(Request $request)
    {

        $query = PhotographerApplication::with('photographerAd')->latest();

        // بحث برقم الهوية
        if ($request->national_id) {
            $query->where('national_id', 'like', '%' . $request->national_id . '%');
        }

        // فلترة بنوع المعدة
        if ($request->filled('camera_equipment')) {
            if ($request->camera_equipment === 'other') {
                $query->whereNotIn('camera_equipment', [
                    'كاميرا احترافية',
                    'هاتف ايفون 17 pro max',
                    'هاتف ايفون 16 pro max',
                    'هاتف ايفون 15 pro max',
                    'هاتف سامسونج 25 الترا',
                    'هاتف سامسونج 24 الترا',
                    'هاتف سامسونج 23 الترا',
                ]);
            } else {
                $query->where('camera_equipment', $request->camera_equipment);
            }
        }

        $applications = $query->paginate(15);

        return view('photographer.applications', compact('applications'));
    }


}