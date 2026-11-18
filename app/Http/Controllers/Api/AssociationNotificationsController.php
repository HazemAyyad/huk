<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Association\AssociationNotificationAdmin;
use App\Services\Association\AssociationInbox;
use Illuminate\Http\Request;

final class AssociationNotificationsController extends Controller
{
    public function index(Request $request, AssociationNotificationAdmin $notifications)
    {
        $filter = $request->validate(['q' => 'nullable|string|max:120', 'status' => 'nullable|string|max:30', 'page' => 'nullable|integer|min:1']);

        return response()->json(['data' => $notifications->index($request->user(), $filter)])->header('Cache-Control', 'no-store');
    }
    public function show(Request $request, int $id, AssociationNotificationAdmin $notifications) { return response()->json(['data' => $notifications->show($request->user(), $id)])->header('Cache-Control', 'no-store'); }
    public function save(Request $request, AssociationNotificationAdmin $notifications, ?int $id = null) { return response()->json(['data' => $notifications->save($request->user(), $request->all(), $id)]); }
    public function preview(Request $request, int $notification, AssociationNotificationAdmin $notifications) { return response()->json(['data' => $notifications->preview($request->user(), $notification)])->header('Cache-Control', 'no-store'); }
    public function action(Request $request, int $id, AssociationNotificationAdmin $notifications) { return response()->json(['data' => $notifications->action($request->user(), $id, $request->all())]); }
    public function inbox(Request $request, AssociationInbox $inbox)
    {
        $request->validate(['page' => 'nullable|integer|min:1']);
        return response()->json(['data' => $inbox->index($request->user())])->header('Cache-Control', 'no-store');
    }
    public function acknowledge(Request $request, int $notification, AssociationInbox $inbox) { return response()->json(['data' => $inbox->acknowledge($request->user(), $notification, $request->all())]); }
    public function inboxShow(Request $request, int $notification, AssociationInbox $inbox) { return response()->json(['data' => $inbox->show($request->user(), $notification)])->header('Cache-Control', 'no-store'); }
}
