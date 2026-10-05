<?php

namespace App\Http\Controllers\HumanitarianCases;

use App\Http\Controllers\Controller;
use App\Models\BreadwinnerFamilyMember;
use App\Models\FamilyBreadwinner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FamilyBreadwinnerController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    public function create()
    {
        return view('humanitarian-cases.families.create');
    }
    
    public function store(Request $request)
    {
        $data = $request->validate([
            // بيانات الأم (رب الأسرة بدون معيل)
            'wife_full_name' => [
                'required',
                'string',
                'max:191',
                'regex:/^(\S+\s+){3,}\S+$/'  // على الأقل 4 كلمات (اسم رباعي)
            ],
            'wife_national_id' => 'required|digits:14|unique:families_without_breadwinner,wife_national_id',
            'wife_main_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'wife_additional_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'wife_birth_date' => 'required|date',
            'wife_age' => 'required|integer|min:18',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'detailed_address' => 'required|string|max:500',
            'location' => 'nullable|string', // رابط Google Maps
            'housing_status' => 'required|in:rent,own,hosted',
            'rent_value' => 'required_if:housing_status,rent|nullable|numeric|min:0',
            'social_status' => 'required|in:widow,divorced,abandoned',
            'entry_date_to_egypt' => 'nullable|date',
            'entry_method' => 'nullable|string|max:191',
            'wife_health_status' => 'required|string|max:500',

            // الوضع الاقتصادي
            'has_fixed_income' => 'required|boolean',
            'income_value' => 'required_if:has_fixed_income,true|nullable|numeric|min:0',
            'income_source' => 'required_if:has_fixed_income,true|nullable|string|max:191',
            'notes' => 'nullable|string|max:1000',

            // بيانات الزوج (المعيل الغائب)
            'husband_name' => 'required|string|max:191',
            'husband_national_id' => 'nullable|digits:14',
            'husband_location' => 'required|string|max:500', // سبب الغياب أو مكانه

            // المرفقات الرئيسية
            'wife_id_attachment' => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
            'wife_passport_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'wife_entry_stamp_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'wife_medical_report_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'rent_contract_attachment' => 'required_if:housing_status,rent|mimes:jpg,jpeg,png,pdf|max:1024',
            'divorce_certificate_attachment' => 'required_if:social_status,divorced|mimes:jpg,jpeg,png,pdf|max:1024',
            'death_certificate_attachment' => 'required_if:social_status,widow|mimes:jpg,jpeg,png,pdf|max:1024',

            // أفراد الأسرة (الأبناء وغيرهم)
            'members' => 'required|array|min:1',
            'members.*.name' => [
                'required',
                'string',
                'max:191',
                'regex:/^(\S+\s+){3,}\S+$/'
            ],
            'members.*.national_id' => 'nullable|digits:14',
            'members.*.phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'members.*.passport_number' => 'nullable|string|max:50',
            'members.*.birth_date' => 'required|date',
            'members.*.age' => 'required|integer|min:0',
            'members.*.relation' => 'required|string|max:100',
            'members.*.education_level' => 'nullable|string|max:191',
            'members.*.health_status' => 'nullable|string|max:500',
            'members.*.disease_type' => 'nullable|string|max:191',
            'members.*.has_disability' => 'required|boolean',
            'members.*.disability_type' => 'required_if:members.*.has_disability,true|nullable|in:physical,mental,both',
            'members.*.disability_description' => 'required_if:members.*.has_disability,true|nullable|string|max:500',

            // مرفقات أفراد الأسرة
            'members.*.passport_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'members.*.entry_stamp_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'members.*.medical_reports_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
        ]);

        try {
            DB::beginTransaction();

            // رفع المرفقات الرئيسية (الأم)
            $data['wife_id_attachment'] = $this->uploadFile($request->file('wife_id_attachment'), 'families_without_breadwinner');
            $data['wife_passport_attachment'] = $request->hasFile('wife_passport_attachment') 
                ? $this->uploadFile($request->file('wife_passport_attachment'), 'families_without_breadwinner') 
                : null;
            $data['wife_entry_stamp_attachment'] = $request->hasFile('wife_entry_stamp_attachment') 
                ? $this->uploadFile($request->file('wife_entry_stamp_attachment'), 'families_without_breadwinner') 
                : null;
            $data['wife_medical_report_attachment'] = $request->hasFile('wife_medical_report_attachment') 
                ? $this->uploadFile($request->file('wife_medical_report_attachment'), 'families_without_breadwinner') 
                : null;
            $data['rent_contract_attachment'] = $request->hasFile('rent_contract_attachment') 
                ? $this->uploadFile($request->file('rent_contract_attachment'), 'families_without_breadwinner') 
                : null;
            $data['divorce_certificate_attachment'] = $request->hasFile('divorce_certificate_attachment') 
                ? $this->uploadFile($request->file('divorce_certificate_attachment'), 'families_without_breadwinner') 
                : null;
            $data['death_certificate_attachment'] = $request->hasFile('death_certificate_attachment') 
                ? $this->uploadFile($request->file('death_certificate_attachment'), 'families_without_breadwinner') 
                : null;

            $data['user_id'] = Auth::id();

            // إنشاء حالة الأسرة بدون معيل
            $family = FamilyBreadwinner::create(collect($data)->except(['members'])->toArray());

            // إنشاء أفراد الأسرة مع رفع مرفقاتهم
            if ($request->has('members') && is_array($request->members)) {
                foreach ($request->members as $i => $memberData) {
                    $memberData['family_id'] = $family->id;

                    // رفع مرفقات العضو
                    if ($request->hasFile("members.$i.passport_attachment")) {
                        $memberData['passport_attachment'] = $this->uploadMemberFile(
                            $request->file("members.$i.passport_attachment"), 
                            'families_without_breadwinner/members'
                        );
                    }
                    if ($request->hasFile("members.$i.entry_stamp_attachment")) {
                        $memberData['entry_stamp_attachment'] = $this->uploadMemberFile(
                            $request->file("members.$i.entry_stamp_attachment"), 
                            'families_without_breadwinner/members'
                        );
                    }
                    if ($request->hasFile("members.$i.medical_reports_attachment")) {
                        $memberData['medical_reports_attachment'] = $this->uploadMemberFile(
                            $request->file("members.$i.medical_reports_attachment"), 
                            'families_without_breadwinner/members'
                        );
                    }

                    BreadwinnerFamilyMember::create($memberData);
                }
            }

            // توليد QR Code للحالة
            $this->generateQr($family->id);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إضافة حالة أسرة بدون معيل بنجاح',
                'family_id' => $family->id,
                'qr_code' => $family->qr_code
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

    protected function uploadFile($file, $folder = 'family_breadwinners')
    {
        if (!$file) return null;

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("humanitarian_cases/{$folder}", $fileName, 'public_upload');

        return Storage::disk('public_upload')->url($path);
    }

    protected function uploadMemberFile($file, $subfolder = 'family_breadwinners/members')
    {
        if (!$file) return null;

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("humanitarian_cases/{$subfolder}", $fileName, 'public_upload');

        return Storage::disk('public_upload')->url($path);
    }

    private function generateQr(int $id)
    {
        $family = FamilyBreadwinner::findOrFail($id);

        $url = route('admin.humanitarian.cases.family-breadwinner.show', $family->id);

        $fileName = 'family_case_' . $family->id . '.png';
        $diskPath = "humanitarian_cases/family_breadwinners/qrcodes/{$fileName}";

        $qrImage = QrCode::format('png')->size(300)->generate($url);

        Storage::disk('public_upload')->put($diskPath, $qrImage);

        $publicUrl = Storage::disk('public_upload')->url($diskPath);

        $family->update(['qr_code' => $publicUrl]);
    }
}
