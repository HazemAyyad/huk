<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Association\AssociationReports;
use Illuminate\Http\Request;

final class AssociationReportsController extends Controller
{
    public function usage(Request $request, AssociationReports $reports) { return response()->json(['data' => $reports->usage($request->user())])->header('Cache-Control', 'no-store'); }
    public function settings(Request $request, AssociationReports $reports) { return response()->json(['data' => $reports->settings($request->user())])->header('Cache-Control', 'no-store'); }
    public function saveSettings(Request $request, AssociationReports $reports) { return response()->json(['data' => $reports->saveSettings($request->user(), $request->all())]); }
    public function preferences(Request $request, AssociationReports $reports) { return response()->json(['data' => $reports->preferences($request->user(), $request->isMethod('put') ? $request->all() : null)])->header('Cache-Control', 'no-store'); }
}
