<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    /**
     * عرض الجلسات النشطة مع البحث والترقيم.
     */
    public function index(Request $request)
    {
        $lifetime = config('session.lifetime', 120);

        $query = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id',
                'sessions.user_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'users.name',
                'users.email'
            )
            ->where('sessions.last_activity', '>=', now()->timestamp - ($lifetime * 60));

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', '%' . $search . '%')
                    ->orWhere('users.email', 'like', '%' . $search . '%')
                    ->orWhere('sessions.ip_address', 'like', '%' . $search . '%')
                    ->orWhere('sessions.user_agent', 'like', '%' . $search . '%');
            });
        }

        // الترقيم - 50 في كل صفحة
        $sessions = $query
            ->orderByDesc('sessions.last_activity')
            ->paginate(50)
            ->withQueryString(); // يحافظ على معلمات البحث في الروابط

        return view('admin.sessions.index', compact('sessions', 'lifetime'));
    }

    /**
     * إنهاء جلسة محددة.
     */
    public function destroy(Request $request, string $id)
    {
        DB::table('sessions')
            ->where('id', $id)
            ->delete();

        return back()->with('success', 'تم إنهاء الجلسة بنجاح.');
    }

    /**
     * إنهاء جميع الجلسات الأخرى للمستخدم الحالي.
     */
    public function destroyOthers(Request $request)
    {
        $currentSessionId = $request->session()->getId();

        DB::table('sessions')
            ->where('user_id', auth()->id())
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return back()->with('success', 'تم إنهاء جميع الجلسات الأخرى.');
    }
}