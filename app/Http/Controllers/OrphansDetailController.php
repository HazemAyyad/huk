<?php

namespace App\Http\Controllers;

use App\Models\GuardianDetail;
use App\Models\OrphanCase;
use App\Models\OrphanChild;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class OrphansDetailController extends Controller
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
        // تحميل العلاقات اللازمة بما فيها الفئة (للوصول لبروفايل الحالة)
        $user = Auth::user()->load([
            'telegramUser.category', // مهمة جداً للتحقق
            'telegramUser.guardianDetail',
            'telegramUser.orphanCase',
            'telegramUser.familyMembers.orphanChild'
        ]);

        // 1. التحقق من وجود بيانات تليجرام
        if (!$user->telegramUser) {
            abort(403, 'لا يوجد بيانات تيليجرام');
        }

        $telegramUser = $user->telegramUser;

        $orphanProfileId = 1; 
        $hasOrphanCategory = $telegramUser->categories()
            ->where('case_profile_id', $orphanProfileId)
            ->exists();

        if (!$hasOrphanCategory) {
            abort(403, 'عذراً، هذا القسم مخصص لحالات الأيتام فقط.');
        }

        $orphanCase = $telegramUser->orphanCase;

        // 3. منطق العرض (Pending vs Create)
        $viewData = [
            'telegramUser'  => $telegramUser,
            'guardianDetail'=> $telegramUser->guardianDetail,
            'orphanCase'    => $orphanCase,
            'familyMembers' => $telegramUser->familyMembers,
        ];

        if ($orphanCase && $orphanCase->status === 'pending') {
            return view('users.orphans.show', $viewData);
        }

        return view('users.orphans.create', $viewData);
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

        $telegramUser = $user->telegramUser;
        $telegramUserId = $telegramUser->id;

        $request->validate([

            // بيانات ولي الأمر
            'birth_date' => 'required|date',
            'age' => 'required|integer',
            'relationship_to_case' => 'required|string',
            'gender' => 'required|in:male,female',
            'housing_status' => 'required|string',
            'monthly_income' => 'required|numeric|min:0',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',

            'personal_photo' => 'required|image|max:10240',
            'id_document' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
            'passport' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
            'entry_stamp' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
            'medical_reports' => 'file|mimes:pdf,jpg,png|max:5120',
            'rent_contract' => 'file|mimes:pdf,jpg,png|max:10240',

            // حالة اليتيم
            'orphan_status' => 'required|in:father,mother,both',
            'death_certificate' => 'required|file|mimes:pdf,jpg,png|max:10240',

            // الأطفال
            'members' => 'required|array|min:1',
            'members.*.family_member_id' => 'required',
            'members.*.is_orphan' => 'required|boolean',

            'members.*.birth_date' => 'required_if:members.*.is_orphan,1|nullable|date',
            'members.*.age' => 'required_if:members.*.is_orphan,1|nullable|integer',
            'members.*.gender' => 'required_if:members.*.is_orphan,1|nullable|in:male,female',
            'members.*.personal_photo' => 'required_if:members.*.is_orphan,1|nullable|image|max:10240',
            'members.*.lives_with_guardian' => 'required_if:members.*.is_orphan,1|nullable|boolean',
            'members.*.education_level' => 'required_if:members.*.is_orphan,1|nullable|string',
            'members.*.study_type' => 'nullable|string',
            'members.*.has_chronic_disease' => 'required_if:members.*.is_orphan,1|nullable|boolean',
            'members.*.has_disability' => 'required_if:members.*.is_orphan,1|nullable|boolean',

        ], [

            /* ========================= */
            /* الرسائل العامة بالعربي */
            /* ========================= */

            'required' => 'حقل :attribute مطلوب',
            'integer' => 'حقل :attribute يجب أن يكون رقمًا صحيحًا',
            'numeric' => 'حقل :attribute يجب أن يكون رقمًا',
            'date' => 'حقل :attribute يجب أن يكون تاريخًا صحيحًا',
            'image' => 'حقل :attribute يجب أن يكون صورة',
            'mimes' => 'صيغة :attribute غير مدعومة',
            'max' => 'حجم :attribute أكبر من المسموح به',
            'min' => 'قيمة :attribute أقل من الحد الأدنى المسموح',
            'in' => 'قيمة :attribute غير صحيحة',
            'required_if' => 'حقل :attribute مطلوب',

        ], [

            /* ========================= */
            /* أسماء الحقول بالعربي */
            /* ========================= */

            'birth_date' => 'تاريخ الميلاد',
            'age' => 'العمر',
            'relationship_to_case' => 'صلة القرابة',
            'gender' => 'الجنس',
            'housing_status' => 'الحالة السكنية',
            'monthly_income' => 'الدخل الشهري',
            'latitude' => 'الموقع الجغرافي',
            'longitude' => 'الموقع الجغرافي',
            'personal_photo' => 'الصورة الشخصية لولي الأمر',
            'id_document' => 'الهوية',
            'passport' => 'جواز السفر',
            'entry_stamp' => 'ختم الدخول',
            'medical_reports' => 'التقارير الطبية',
            'rent_contract' => 'عقد الإيجار',
            'orphan_status' => 'حالة اليتيم',
            'death_certificate' => 'شهادة الوفاة',
            'members' => 'بيانات الأيتام',
            'members.*.birth_date' => 'تاريخ ميلاد اليتيم',
            'members.*.age' => 'عمر اليتيم',
            'members.*.gender' => 'جنس اليتيم',
            'members.*.personal_photo' => 'الصورة الشخصية لليتيم',
            'members.*.education_level' => 'المستوى التعليمي',
            'members.*.lives_with_guardian' => 'الإقامة مع ولي الأمر',
            'members.*.has_chronic_disease' => 'الأمراض المزمنة',
            'members.*.has_disability' => 'الإعاقة',

        ]);

        try {
            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ حفظ guardian_details
            |--------------------------------------------------------------------------
            */

            $guardian = GuardianDetail::firstOrNew([
                'telegram_user_id' => $telegramUserId
            ]);

            $guardian->fill([
                'birth_date' => $request->birth_date,
                'age' => $request->age,
                'relationship_to_case' => $request->relationship_to_case,
                'gender' => $request->gender,
                'housing_status' => $request->housing_status,
                'monthly_income' => $request->monthly_income,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            $this->handleGuardianFiles($request, $guardian);

            $guardian->save();

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ حفظ orphan_cases
            |--------------------------------------------------------------------------
            */

            $orphanCase = OrphanCase::firstOrNew([
                'telegram_user_id' => $telegramUserId
            ]);

            $orphanCase->orphan_status = $request->orphan_status;

            if ($request->hasFile('death_certificate')) {
                $orphanCase->death_certificate =
                    $this->uploadFile(
                        $request->file('death_certificate'),
                        'cases/orphans/deaths'
                    );
            }

            $orphanCase->save();

            /*
            |--------------------------------------------------------------------------
            | 3️⃣ حفظ بيانات الأطفال
            |--------------------------------------------------------------------------
            */

            foreach ($request->members as $index => $memberData) {

                // تحديث حالة المرافق
                $familyMember = FamilyMember::where('id', $memberData['family_member_id'])
                    ->where('telegram_user_id', $telegramUserId)
                    ->first();

                $familyMember->update([
                    'is_orphan' => $memberData['is_orphan'] ?? 0
                ]);

                // إذا لم يكن يتيم → لا نحفظ orphan_child
                if (!isset($memberData['is_orphan']) || $memberData['is_orphan'] == 0) {
                    continue;
                }

                // إذا كان يتيم → احفظ بياناته
                $orphanChild = OrphanChild::firstOrNew([
                    'family_member_id' => $familyMember->id
                ]);

                $orphanChild->fill([
                    'birth_date' => $memberData['birth_date'],
                    'age' => $memberData['age'],
                    'gender' => $memberData['gender'],
                    'lives_with_guardian' => $memberData['lives_with_guardian'],
                    'education_level' => $memberData['education_level'],
                    'study_type' => $memberData['study_type'] ?? null,
                    'has_chronic_disease' => $memberData['has_chronic_disease'],
                    'has_disability' => $memberData['has_disability'],
                ]);

                $this->handleChildFiles($request, $orphanChild, $index);

                $orphanChild->save();
            }


            $orphanCase->status = 'pending';
            $orphanCase->save();

            DB::commit();

            return back()->with('success', 'تم حفظ البيانات بنجاح');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء الحفظ');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | رفع ملفات ولي الأمر
    |--------------------------------------------------------------------------
    */
    protected function handleGuardianFiles(Request $request, $guardian)
    {
        $files = [
            'personal_photo' => 'guardians/photos',
            'id_document' => 'guardians/documents',
            'passport' => 'guardians/passports',
            'entry_stamp' => 'guardians/stamps',
            'medical_reports' => 'guardians/medical',
            'rent_contract' => 'guardians/rent',
        ];

        foreach ($files as $field => $folder) {
            if ($request->hasFile($field)) {
                $guardian->$field = $this->uploadFile(
                    $request->file($field),
                    $folder
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | رفع ملفات الأطفال
    |--------------------------------------------------------------------------
    */
    protected function handleChildFiles(Request $request, $orphanChild, $index)
    {
        if ($request->hasFile("members.$index.personal_photo")) {
            $orphanChild->personal_photo = $this->uploadFile(
                $request->file("members.$index.personal_photo"),
                'members/photos'
            );
        }

        $fileFields = [
            'passport' => 'members/passports',
            'entry_stamp' => 'members/stamps',
            'birth_certificate' => 'members/birth_certificates',
        ];

        foreach ($fileFields as $field => $folder) {
            if ($request->hasFile("members.$index.$field")) {
                $orphanChild->$field = $this->uploadFile(
                    $request->file("members.$index.$field"),
                    $folder
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | دالة رفع الملفات
    |--------------------------------------------------------------------------
    */
    protected function uploadFile($file, $folder)
    {
        $fileName = Str::random(30) . '.' . $file->getClientOriginalExtension();

        // اضفنا orphans كبداية المسار
        $destination = public_path("uploads/orphans/$folder");

        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $fileName);

        // مسار النسخة النهائية للاستخدام في العرض
        return "uploads/orphans/$folder/" . $fileName;
    }
}
