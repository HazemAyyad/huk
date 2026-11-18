<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Association\AssociationProfile;
use App\Services\Association\AssociationPortal;
use App\Services\Association\AssociationTransfers;
use Illuminate\Http\Request;

final class AssociationProfileController extends Controller
{
    public function show(Request $request, AssociationProfile $profile) { return response()->json(['data' => $profile->show($request->user())])->header('Cache-Control', 'no-store'); }
    public function update(Request $request, AssociationProfile $profile) { return response()->json(['data' => $profile->update($request->user(), $request->all())]); }
    public function openFields(Request $request, int $beneficiary, AssociationProfile $profile) { return response()->json(['data' => $profile->openFields($request->user(), $beneficiary, $request->all())]); }
    public function startTransfer(Request $request, AssociationPortal $portal, AssociationTransfers $transfers)
    {
        $person = $portal->person($request->user());
        return response()->json(['data' => $transfers->start($request->user(), [...$request->all(), 'module' => 'profile', 'record_id' => $person->id, 'field' => 'personal_photo'], null, true)]);
    }
}
