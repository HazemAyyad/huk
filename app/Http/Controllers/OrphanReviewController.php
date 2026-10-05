<?php

namespace App\Http\Controllers;

use App\Models\OrphanCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OrphanChild;
use Illuminate\Support\Str;
use App\Models\FamilyMember;
use App\Models\GuardianDetail;
use Illuminate\Support\Facades\DB;

class OrphanReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin|adminorphans']);
    }

    /*
    |--------------------------------------------------------------------------
    | عرض كل الطلبات
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
{
    $query = OrphanCase::with([
    'telegramUser.guardianDetail',
    'telegramUser.categories',
    'telegramUser.familyMembers.orphanChild'
])->latest();

    // 🔍 البحث
    if ($request->search) {
        $query->whereHas('telegramUser', function ($q2) use ($request) {
            $q2->where('full_name', 'like', '%' . $request->search . '%')
                ->orWhere('egypt_number', 'like', '%' . $request->search . '%')
                ->orWhere('access_code', 'like', '%' . $request->search . '%');
        });
    }

    // ✅ فلتر الفئة
    if ($request->category_id) {
        $query->whereHas('telegramUser.categories', function ($q) use ($request) {
            $q->where('categories.id', $request->category_id);
        });
    }

    // ✅ فلتر الحالة
    if ($request->status && in_array($request->status, ['pending', 'approved', 'rejected'])) {
        $query->where('status', $request->status);
    }

    $cases = $query->paginate(15)->withQueryString();

    // 📊 الإحصائيات
    $statsQuery = OrphanCase::query();

    if ($request->category_id) {
        $statsQuery->whereHas('telegramUser.categories', function ($q) use ($request) {
            $q->where('categories.id', $request->category_id);
        });
    }

    $total = $statsQuery->count();

    $pending = (clone $statsQuery)
        ->where('status', 'pending')
        ->count();

    $approved = (clone $statsQuery)
        ->where('status', 'approved')
        ->count();

    $rejected = (clone $statsQuery)
        ->where('status', 'rejected')
        ->count();

    // 📌 الفئات
    $categories = \App\Models\Category::where('case_profile_id', 1)->get();

    return view('admin.orphans.index', compact(
        'cases',
        'total',
        'pending',
        'approved',
        'rejected',
        'categories'
    ));
}

    /*
    |--------------------------------------------------------------------------
    | عرض تفاصيل طلب
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $case = OrphanCase::with([
            'telegramUser.guardianDetail',
            'telegramUser.familyMembers.orphanChild'
        ])->findOrFail($id);

        return view('admin.orphans.show', compact('case'));
    }

    /*
    |--------------------------------------------------------------------------
    | اعتماد أو رفض الطلب
    |--------------------------------------------------------------------------
    */
    public function review(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string'
        ]);

        $case = OrphanCase::findOrFail($id);

        $case->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.orphans.index')
            ->with('success', 'تم تحديث حالة الطلب بنجاح');
    }

    public function destroy($id)
    {
        $case = OrphanCase::with('telegramUser.familyMembers.orphanChild')->findOrFail($id);

        $telegramUser = $case->telegramUser;

        // حذف بيانات الأطفال
        foreach ($telegramUser->familyMembers as $member) {
            if ($member->orphanChild) {
                $member->orphanChild->delete();
            }
        }

        // حذف بيانات ولي الأمر
        $telegramUser->guardianDetail?->delete();

        // حذف الطلب نفسه
        $case->delete();

        return redirect()->route('admin.orphans.index')
            ->with('success', 'تم حذف جميع البيانات ويمكن إعادة التسجيل من جديد');
    }


    public function updateImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'type' => 'required|string'
        ]);

        $case = OrphanCase::with('telegramUser.guardianDetail')->findOrFail($id);

        switch ($request->type) {

            // ✅ صورة ولي الأمر
            case 'guardian_photo':
                $guardian = $case->telegramUser->guardianDetail;

                if ($guardian) {

                    // حذف القديمة (اختياري 🔥)
                    if ($guardian->personal_photo && file_exists(public_path($guardian->personal_photo))) {
                        unlink(public_path($guardian->personal_photo));
                    }

                    $guardian->personal_photo = $this->uploadFile(
                        $request->file('image'),
                        'guardians/photos' // نفس القديم
                    );

                    $guardian->save();
                }
                break;

            // ✅ صورة طفل
            case 'child_photo':
                $child = OrphanChild::findOrFail($request->child_id);

                // حذف القديمة
                if ($child->personal_photo && file_exists(public_path($child->personal_photo))) {
                    unlink(public_path($child->personal_photo));
                }

                $child->personal_photo = $this->uploadFile(
                    $request->file('image'),
                    'members/photos' // نفس الكونترولر الأساسي
                );

                $child->save();
                break;
        }

        return back()->with('success', 'تم تحديث الصورة بنجاح ✅');
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

    public function updateAttachment(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,png|max:10240',
            'type' => 'required|string|in:id_document,passport,entry_stamp,medical_reports,rent_contract,death_certificate'
        ]);

        $case = OrphanCase::with('telegramUser.guardianDetail')->findOrFail($id);

        $guardian = $case->telegramUser->guardianDetail;

        if (!$guardian) {
            return back()->with('error', 'لا يوجد بيانات ولي أمر');
        }

        $field = $request->type;

        // 📁 تحديد المجلد حسب النوع
        $folders = [
            'id_document' => 'guardians/documents',
            'passport' => 'guardians/passports',
            'entry_stamp' => 'guardians/stamps',
            'medical_reports' => 'guardians/medical',
            'rent_contract' => 'guardians/rent',
        ];

        // 🔥 شهادة الوفاة تبع case
        if ($field === 'death_certificate') {

            if ($case->death_certificate && file_exists(public_path($case->death_certificate))) {
                unlink(public_path($case->death_certificate));
            }

            $case->death_certificate = $this->uploadFile(
                $request->file('file'),
                'cases/orphans/deaths'
            );

            $case->save();

        } else {

            // حذف القديم
            if ($guardian->$field && file_exists(public_path($guardian->$field))) {
                unlink(public_path($guardian->$field));
            }

            $guardian->$field = $this->uploadFile(
                $request->file('file'),
                $folders[$field]
            );

            $guardian->save();
        }

        return back()->with('success', 'تم رفع/تحديث الملف بنجاح ✅');
    }

