<?php

namespace App\Http\Controllers;

use App\Models\AmputationDetailCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AmputationDetailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
    |--------------------------------------------------------------------------
    | عرض الصفحة
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $user = Auth::user()->load([
            'telegramUser.category',
            'telegramUser.amputationDetailCase'
        ]);

        if (!$user->telegramUser) {
            abort(403, 'لا يوجد بيانات تيليجرام');
        }

        $telegramUser = $user->telegramUser;

        $amputationProfileId = 2;

        $hasAmputationCategory = $telegramUser->categories()
            ->where('case_profile_id', $amputationProfileId)
            ->exists();

        if (!$hasAmputationCategory) {
            abort(403, 'هذه الصفحة مخصصة لحالات البتر فقط');
        }

        $amputationDetailCase = $telegramUser->amputationDetailCase;

        $viewData = [
            'telegramUser' => $telegramUser,
            'amputationCase' => $amputationDetailCase,
        ];

        if ($amputationDetailCase && $amputationDetailCase->status === 'pending') {
            return view('users.amputation.show', $viewData);
        }

        return view('users.amputation.create', $viewData);
    }

    /*
    |--------------------------------------------------------------------------
    | حفظ البيانات
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->telegramUser) {
            abort(403);
        }

        $telegram = $user->telegramUser;

        $request->validate([

            'birth_date' => 'required|date',
            'age' => 'required|integer',
            'gender' => 'required|in:male,female',
            'housing_status' => 'required|string',
            'marital_status' => 'required|string',

            'entry_date' => 'required|date',
            'entry_method' => 'required|string',

            'amputation_date' => 'required|date',
            'amputation_type' => 'required|string',
            'injury_reason' => 'required|string',

            'needs_prosthetic' => 'required|in:0,1',

            'received_help' => 'required|in:0,1',
            'help_type' => 'nullable|string',

            'health_description' => 'required|string',
            'has_income' => 'required|in:0,1',

            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',

            // ملفات
            'person_image' => 'required|image|max:10240',
            'amputation_image' => 'required|image|max:10240',

            'guardian_id' => 'required|file|mimes:pdf,jpg,png,jpeg|max:10240',
            'guardian_passport' => 'required|file|mimes:pdf,jpg,png,jpeg|max:10240',
            'entry_stamp' => 'required|file|mimes:pdf,jpg,png,jpeg|max:10240',
            'medical_reports' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:10240',
            'rent_contract' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:10240',
            // الجديد
            'rent_value' => 'nullable|numeric',

            'needs_companion' => 'required|in:0,1',

            'companion_full_name' => 'required_if:needs_companion,1|string|nullable',
            'companion_national_id' => 'required_if:needs_companion,1|string|nullable',
            'companion_birth_date' => 'required_if:needs_companion,1|date|nullable',
            'companion_relationship' => 'required_if:needs_companion,1|string|nullable',

        ]);

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | حفظ الحالة
            |--------------------------------------------------------------------------
            */

            $case = AmputationDetailCase::firstOrNew([
                'telegram_user_id' => $telegram->id
            ]);

            $case->fill([
                'birth_date' => $request->birth_date,
                'age' => $request->age,
                'gender' => $request->gender,
                'housing_status' => $request->housing_status,
                'marital_status' => $request->marital_status,
                'entry_date' => $request->entry_date,
                'entry_method' => $request->entry_method,
                'amputation_date' => $request->amputation_date,
                'amputation_type' => $request->amputation_type,
                'injury_reason' => $request->injury_reason,
                'needs_prosthetic' => $request->needs_prosthetic,
                'received_help' => $request->received_help,
                'help_type' => $request->help_type,
                'health_description' => $request->health_description,
                'has_income' => $request->has_income,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,

                // الجديد
                'rent_value' => $request->rent_value,
                'needs_companion' => $request->needs_companion,

                'companion_full_name' => $request->companion_full_name,
                'companion_national_id' => $request->companion_national_id,
                'companion_birth_date' => $request->companion_birth_date,
                'companion_relationship' => $request->companion_relationship,
            ]);

            if ($request->needs_companion == 0) {
                $case->companion_full_name = null;
                $case->companion_national_id = null;
                $case->companion_birth_date = null;
                $case->companion_relationship = null;
            }
            
            $this->handleFiles($request, $case);

            // بيانات من التليجرام
            $case->full_name = $telegram->full_name;
            $case->national_id = $telegram->national_id;

            $case->status = 'pending';

            $case->save();

            DB::commit();

            return back()->with('success', 'تم حفظ البيانات بنجاح');

        } catch (\Exception $e) {

            DB::rollBack();

            dd($e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | رفع الملفات
    |--------------------------------------------------------------------------
    */
    protected function handleFiles(Request $request, $case)
    {
        $files = [
            'person_image' => 'person',
            'amputation_image' => 'amputation',
            'guardian_id' => 'documents',
            'guardian_passport' => 'passports',
            'entry_stamp' => 'stamps',
            'medical_reports' => 'medical',
            'rent_contract' => 'rent',
        ];

        foreach ($files as $field => $folder) {
            if ($request->hasFile($field)) {
                $case->$field = $this->uploadFile(
                    $request->file($field),
                    "amputations/$folder"
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | رفع الملفات (مشترك)
    |--------------------------------------------------------------------------
    */
    protected function uploadFile($file, $folder)
    {
        $fileName = Str::random(30) . '.' . $file->getClientOriginalExtension();

        $destination = public_path("uploads/amputation/$folder");

        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $fileName);

        return "uploads/amputation/$folder/" . $fileName;
    }
}