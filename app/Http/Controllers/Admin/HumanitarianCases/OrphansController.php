<?php

namespace App\Http\Controllers\Admin\HumanitarianCases;

use App\Http\Controllers\Controller;
use App\Models\Deceased;
use App\Models\Guardian;
use App\Models\GuardianMessage;
use App\Models\Orphan;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;


class OrphansController extends Controller
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
        return response()->json(Guardian::with(['deceased', 'orphans', 'city', 'area', 'governorate'])->filter(request(['code', 'national_id', 'name', 'governorate', 'city', 'area', 'from', 'to', 'status']))->paginate(15), 200);
    }

    public function getPrograms()
    {
        return response()->json(Program::all(['id', 'name']), 200);
    }

    public function getItem($id)
    {
        return response()->json(Guardian::with(['deceased', 'orphans', 'city', 'area', 'governorate'])->findOrFail($id), 200);
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
        $minimalAllowed = ['full_name', 'case_code', 'phone', 'national_id'];

        $isMinimalCase =
            count($filledFields) === count($minimalAllowed) &&
            !array_diff($filledFields, $minimalAllowed);

        if ($isMinimalCase) {
            $data = $request->validate([
                'national_id' => 'required|digits:9|unique:guardians,national_id',
                'phone'       => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
                'full_name'   => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
                'case_code' => [
                    'required',
                    'string',
                    'max:191',
                    'regex:/^A[0-9]+$/',
                    'unique:guardians,case_code',
                ],
            ]);

            $data['user_id'] = Auth::id();
            $data['is_reviewed'] = true;
            $guardian = Guardian::create($data);

            $message = GuardianMessage::create([
                'guardian_id' => $guardian->id,
                'user_id'     => Auth::id(),
                'message'     => 'تم الاستفادة',
            ]);

            return response()->json([
                'case'    => $guardian,
                'message' => $message,
            ], 201);
        }

        // ======== لو الريكوست FULL يكمل الفاليديشن العادي ========
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'national_id' => 'required|digits:9|unique:guardians,national_id',
            'phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'phone2' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'birth_date' => 'required|date',
            'age' => 'required|integer',
            'relation' => 'required|string',
            'gender' => 'required|in:male,female',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'address' => 'required|string',
            'housing_status' => 'required|in:rent,own,hosted',
            'rent_value' => 'required_if:housing_status,rent|numeric',

            'marital_status' => 'nullable|string',
            'family_count' => 'nullable|integer',
            'entry_date' => 'nullable|date',
            'entry_method' => 'nullable|string',

            'guardian_id' => 'sometimes|file',
            'guardian_passport' => 'sometimes|file',
            'guardian_entry_stamp' => 'sometimes|file',
            'medical_reports' => 'sometimes|file',
            'rent_contract' => 'sometimes|file',
            'death_certificate' => 'sometimes|file',

            'deceased.type' => 'required|in:father_orphan,both_orphan',

            'deceased.father_name' => [
                Rule::requiredIf(fn() => in_array($request->input('deceased.type'), ['father_orphan', 'both_orphan'])),
            ],
            'deceased.father_death_date' => [
                Rule::requiredIf(fn() => in_array($request->input('deceased.type'), ['father_orphan', 'both_orphan'])),
            ],
            'deceased.father_death_reason' => [
                Rule::requiredIf(fn() => in_array($request->input('deceased.type'), ['father_orphan', 'both_orphan'])),
            ],
            'deceased.father_job' => [
                Rule::requiredIf(fn() => in_array($request->input('deceased.type'), ['father_orphan', 'both_orphan'])),
            ],

            'deceased.mother_name'         => 'required_if:deceased.type,both_orphan',
            'deceased.mother_death_date'   => 'required_if:deceased.type,both_orphan',
            'deceased.mother_death_reason' => 'required_if:deceased.type,both_orphan',
            'deceased.mother_job'          => 'required_if:deceased.type,both_orphan',

            'deceased.income' => 'required|numeric|min:0',

            'orphans' => 'required|array|min:1',
            'orphans.*.name' => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
            'orphans.*.birth_date' => 'required|date',
            'orphans.*.age' => 'required|integer|min:0',
            'orphans.*.gender' => 'required|in:male,female',
            'orphans.*.lives_with_guardian' => 'required|boolean',
            'orphans.*.education_stage' => 'required|string|max:191',
            'orphans.*.study_type' => 'required|string',
            'orphans.*.has_chronic_disease' => 'required|boolean',
            'orphans.*.disease_description' => 'required_if:orphans.*.has_chronic_disease,true|string|max:255',
            'orphans.*.has_disability' => 'required|boolean',
            'orphans.*.disability_type' => 'required_if:orphans.*.has_disability,true',

            'orphans.*.orphan_passport' => 'sometimes|file',
            'orphans.*.orphan_entry_stamp' => 'sometimes|file',
            'orphans.*.orphan_birth_certificate' => 'sometimes|file',
        ]);

        try {
            DB::beginTransaction();

            $data['guardian_id'] = $request->hasFile('guardian_id')
                ? $this->uploadFile($request->file('guardian_id'))
                : null;

            $data['guardian_passport'] = $request->hasFile('guardian_passport')
                ? $this->uploadFile($request->file('guardian_passport'))
                : null;

            $data['guardian_entry_stamp'] = $request->hasFile('guardian_entry_stamp')
                ? $this->uploadFile($request->file('guardian_entry_stamp'))
                : null;

            $data['medical_reports'] = $request->hasFile('medical_reports')
                ? $this->uploadFile($request->file('medical_reports'))
                : null;

            $data['rent_contract'] = $request->hasFile('rent_contract')
                ? $this->uploadFile($request->file('rent_contract'))
                : null;

            $data['death_certificate'] = $request->hasFile('death_certificate')
                ? $this->uploadFile($request->file('death_certificate'))
                : null;

            $data['is_reviewed'] = true;
            $guardian = Guardian::create(collect($data)->except(['deceased', 'orphans'])->toArray());

            if ($request->filled('deceased')) {
                $dec = $request->input('deceased');
                $dec['guardian_id'] = $guardian['id'];
                Deceased::create($dec);
            }

            if ($request->has('orphans') && is_array($request['orphans'])) {
                foreach ($request['orphans'] as $i => $o) {
                    $o['guardian_id'] = $guardian['id'];
                    $o['orphan_passport'] = $request->hasFile("orphans.$i.orphan_passport")
                        ? $this->uploadOrphanFile($request->file("orphans.$i.orphan_passport"))
                        : null;

                    $o['orphan_entry_stamp'] = $request->hasFile("orphans.$i.orphan_entry_stamp")
                        ? $this->uploadOrphanFile($request->file("orphans.$i.orphan_entry_stamp"))
                        : null;

                    $o['orphan_birth_certificate'] = $request->hasFile("orphans.$i.orphan_birth_certificate")
                        ? $this->uploadOrphanFile($request->file("orphans.$i.orphan_birth_certificate"))
                        : null;


                    Orphan::create($o);
                }
            }

            $this->generateQr($guardian['id']);
            DB::commit();
            return response()->json(['status' => 'success', 'guardian_id' => $guardian->id], 201);
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

    protected function uploadOrphanFile($file)
    {
        if (!$file) {
            return null;
        }

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs(
            'humanitarian_cases/guardians/orphans',
            $fileName,
            'public_upload'
        );

        return Storage::disk('public_upload')->url($path);
    }

    protected function uploadFile($file)
    {
        if (!$file) {
            return null;
        }

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();

        // تخزين الملف
        $path = $file->storeAs('humanitarian_cases/guardians', $fileName, 'public_upload');

        return Storage::disk('public_upload')->url($path);
    }

    private function generateQr(int $id)
    {
        $case = Guardian::findOrFail($id);
        $url = route('admin.humanitarian.cases.orphans.show', $case->id);

        $fileName = 'case_' . $case->id . '.png';
        $diskPath = "humanitarian_cases/guardians/qrcodes/{$fileName}";

        $qrImage = QrCode::format('png')->size(300)->generate($url);
        Storage::disk('public_upload')->put($diskPath, $qrImage);

        $publicUrl = Storage::disk('public_upload')->url($diskPath);
        $case->update(['qr_code' => $publicUrl]);
    }

    public function attended($id)
    {
        try {
            DB::beginTransaction();
            $case = Guardian::findOrFail($id);
            $case->update([
                'is_attended' => true,
            ]);

            $message = GuardianMessage::create([
                "guardian_id" => $case['id'],
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

    public function benefited($id)
    {
        try {
            DB::beginTransaction();
            $case = Guardian::findOrFail($id);
            $case->update([
                'is_benefited' => true,
            ]);

            $message = GuardianMessage::create([
                "guardian_id" => $case['id'],
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
            $case = Guardian::findOrFail($id);
            $case->update([
                'is_sent_for_attendance' => true,
            ]);

            $message = GuardianMessage::create([
                "guardian_id" => $case['id'],
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
            'family_deserves' => 'required|in:yes,no',
            'family_reason' => 'required|string',
        ]);

        try {
            DB::beginTransaction();
            $case = Guardian::findOrFail($id);
            $validated['is_reviewed'] = true;
            $validated['researcher_id'] = Auth::id();
            $case->update($validated);

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

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:191',
            'national_id' => 'nullable|digits:9',
            'phone' => ['nullable', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'phone2' => 'nullable|string|max:20',
            'passport_number' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'relation' => 'nullable|string',
            'gender' => 'nullable|in:male,female',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'address' => 'nullable|string',
            'housing_status' => 'nullable|in:rent,own,hosted',
            'rent_value' => 'nullable|numeric',
            'marital_status' => 'nullable|string',
            'family_count' => 'nullable|integer',
            'entry_date' => 'nullable|date',
            'entry_method' => 'nullable|string',
            'has_minors' => 'nullable|boolean',
            // deceased and orphans validated below
        ]);

        try {
            DB::beginTransaction();
            $data['has_minors'] = false;
            $guardian = Guardian::create($data);

            if ($request->filled('deceased')) {
                $dec = $request->input('deceased');
                $dec['guardian_id'] = $guardian->id;
                Deceased::create($dec);
            }

            if ($request->filled('orphans') && is_array($request->input('orphans'))) {
                foreach ($request->input('orphans') as $o) {
                    $o['guardian_id'] = $guardian->id;
                    Orphan::create($o);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'guardian_id' => $guardian->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            Guardian::findOrFail($id)->delete();
            DB::commit();
            return response()->json(200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}