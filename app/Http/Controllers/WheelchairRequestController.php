<?php

namespace App\Http\Controllers;

use App\Models\WheelchairRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WheelchairRequestController extends Controller
{

    /**
     * عرض صفحة التحقق من رقم الهوية
     */
    public function checkPage()
    {
        return view('wheelchair.check');
    }

    /**
     * عرض صفحة التسجيل
     */
    public function registerPage(Request $request)
    {
        return view('wheelchair.register', [
            'national_id' => $request->national_id
        ]);
    }

    /**
     * التحقق من وجود رقم الهوية
     */
    public function checkId(Request $request)
    {
        $request->validate([
            'national_id' => 'required'
        ], [
            'national_id.required' => 'يرجى إدخال رقم الهوية'
        ]);

        $exists = WheelchairRequest::where(
            'national_id',
            $request->national_id
        )->exists();

        if ($exists) {

            return back()->with(
                'error',
                'هذا الرقم مسجل مسبقاً ولا يمكن التسجيل مرة أخرى.'
            );

        }

        return redirect()->route('wheelchair.register', [
            'national_id' => $request->national_id
        ]);
    }

    /**
     * حفظ الطلب
     */
    public function store(Request $request)
    {

        $request->validate([

            'full_name'            => 'required|string|max:255',
            'national_id'          => 'required|unique:wheelchair_requests,national_id',
            'phone'                => 'required|string|max:20',
            'whatsapp'             => 'nullable|string|max:20',

            'personal_photo'       => 'required|image|max:5120',
            'disability_photo'     => 'required|image|max:5120',
            'passport_photo'       => 'required|image|max:5120',
            'entry_stamp_photo'    => 'required|image|max:5120',
            'medical_report'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            'age'                  => 'required|integer|min:1|max:120',
            'address'              => 'required|string|max:255',
            'entry_date'           => 'required|date',

            'medical_condition'    => 'required|string',
            'injury_reason'        => 'required|string',

            'has_wheelchair'       => 'required|boolean',
            'wheelchair_status'    => 'nullable|string',

            'received_before'      => 'required|boolean',
            'organization_name'    => 'nullable|string|max:255',

            'notes'                => 'nullable|string',

        ], [

            'required' => 'حقل :attribute مطلوب.',
            'image'    => 'حقل :attribute يجب أن يكون صورة.',
            'mimes'    => 'صيغة :attribute غير مدعومة.',
            'max'      => 'حجم :attribute أكبر من المسموح.',
            'integer'  => ':attribute يجب أن يكون رقماً.',
            'date'     => ':attribute يجب أن يكون تاريخاً صحيحاً.',
            'unique'   => 'رقم الهوية مسجل مسبقاً.',

        ], [

            'full_name'          => 'الاسم الرباعي',
            'national_id'        => 'رقم الهوية',
            'phone'              => 'رقم الهاتف',
            'whatsapp'           => 'رقم الواتساب',
            'personal_photo'     => 'الصورة الشخصية',
            'disability_photo'   => 'صورة الحالة المرضية',
            'passport_photo'     => 'صورة الجواز',
            'entry_stamp_photo'  => 'صورة ختم الدخول',
            'medical_report'     => 'التقرير الطبي',
            'age'                => 'العمر',
            'address'            => 'عنوان السكن',
            'entry_date'         => 'تاريخ دخول مصر',
            'medical_condition'  => 'شرح الحالة المرضية',
            'injury_reason'      => 'سبب الإصابة',
            'has_wheelchair'     => 'امتلاك كرسي',
            'wheelchair_status'  => 'حالة الكرسي',
            'received_before'    => 'الاستلام من جمعية أخرى',
            'organization_name'  => 'اسم الجمعية',
            'notes'              => 'الملاحظات',

        ]);

        try {

            DB::beginTransaction();

            $wheelchair = new WheelchairRequest();

            $wheelchair->fill([

                'full_name'           => $request->full_name,
                'national_id'         => $request->national_id,
                'phone'               => $request->phone,
                'whatsapp'            => $request->whatsapp,

                'age'                 => $request->age,
                'address'             => $request->address,
                'entry_date'          => $request->entry_date,

                'medical_condition'   => $request->medical_condition,
                'injury_reason'       => $request->injury_reason,

                'has_wheelchair'      => $request->has_wheelchair,
                'wheelchair_status'   => $request->wheelchair_status,

                'received_before'     => $request->received_before,
                'organization_name'   => $request->organization_name,

                'notes'               => $request->notes,

            ]);

            // رفع الملفات
            $this->handleFiles($request, $wheelchair);

            // حفظ البيانات
            $wheelchair->save();

            DB::commit();

            return redirect()->route('wheelchair.success');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء حفظ البيانات.');
        }

    }
    /**
     * رفع الملفات
     */
    protected function handleFiles(Request $request, WheelchairRequest $wheelchair)
    {
        $files = [
            'personal_photo'     => 'personal',
            'disability_photo'   => 'disability',
            'passport_photo'     => 'passports',
            'entry_stamp_photo'  => 'entry_stamps',
            'medical_report'     => 'medical_reports',
        ];

        foreach ($files as $field => $folder) {

            if ($request->hasFile($field)) {

                $wheelchair->$field = $this->uploadFile(
                    $request->file($field),
                    $folder
                );

            }

        }
    }

    /**
     * دالة رفع الملفات
     */
    protected function uploadFile($file, $folder)
    {
        $fileName = Str::random(30) . '.' . $file->getClientOriginalExtension();

        $destination = public_path("uploads/wheelchairs/$folder");

        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $fileName);

        return "uploads/wheelchairs/$folder/" . $fileName;
    }
}