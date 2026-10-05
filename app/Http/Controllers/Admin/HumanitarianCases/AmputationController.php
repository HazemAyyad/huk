<?php

namespace App\Http\Controllers\Admin\HumanitarianCases;

use App\Http\Controllers\Controller;
use App\Models\AmputationCase;
use App\Models\AmputationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AmputationController extends Controller
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
        return response()->json(AmputationCase::filter(request(['code', 'national_id', 'name', 'governorate', 'city', 'area', 'from', 'to', 'status']))->paginate(15));
    }

    public function getItem($id)
    {
        return response()->json(AmputationCase::with(['city', 'area', 'governorate'])->findOrFail($id), 200);
    }

    public function store(Request $request)
    {
        // دالة لجمع الفيلدز اللي فيها قيمة
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
        $minimalAllowed = ['full_name', 'case_code', 'primary_phone', 'national_id'];

        // تحقق إن المدخلات هي الثلاثة فقط (بغض النظر عن الترتيب)
        $isMinimalCase =
            count($filledFields) === count($minimalAllowed) &&
            !array_diff($filledFields, $minimalAllowed);

        if ($isMinimalCase) {

            // التحقق من البيانات
            $data = $request->validate([
                'national_id' => 'required|digits:9|unique:amputation_cases,national_id',
                'primary_phone'       => ['required', 'regex:/^01[0-2,5][0-9]{8}$/'],
                'full_name'   => ['required', 'string', 'max:191', 'regex:/^(\S+\s+){3,}\S+$/'],
                'case_code' => [
                    'required',
                    'string',
                    'max:191',
                    'regex:/^B[0-9]+$/',
                    'unique:amputation_cases,case_code',
                ],
            ]);

            $data['user_id'] = Auth::id();
            $data['is_reviewed'] = true;
            $case = AmputationCase::create($data);

            $message = AmputationMessage::create([
                'amputation_id' => $case->id,
                'user_id'     => Auth::id(),
                'message'     => 'تم الاستفادة',
            ]);

            return response()->json([
                'case'    => $case,
                'message' => $message,
            ], 201);
        }

        // 3) غير كده → Validation كامل
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

            // الملفات
            'case_id' => 'sometimes|file',
            'case_passport' => 'sometimes|file',
            'case_entry_stamp' => 'sometimes|file',
            'medical_reports' => 'sometimes|file',
            'rent_contract' => 'sometimes|file',
        ]);

        try {
            DB::beginTransaction();

            // حفظ الملفات
            $data['case_id'] = $request->hasFile('case_id') ? $this->uploadFile($request['case_id']) : null;
            $data['case_passport'] = $request->hasFile('case_passport') ? $this->uploadFile($request['case_passport']) : null;
            $data['case_entry_stamp'] = $request->hasFile('case_entry_stamp') ? $this->uploadFile($request['case_entry_stamp']) : null;
            $data['medical_reports'] = $request->hasFile('medical_reports') ? $this->uploadFile($request['medical_reports']) : null;
            $data['rent_contract'] = $request->hasFile('rent_contract') ? $this->uploadFile($request['rent_contract']) : null;
            $data['user_id'] = Auth::id();

            $data['is_reviewed'] = true;
            $item = AmputationCase::create($data);
            $this->generateQr($item['id']);

            DB::commit();
            return response()->json(['status' => 'success'], 201);
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


    protected function uploadFile($file)
    {
        if (!$file) {
            return null;
        }
        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
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

    public function attended($id)
    {
        try {
            DB::beginTransaction();
            $case = AmputationCase::findOrFail($id);
            $case->update([
                'is_attended' => true,
            ]);

            $message = AmputationMessage::create([
                "solidarity_housing_id" => $case['id'],
                "user_id" => Auth::id(),
                "latitude" => request('latitude'),
                "longitude" => request('latitude'),
                "message" => request('message'),
            ]);

            DB::commit();
            return response()->json([
                'message' => $message,
                'case' => $case,
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
            $case = AmputationCase::findOrFail($id);
            $case->update([
                'is_benefited' => true,
            ]);

            $message = AmputationMessage::create([
                "solidarity_housing_id" => $case['id'],
                "user_id" => Auth::id(),
                "latitude" => request('latitude'),
                "longitude" => request('latitude'),
                "message" => request('message'),
            ]);

            DB::commit();
            return response()->json([
                'message' => $message,
                'case' => $case,
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
            $case = AmputationCase::findOrFail($id);
            $case->update([
                'is_sent_for_attendance' => true,
            ]);

            $message = AmputationMessage::create([
                "solidarity_housing_id" => $case['id'],
                "user_id" => Auth::id(),
                "latitude" => request('latitude'),
                "longitude" => request('latitude'),
                "message" => request('message'),
            ]);

            DB::commit();
            return response()->json([
                'message' => $message,
                'case' => $case,
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
            $case = AmputationCase::findOrFail($id);
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
            AmputationCase::create($data);

            DB::commit();
            return response()->json(['status' => 'success'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            AmputationCase::findOrFail($id)->delete();
            DB::commit();
            return response()->json(200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}