<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Association\AssociationTransfers;
use Illuminate\Http\Request;

final class AssociationTransfersController extends Controller
{
    public function __construct(private AssociationTransfers $transfers) {}

    public function start(Request $request) { return response()->json(['data' => $this->transfers->start($request->user(), $request->all())]); }
    public function status(Request $request, string $transfer) { return response()->json(['data' => $this->transfers->status($request->user(), $transfer)])->header('Cache-Control', 'no-store'); }
    public function part(Request $request, string $transfer, int $part)
    {
        $request->validate(['file' => 'required|file']);

        return response()->json(['data' => $this->transfers->part($request->user(), $transfer, $part, $request->file('file'))]);
    }
    public function finish(Request $request, string $transfer) { return response()->json(['data' => $this->transfers->finish($request->user(), $transfer)], 202); }
    public function portalStart(Request $request, int $form)
    {
        return response()->json(['data' => $this->transfers->start($request->user(), [...$request->all(), 'module' => 'submissions', 'record_id' => null], $form)]);
    }
    public function threadStart(Request $request, int $thread, \App\Services\Association\AssociationConversations $conversations)
    {
        $row = $conversations->show($request->user(), $thread, [])['record'];
        $module = $row['kind'] === 'support' ? 'support' : 'chat';
        return response()->json(['data' => $this->transfers->start($request->user(), [...$request->all(), 'module' => $module, 'record_id' => $thread, 'field' => 'attachment'], null, true)]);
    }
}
