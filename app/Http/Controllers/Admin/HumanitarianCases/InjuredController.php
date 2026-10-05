<?php

namespace App\Http\Controllers\Admin\HumanitarianCases;

use App\Http\Controllers\Controller;
use App\Models\InjuredCase;
use App\Models\InjuredCaseMessage;
use App\Models\InjuredFamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class InjuredController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
        $this->middleware(["role:superadmin|admin"]);
    }

    public function index()
    {
        return view('admin.index');
    }

    public function getItems()
    {
        return response()->json(InjuredCase::with(['familyMembers'])->filter(request(['code', 'national_id', 'name', 'governorate', 'from', 'to', 'status']))->paginate(15));
    }

    public function getItem($id)
    {
        return response()->json(InjuredCase::with(['familyMembers'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $getFilledFields = function ($array, $prefix = '') use (&$getFilledFields) {
            $filled = [];

            foreach ($array as $key => $value) {
                $fullKey = $prefix ? "$prefix.$key" : $key;

                if (is_array($value)) {
                    $filled = array_merge($filled, $getFilledFields($value, $fullKey));
                } elseif (!is_null($value) && $value !== '') {
                    $filled[] = $fullKey;
                }
            }

            return $filled;
        };

        $filledFields = $getFilledFields($request->all());
        $minimalAllowed = ['full_name', 'national_id', 'main_phone'];

        $isMinimalCase =
            count($filledFields) === count($minimalAllowed) &&
            !array_diff($filledFields, $minimalAllowed);

        if ($isMinimalCase) {
            $data = $request->validate([
                'national_id' => 'required|digits:14|unique:injured_cases,national_id',
                'main_phone'  => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
                'full_name'   => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            ]);

            $data['user_id'] = Auth::id();
            $data['is_reviewed'] = true;
            $injuredCase = InjuredCase::create($data);

            $message = InjuredCaseMessage::create([
                'injured_case_id' => $injuredCase->id,
                'user_id'   => Auth::id(),
                'message'   => 'تم الاستفادة',
            ]);

            return response()->json([
                'case'    => $injuredCase,
                'message' => $message,
            ], 201);
        }

        // Full validation for complete data
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'national_id' => 'required|digits:14|unique:injured_cases,national_id',
            'main_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'additional_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'passport_number' => 'nullable|string|max:50',
            'birth_date' => 'required|date',
            'age' => 'required|integer',
            'governorate' => 'required|string|max:191',
            'detailed_address' => 'required|string',
            'location' => 'nullable|string',
            'housing_status' => 'required|in:Rented,Owned,Hosted',
            'rent_value' => 'required_if:housing_status,Rented|numeric',
            'social_status' => 'nullable|string',
            'entry_date_to_egypt' => 'nullable|date',
            'entry_method' => 'nullable|in:Hala Company,External Lists,Foreign Lists,Medical Referrals',

            // بيانات الإصابة
            'injury_date' => 'nullable|date',
            'injury_location' => 'nullable|string',
            'injury_caused_disability' => 'required|boolean',
            'disability_type' => 'required_if:injury_caused_disability,true|in:physical,mental,both',
            'received_treatment_from_haq' => 'required|boolean',
            'treatment_type' => 'required_if:received_treatment_from_haq,true|string',
            'health_description' => 'nullable|text',

            // المرافق
            'has_companion' => 'required|boolean',
            'companion_name' => 'required_if:has_companion,true|string|max:191',
            'companion_national_id' => 'required_if:has_companion,true|digits:14',
            'companion_passport_number' => 'nullable|string|max:50',
            'companion_phone' => 'required_if:has_companion,true|regex:/^01[0-2,5][0-9]{8}$/',

            // الوضع الاقتصادي
            'has_fixed_income' => 'required|boolean',
            'income_value' => 'required_if:has_fixed_income,true|numeric',
            'income_source' => 'required_if:has_fixed_income,true|string',

            'notes' => 'nullable|text',

            // Attachments
            'id_attachment' => 'sometimes|file',
            'passport_attachment' => 'sometimes|file',
            'entry_stamp_attachment' => 'sometimes|file',
            'medical_report_attachment' => 'sometimes|file',
            'rent_contract_attachment' => 'sometimes|file',
            'companion_id_attachment' => 'sometimes|file',
            'companion_passport_attachment' => 'sometimes|file',
            'companion_entry_stamp_attachment' => 'sometimes|file',

            // Family Members (أفراد الأسرة المرافقين غير المرافق الرئيسي)
            'family_members' => 'required|array|min:0',
            'family_members.*.name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'family_members.*.national_id' => 'nullable|digits:14',
            'family_members.*.passport_number' => 'nullable|string|max:50',
            'family_members.*.phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'family_members.*.birth_date' => 'required|date',
            'family_members.*.age' => 'required|integer|min:0',
            'family_members.*.relation' => 'required|string|max:191',
            'family_members.*.education_level' => 'nullable|string|max:191',
            'family_members.*.health_status' => 'nullable|string',
            'family_members.*.disease_type' => 'nullable|string',
            'family_members.*.has_disability' => 'required|boolean',
            'family_members.*.disability_type' => 'required_if:family_members.*.has_disability,true|in:physical,mental,both',
            'family_members.*.disability_description' => 'required_if:family_members.*.has_disability,true|string',

            'family_members.*.passport_attachment' => 'sometimes|file',
            'family_members.*.entry_stamp_attachment' => 'sometimes|file',
            'family_members.*.medical_reports_attachment' => 'sometimes|file',
        ]);

        try {
            DB::beginTransaction();

            // Upload main case attachments
            $attachmentFields = [
                'id_attachment',
                'passport_attachment',
                'entry_stamp_attachment',
                'medical_report_attachment',
                'rent_contract_attachment',
                'companion_id_attachment',
                'companion_passport_attachment',
                'companion_entry_stamp_attachment',
            ];

            foreach ($attachmentFields as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = $this->uploadFile($request->file($field), 'injured_cases');
                }
            }

            $data['is_reviewed'] = true;
            $injuredCase = InjuredCase::create($data);

            // Create family members
            if ($request->has('family_members') && is_array($request['family_members'])) {
                foreach ($request['family_members'] as $i => $memberData) {
                    $memberData['injured_case_id'] = $injuredCase->id;

                    $memberAttachmentFields = [
                        'passport_attachment',
                        'entry_stamp_attachment',
                        'medical_reports_attachment',
                    ];

                    foreach ($memberAttachmentFields as $field) {
                        if ($request->hasFile("family_members.$i.$field")) {
                            $memberData[$field] = $this->uploadMemberFile($request->file("family_members.$i.$field"));
                        }
                    }

                    InjuredFamilyMember::create($memberData);
                }
            }

            $this->generateQr($injuredCase->id);
            DB::commit();
            return response()->json(['status' => 'success', 'injured_case_id' => $injuredCase->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $injuredCase = InjuredCase::findOrFail($id);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'national_id' => 'required|digits:14|unique:injured_cases,national_id,' . $id,
            'main_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'additional_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'passport_number' => 'nullable|string|max:50',
            'birth_date' => 'required|date',
            'age' => 'required|integer',
            'governorate' => 'required|string|max:191',
            'detailed_address' => 'required|string',
            'location' => 'nullable|string',
            'housing_status' => 'required|in:Rented,Owned,Hosted',
            'rent_value' => 'required_if:housing_status,Rented|numeric',
            'social_status' => 'nullable|string',
            'entry_date_to_egypt' => 'nullable|date',
            'entry_method' => 'nullable|in:Hala Company,External Lists,Foreign Lists,Medical Referrals',

            'injury_date' => 'nullable|date',
            'injury_location' => 'nullable|string',
            'injury_caused_disability' => 'required|boolean',
            'disability_type' => 'required_if:injury_caused_disability,true|in:physical,mental,both',
            'received_treatment_from_haq' => 'required|boolean',
            'treatment_type' => 'required_if:received_treatment_from_haq,true|string',
            'health_description' => 'nullable|text',

            'has_companion' => 'required|boolean',
            'companion_name' => 'required_if:has_companion,true|string|max:191',
            'companion_national_id' => 'required_if:has_companion,true|digits:14',
            'companion_passport_number' => 'nullable|string|max:50',
            'companion_phone' => 'required_if:has_companion,true|regex:/^01[0-2,5][0-9]{8}$/',

            'has_fixed_income' => 'required|boolean',
            'income_value' => 'required_if:has_fixed_income,true|numeric',
            'income_source' => 'required_if:has_fixed_income,true|string',

            'notes' => 'nullable|text',

            // Attachments
            'id_attachment' => 'sometimes|file',
            'passport_attachment' => 'sometimes|file',
            'entry_stamp_attachment' => 'sometimes|file',
            'medical_report_attachment' => 'sometimes|file',
            'rent_contract_attachment' => 'sometimes|file',
            'companion_id_attachment' => 'sometimes|file',
            'companion_passport_attachment' => 'sometimes|file',
            'companion_entry_stamp_attachment' => 'sometimes|file',

            // Family Members
            'family_members' => 'required|array|min:0',
            'family_members.*.id' => 'sometimes|exists:injured_family_members,id',
            'family_members.*.name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'family_members.*.national_id' => 'nullable|digits:14',
            'family_members.*.passport_number' => 'nullable|string|max:50',
            'family_members.*.phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'family_members.*.birth_date' => 'required|date',
            'family_members.*.age' => 'required|integer|min:0',
            'family_members.*.relation' => 'required|string|max:191',
            'family_members.*.education_level' => 'nullable|string|max:191',
            'family_members.*.health_status' => 'nullable|string',
            'family_members.*.disease_type' => 'nullable|string',
            'family_members.*.has_disability' => 'required|boolean',
            'family_members.*.disability_type' => 'required_if:family_members.*.has_disability,true|in:physical,mental,both',
            'family_members.*.disability_description' => 'required_if:family_members.*.has_disability,true|string',

            'family_members.*.passport_attachment' => 'sometimes|file',
            'family_members.*.entry_stamp_attachment' => 'sometimes|file',
            'family_members.*.medical_reports_attachment' => 'sometimes|file',
        ]);

        try {
            DB::beginTransaction();

            // Update attachments (delete old if new uploaded)
            $attachmentFields = [
                'id_attachment',
                'passport_attachment',
                'entry_stamp_attachment',
                'medical_report_attachment',
                'rent_contract_attachment',
                'companion_id_attachment',
                'companion_passport_attachment',
                'companion_entry_stamp_attachment',
            ];

            foreach ($attachmentFields as $field) {
                if ($request->hasFile($field)) {
                    if ($injuredCase->$field) {
                        $this->deleteFile($injuredCase->$field);
                    }
                    $data[$field] = $this->uploadFile($request->file($field), 'injured_cases');
                }
            }

            $injuredCase->update($data);

            // Sync family members
            $existingMemberIds = $injuredCase->familyMembers->pluck('id')->toArray();
            $newMemberIds = [];

            if ($request->has('family_members') && is_array($request['family_members'])) {
                foreach ($request['family_members'] as $i => $memberData) {
                    $memberData['injured_case_id'] = $injuredCase->id;

                    $memberAttachmentFields = [
                        'passport_attachment',
                        'entry_stamp_attachment',
                        'medical_reports_attachment',
                    ];

                    foreach ($memberAttachmentFields as $field) {
                        if ($request->hasFile("family_members.$i.$field")) {
                            if (isset($memberData['id'])) {
                                $existingMember = InjuredFamilyMember::find($memberData['id']);
                                if ($existingMember && $existingMember->$field) {
                                    $this->deleteFile($existingMember->$field);
                                }
                            }
                            $memberData[$field] = $this->uploadMemberFile($request->file("family_members.$i.$field"));
                        }
                    }

                    if (isset($memberData['id'])) {
                        $member = InjuredFamilyMember::findOrFail($memberData['id']);
                        $member->update($memberData);
                        $newMemberIds[] = $member->id;
                    } else {
                        $member = InjuredFamilyMember::create($memberData);
                        $newMemberIds[] = $member->id;
                    }
                }
            }

            // Delete removed members
            $membersToDelete = array_diff($existingMemberIds, $newMemberIds);
            foreach ($membersToDelete as $deleteId) {
                $member = InjuredFamilyMember::find($deleteId);
                if ($member) {
                    $this->deleteFile($member->passport_attachment);
                    $this->deleteFile($member->entry_stamp_attachment);
                    $this->deleteFile($member->medical_reports_attachment);
                    $member->delete();
                }
            }

            $this->generateQr($injuredCase->id);
            DB::commit();
            return response()->json(['status' => 'success', 'injured_case_id' => $injuredCase->id], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $injuredCase = InjuredCase::with('familyMembers')->findOrFail($id);

            // Delete all attachments
            $attachmentFields = [
                $injuredCase->id_attachment,
                $injuredCase->passport_attachment,
                $injuredCase->entry_stamp_attachment,
                $injuredCase->medical_report_attachment,
                $injuredCase->rent_contract_attachment,
                $injuredCase->companion_id_attachment,
                $injuredCase->companion_passport_attachment,
                $injuredCase->companion_entry_stamp_attachment,
                $injuredCase->qr_code,
            ];

            foreach ($attachmentFields as $path) {
                $this->deleteFile($path);
            }

            // Delete family member attachments
            foreach ($injuredCase->familyMembers as $member) {
                $this->deleteFile($member->passport_attachment);
                $this->deleteFile($member->entry_stamp_attachment);
                $this->deleteFile($member->medical_reports_attachment);
            }

            // Delete case (cascades to members)
            $injuredCase->delete();

            InjuredCaseMessage::where('injured_case_id', $id)->delete();

            DB::commit();
            return response()->json(200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ], 500);
        }
    }

    protected function uploadFile($file, $subfolder)
    {
        if (!$file) return null;

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("humanitarian_cases/$subfolder", $fileName, 'public_upload');
        return Storage::disk('public_upload')->url($path);
    }

    protected function uploadMemberFile($file)
    {
        if (!$file) return null;

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('humanitarian_cases/injured_cases/members', $fileName, 'public_upload');
        return Storage::disk('public_upload')->url($path);
    }

    protected function deleteFile($url)
    {
        if ($url) {
            $path = parse_url($url, PHP_URL_PATH);
            $path = ltrim($path, '/');
            Storage::disk('public_upload')->delete($path);
        }
    }

    private function generateQr(int $id)
    {
        $injuredCase = InjuredCase::findOrFail($id);
        $url = route('admin.humanitarian.cases.injured.show', $injuredCase->id); // Adjust route name

        $fileName = 'injured_case_' . $injuredCase->id . '.png';
        $diskPath = "humanitarian_cases/injured_cases/qrcodes/{$fileName}";

        $qrImage = QrCode::format('png')->size(300)->generate($url);
        Storage::disk('public_upload')->put($diskPath, $qrImage);

        $publicUrl = Storage::disk('public_upload')->url($diskPath);
        $injuredCase->update(['qr_code' => $publicUrl]);
    }

    public function attended($id)
    {
        try {
            DB::beginTransaction();
            $case = InjuredCase::findOrFail($id);
            $case->update(['is_attended' => true]);

            $message = InjuredCaseMessage::create([
                'injured_case_id' => $case->id,
                'user_id' => Auth::id(),
                'latitude' => request('latitude'),
                'longitude' => request('longitude'),
                'message' => request('message'),
            ]);

            DB::commit();
            return response()->json(['case' => $case, 'message' => $message], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function benefited($id)
    {
        try {
            DB::beginTransaction();
            $case = InjuredCase::findOrFail($id);
            $case->update([
                'is_benefited' => true,
            ]);

            $message = InjuredCaseMessage::create([
                "injured_case_id" => $case['id'],
                "user_id" => Auth::id(),
                "latitude" => request('latitude'),
                "longitude" => request('latitude'),
                "message" => request('message'),
            ]);

            DB::commit();
            return response()->json([
                'case' => $case,
                'message' => $message,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ], 500);
        }
    }

    public function sentAttendance($id)
    {
        try {
            DB::beginTransaction();
            $case = InjuredCase::findOrFail($id);
            $case->update([
                'is_sent_for_attendance' => true,
            ]);

            $message = InjuredCaseMessage::create([
                "injured_case_id" => $case['id'],
                "user_id" => Auth::id(),
                "latitude" => request('latitude'),
                "longitude" => request('latitude'),
                "message" => request('message'),
            ]);

            DB::commit();
            return response()->json([
                'case' => $case,
                'message' => $message,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ], 500);
        }
    }

    public function updateResearcherOpinion(Request $request, $id)
    {
        $validated = $request->validate([
            'case_description' => 'required|string',
            'researcher_opinion' => 'required|string',
            'deserves_support' => 'required|boolean',
            'deserve_reason' => 'required|string',
        ]);

        try {
            DB::beginTransaction();
            $case = InjuredCase::findOrFail($id);
            $validated['is_reviewed'] = true;
            $validated['researcher_name'] = Auth::user()->name;
            $case->update($validated);

            DB::commit();
            return response()->json(200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
