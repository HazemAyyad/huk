<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Association\AssociationAccess;
use App\Services\Association\AssociationEntities;
use App\Services\Association\AssociationMedia;
use App\Services\Association\AssociationModules;
use App\Services\Association\AssociationRecordRules;
use App\Services\Association\AssociationRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

final class AssociationAdminController extends Controller
{
    public function __construct(private AssociationAccess $access, private AssociationRecords $records, private AssociationEntities $entities) {}

    public function manifest(Request $request)
    {
        $grant = $this->access->workspace($request->user());
        $modules = [];
        foreach (AssociationModules::DEFINITIONS as $key => $definition) {
            if (! $this->access->allows($grant, $key)) {
                continue;
            }
            $actions = [];
            foreach (explode(',', $definition['actions']) as $action) {
                if ($this->access->allows($grant, $key, $action)) {
                    $actions[] = $action;
                }
            }
            $modules[] = ['id' => $key, 'title' => $definition['title'], 'title_en' => $definition['english'],
                'actions' => $actions, ...$this->home($key)];
        }

        return response()->json(['data' => ['modules' => $modules, 'scope' => ['global' => $this->access->global($grant),
            'categories' => $this->access->categories($grant)], 'attachment_max_bytes' => config('association.attachment_max_kb') === null ? null : config('association.attachment_max_kb') * 1024]])->header('Cache-Control', 'no-store');
    }

    private function home(string $module): array
    {
        return match ($module) {
            'beneficiaries', 'family-members', 'beneficiary-notes' => ['space' => 'association', 'home_module' => 'beneficiaries'],
            'forms' => ['space' => 'association', 'home_module' => 'forms'],
            'submissions', 'orphan-requests', 'orphan-children', 'guardian-details', 'amputation-requests', 'wheelchair-requests' => ['space' => 'association', 'home_module' => 'requests'],
            'categories', 'case-types' => ['space' => 'association', 'home_module' => 'categories'],
            'events', 'invitations', 'event-tasks' => ['space' => 'association', 'home_module' => 'events'],
            'attendance', 'deliveries' => ['space' => 'association', 'home_module' => 'scan'],
            'notifications', 'chat', 'support', 'calls' => ['space' => 'communication', 'home_module' => match ($module) { 'chat' => 'chat', 'support' => 'support', 'calls' => 'calls', default => 'notifications' }],
            'staff' => ['space' => 'administration', 'home_module' => 'team'],
            'sessions' => ['space' => 'administration', 'home_module' => 'accounts'],
            'settings', 'support-categories' => ['space' => 'administration', 'home_module' => 'settings'],
            'audit', 'scan-history' => ['space' => 'administration', 'home_module' => 'audit'],
            'usage' => ['space' => 'administration', 'home_module' => 'usage'],
            default => ['space' => 'association', 'home_module' => 'legacy'],
        };
    }

