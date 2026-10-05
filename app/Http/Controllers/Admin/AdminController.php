<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmputationDetailCase;
use App\Models\FamilyBreadwinner;
use App\Models\Guardian;
use App\Models\InjuredCase;
use App\Models\OrphanCase;
use App\Models\PatientCase;
use App\Models\SolidarityHousing;
use App\Models\StrandedFamily;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use App\Models\GuardianDetail;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
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
    
    
     public function indexadmin()
    {
        $cases = OrphanCase::with('telegramUser.user')
            ->latest()
            ->get();
        $totalUsers = TelegramUser::count();
        $totalOrphan = $cases->count();
        $totalAmputation = AmputationDetailCase::count();
        $totalSolidarityHousing = TelegramUser::whereHas('category', function ($query) {
            $query->where('case_profile_id', 3); // الرقم 3 هو ID مساكن العبور
        })->count();
        $totalFamilyBreadwinner = FamilyBreadwinner::count();
        $totalStrandedFamily = StrandedFamily::count();
        $totalInjuredCase = InjuredCase::count();
        $totalPatientCase = TelegramUser::where('target_case', 'الحالات المرضية')->count();
        $totalOtherCategories = TelegramUser::where('target_case', 'فئات أخرى')->count();

        // =========================================
        // 👶 إحصائيات الأيتام
        // =========================================

        // إجمالي المسجلين في فئة الأيتام
        $orphansCount = TelegramUser::where('target_case', 'الأيتام')->count();

        // عدد الحالات المسجلة عبر رابط الأيتام
        $orphansRegisteredInLink = DB::table('orphan_cases')->count();

        // المتبقي غير المسجل في الرابط
        $orphansRemaining = max(0, $orphansCount - $orphansRegisteredInLink);

        // عدد الأيتام الذين لديهم كود
        $orphansWithCode = TelegramUser::where('target_case', 'الأيتام')
            ->whereNotNull('access_code')
            ->where('access_code', '!=', '')
            ->count();

        // عدد الأيتام الذين ليس لديهم كود
        $orphansWithoutCode = max(0, $orphansCount - $orphansWithCode);

        // =========================================
        // 🦽 إحصائيات حالات البتر
        // =========================================

        // إجمالي المسجلين في فئة البتر
        $amputationsCount = TelegramUser::where('target_case', 'حالات البتر')->count();

        // عدد الحالات المسجلة عبر رابط البتر
        $amputationsRegisteredInLink = DB::table('amputation_detail_cases')->count();

        // المتبقي غير المسجل في الرابط
        $amputationsRemaining = max(0, $amputationsCount - $amputationsRegisteredInLink);

        // عدد حالات البتر الذين لديهم كود
        $amputationsWithCode = TelegramUser::where('target_case', 'حالات البتر')
            ->whereNotNull('access_code')
            ->where('access_code', '!=', '')
            ->count();

        // عدد حالات البتر الذين ليس لديهم كود
        $amputationsWithoutCode = max(0, $amputationsCount - $amputationsWithCode);

        return view('admin.indexadmin', compact(
            'cases',
            'totalUsers',
            'totalOrphan',
            'totalAmputation',
            'totalSolidarityHousing',
            'totalFamilyBreadwinner',
            'totalStrandedFamily',
            'totalInjuredCase',
            'totalPatientCase',
            'totalOtherCategories',
            // إحصائيات الأيتام
            'orphansCount',
            'orphansRegisteredInLink',
            'orphansRemaining',
            'orphansWithCode',
            'orphansWithoutCode',
            // إحصائيات البتر
            'amputationsCount',
            'amputationsRegisteredInLink',
            'amputationsRemaining',
            'amputationsWithCode',
            'amputationsWithoutCode'
        ));
    }

    public function getAnalysis()
    {
        return response()->json([
            'orphans' => Guardian::count(),
            'amputations' => AmputationCase::count(),
            'solidarityHousing' => SolidarityHousing::count(),
            'familyBreadwinner' => FamilyBreadwinner::count(),
            'strandedFamily' => StrandedFamily::count(),
            'injuredCase' => InjuredCase::count(),
            'patientCase' => PatientCase::count(),
            'assistanceRequest' => AssistanceRequest::count(),
            'total' => PatientCase::count() + AssistanceRequest::count() + InjuredCase::count() + StrandedFamily::count() + FamilyBreadwinner::count() + SolidarityHousing::count() + AmputationCase::count() + Guardian::count(),
        ], 200);
    }

    public function generalResearch()
    {
        $filters = request()->only(['national_id', 'code', 'name', 'general_research']);

        $data = collect()
            ->merge(Guardian::filter($filters)->get())
            ->merge(AmputationCase::filter($filters)->get())
            ->merge(SolidarityHousing::filter($filters)->get())
            ->merge(FamilyBreadwinner::filter($filters)->get())
            ->merge(StrandedFamily::filter($filters)->get())
            ->merge(InjuredCase::filter($filters)->get())
            ->merge(PatientCase::filter($filters)->get())
            ->values();

        return response()->json([
            'data' => $data
        ]);
    }

    public function getHumanCases()
    {
        $data = collect()

            ->merge(
                Guardian::select(
                    'id',
                    'full_name as name',
                    'latitude',
                    'longitude'
                )->get()->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'lat' => (float) $item->latitude,
                    'lng' => (float) $item->longitude,
                    'type' => 'guardian',
                ])
            )

            ->merge(
                AmputationCase::select(
                    'id',
                    'full_name as name',
                    'latitude',
                    'longitude'
                )->get()->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'lat' => (float) $item->latitude,
                    'lng' => (float) $item->longitude,
                    'type' => 'amputation',
                ])
            )

            ->merge(
                SolidarityHousing::select(
                    'id',
                    'full_name as name',
                    'latitude',
                    'longitude'
                )->get()->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'lat' => (float) $item->latitude,
                    'lng' => (float) $item->longitude,
                    'type' => 'housing',
                ])
            )

            ->merge(
                FamilyBreadwinner::select(
                    'id',
                    'wife_full_name as name',
                    'latitude',
                    'longitude'
                )->get()->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'lat' => (float) $item->latitude,
                    'lng' => (float) $item->longitude,
                    'type' => 'breadwinner',
                ])
            )

            ->merge(
                StrandedFamily::select(
                    'id',
                    'head_full_name as name',
                    'latitude',
                    'longitude'
                )->get()->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'lat' => (float) $item->latitude,
                    'lng' => (float) $item->longitude,
                    'type' => 'stranded',
                ])
            )

            ->merge(
                InjuredCase::select(
                    'id',
                    'full_name as name',
                    'latitude',
                    'longitude'
                )->get()->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'lat' => (float) $item->latitude,
                    'lng' => (float) $item->longitude,
                    'type' => 'injured',
                ])
            )

            ->merge(
                PatientCase::select(
                    'id',
                    'full_name as name',
                    'latitude',
                    'longitude'
                )->get()->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'lat' => (float) $item->latitude,
                    'lng' => (float) $item->longitude,
                    'type' => 'patient',
                ])
            )

            ->values();

        return response()->json([
            'data' => $data
        ]);
    }

    public function getReviews()
    {
        return view('admin.index');
    }

    public function getAllReviews()
    {
        return response()->json([
            'data' => Review::get(),
        ], 200);
    }

    public function updateReviewConfirm($id)
    {
        Review::findOrFail($id)->update([
            'status' => true,
        ]);

        return response()->json([
            'message' => "Done",
        ], 200);
    }

    public function updateReviewUnConfirm($id)
    {
        Review::findOrFail($id)->update([
            'status' => false,
        ]);

        return response()->json([
            'message' => "Done",
        ], 200);
    }

    public function updateReviewDestroy($id)
    {
        Review::findOrFail($id)->delete();
        return response()->json([
            'message' => "Done",
        ], 200);
    }


    public function getGuardianCases()
{
    $data = GuardianDetail::with('telegramUser')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->telegram_user_id, // 🔥 المهم
                'name' => optional($item->telegramUser)->full_name ?? 'بدون اسم',
                'lat' => (float) $item->latitude,
                'lng' => (float) $item->longitude,
                'type' => 'guardian'
            ];
        });

    return response()->json([
        'data' => $data
    ]);
}

}
