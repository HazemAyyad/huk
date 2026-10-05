<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }
    /**
     * عرض جميع المستخدمين + البحث
     */
    public function useradmin(Request $request)
    {
        $query = User::query();

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%');
            });
        }

        $users = $query
            ->orderBy('updated_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.useradmin', compact('users'));
    }


    /**
     * صفحة تعديل المستخدم
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }


    /**
     * تحديث بيانات المستخدم
     */
    public function update(Request $request, $id)
{
    // جلب المستخدم
    $user = User::findOrFail($id);

    // التحقق من البيانات
    $validated = $request->validate([

        'name' => [
            'required',
            'string',
            'max:255',
        ],

        'email' => [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($user->id),
        ],

        'title' => [
            'nullable',
            'string',
            'max:255',
        ],

        'basic_salary' => [
            'nullable',
            'numeric',
            'min:0',
        ],

        'password' => [
            'nullable',
            'string',
            'min:8',
            'confirmed',
        ],
    ]);


    // معرفة هل المستخدم أدخل كلمة مرور جديدة
    $passwordChanged = !empty($validated['password']);


    // =====================================================
    // تحديث البيانات الأساسية
    // =====================================================

    $user->name = $validated['name'];

    $user->email = $validated['email'];

    $user->title = $validated['title'] ?? null;

    $user->basic_salary = $validated['basic_salary'] ?? null;


    // =====================================================
    // تغيير كلمة المرور
    // =====================================================

    if ($passwordChanged) {

        // بسبب:
        // protected $casts = ['password' => 'hashed'];
        //
        // Laravel سيقوم بتشفير كلمة المرور تلقائياً
        $user->password = $validated['password'];
    }


    // =====================================================
    // تسجيل الشخص الذي قام بالتعديل
    // =====================================================

    $user->updated_by = auth()->id();


    // حفظ المستخدم
    $user->save();


    // =====================================================
    // إذا تم تغيير كلمة المرور
    // =====================================================

    if ($passwordChanged) {

        /*
        |--------------------------------------------------------------------------
        | حذف جميع جلسات المستخدم
        |--------------------------------------------------------------------------
        |
        | هذا يعني أن المستخدم سيتم تسجيل خروجه
        | من جميع الأجهزة والمتصفحات.
        |
        */

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | إذا كان المستخدم الذي تم تعديل كلمة مروره
        | هو نفس المستخدم الحالي
        |--------------------------------------------------------------------------
        */

        if (auth()->id() == $user->id) {

            // تسجيل الخروج
            auth()->logout();

            // إلغاء الـ Session الحالية
            $request->session()->invalidate();

            // إنشاء CSRF Token جديد
            $request->session()->regenerateToken();


            // إرسال المستخدم إلى صفحة تسجيل الدخول
            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'تم تغيير كلمة المرور بنجاح. يرجى تسجيل الدخول مرة أخرى.'
                );
        }
    }


    // =====================================================
    // في حالة تعديل مستخدم آخر
    // =====================================================

    return redirect()
        ->route('admin.useradmin')
        ->with(
            'success',
            'تم تحديث بيانات المستخدم بنجاح.'
        );
}
}