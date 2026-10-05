<?php

namespace App\Http\Controllers\HumanitarianCases;

use App\Http\Controllers\Controller;
use App\Models\SolidarityAttachment;
use App\Models\SolidarityFamilyMember;
use App\Models\SolidarityHousing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class SolidarityHousingController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    public function create()
    {
        return view('humanitarian-cases.solidarity-housing.create');
    }

    public function store(Request $request)
    {
        $validation = $request->validate([
            'full_name' => [
                'required',
                'string',
                'max:191',
                'regex:/^(\S+\s+){3,}\S+$/'
            ],
            'national_id' => 'required|digits:9|unique:solidarity_housings,national_id',
            'primary_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'secondary_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'passport_number' => 'nullable|string|max:50',
            'birth_date' => 'required|date',
            'age' => 'required|integer|min:0|max:120',
            'gender' => 'required|in:male,female',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'address' => 'required|string',
            'housing_status' => 'required|string',
            'rent_value' => 'required_if:housing_status,rent',
            'marital_status' => 'required|string',
            'health_status' => 'required|string',
            'entry_date' => 'required|date',
            'entry_method' => 'required|string',

            'amputation_date' => 'required|date',
            'amputation_place' => 'required|string',
            'injury_cause' => 'required|string',
            'amputated_part' => 'required|string',
            'needs_prosthetic' => 'required|boolean',
            'received_aid_from_haq' => 'required|boolean',
            'aid_type' => 'required|string',
            'health_description' => 'required|string',

            'has_companion' => 'required|boolean',
            'companion_name' => 'required|string',
            'companion_national_id' => 'required|string',
            'companion_phone' => 'required|string',

            'family_members_count' => 'required|integer',
            'family_composition' => 'required|string',
            'has_fixed_income' => 'required|boolean',
            'monthly_income' => 'required|numeric',
            'income_source' => 'required|string',
            'economic_status' => 'required|string',

            'case_id' => 'required',
            'case_passport' => 'required',
            'case_entry_stamp' => 'required',
            'medical_reports' => 'required',
            'rent_contract' => 'required',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'image' => 'required',

            'family_members' => 'required|array|min:1',
            'family_members.*.name' => [
                'required',
                'string',
                'max:191',
                'regex:/^(\S+\s+){3,}\S+$/'
            ],
            'family_members.*.national_id' => 'required|digits:9',
            'family_members.*.passport_number' => 'required|string|max:50',
            'family_members.*.birth_date' => 'required|date',
            'family_members.*.age' => 'required|integer',
            'family_members.*.relation' => 'required|string',
            'family_members.*.education_level' => 'required|string',
            'family_members.*.health_status' => 'required|string',
            'family_members.*.disease_type' => 'sametimes|string',
            'family_members.*.has_disability' => 'sometimes|boolean',
            'family_members.*.disability_type' => 'sometimes|string',
            'family_members.*.disability_description' => 'sometimes|string',
        ]);

        try {
            DB::beginTransaction();
            $validation['case_id'] = $this->uploadFile($request['case_id']);
            $validation['case_passport'] = $this->uploadFile($request['case_passport']);
            $validation['case_entry_stamp'] = $this->uploadFile($request['case_entry_stamp']);
            $validation['medical_reports'] = $this->uploadFile($request['medical_reports']);
            $validation['rent_contract'] = $this->uploadFile($request['rent_contract']);
            $validation['image'] = $this->uploadFile($request['image']);
            $validation['user_id'] = Auth::id();

            $case = SolidarityHousing::create(collect($validation)->except(['family_members'])->toArray());
            if ($request->filled('family_members') && is_array($request->input('family_members'))) {
                foreach ($validation['family_members'] as $fm) {
                    $fm['solidarity_housing_id'] = $case['id'];
                    SolidarityFamilyMember::create($fm);
                }
            }

            $this->generateQr($case['id']);
            DB::commit();
            return response()->json(201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    protected function uploadFile($file)
    {
        if (!$file) {
            return null;
        }

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();

        // تخزين الملف
        $path = $file->storeAs('humanitarian_cases/solidarity_housing', $fileName, 'public_upload');

        return Storage::disk('public_upload')->url($path);
    }

    private function generateQr(int $id)
    {
        $case = SolidarityHousing::findOrFail($id);
        $url = route('admin.humanitarian.cases.solidarity-housing.show', $case['id']);

        $fileName = 'case_' . $case['id'] . '.png';
        $diskPath = "humanitarian_cases/solidarity_housing/qrcodes/{$fileName}";

        $qrImage = QrCode::format('png')->size(300)->generate($url);
        Storage::disk('public_upload')->put($diskPath, $qrImage);

        $publicUrl = Storage::disk('public_upload')->url($diskPath);
        $case->update(['qr_code' => $publicUrl]);
    }
}
