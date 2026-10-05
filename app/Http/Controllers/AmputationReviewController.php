<?php

namespace App\Http\Controllers;

use App\Models\AmputationDetailCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AmputationReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin|adminamputation']);
    }

    /*
    |------------------------------------------------------------------
    | عرض كل الطلبات
    |------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = AmputationDetailCase::with('telegramUser')->latest();

        // 🔍 البحث
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('national_id', 'like', '%' . $request->search . '%');
            });
        }

        $cases = $query->paginate(15)->withQueryString();

        // 📊 الإحصائيات
        $total = AmputationDetailCase::count();
        $pending = AmputationDetailCase::where('status', 'pending')->count();
        $approved = AmputationDetailCase::where('status', 'approved')->count();
        $rejected = AmputationDetailCase::where('status', 'rejected')->count();

        return view('admin.amputations.index', compact(
            'cases', 'total', 'pending', 'approved', 'rejected'
        ));
    }

    /*
    |------------------------------------------------------------------
    | عرض التفاصيل
    |------------------------------------------------------------------
    */
    public function show($id)
    {
        $case = AmputationDetailCase::with([
            'telegramUser',
            'reviewer'
        ])->findOrFail($id);

        return view('admin.amputations.show', compact('case'));
    }

    /*
    |------------------------------------------------------------------
    | مراجعة الطلب
    |------------------------------------------------------------------
    */
    public function review(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string'
        ]);

        $case = AmputationDetailCase::findOrFail($id);

        $case->update([
            'status'      => $request->status,
            'admin_note'  => $request->admin_note,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'تم تحديث الحالة بنجاح');
    }

    /*
    |------------------------------------------------------------------
    | حذف الطلب
    |------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $case = AmputationDetailCase::findOrFail($id);

        // حذف الصور المرتبطة
        $this->deleteFile($case->person_image);
        $this->deleteFile($case->amputation_image);
        $this->deleteFile($case->guardian_passport);
        $this->deleteFile($case->entry_stamp);
        $this->deleteFile($case->medical_reports);
        $this->deleteFile($case->rent_contract);

        $case->delete();

        return redirect()->route('admin.amputations.index')
            ->with('success', 'تم حذف الطلب بنجاح');
    }

    /*
    |------------------------------------------------------------------
    | تحديث الصور
    |------------------------------------------------------------------
    */


public function updateImage(Request $request, $id)
{
    $request->validate([
        'image' => 'required|image|max:4096',
        'type'  => 'required|string'
    ]);

    $case = AmputationDetailCase::findOrFail($id);

    switch ($request->type) {

        // صورة الشخص
        case 'person_image':

            if ($case->person_image && file_exists(public_path($case->person_image))) {
                unlink(public_path($case->person_image));
            }

            $case->person_image = $this->uploadFile(
                $request->file('image'),
                'amputations/person'
            );

            $case->save();

        break;

        // صورة البتر
        case 'amputation_image':

            if ($case->amputation_image && file_exists(public_path($case->amputation_image))) {
                unlink(public_path($case->amputation_image));
            }

            $case->amputation_image = $this->uploadFile(
                $request->file('image'),
                'amputations/amputation'
            );

            $case->save();

        break;
    }

    return back()->with('success', 'تم تحديث الصورة بنجاح ✅');
}

    /*
    |------------------------------------------------------------------
    | رفع ملف
    |------------------------------------------------------------------
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

    /*
    |------------------------------------------------------------------
    | حذف ملف
    |------------------------------------------------------------------
    */
    protected function deleteFile($path)
    {
        if ($path && file_exists(public_path($path))) {
            unlink(public_path($path));
        }
    }


    public function updateAttachment(Request $request, $id)
{
    $request->validate([
        'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        'type' => 'required|string'
    ]);

    $case = AmputationDetailCase::findOrFail($id);

    $field = $request->type;

    $allowedFields = [
        'medical_reports',
        'rent_contract',
        'guardian_passport',
        'entry_stamp'
    ];

    if (!in_array($field, $allowedFields)) {
        return back()->with('error', 'نوع الملف غير صالح');
    }

    // حذف الملف القديم
    if ($case->$field && file_exists(public_path($case->$field))) {
        unlink(public_path($case->$field));
    }

    $case->$field = $this->uploadFile(
        $request->file('file'),
        "attachments/$field"
    );

    $case->save();

    return back()->with('success', 'تم تحديث الملف بنجاح ✅');
}
}