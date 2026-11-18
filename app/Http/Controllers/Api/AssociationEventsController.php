<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Association\AssociationEvents;
use Illuminate\Http\Request;

final class AssociationEventsController extends Controller
{
    public function scan(Request $request, string $mode, AssociationEvents $events)
    {
        return response()->json(['data' => $events->scan($request->user(), $mode, $request->all())]);
    }

    public function confirm(Request $request, string $mode, AssociationEvents $events)
    {
        return response()->json(['data' => $events->confirm($request->user(), $mode, $request->all())]);
    }

    public function events(Request $request, string $mode, AssociationEvents $events)
    {
        return response()->json(['data' => $events->events($request->user(), $mode)])->header('Cache-Control', 'no-store');
    }

    public function offlineScan(Request $request, string $mode, AssociationEvents $events)
    {
        return response()->json(['data' => $events->offlineScan($request->user(), $mode, $request->all())]);
    }

    public function participants(Request $request, int $event, string $mode, AssociationEvents $events)
    {
        $filter = $request->validate(['q' => 'nullable|string|max:120', 'page' => 'nullable|integer|min:1|max:100000']);

        return response()->json(['data' => $events->participants($request->user(), $event, $mode, $filter)])->header('Cache-Control', 'no-store');
    }
}
