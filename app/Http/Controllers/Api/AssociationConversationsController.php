<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Association\AssociationAccess;
use App\Services\Association\AssociationConversations;
use Illuminate\Http\Request;

final class AssociationConversationsController extends Controller
{
    public function __construct(private AssociationConversations $conversations) {}
    public function index(Request $request, string $module)
    {
        $filter = $request->validate(['q' => 'nullable|string|max:120', 'status' => 'nullable|in:open,closed', 'archived' => 'sometimes|boolean', 'page' => 'nullable|integer|min:1']);
        return response()->json(['data' => $this->conversations->index($request->user(), $module, $filter)])->header('Cache-Control', 'no-store');
    }
    public function show(Request $request, int $thread)
    {
        $filter = $request->validate(['before' => 'nullable|integer|min:1', 'after' => 'nullable|integer|min:0']);
        abort_if(isset($filter['before'], $filter['after']), 422);
        return response()->json(['data' => $this->conversations->show($request->user(), $thread, $filter)])->header('Cache-Control', 'no-store');
    }
    public function create(Request $request) { return response()->json(['data' => $this->conversations->create($request->user(), $request->all())]); }
    public function ownerStart(Request $request) { return response()->json(['data' => $this->conversations->ownerStart($request->user(), $request->all())]); }
    public function openSupport(Request $request) { return response()->json(['data' => $this->conversations->openSupport($request->user(), $request->all())]); }
    public function contacts(Request $request) { return response()->json(['data' => $this->conversations->contacts($request->user())])->header('Cache-Control', 'no-store'); }
    public function send(Request $request, int $thread) { return response()->json(['data' => $this->conversations->send($request->user(), $thread, $request->all())]); }
    public function presence(Request $request, int $thread) { return response()->json(['data' => $this->conversations->presence($request->user(), $thread, $request->all())]); }
    public function action(Request $request, int $thread) { return response()->json(['data' => $this->conversations->action($request->user(), $thread, $request->all())]); }
    public function grant(Request $request) { return response()->json(['data' => $this->conversations->grant($request->user(), $request->all())]); }
    public function supportCategories(\App\Services\Association\AssociationSupportCategories $categories) { return response()->json(['data' => $categories->portal()]); }
    public function peers(Request $request, AssociationAccess $access)
    {
        $data = $request->validate(['q' => 'nullable|string|max:120', 'page' => 'nullable|integer|min:1', 'module' => 'required|in:chat,support']);
        $access->require($request->user(), $data['module']);
        $query = \App\Models\AppAccount::where('active', true)->where('association_enabled', true)->whereNotNull('user_id');
        if (! empty($data['q'])) { $query->whereIn('user_id', \App\Models\User::where('name', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $data['q']).'%')->select('id')); }
        $page = $query->orderBy('id')->paginate(30);
        $items = [];
        foreach ($page->items() as $peer) {
            if ($access->can($peer, $data['module'])) { $items[] = ['id' => $peer->id, 'label' => \App\Models\User::whereKey($peer->user_id)->value('name')]; }
        }
        return response()->json(['data' => ['items' => $items, 'page' => $page->currentPage(), 'has_more' => $page->hasMorePages()]])->header('Cache-Control', 'no-store');
    }
    public function beneficiaryAccounts(Request $request, AssociationAccess $access)
    {
        $grant = $access->require($request->user(), 'chat');
        abort_unless($access->allows($grant, 'chat', 'create') || $access->allows($grant, 'chat', 'grant'), 403);
        $data = $request->validate(['q' => 'nullable|string|max:120', 'page' => 'nullable|integer|min:1']);
        $query = \Illuminate\Support\Facades\DB::table('app_accounts')->join('users', 'users.id', '=', 'app_accounts.user_id')->join('telegram_users', 'telegram_users.id', '=', 'users.telegram_user_id')
            ->where('app_accounts.active', true)->where('app_accounts.association_enabled', true)->whereIn('telegram_users.id', $access->beneficiaryQuery($grant)->select('telegram_users.id'));
        if (! empty($data['q'])) {
            $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $data['q']).'%';
            $query->where(fn ($query) => $query->where('telegram_users.full_name', 'like', $term)->orWhere('telegram_users.national_id', 'like', $term));
        }
        $page = $query->select('app_accounts.id', 'telegram_users.full_name', 'telegram_users.national_id')->orderBy('app_accounts.id')->paginate(30);
        return response()->json(['data' => ['items' => array_map(fn ($row) => ['id' => $row->id, 'label' => $row->full_name.' · '.$row->national_id], $page->items()),
            'page' => $page->currentPage(), 'has_more' => $page->hasMorePages()]])->header('Cache-Control', 'no-store');
    }
}