    public function index(Request $request, string $module)
    {
        if (in_array($module, ['chat', 'support'], true)) { $this->access->require($request->user(), $module); return app(AssociationConversationsController::class)->index($request, $module); }
        if ($module === 'support-categories') { return response()->json(['data' => app(\App\Services\Association\AssociationSupportCategories::class)->index($request->user())]); }
        if ($module === 'sessions') { return app(AssociationDevicesController::class)->index($request, $this->access); }
        if ($module === 'notifications') { return app(AssociationNotificationsController::class)->index($request, app(\App\Services\Association\AssociationNotificationAdmin::class)); }
        if (in_array($module, ['forms', 'submissions'], true)) { return app(AssociationFormsController::class)->index($request, $module); }
        if (in_array($module, ['audit', 'scan-history'], true)) { return app(AssociationOverviewController::class)->history($request, $module); }
        $grant = $this->access->require($request->user(), $module);
        $filter = $request->validate(['q' => 'nullable|string|max:120', 'page' => 'nullable|integer|min:1|max:100000',
            'per_page' => 'nullable|integer|min:1|max:100', 'status' => 'nullable|string|max:80', 'trashed' => 'sometimes|boolean',
            'event_id' => 'nullable|integer|min:1', 'telegram_user_id' => 'nullable|integer|min:1',
            'category_id' => 'nullable|integer|min:1', 'family_member_id' => 'nullable|integer|min:1',
            'beneficiary_id' => 'nullable|integer|min:1', 'user_id' => 'nullable|integer|min:1']);
        $filter = [...$filter, ...$request->validate(array_fill_keys(array_diff(array_keys(AssociationRecordRules::LOOKUPS), array_keys($filter)), 'nullable|integer|min:1'))];
        $trashed = ! empty($filter['trashed']);
        if ($trashed) { abort_unless($this->access->allows($grant, $module, 'restore'), 403); }
        $query = $this->records->query($module, $grant, $filter, $trashed);
        if ($trashed) {
            $table = $query->getModel()->getTable(); $columns = array_column($this->records->columns($module), 'name');
            $query->where(function ($q) use ($table, $columns, $module): void {
                $q->whereIn($table.'.id', \Illuminate\Support\Facades\DB::table('association_deleted_records')->where('module', $module)->select('record_id'));
                foreach (['deleted_at', 'deleted_by'] as $key) { if (in_array($key, $columns, true)) { $q->orWhereNotNull($table.'.'.$key); } }
            });
        }
        $page = $query->orderByDesc($query->getModel()->getTable().'.id')->paginate($filter['per_page'] ?? 30);

        return response()->json(['data' => [...$this->metadata($request, $grant, $module), ...($trashed ? ['can_create' => false, 'can_import' => false, 'can_export' => false] : []),
            'items' => array_map(fn ($record) => $this->entities->describe($module, $record), $page->items()),
            'total' => $page->total(), 'page' => $page->currentPage(), 'has_more' => $page->hasMorePages()]])->header('Cache-Control', 'no-store');
    }

    public function show(Request $request, string $module, int $id)
    {
        if ($module === 'support-categories') { return response()->json(['data' => app(\App\Services\Association\AssociationSupportCategories::class)->show($request->user(), $id)]); }
        if ($module === 'notifications') { return app(AssociationNotificationsController::class)->show($request, $id, app(\App\Services\Association\AssociationNotificationAdmin::class)); }
        if (in_array($module, ['forms', 'submissions'], true)) { return app(AssociationFormsController::class)->show($request, $module, $id); }
        $grant = $this->access->require($request->user(), $module);
        $request->validate(['trashed' => 'sometimes|boolean']);
        $trashed = $request->boolean('trashed');
        if ($trashed) { abort_unless($this->access->allows($grant, $module, 'restore'), 403); }
        $record = $this->records->query($module, $grant, [], $trashed)->whereKey($id)->firstOrFail();

        return response()->json(['data' => [...$this->metadata($request, $grant, $module), ...($trashed ? ['can_edit' => false, 'actions' => ['restore']] : []), 'record' => $this->entities->describe($module, $record)]])->header('Cache-Control', 'no-store');
    }

    private function metadata(Request $request, object $grant, string $module): array
    {
        $definition = AssociationModules::definition($module);
        $fields = $this->records->fields($module);
        if ($module === 'staff') {
            foreach ($fields as &$field) {
                if ($field['key'] === 'app_permissions') { $field['options'] = $grant->effective_permissions; }
                if ((! $grant->is_superadmin && in_array($field['key'], ['roles', 'permissions'], true)) ||
                    (! $this->access->allows($grant, 'staff', 'permissions') && ! in_array($field['key'], ['name', 'email', 'title', 'password', 'basic_salary'], true))) { $field['readonly'] = true; }
            }
            unset($field);
        }

        return ['module' => $module, 'title' => $definition['title'], 'title_en' => $definition['english'],
            'fields' => $fields, 'actions' => array_values(array_filter($definition['actions'], fn ($action) => $this->access->allows($grant, $module, $action))),
            'can_create' => $this->access->allows($grant, $module, 'create'), 'can_edit' => $this->access->allows($grant, $module, 'update'),
            'can_import' => $this->access->allows($grant, $module, 'import'), 'can_export' => $this->access->allows($grant, $module, 'export'),
            'action_forms' => collect($definition['actions'])->filter(fn ($action) => $this->access->allows($grant, $module, $action))
                ->mapWithKeys(fn ($action) => [$action => app(\App\Services\Association\AssociationActionForms::class)->fields($module, $action)])->all()];
    }

