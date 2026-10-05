<?php

namespace App\Http\Controllers\HumanitarianCases;

use App\Http\Controllers\Controller;
use App\Models\PatientCase;
use App\Models\PatientFamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    public function create()
    {
        return view('humanitarian-cases.patient.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // بيانات المريض الرئيسي
            'full_name' => [
                'required',
                'string',
                'max:191',
                'regex:/^(\S+\s+){3,}\S+$/'  // اسم رباعي على الأقل
            ],
            'national_id' => 'required|digits:14|unique:sick_cases,national_id',
            'main_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'additional_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'passport_number' => 'nullable|string|max:50',
            'birth_date' => 'required|date',
            'age' => 'required|integer|min:0',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'detailed_address' => 'required|string|max:500',
            'location' => 'nullable|string', // رابط Google Maps
            'housing_status' => 'required|in:rent,own,hosted',
            'rent_value' => 'required_if:housing_status,rent|nullable|numeric|min:0',
            'social_status' => 'required|string|max:191',
            'entry_date_to_egypt' => 'required|date',
            'entry_method' => 'required|string|max:191',

            // تفاصيل المرض
            'disease_name' => 'required|string|max:500',
            'diagnosis_date' => 'required|date',
            'disease_caused_disability' => 'required|boolean',
            'disability_type' => 'required_if:disease_caused_disability,true|nullable|in:physical,mental,both',
            'received_treatment_from_haq' => 'required|boolean',
            'treatment_type' => 'required_if:received_treatment_from_haq,true|nullable|string|max:500',
            'health_description' => 'required|string|max:1000',

            // المرافق (إن وجد)
            'has_companion' => 'required|boolean',
            'companion_name' => 'required_if:has_companion,true|nullable|string|max:191',
            'companion_national_id' => 'required_if:has_companion,true|nullable|digits:14',
            'companion_passport_number' => 'required_if:has_companion,true|nullable|string|max:50',
            'companion_phone' => 'required_if:has_companion,true|nullable|regex:/^01[0-2,5][0-9]{8}$/',

            // الوضع الاقتصادي
            'has_fixed_income' => 'required|boolean',
            'income_value' => 'required_if:has_fixed_income,true|nullable|numeric|min:0',
            'income_source' => 'required_if:has_fixed_income,true|nullable|string|max:191',
            'notes' => 'nullable|string|max:1000',

            // المرفقات الرئيسية (المريض + المرافق + عامة)
            'id_attachment' => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
            'passport_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'entry_stamp_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'medical_report_attachment' => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
            'rent_contract_attachment' => 'required_if:housing_status,rent|mimes:jpg,jpeg,png,pdf|max:1024',

            'companion_id_attachment' => 'required_if:has_companion,true|mimes:jpg,jpeg,png,pdf|max:1024',
            'companion_passport_attachment' => 'required_if:has_companion,true|mimes:jpg,jpeg,png,pdf|max:1024',
            'companion_entry_stamp_attachment' => 'required_if:has_companion,true|mimes:jpg,jpeg,png,pdf|max:1024',

            // أفراد الأسرة المرافقين (غير المرافق الرئيسي)
            'family_members' => 'required|array|min:1',
            'family_members.*.name' => [
                'required',
                'string',
                'max:191',
                'regex:/^(\S+\s+){3,}\S+$/'
            ],
            'family_members.*.national_id' => 'nullable|digits:14',
            'family_members.*.passport_number' => 'nullable|string|max:50',
            'family_members.*.phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'family_members.*.birth_date' => 'required|date',
            'family_members.*.age' => 'required|integer|min:0',
            'family_members.*.relation' => 'required|string|max:100',
            'family_members.*.education_level' => 'nullable|string|max:191',
            'family_members.*.health_status' => 'nullable|string|max:500',
            'family_members.*.disease_type' => 'nullable|string|max:191',
            'family_members.*.has_disability' => 'required|boolean',
            'family_members.*.disability_type' => 'required_if:family_members.*.has_disability,true|nullable|in:physical,mental,both',
            'family_members.*.disability_description' => 'required_if:family_members.*.has_disability,true|nullable|string|max:500',

            // مرفقات أفراد الأسرة
            'family_members.*.passport_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'family_members.*.entry_stamp_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'family_members.*.medical_reports_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
        ]);

        try {
            DB::beginTransaction();

            // رفع المرفقات الرئيسية (المريض)
            $data['id_attachment'] = $this->uploadFile($request->file('id_attachment'), 'sick_cases');
            $data['passport_attachment'] = $request->hasFile('passport_attachment') 
                ? $this->uploadFile($request->file('passport_attachment'), 'sick_cases') 
                : null;
            $data['entry_stamp_attachment'] = $request->hasFile('entry_stamp_attachment') 
                ? $this->uploadFile($request->file('entry_stamp_attachment'), 'sick_cases') 
                : null;
            $data['medical_report_attachment'] = $this->uploadFile($request->file('medical_report_attachment'), 'sick_cases');
            $data['rent_contract_attachment'] = $request->hasFile('rent_contract_attachment') 
                ? $this->uploadFile($request->file('rent_contract_attachment'), 'sick_cases') 
                : null;

            // مرفقات المرافق (إذا موجود)
            $data['companion_id_attachment'] = $request->hasFile('companion_id_attachment') 
                ? $this->uploadFile($request->file('companion_id_attachment'), 'sick_cases') 
                : null;
            $data['companion_passport_attachment'] = $request->hasFile('companion_passport_attachment') 
                ? $this->uploadFile($request->file('companion_passport_attachment'), 'sick_cases') 
                : null;
            $data['companion_entry_stamp_attachment'] = $request->hasFile('companion_entry_stamp_attachment') 
                ? $this->uploadFile($request->file('companion_entry_stamp_attachment'), 'sick_cases') 
                : null;

            $data['user_id'] = Auth::id();

            // إنشاء حالة المريض
            $sick = PatientCase::create(collect($data)->except(['family_members'])->toArray());

            // إنشاء أفراد الأسرة المرافقين مع رفع مرفقاتهم
            if ($request->has('family_members') && is_array($request->family_members)) {
                foreach ($request->family_members as $i => $memberData) {
                    $memberData['sick_case_id'] = $sick->id;

                    // رفع مرفقات العضو
                    if ($request->hasFile("family_members.$i.passport_attachment")) {
                        $memberData['passport_attachment'] = $this->uploadMemberFile(
                            $request->file("family_members.$i.passport_attachment"), 
                            'sick_cases/members'
                        );
                    }
                    if ($request->hasFile("family_members.$i.entry_stamp_attachment")) {
                        $memberData['entry_stamp_attachment'] = $this->uploadMemberFile(
                            $request->file("family_members.$i.entry_stamp_attachment"), 
                            'sick_cases/members'
                        );
                    }
                    if ($request->hasFile("family_members.$i.medical_reports_attachment")) {
                        $memberData['medical_reports_attachment'] = $this->uploadMemberFile(
                            $request->file("family_members.$i.medical_reports_attachment"), 
                            'sick_cases/members'
                        );
                    }

                    PatientFamilyMember::create($memberData);
                }
            }

            // توليد QR Code للحالة
            $this->generateQr($sick->id);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إضافة حالة مرضى بنجاح',
                'case_id' => $sick->id,
                'qr_code' => $sick->qr_code
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء حفظ البيانات',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    protected function uploadFile($file, $folder = 'patient_cases')
    {
        if (!$file) return null;

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("humanitarian_cases/{$folder}", $fileName, 'public_upload');

        return Storage::disk('public_upload')->url($path);
    }

    protected function uploadMemberFile($file, $subfolder = 'patient_cases/members')
    {
        if (!$file) return null;

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("humanitarian_cases/{$subfolder}", $fileName, 'public_upload');

        return Storage::disk('public_upload')->url($path);
    }

    private function generateQr(int $id)
    {
        $case = PatientCase::findOrFail($id);

        $url = route('admin.humanitarian.cases.patients.show', $case->id);

        $fileName = 'sick_case_' . $case->id . '.png';
        $diskPath = "humanitarian_cases/patient_cases/qrcodes/{$fileName}";

        $qrImage = QrCode::format('png')->size(300)->generate($url);

        Storage::disk('public_upload')->put($diskPath, $qrImage);

        $publicUrl = Storage::disk('public_upload')->url($diskPath);

        $case->update(['qr_code' => $publicUrl]);
    }
}
