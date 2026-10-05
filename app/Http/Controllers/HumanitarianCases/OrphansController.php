<?php

namespace App\Http\Controllers\HumanitarianCases;

use App\Http\Controllers\Controller;
use App\Models\Deceased;
use App\Models\Guardian;
use App\Models\Orphan;
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
    }

    public function create()
    {
        $user = Auth::user()->load('telegramUser');

        if (!$user->telegramUser) {
            abort(403, 'لا توجد بيانات تيليجرام مرتبطة بك');
        }

        $telegramUser = $user->telegramUser;

        return view('humanitarian-cases.orphans.create', compact('telegramUser'));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => [
                'required',
                'string',
                'max:191',
                'regex:/^(\S+\s+){3,}\S+$/'
            ],
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
            'rent_value' => 'required_if:housing_status,rent',

            'marital_status' => 'nullable|string',
            'family_count' => 'nullable|integer',
            'entry_date' => 'nullable|date',
            'entry_method' => 'nullable|string',
            'has_minors' => 'nullable|boolean',

            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',

            'image' => 'required',

            'guardian_id' => 'required',
            'guardian_passport' => 'required',
            'guardian_entry_stamp' => 'required',
            'medical_reports' => 'required',
            'rent_contract' => 'required',
            'death_certificate' => 'required',

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
            'orphans.*.name' => [
                'required',
                'string',
                'max:191',
                'regex:/^(\S+\s+){3,}\S+$/'
            ],
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

            'orphans.*.orphan_passport' => 'required',
            'orphans.*.orphan_entry_stamp' => 'required',
            'orphans.*.orphan_birth_certificate' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $data['guardian_id'] = $this->uploadFile($request->file('guardian_id'));
            $data['guardian_passport'] = $this->uploadFile($request->file('guardian_passport'));
            $data['guardian_entry_stamp'] = $this->uploadFile($request->file('guardian_entry_stamp'));
            $data['medical_reports'] = $this->uploadFile($request->file('medical_reports'));
            $data['rent_contract'] = $this->uploadFile($request->file('rent_contract'));
            $data['death_certificate'] = $this->uploadFile($request->file('death_certificate'));
            $data['image'] = $this->uploadFile($request->file('image'));

            $data['user_id'] = Auth::id();

            $guardian = Guardian::create(collect($data)->except(['deceased', 'orphans'])->toArray());
            if ($request->filled('deceased')) {
                $dec = $request->input('deceased');
                $dec['guardian_id'] = $guardian['id'];

                Deceased::create($dec);
            }

            if ($request->has('orphans') && is_array($request->orphans)) {
                foreach ($request['orphans'] as $i => $o) {
                    $o['guardian_id'] = $guardian['id'];
                    $o['orphan_passport'] = $this->uploadOrphanFile(
                        $request->file("orphans.$i.orphan_passport")
                    );
                    $o['orphan_entry_stamp'] = $this->uploadOrphanFile(
                        $request->file("orphans.$i.orphan_entry_stamp")
                    );
                    $o['orphan_birth_certificate'] = $this->uploadOrphanFile(
                        $request->file("orphans.$i.orphan_birth_certificate")
                    );

                    Orphan::create($o);
                }
            }

            $this->generateQr($guardian['id']);
            DB::commit();
            return response()->json(['status' => 'success', 'guardian_id' => $guardian['id']], 201);
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
}
