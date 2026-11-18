<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Association\AssociationInvitations;
use App\Services\Association\AssociationBeneficiaryInvitations;
use Illuminate\Http\Request;

final class AssociationInvitationsController extends Controller
{
    public function preview(Request $request, int $event, AssociationInvitations $invitations) { return response()->json(['data' => $invitations->preview($request->user(), $event, $request->all())])->header('Cache-Control', 'no-store'); }
    public function send(Request $request, int $event, AssociationInvitations $invitations) { return response()->json(['data' => $invitations->send($request->user(), $event, $request->all())]); }
    public function withdraw(Request $request, int $invitation, AssociationInvitations $invitations) { return response()->json(['data' => $invitations->withdraw($request->user(), $invitation, $request->all())]); }
    public function index(Request $request, AssociationBeneficiaryInvitations $invitations)
    {
        $request->validate(['page' => 'nullable|integer|min:1']);
        return response()->json(['data' => $invitations->index($request->user())])->header('Cache-Control', 'no-store');
    }
    public function show(Request $request, int $invitation, AssociationBeneficiaryInvitations $invitations) { return response()->json(['data' => $invitations->show($request->user(), $invitation)])->header('Cache-Control', 'no-store'); }
    public function action(Request $request, int $invitation, AssociationBeneficiaryInvitations $invitations) { return response()->json(['data' => $invitations->action($request->user(), $invitation, $request->all())]); }
}