public function edit($id)
{
    $case = OrphanCase::with([
        'telegramUser.guardianDetail',
        'telegramUser.familyMembers.orphanChild'
    ])->findOrFail($id);

    return view('admin.orphans.edit',compact('case'));
}

public function update(Request $request,$id)
{
    DB::beginTransaction();

    try{

        $case=OrphanCase::with([
            'telegramUser.guardianDetail'
        ])->findOrFail($id);

        $user=$case->telegramUser;

        $guardian=$user->guardianDetail;

        /*
        |--------------------------------------------------------------------------
        | تحديث بيانات المستخدم
        |--------------------------------------------------------------------------
        */

        $user->update([

            'full_name'=>$request->full_name,

            'national_id'=>$request->national_id,

            'birth_date'=>$request->birth_date,

            'whatsapp'=>$request->whatsapp,

            'telegram_number'=>$request->telegram_number,

            'egypt_number'=>$request->egypt_number,

            'governorate'=>$request->governorate,

            'city'=>$request->city,

            'address'=>$request->address,

            'entry_reason'=>$request->entry_reason,

            'entry_to_egypt'=>$request->entry_to_egypt,

            'rent_amount'=>$request->rent_amount,

            'landlord_phone'=>$request->landlord_phone,

        ]);

        /*
        |--------------------------------------------------------------------------
        | بيانات ولي الأمر
        |--------------------------------------------------------------------------
        */

        if($guardian){

            $guardian->update([

                'birth_date'=>$request->guardian_birth_date,

                'age'=>$request->guardian_age,

                'relationship_to_case'=>$request->relationship_to_case,

                'gender'=>$request->guardian_gender,

                'housing_status'=>$request->housing_status,

                'monthly_income'=>$request->monthly_income,

                'latitude'=>$request->latitude,

                'longitude'=>$request->longitude,

            ]);

        }

        /*
        |--------------------------------------------------------------------------
        | بيانات الطلب
        |--------------------------------------------------------------------------
        */

        $case->update([

            'orphan_status'=>$request->orphan_status,

            'status'=>$request->status,

            'admin_note'=>$request->admin_note,

        ]);

        DB::commit();

        return back()->with('success','تم حفظ جميع البيانات بنجاح');

    }catch(\Exception $e){

        DB::rollBack();

        return back()->with('error',$e->getMessage());

    }
}

public function storeChild(Request $request,$id)
{
    DB::beginTransaction();

    try{

        $case=OrphanCase::with('telegramUser')->findOrFail($id);

        $member=FamilyMember::create([

            'telegram_user_id'=>$case->telegramUser->id,

            'full_name'=>$request->full_name,

            'national_id'=>$request->national_id,

            'relation'=>$request->relation,

            'birth_date'=>$request->birth_date,

            'is_orphan'=>1,

        ]);
        $photo = null;

        if ($request->hasFile('personal_photo')) {

            $photo = $this->uploadFile(
                $request->file('personal_photo'),
                'members/photos'
            );

        }
        OrphanChild::create([

            'family_member_id'=>$member->id,

            'birth_date'=>$request->birth_date,

            'age'=>$request->age,
            
            'personal_photo' => $photo,
            
            'gender'=>$request->gender,

            'lives_with_guardian'=>$request->lives_with_guardian,

            'education_level'=>$request->education_level,

            'study_type'=>$request->study_type,

            'has_chronic_disease'=>$request->has_chronic_disease,

            'has_disability'=>$request->has_disability,

        ]);

        DB::commit();

        return back()->with('success','تم إضافة اليتيم');

    }catch(\Exception $e){

        DB::rollBack();

        return back()->with('error',$e->getMessage());

    }
}

public function updateChild(Request $request,OrphanChild $child)
{
    DB::beginTransaction();

    try{

        $member=$child->familyMember;

        $member->update([

            'full_name'=>$request->full_name,

            'national_id'=>$request->national_id,

            'relation'=>$request->relation,

            'birth_date'=>$request->birth_date,

        ]);

        $child->update([

            'birth_date'=>$request->birth_date,

            'age'=>$request->age,

            'gender'=>$request->gender,

            'lives_with_guardian'=>$request->lives_with_guardian,

            'education_level'=>$request->education_level,

            'study_type'=>$request->study_type,

            'has_chronic_disease'=>$request->has_chronic_disease,

            'has_disability'=>$request->has_disability,

        ]);

        DB::commit();

        return back()->with('success','تم تعديل بيانات اليتيم');

    }catch(\Exception $e){

        DB::rollBack();

        return back()->with('error',$e->getMessage());

    }
}

public function destroyChild(OrphanChild $child)
{
    DB::beginTransaction();

    try{

        if($child->personal_photo){

            $path=public_path($child->personal_photo);

            if(file_exists($path)){
                unlink($path);
            }

        }

        $member=$child->familyMember;

        $child->delete();

        if($member){
            $member->delete();
        }

        DB::commit();

        return back()->with('success','تم حذف اليتيم');

    }catch(\Exception $e){

        DB::rollBack();

        return back()->with('error',$e->getMessage());

    }
}


}