    public function save(Request $request, string $module, ?int $id = null)
    {
        if ($module === 'support-categories') { return response()->json(['data' => app(\App\Services\Association\AssociationSupportCategories::class)->save($request->user(), $request->all(), $id)]); }
        if ($module === 'notifications') { return app(AssociationNotificationsController::class)->save($request, app(\App\Services\Association\AssociationNotificationAdmin::class), $id); }
        if (in_array($module, ['forms', 'submissions'], true)) { return app(AssociationFormsController::class)->save($request, $module, $id); }
        return response()->json(['data' => $this->entities->save($request->user(), $module, $request->all(), $id)]);
    }

    public function action(Request $request, string $module, int $id, \App\Services\Association\AssociationActions $actions)
    {
        if ($module === 'notifications') { return app(AssociationNotificationsController::class)->action($request, $id, app(\App\Services\Association\AssociationNotificationAdmin::class)); }
        if (in_array($module, ['forms', 'submissions'], true)) { return app(AssociationFormsController::class)->action($request, $module, $id); }
        return response()->json(['data' => $actions->run($request->user(), $module, $id, $request->all())]);
    }

    public function lookup(Request $request, string $module)
    {
        $data = $request->validate(['for_module' => 'required|string|max:80', 'q' => 'nullable|string|max:120', 'page' => 'nullable|integer|min:1|max:100000']);
        $grant = $this->access->workspace($request->user());
        if ($data['for_module'] === 'support-categories' && $module === 'support-staff') {
            $this->access->require($request->user(), 'support-categories');
            $request->merge(['module' => 'support']);
            return app(AssociationConversationsController::class)->peers($request, $this->access);
        }
        abort_unless($this->access->allows($grant, $data['for_module'], 'create') || $this->access->allows($grant, $data['for_module'], 'update') || ($data['for_module'] === 'events' && $this->access->allows($grant, 'events', 'invite')), 403);
        $custom = in_array($data['for_module'], ['forms', 'notifications', 'events'], true);
        if ($module === 'app-accounts' && $data['for_module'] === 'notifications') {
            abort_unless($this->access->global($grant), 403);
            $query = \Illuminate\Support\Facades\DB::table('app_accounts')->leftJoin('users', 'users.id', '=', 'app_accounts.user_id')->where('app_accounts.active', true);
            if (! empty($data['q'])) { $query->where('users.name', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $data['q']).'%'); }
            $page = $query->select('app_accounts.id', 'users.name')->orderBy('app_accounts.id')->paginate(30);
            return response()->json(['data' => ['items' => array_map(fn ($row) => ['id' => $row->id, 'label' => $row->name ?: 'حساب رقم '.$row->id], $page->items()), 'page' => $page->currentPage(), 'has_more' => $page->hasMorePages()]])->header('Cache-Control', 'no-store');
        }
        $fields = $custom ? [] : $this->records->fields($data['for_module']);
        $lookups = $custom ? ['categories', 'beneficiaries', ...$data['for_module'] === 'notifications' ? ['events'] : []] : array_column(array_filter($fields, fn ($field) => isset($field['lookup'])), 'lookup');
        abort_unless(in_array($module, $lookups, true), 403);
        $page = $this->records->query($module, $grant, $data)->orderBy('id')->paginate(30);

        return response()->json(['data' => ['items' => array_map(function ($record): array {
            $data = $this->records->serialize($record);

            return ['id' => $data['id'], 'label' => $data['_label']];
        }, $page->items()), 'has_more' => $page->hasMorePages(), 'page' => $page->currentPage()]])->header('Cache-Control', 'no-store');
    }

    public function upload(Request $request, AssociationMedia $media)
    {
        $data = $request->validate(['module' => 'required|string|max:80', 'field' => 'required|string|max:100', 'record_id' => 'nullable|integer|min:1', 'file' => 'required|file']);
        $grant = $this->access->require($request->user(), $data['module'], isset($data['record_id']) ? 'update' : 'create');
        if (isset($data['record_id'])) {
            $this->records->query($data['module'], $grant)->whereKey($data['record_id'])->firstOrFail();
        }
        $field = collect($this->records->fields($data['module']))->firstWhere('key', $data['field']);
        abort_unless($field && $field['type'] === 'file' && empty($field['readonly']), 422, 'invalid_attachment_field');

        return response()->json(['data' => $media->store($request->user(), $data['module'], $data['field'], $request->file('file'))]);
    }
}
