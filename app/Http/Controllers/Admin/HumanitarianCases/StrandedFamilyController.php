<?php

namespace App\Http\Controllers\Admin\HumanitarianCases;

use App\Http\Controllers\Controller;
use App\Models\StrandedFamily;
use App\Models\StrandedFamilyMember;
use App\Models\StrandedFamilyMessage;
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
        $this->middleware(["role:superadmin|admin"]);
    }

    public function index()
    {
        return view('admin.index');
    }

    public function getItems()
    {
        return response()->json(StrandedFamily::with(['members'])->filter(request(['code', 'national_id', 'name', 'governorate', 'city', 'area', 'from', 'to', 'status']))->paginate(15));
    }

    public function getItem($id)
    {
        return response()->json(StrandedFamily::with(['members'])->findOrFail($id));
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
        $minimalAllowed = ['head_full_name', 'head_national_id', 'head_main_phone'];

        $isMinimalCase =
            count($filledFields) === count($minimalAllowed) &&
            !array_diff($filledFields, $minimalAllowed);

        if ($isMinimalCase) {
            $data = $request->validate([
                'head_national_id' => 'required|digits:14|unique:stranded_families,head_national_id',
                'head_main_phone'  => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
                'head_full_name'   => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            ]);

            $data['user_id'] = Auth::id();
            $data['is_reviewed'] = true;
            $strandedFamily = StrandedFamily::create($data);

            $message = StrandedFamilyMessage::create([
                'stranded_family_id' => $strandedFamily->id,
                'user_id'   => Auth::id(),
                'message'   => 'تم الاستفادة',
            ]);

            return response()->json([
                'case'    => $strandedFamily,
                'message' => $message,
            ], 201);
        }

        // Full validation for complete data
        $data = $request->validate([
            'head_full_name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'head_national_id' => 'required|digits:14|unique:stranded_families,head_national_id',
            'head_main_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'head_additional_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'head_passport_number' => 'nullable|string|max:50',
            'head_birth_date' => 'required|date',
            'head_age' => 'required|integer',
            'governorate' => 'required|string|max:191',
            'detailed_address' => 'required|string',
            'location' => 'nullable|string',
            'housing_status' => 'required|in:Rented,Owned,Hosted',
            'rent_value' => 'required_if:housing_status,Rented|numeric',
            'social_status' => 'nullable|string',
            'head_health_status' => 'nullable|string',

            'head_is_employed' => 'required|boolean',
            'head_job' => 'required_if:head_is_employed,true|string',
            'head_income' => 'required_if:head_is_employed,true|numeric',

            'entry_date_to_egypt' => 'nullable|date',
            'entry_method' => 'nullable|in:Hala Company,External Lists,Foreign Lists,Medical Referrals',

            'wife_name' => 'nullable|string|max:191',
            'wife_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'wife_national_id' => 'nullable|digits:14',

            'economic_notes' => 'nullable|text',

            // Attachments
            'head_id_attachment' => 'sometimes|file',
            'head_passport_attachment' => 'sometimes|file',
            'head_entry_stamp_attachment' => 'sometimes|file',
            'head_medical_reports_attachment' => 'sometimes|file',
            'rent_contract_attachment' => 'sometimes|file',
            'wife_id_attachment' => 'sometimes|file',
            'wife_passport_attachment' => 'sometimes|file',
            'wife_entry_stamp_attachment' => 'sometimes|file',

            // Family Members
            'members' => 'required|array|min:0',
            'members.*.name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'members.*.national_id' => 'nullable|digits:14',
            'members.*.phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'members.*.passport_number' => 'nullable|string|max:50',
            'members.*.birth_date' => 'required|date',
            'members.*.age' => 'required|integer|min:0',
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
                'head_id_attachment',
                'head_passport_attachment',
                'head_entry_stamp_attachment',
                'head_medical_reports_attachment',
                'rent_contract_attachment',
                'wife_id_attachment',
                'wife_passport_attachment',
                'wife_entry_stamp_attachment',
            ];

            foreach ($attachmentFields as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = $this->uploadFile($request->file($field), 'stranded_families');
                }
            }

            $data['is_reviewed'] = true;
            $strandedFamily = StrandedFamily::create($data);

            // Create family members
            if ($request->has('members') && is_array($request['members'])) {
                foreach ($request['members'] as $i => $memberData) {
                    $memberData['stranded_family_id'] = $strandedFamily->id;

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

                    StrandedFamilyMember::create($memberData);
                }
            }

            $this->generateQr($strandedFamily->id);
            DB::commit();
            return response()->json(['status' => 'success', 'stranded_family_id' => $strandedFamily->id], 201);
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
        $strandedFamily = StrandedFamily::findOrFail($id);

        $data = $request->validate([
            'head_full_name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'head_national_id' => 'required|digits:14|unique:stranded_families,head_national_id,' . $id,
            'head_main_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'head_additional_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'head_passport_number' => 'nullable|string|max:50',
            'head_birth_date' => 'required|date',
            'head_age' => 'required|integer',
            'governorate' => 'required|string|max:191',
            'detailed_address' => 'required|string',
            'location' => 'nullable|string',
            'housing_status' => 'required|in:Rented,Owned,Hosted',
            'rent_value' => 'required_if:housing_status,Rented|numeric',
            'social_status' => 'nullable|string',
            'head_health_status' => 'nullable|string',

            'head_is_employed' => 'required|boolean',
            'head_job' => 'required_if:head_is_employed,true|string',
            'head_income' => 'required_if:head_is_employed,true|numeric',

            'entry_date_to_egypt' => 'nullable|date',
            'entry_method' => 'nullable|in:Hala Company,External Lists,Foreign Lists,Medical Referrals',

            'wife_name' => 'nullable|string|max:191',
            'wife_phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'wife_national_id' => 'nullable|digits:14',

            'economic_notes' => 'nullable|text',

            // Attachments (allow updating)
            'head_id_attachment' => 'sometimes|file',
            'head_passport_attachment' => 'sometimes|file',
            'head_entry_stamp_attachment' => 'sometimes|file',
            'head_medical_reports_attachment' => 'sometimes|file',
            'rent_contract_attachment' => 'sometimes|file',
            'wife_id_attachment' => 'sometimes|file',
            'wife_passport_attachment' => 'sometimes|file',
            'wife_entry_stamp_attachment' => 'sometimes|file',

            // Family Members
            'members' => 'required|array|min:0',
            'members.*.id' => 'sometimes|exists:stranded_family_members,id', // For updating existing
            'members.*.name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'members.*.national_id' => 'nullable|digits:14',
            'members.*.phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'members.*.passport_number' => 'nullable|string|max:50',
            'members.*.birth_date' => 'required|date',
            'members.*.age' => 'required|integer|min:0',
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
                'head_id_attachment',
                'head_passport_attachment',
                'head_entry_stamp_attachment',
                'head_medical_reports_attachment',
                'rent_contract_attachment',
                'wife_id_attachment',
                'wife_passport_attachment',
                'wife_entry_stamp_attachment',
            ];

            foreach ($attachmentFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old file if exists
                    if ($strandedFamily->$field) {
                        $this->deleteFile($strandedFamily->$field);
                    }
                    $data[$field] = $this->uploadFile($request->file($field), 'stranded_families');
                }
            }

            $strandedFamily->update($data);

            // Sync family members: delete old, create/update new
            $existingMemberIds = $strandedFamily->members->pluck('id')->toArray();
            $newMemberIds = [];

            if ($request->has('members') && is_array($request['members'])) {
                foreach ($request['members'] as $i => $memberData) {
                    $memberData['stranded_family_id'] = $strandedFamily->id;

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
                                $existingMember = StrandedFamilyMember::find($memberData['id']);
                                if ($existingMember && $existingMember->$field) {
                                    $this->deleteFile($existingMember->$field);
                                }
                            }
                            $memberData[$field] = $this->uploadMemberFile($request->file("members.$i.$field"));
                        }
                    }

                    if (isset($memberData['id'])) {
                        $member = StrandedFamilyMember::findOrFail($memberData['id']);
                        $member->update($memberData);
                        $newMemberIds[] = $member->id;
                    } else {
                        $member = StrandedFamilyMember::create($memberData);
                        $newMemberIds[] = $member->id;
                    }
                }
            }

            $membersToDelete = array_diff($existingMemberIds, $newMemberIds);
            foreach ($membersToDelete as $deleteId) {
                $member = StrandedFamilyMember::find($deleteId);
                if ($member) {
                    $this->deleteFile($member->passport_attachment);
                    $this->deleteFile($member->entry_stamp_attachment);
                    $this->deleteFile($member->medical_reports_attachment);
                    $member->delete();
                }
            }

            $this->generateQr($strandedFamily->id);
            DB::commit();
            return response()->json(['status' => 'success', 'stranded_family_id' => $strandedFamily->id], 200);
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
            $strandedFamily = StrandedFamily::with('members')->findOrFail($id);

            $attachmentFields = [
                $strandedFamily->head_id_attachment,
                $strandedFamily->head_passport_attachment,
                $strandedFamily->head_entry_stamp_attachment,
                $strandedFamily->head_medical_reports_attachment,
                $strandedFamily->rent_contract_attachment,
                $strandedFamily->wife_id_attachment,
                $strandedFamily->wife_passport_attachment,
                $strandedFamily->wife_entry_stamp_attachment,
                $strandedFamily->qr_code,
            ];

            foreach ($attachmentFields as $path) {
                $this->deleteFile($path);
            }

            foreach ($strandedFamily->members as $member) {
                $this->deleteFile($member->passport_attachment);
                $this->deleteFile($member->entry_stamp_attachment);
                $this->deleteFile($member->medical_reports_attachment);
            }

            $strandedFamily->delete();
            StrandedFamilyMessage::where('stranded_family_id', $id)->delete();

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
        $path = $file->storeAs('humanitarian_cases/stranded_families/members', $fileName, 'public_upload');
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
        $strandedFamily = StrandedFamily::findOrFail($id);
        $url = route('admin.humanitarian.cases.stranded_families.show', $strandedFamily->id); // Adjust route as needed

        $fileName = 'stranded_family_' . $strandedFamily->id . '.png';
        $diskPath = "humanitarian_cases/stranded_families/qrcodes/{$fileName}";

        $qrImage = QrCode::format('png')->size(300)->generate($url);
        Storage::disk('public_upload')->put($diskPath, $qrImage);

        $publicUrl = Storage::disk('public_upload')->url($diskPath);
        $strandedFamily->update(['qr_code' => $publicUrl]);
    }

    // Additional methods like attended, benefited, etc., adapted
    public function attended($id)
    {
        try {
            DB::beginTransaction();
            $strandedFamily = StrandedFamily::findOrFail($id);
            $strandedFamily->update([
                'is_attended' => true,
            ]);

            $message = StrandedFamilyMessage::create([
                "stranded_family_id" => $strandedFamily->id,
                "user_id" => Auth::id(),
                "latitude" => request('latitude'),
                "longitude" => request('longitude'),
                "message" => request('message'),
            ]);

            DB::commit();
            return response()->json([
                'case' => $strandedFamily,
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
            $case = StrandedFamily::findOrFail($id);
            $case->update([
                'is_benefited' => true,
            ]);

            $message = StrandedFamilyMessage::create([
                "stranded_family_id" => $case['id'],
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
            $case = StrandedFamily::findOrFail($id);
            $case->update([
                'is_sent_for_attendance' => true,
            ]);

            $message = StrandedFamilyMessage::create([
                "stranded_family_id" => $case['id'],
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
            $strandedFamily = StrandedFamily::findOrFail($id);
            $validated['is_reviewed'] = true;
            $validated['researcher_name'] = Auth::user()->name; // Or id
            $strandedFamily->update($validated);

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
