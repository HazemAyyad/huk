<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Religious\ReligiousCatalog;
use App\Services\Religious\ReligiousCommunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ReligiousCommunityAdminController extends Controller
{
    public function __construct(private readonly ReligiousCommunity $community)
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, \Closure $next) {
            abort_unless($request->user() instanceof User, 403);

            return $next($request);
        });
        $this->middleware('role:superadmin');
    }

    public function index()
    {
        $ready = $this->community->ready();
        // Allow moderation/preparation before public enablement, but never query an absent schema.
        foreach (ReligiousCommunity::TABLES as $table) {
            abort_unless(\Illuminate\Support\Facades\Schema::hasTable($table), 503, 'مخطط المجتمع لم يُجهّز بعد');
        }

        return view('admin.religious.community', [
            'enabled' => $ready, 'entries' => DB::table('religious_community_entries')->orderByDesc('id')->paginate(20, ['*'], 'entries'),
            'reports' => DB::table('religious_community_reports')->orderByDesc('id')->paginate(20, ['*'], 'reports'),
            'circles' => DB::table('religious_circles')->orderByDesc('id')->paginate(20, ['*'], 'circles'),
            'publications' => DB::table('religious_publications')->orderByDesc('id')->paginate(20, ['*'], 'publications'),
            'items' => app(ReligiousCatalog::class)->dataset()['items'],
        ]);
    }

    public function circle(Request $request)
    {
        app(\App\Services\Religious\CommunityAdministration::class)->circle($request);

        return back()->with('success', 'تم حفظ الحلقة. الهدف جماعي تنظيمي، وليس عددًا تعبديًا منسوبًا للنص.');
    }

    public function review(Request $request, int $entry)
    {
        app(\App\Services\Religious\CommunityAdministration::class)->review($request, $entry);

        return back()->with('success', 'تمت مراجعة المشاركة');
    }

    public function report(Request $request, int $report)
    {
        app(\App\Services\Religious\CommunityAdministration::class)->report($request, $report);

        return back()->with('success', 'تمت معالجة البلاغ');
    }

    public function publication(Request $request)
    {
        app(\App\Services\Religious\CommunityAdministration::class)->publication($request);

        return back()->with('success', 'تم حفظ المنشور. النشر بحسب الوقت والصلاحية؛ رفع الملف وحده لا ينشره.');
    }
}
