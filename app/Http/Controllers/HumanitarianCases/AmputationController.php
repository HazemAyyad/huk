<?php

namespace App\Http\Controllers\HumanitarianCases;

use App\Http\Controllers\Controller;
use App\Models\AmputationCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AmputationController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    public function create()
    {
        return view('humanitarian-cases.amputation.create');
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
            'national_id' => 'required|digits:9|unique:amputation_cases,national_id',
            'primary_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'secondary_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'birth_date' => 'required|date',
            'age' => 'required|integer',
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
            'needs_prosthetic' => 'required|boolean',
            'received_aid_from_haq' => 'required|boolean',
            'aid_type' => 'required|string',
            'health_description' => 'required|string',
            'has_companion' => 'required|boolean',
            'companion_name' => [
                'required',
                'string',
                'max:191',
                'regex:/^(\S+\s+){3,}\S+$/'
            ],
            'companion_national_id' => 'required|digits:9',
            'companion_phone' => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
            'family_members_count' => 'required|integer',
            'family_composition' => 'required|string',
            'has_fixed_income' => 'required|boolean',
            'economic_status' => 'required|string',

            'case_id' => 'required',
            'case_passport' => 'required',
            'case_entry_stamp' => 'required',
            'medical_reports' => 'required',
            'rent_contract' => 'required',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'image' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $data['case_id'] = $this->uploadFile($request['case_id']);
            $data['case_passport'] = $this->uploadFile($request['case_passport']);
            $data['case_entry_stamp'] = $this->uploadFile($request['case_entry_stamp']);
            $data['medical_reports'] = $this->uploadFile($request['medical_reports']);
            $data['rent_contract'] = $this->uploadFile($request['rent_contract']);
            $data['image'] = $this->uploadFile($request['image']);
            $data['user_id'] = Auth::id();
            $case = AmputationCase::create($data);

            $this->generateQr($case['id']);
            DB::commit();
            return response()->json($case, 201);
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

    protected function uploadFile($file)
    {
        if (!$file) {
            return null;
        }

        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();

        // تخزين الملف
        $path = $file->storeAs('humanitarian_cases/amputation', $fileName, 'public_upload');

        return Storage::disk('public_upload')->url($path);
    }

    private function generateQr(int $id)
    {
        $case = AmputationCase::findOrFail($id);
        $url = route('admin.humanitarian.cases.amputation.show', $case['id']);

        $fileName = 'case_' . $case['id'] . '.png';
        $diskPath = "humanitarian_cases/amputation/qrcodes/{$fileName}";

        $qrImage = QrCode::format('png')->size(300)->generate($url);
        Storage::disk('public_upload')->put($diskPath, $qrImage);

        $publicUrl = Storage::disk('public_upload')->url($diskPath);
        $case->update(['qr_code' => $publicUrl]);
    }
}
