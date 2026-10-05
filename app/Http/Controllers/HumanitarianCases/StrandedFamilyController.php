<?php

namespace App\Http\Controllers\HumanitarianCases;

use App\Http\Controllers\Controller;
use App\Models\StrandedFamily;
use App\Models\StrandedFamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class StrandedFamilyController extends Controller
{
    
    public function __construct()
    {
        $this->middleware("auth");
    }

    public function create()
    {
        return view('humanitarian-cases.stranded.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // بيانات رب الأسرة (الأب - العالق)
            'head_full_name' => [
                'required',
                'string',
                'max:191',
                'regex:/^(\S+\s+){3,}\S+$/'  // اسم رباعي على الأقل
            ],
            'head_national_id' => 'required|digits:14|unique:stranded_families,head_national_id',
            'head_main_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'head_additional_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'head_passport_number' => 'nullable|string|max:50',
            'head_birth_date' => 'required|date',
            'head_age' => 'required|integer|min:18',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'detailed_address' => 'required|string|max:500',
            'location' => 'nullable|string', // رابط Google Maps
            'housing_status' => 'required|in:rent,own,hosted',
            'rent_value' => 'required_if:housing_status,rent|nullable|numeric|min:0',
            'social_status' => 'required|string|max:191',
            'head_health_status' => 'required|string|max:500',
            'head_is_employed' => 'required|boolean',
            'head_job' => 'required_if:head_is_employed,true|nullable|string|max:191',
            'head_income' => 'required_if:head_is_employed,true|nullable|numeric|min:0',
            'entry_date_to_egypt' => 'required|date',
            'entry_method' => 'required|string|max:191',

            // بيانات الزوجة
            'wife_name' => 'nullable|string|max:191',
            'wife_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'wife_national_id' => 'nullable|digits:14',
            'economic_notes' => 'nullable|string|max:1000',

            // المرفقات الرئيسية (رب الأسرة + الزوجة + عامة)
            'head_id_attachment' => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
            'head_passport_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'head_entry_stamp_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'head_medical_reports_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'rent_contract_attachment' => 'required_if:housing_status,rent|mimes:jpg,jpeg,png,pdf|max:1024',

            'wife_id_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'wife_passport_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',
            'wife_entry_stamp_attachment' => 'nullable|mimes:jpg,jpeg,png,pdf|max:1024',

            // أفراد الأسرة (الأبناء وغيرهم عدا الزوجة)
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

            // رفع المرفقات الرئيسية
            $data['head_id_attachment'] = $this->uploadFile($request->file('head_id_attachment'), 'stranded_families');
            $data['head_passport_attachment'] = $request->hasFile('head_passport_attachment') 
                ? $this->uploadFile($request->file('head_passport_attachment'), 'stranded_families') 
                : null;
            $data['head_entry_stamp_attachment'] = $request->hasFile('head_entry_stamp_attachment') 
                ? $this->uploadFile($request->file('head_entry_stamp_attachment'), 'stranded_families') 
                : null;
            $data['head_medical_reports_attachment'] = $request->hasFile('head_medical_reports_attachment') 
                ? $this->uploadFile($request->file('head_medical_reports_attachment'), 'stranded_families') 
                : null;
            $data['rent_contract_attachment'] = $request->hasFile('rent_contract_attachment') 
                ? $this->uploadFile($request->file('rent_contract_attachment'), 'stranded_families') 
                : null;

            $data['wife_id_attachment'] = $request->hasFile('wife_id_attachment') 
                ? $this->uploadFile($request->file('wife_id_attachment'), 'stranded_families') 
                : null;
            $data['wife_passport_attachment'] = $request->hasFile('wife_passport_attachment') 
                ? $this->uploadFile($request->file('wife_passport_attachment'), 'stranded_families') 
                : null;
            $data['wife_entry_stamp_attachment'] = $request->hasFile('wife_entry_stamp_attachment') 
                ? $this->uploadFile($request->file('wife_entry_stamp_attachment'), 'stranded_families') 
                : null;

            $data['user_id'] = Auth::id();

            // إنشاء حالة العالقين
            $family = StrandedFamily::create(collect($data)->except(['members'])->toArray());

            // إنشاء أفراد الأسرة مع رفع مرفقاتهم
            if ($request->has('members') && is_array($request->members)) {
                foreach ($request->members as $i => $memberData) {
                    $memberData['family_id'] = $family->id;

                    // رفع مرفقات العضو
                    if ($request->hasFile("members.$i.passport_attachment")) {
                        $memberData['passport_attachment'] = $this->uploadMemberFile(
                            $request->file("members.$i.passport_attachment"), 
                            'stranded_families/members'
                        );
                    }
                    if ($request->hasFile("members.$i.entry_stamp_attachment")) {
                        $memberData['entry_stamp_attachment'] = $this->uploadMemberFile(
                            $request->file("members.$i.entry_stamp_attachment"), 
                            'stranded_families/members'
                        );
                    }
                    if ($request->hasFile("members.$i.medical_reports_attachment")) {
                        $memberData['medical_reports_attachment'] = $this->uploadMemberFile(
                            $request->file("members.$i.medical_reports_attachment"), 
                            'stranded_families/members'
                        );
                    }

                    StrandedFamilyMember::create($memberData);
                }
            }

            // توليد QR Code للحالة
            $this->generateQr($family->id);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إضافة حالة عالقين بنجاح',
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

    protected function uploadFile($file, $folder = 'stranded_families')
    {
        if (!$file) return null;

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("humanitarian_cases/{$folder}", $fileName, 'public_upload');

        return Storage::disk('public_upload')->url($path);
    }

    protected function uploadMemberFile($file, $subfolder = 'stranded_families/members')
    {
        if (!$file) return null;

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("humanitarian_cases/{$subfolder}", $fileName, 'public_upload');

        return Storage::disk('public_upload')->url($path);
    }

    private function generateQr(int $id)
    {
        $family = StrandedFamily::findOrFail($id);

        $url = route('admin.humanitarian.cases.stranded-families.show', $family->id);

        $fileName = 'stranded_family_case_' . $family->id . '.png';
        $diskPath = "humanitarian_cases/stranded_families/qrcodes/{$fileName}";

        $qrImage = QrCode::format('png')->size(300)->generate($url);

        Storage::disk('public_upload')->put($diskPath, $qrImage);

        $publicUrl = Storage::disk('public_upload')->url($diskPath);

        $family->update(['qr_code' => $publicUrl]);
    }
}
