<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Association\AssociationAccess;
use App\Services\Association\AssociationEntities;
use App\Services\Association\AssociationModules;
use App\Services\Association\AssociationRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AssociationOverviewController extends Controller
{
    public function __construct(private AssociationAccess $access, private AssociationRecords $records, private AssociationEntities $entities) {}

    public function overview(Request $request)
    {
        $grant = $this->access->workspace($request->user());
        $people = $this->access->allows($grant, 'beneficiaries') ? $this->records->query('beneficiaries', $grant)->count() : null;
        $pending = null;
        foreach (['orphan-requests', 'amputation-requests', 'wheelchair-requests'] as $module) {
            $class = 'App\\Models\\'.AssociationModules::DEFINITIONS[$module]['model'];
            if ($this->access->allows($grant, $module) && Schema::hasTable((new $class)->getTable())) {
                $pending = ($pending ?? 0) + $this->records->query($module, $grant, ['status' => 'pending'])->count();
            }
        }
        if ($this->access->allows($grant, 'submissions')) {
            $query = DB::table('association_form_submissions')->whereIn('status', ['submitted', 'correction_requested']);
            if (! $this->access->global($grant)) { $query->whereIn('beneficiary_id', $this->access->beneficiaryQuery($grant)->select('telegram_users.id')); }
            $pending = ($pending ?? 0) + $query->count();
        }
        $events = null;
        if ($this->access->allows($grant, 'events')) {
            $events = 0;
            foreach ($this->records->query('events', $grant)->where('is_archived', false)->where('event_date', '<=', now()->toDateString())->cursor() as $event) {
                if ($this->entities->eventActive($event)) { $events++; }
            }
        }

        return response()->json(['data' => ['beneficiaries' => $people, 'pending_requests' => $pending, 'active_events' => $events,
            'open_threads' => DB::table('association_threads')->where('status', 'open')->whereNull('archived_at')
                ->whereIn('id', DB::table('association_thread_members')->where('account_id', $request->user()->id)->where('active', true)->select('thread_id'))->count(),
            'as_of' => now()->toIso8601String()]])->header('Cache-Control', 'no-store');
    }

    public function history(Request $request, string $module)
    {
        abort_unless(in_array($module, ['audit', 'scan-history'], true), 404);
        $grant = $this->access->require($request->user(), $module);
        $filter = $request->validate(['q' => 'nullable|string|max:120', 'page' => 'nullable|integer|min:1', 'event_id' => 'nullable|integer|min:1', 'from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);
        $query = DB::table($module === 'audit' ? 'association_audit' : 'association_scan_history');
        if ($module === 'scan-history' && ! $this->access->global($grant)) {
            $query->where(function ($query) use ($grant, $request): void {
                $query->where('account_id', $request->user()->id)->orWhereIn('beneficiary_id', $this->access->beneficiaryQuery($grant)->select('telegram_users.id'));
            });
        }
        if (isset($filter['event_id']) && $module === 'scan-history') { $query->where('event_id', $filter['event_id']); }
        if (! empty($filter['q'])) {
            $search = '%'.str_replace(['%', '_'], ['\%', '\_'], $filter['q']).'%';
            $query->where(function ($query) use ($module, $search): void {
                foreach ($module === 'audit' ? ['module', 'action', 'record_id', 'reason'] : ['mode', 'result', 'beneficiary_id', 'event_id'] as $key) { $query->orWhere($key, 'like', $search); }
            });
        }
        foreach (['from' => '>=', 'to' => '<='] as $key => $operator) { if (isset($filter[$key])) { $query->whereDate('created_at', $operator, $filter[$key]); } }
        $page = $query->orderByDesc('id')->paginate(30);

        return response()->json(['data' => ['items' => array_map(fn ($row) => $this->records->serialize($row), $page->items()), 'page' => $page->currentPage(), 'has_more' => $page->hasMorePages(),
            'total' => $page->total(), 'can_create' => false, 'can_edit' => false, 'fields' => [], 'actions' => [],
            'title' => AssociationModules::DEFINITIONS[$module]['title']]])->header('Cache-Control', 'no-store');
    }
}
