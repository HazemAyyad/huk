<?php

namespace App\Http\Controllers\Admin\HumanitarianCases;

use App\Http\Controllers\Controller;
use App\Models\BreadwinnerFamilyMember;
use App\Models\FamilyBreadwinner;
use App\Models\FamilyBreadwinnerMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class FamilyBreadwinnerController extends Controller
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
        return response()->json(FamilyBreadwinner::with(['members'])->filter(request(['code', 'national_id', 'name', 'governorate', 'city', 'area', 'from', 'to', 'status']))->paginate(15));
    }

    public function getItem($id)
    {
        return response()->json(FamilyBreadwinner::with(['members'])->findOrFail($id));
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
        $minimalAllowed = ['wife_full_name', 'wife_national_id', 'wife_main_phone'];

        $isMinimalCase =
            count($filledFields) === count($minimalAllowed) &&
            !array_diff($filledFields, $minimalAllowed);

        if ($isMinimalCase) {
            $data = $request->validate([
                'wife_national_id' => 'required|digits:14|unique:families,wife_national_id',
                'wife_main_phone'  => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
                'wife_full_name'   => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            ]);

            $data['user_id'] = Auth::id();
            $data['is_reviewed'] = true;
            $family = FamilyBreadwinner::create($data);

            $message = FamilyMessage::create([
                'family_id' => $family->id,
                'user_id'   => Auth::id(),
                'message'   => 'تم الاستفادة',
            ]);

            return response()->json([
                'case'    => $family,
                'message' => $message,
            ], 201);
        }

        // Full validation for complete data
        $data = $request->validate([
            'wife_full_name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'wife_national_id' => 'required|digits:14|unique:families,wife_national_id',
            'wife_main_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'wife_additional_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'wife_passport_number' => 'nullable|string|max:50',
            'wife_birth_date' => 'required|date',
            'wife_age' => 'required|integer',
            'governorate' => 'required|string|max:191',
            'detailed_address' => 'required|string',
            'location' => 'nullable|string',
            'housing_status' => 'required|in:Rented,Owned,Hosted',
            'rent_value' => 'required_if:housing_status,Rented|numeric',
            'social_status' => 'required|in:married_husband_abroad,divorced,widowed_non_martyr',
            'wife_health_status' => 'nullable|string',
            'entry_date_to_egypt' => 'nullable|date',
            'entry_method' => 'nullable|in:Hala Company,External Lists,Foreign Lists,Medical Referrals',

            'husband_name' => 'nullable|string|max:191',
            'husband_national_id' => 'nullable|digits:14',
            'husband_location' => 'nullable|string',

            'has_fixed_income' => 'required|boolean',
            'income_value' => 'required_if:has_fixed_income,true|numeric',
            'income_source' => 'required_if:has_fixed_income,true|string',

            'notes' => 'nullable|text',

            // Attachments
            'wife_id_attachment' => 'sometimes|file',
            'wife_passport_attachment' => 'sometimes|file',
            'wife_entry_stamp_attachment' => 'sometimes|file',
            'wife_medical_report_attachment' => 'sometimes|file',
            'rent_contract_attachment' => 'sometimes|file',
            'divorce_certificate_attachment' => 'sometimes|file',
            'death_certificate_attachment' => 'sometimes|file',

            // Family Members
            'members' => 'required|array|min:0',
            'members.*.name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'members.*.national_id' => 'nullable|digits:14',
            'members.*.birth_date' => 'required|date',
            'members.*.age' => 'required|integer|min:0',
            'members.*.passport_number' => 'nullable|string|max:50',
            'members.*.phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'members.*.relation' => 'required|string|max:191',
            'members.*.education_level' => 'nullable|string|max:191',
            'members.*.health_status' => 'nullable|string',
            'members.*.disease_type' => 'nullable|string',
            'members.*.has_disability' => 'required|boolean',
            'members.*.disability_type' => 'required_if:members.*.has_disability,true|in:physical,mental,both',
            'members.*.disability_description' => 'required_if:members.*.has_disability,true|string',

            'members.*.passport_attachment' => 'sometimes|file',
            'members.*.entry_stamp_attachment' => 'sometimes|file',
            'members.*.medical_reports_attachment' => 'sometimes|file',
        ]);

        try {
            DB::beginTransaction();

            // Upload family attachments
            $attachmentFields = [
                'wife_id_attachment',
                'wife_passport_attachment',
                'wife_entry_stamp_attachment',
                'wife_medical_report_attachment',
                'rent_contract_attachment',
                'divorce_certificate_attachment',
                'death_certificate_attachment',
            ];

            foreach ($attachmentFields as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = $this->uploadFile($request->file($field), 'family_breadwinners');
                }
            }

            $data['is_reviewed'] = true;
            $family = FamilyBreadwinner::create($data);

            // Create family members
            if ($request->has('members') && is_array($request['members'])) {
                foreach ($request['members'] as $i => $memberData) {
                    $memberData['family_id'] = $family->id;

                    // Upload member attachments
                    $memberAttachmentFields = [
                        'passport_attachment',
                        'entry_stamp_attachment',
                        'medical_reports_attachment',
                    ];

                    foreach ($memberAttachmentFields as $field) {
                        if ($request->hasFile("members.$i.$field")) {
                            $memberData[$field] = $this->uploadMemberFile($request->file("members.$i.$field"));
                        }
                    }

                    BreadwinnerFamilyMember::create($memberData);
                }
            }

            $this->generateQr($family->id);
            DB::commit();
            return response()->json(['status' => 'success', 'family_id' => $family->id], 201);
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
        $family = FamilyBreadwinner::findOrFail($id);

        $data = $request->validate([
            'wife_full_name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'wife_national_id' => 'required|digits:14|unique:families,wife_national_id,' . $id,
            'wife_main_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'wife_additional_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'wife_passport_number' => 'nullable|string|max:50',
            'wife_birth_date' => 'required|date',
            'wife_age' => 'required|integer',
            'governorate' => 'required|string|max:191',
            'detailed_address' => 'required|string',
            'location' => 'nullable|string',
            'housing_status' => 'required|in:Rented,Owned,Hosted',
            'rent_value' => 'required_if:housing_status,Rented|numeric',
            'social_status' => 'required|in:married_husband_abroad,divorced,widowed_non_martyr',
            'wife_health_status' => 'nullable|string',
            'entry_date_to_egypt' => 'nullable|date',
            'entry_method' => 'nullable|in:Hala Company,External Lists,Foreign Lists,Medical Referrals',

            'husband_name' => 'nullable|string|max:191',
            'husband_national_id' => 'nullable|digits:14',
            'husband_location' => 'nullable|string',

            'has_fixed_income' => 'required|boolean',
            'income_value' => 'required_if:has_fixed_income,true|numeric',
            'income_source' => 'required_if:has_fixed_income,true|string',

            'notes' => 'nullable|text',

            // Attachments (allow updating)
            'wife_id_attachment' => 'sometimes|file',
            'wife_passport_attachment' => 'sometimes|file',
            'wife_entry_stamp_attachment' => 'sometimes|file',
            'wife_medical_report_attachment' => 'sometimes|file',
            'rent_contract_attachment' => 'sometimes|file',
            'divorce_certificate_attachment' => 'sometimes|file',
            'death_certificate_attachment' => 'sometimes|file',

            // Family Members
            'members' => 'required|array|min:0',
            'members.*.id' => 'sometimes|exists:family_members,id', // For updating existing
            'members.*.name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'members.*.national_id' => 'nullable|digits:14',
            'members.*.birth_date' => 'required|date',
            'members.*.age' => 'required|integer|min:0',
            'members.*.passport_number' => 'nullable|string|max:50',
            'members.*.phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'members.*.relation' => 'required|string|max:191',
            'members.*.education_level' => 'nullable|string|max:191',
            'members.*.health_status' => 'nullable|string',
            'members.*.disease_type' => 'nullable|string',
            'members.*.has_disability' => 'required|boolean',
            'members.*.disability_type' => 'required_if:members.*.has_disability,true|in:physical,mental,both',
            'members.*.disability_description' => 'required_if:members.*.has_disability,true|string',

            'members.*.passport_attachment' => 'sometimes|file',
            'members.*.entry_stamp_attachment' => 'sometimes|file',
            'members.*.medical_reports_attachment' => 'sometimes|file',
        ]);

        try {
            DB::beginTransaction();

            // Update family attachments (delete old if new uploaded)
            $attachmentFields = [
                'wife_id_attachment',
                'wife_passport_attachment',
                'wife_entry_stamp_attachment',
                'wife_medical_report_attachment',
                'rent_contract_attachment',
                'divorce_certificate_attachment',
                'death_certificate_attachment',
            ];

            foreach ($attachmentFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old file if exists
                    if ($family->$field) {
                        $this->deleteFile($family->$field);
                    }
                    $data[$field] = $this->uploadFile($request->file($field), 'family_breadwinners');
                }
            }

            $family->update($data);

            // Sync family members: delete old, create/update new
            $existingMemberIds = $family->members->pluck('id')->toArray();
            $newMemberIds = [];

            if ($request->has('members') && is_array($request['members'])) {
                foreach ($request['members'] as $i => $memberData) {
                    $memberData['family_id'] = $family->id;

                    // Upload member attachments
                    $memberAttachmentFields = [
                        'passport_attachment',
                        'entry_stamp_attachment',
                        'medical_reports_attachment',
                    ];

                    foreach ($memberAttachmentFields as $field) {
                        if ($request->hasFile("members.$i.$field")) {
                            // If updating existing, delete old
                            if (isset($memberData['id'])) {
                                $existingMember = BreadwinnerFamilyMember::find($memberData['id']);
                                if ($existingMember && $existingMember->$field) {
                                    $this->deleteFile($existingMember->$field);
                                }
                            }
                            $memberData[$field] = $this->uploadMemberFile($request->file("members.$i.$field"));
                        }
                    }

                    if (isset($memberData['id'])) {
                        $member = BreadwinnerFamilyMember::findOrFail($memberData['id']);
                        $member->update($memberData);
                        $newMemberIds[] = $member->id;
                    } else {
                        $member = BreadwinnerFamilyMember::create($memberData);
                        $newMemberIds[] = $member->id;
                    }
                }
            }

            // Delete members not in new list
            $membersToDelete = array_diff($existingMemberIds, $newMemberIds);
            foreach ($membersToDelete as $deleteId) {
                $member = BreadwinnerFamilyMember::find($deleteId);
                if ($member) {
                    // Delete member attachments
                    $this->deleteFile($member->passport_attachment);
                    $this->deleteFile($member->entry_stamp_attachment);
                    $this->deleteFile($member->medical_reports_attachment);
                    $member->delete();
                }
            }

            $this->generateQr($family->id);
            DB::commit();
            return response()->json(['status' => 'success', 'family_id' => $family->id], 200);
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
            $family = FamilyBreadwinner::with('members')->findOrFail($id);

            // Collect and delete all attachments
            $attachmentFields = [
                $family->wife_id_attachment,
                $family->wife_passport_attachment,
                $family->wife_entry_stamp_attachment,
                $family->wife_medical_report_attachment,
                $family->rent_contract_attachment,
                $family->divorce_certificate_attachment,
                $family->death_certificate_attachment,
                $family->qr_code, // If QR is stored as file
            ];

            foreach ($attachmentFields as $path) {
                $this->deleteFile($path);
            }

            // Delete member attachments
            foreach ($family->members as $member) {
                $this->deleteFile($member->passport_attachment);
                $this->deleteFile($member->entry_stamp_attachment);
                $this->deleteFile($member->medical_reports_attachment);
            }

            // Delete family (cascades to members)
            $family->delete();

            // Delete related messages if any
            FamilyBreadwinnerMessage::where('family_id', $id)->delete();

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
        if (!$file) {
            return null;
        }

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("humanitarian_cases/$subfolder", $fileName, 'public_upload');
        return Storage::disk('public_upload')->url($path);
    }

    protected function uploadMemberFile($file)
    {
        if (!$file) {
            return null;
        }

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('humanitarian_cases/family_breadwinners/members', $fileName, 'public_upload');
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
        $family = FamilyBreadwinner::findOrFail($id);
        $url = route('admin.humanitarian.cases.family-breadwinners.show', $family->id); // Adjust route as needed

        $fileName = 'family_' . $family->id . '.png';
        $diskPath = "humanitarian_cases/family_breadwinners/qrcodes/{$fileName}";

        $qrImage = QrCode::format('png')->size(300)->generate($url);
        Storage::disk('public_upload')->put($diskPath, $qrImage);

        $publicUrl = Storage::disk('public_upload')->url($diskPath);
        $family->update(['qr_code' => $publicUrl]);
    }

    // Additional methods like attended, benefited, etc., adapted
    public function attended($id)
    {
        try {
            DB::beginTransaction();
            $family = FamilyBreadwinner::findOrFail($id);
            $family->update([
                'is_attended' => true,
            ]);

            $message = FamilyBreadwinnerMessage::create([
                "family_id" => $family->id,
                "user_id" => Auth::id(),
                "latitude" => request('latitude'),
                "longitude" => request('longitude'),
                "message" => request('message'),
            ]);

            DB::commit();
            return response()->json([
                'case' => $family,
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

    public function benefited($id)
    {
        try {
            DB::beginTransaction();
            $case = FamilyBreadwinner::findOrFail($id);
            $case->update([
                'is_benefited' => true,
            ]);

            $message = FamilyBreadwinnerMessage::create([
                "family_id" => $case['id'],
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
            $case = FamilyBreadwinner::findOrFail($id);
            $case->update([
                'is_sent_for_attendance' => true,
            ]);

            $message = FamilyBreadwinnerMessage::create([
                "family_id" => $case['id'],
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
            $family = FamilyBreadwinner::findOrFail($id);
            $validated['is_reviewed'] = true;
            $validated['researcher_name'] = Auth::user()->name; // Or id
            $family->update($validated);

            DB::commit();
            return response()->json(200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ], 500);
        }
    }
}
