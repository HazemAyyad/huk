<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AssociationExportRows
{
    public function __construct(private AssociationAccess $access, private AssociationRecords $records) {}

    public function dataset(AppAccount $account, string $module, object $grant, array $filter): array
    {
        if ($module === 'usage') {
            $usage = app(AssociationReports::class)->usage($account);
            $rows = [];
            foreach ($usage['metrics'] as $metric) { $rows[] = ['kind' => 'summary', 'key' => $metric['key'], 'label' => $metric['label'], 'day' => null, 'value' => $metric['value'], 'note' => $metric['note'] ?? null]; }
            foreach ($usage['daily'] as $day) { $rows[] = ['kind' => 'daily', 'key' => 'active_accounts', 'label' => 'حسابات استخدمت التطبيق', 'day' => $day['day'], 'value' => $day['accounts'], 'note' => null]; }
            return ['columns' => ['kind', 'key', 'label', 'day', 'value', 'note'], 'count' => count($rows), 'last' => null, 'rows' => function () use ($rows) { yield from $rows; }];
        }
        $definition = AssociationModules::DEFINITIONS[$module];
        if ($definition['model']) {
            $query = $this->records->query($module, $grant, $filter);
            $table = $query->getModel()->getTable();
            $columns = array_column($this->records->columns($module), 'name');
        } else {
            $table = match ($module) {
                'forms' => 'association_forms', 'submissions' => 'association_form_submissions', 'notifications' => 'association_notifications',
                'audit' => 'association_audit', 'scan-history' => 'association_scan_history', 'support' => 'association_threads', 'calls' => 'association_calls', default => abort(404),
            };
            $query = match ($module) {
                'forms', 'submissions' => app(AssociationForms::class)->adminQuery($grant, $module, $filter),
                'notifications' => app(AssociationNotificationAdmin::class)->query($grant),
                default => DB::table($table),
            };
            $columns = Schema::getColumnListing($table);
            if ($module === 'scan-history' && ! $this->access->global($grant)) {
                $query->where(fn ($q) => $q->where('account_id', $account->id)->orWhereIn('beneficiary_id', $this->access->beneficiaryQuery($grant)->select('telegram_users.id')));
            }
            if ($module === 'support') {
                $query->where('kind', 'support')->whereIn('id', DB::table('association_thread_members')->where('account_id', $account->id)->where('active', true)->select('thread_id'));
                abort_if((clone $query)->count() > config('association.export_row_limit'), 422, 'export_row_limit_exceeded');
                $ids = [];
                foreach ((clone $query)->select('id')->cursor() as $row) { if (app(AssociationConversations::class)->canRead($account, $row->id)) { $ids[] = $row->id; } }
                $query->whereIn('id', $ids);
            }
            if ($module === 'calls' && ! $this->access->global($grant)) { $query->where('caller_id', $account->id); }
            if (! empty($filter['status']) && in_array('status', $columns, true)) { $query->where('status', $filter['status']); }
            if (! empty($filter['q']) && ! in_array($module, ['forms', 'submissions'], true)) {
                $search = '%'.str_replace(['%', '_'], ['\%', '\_'], $filter['q']).'%';
                $keys = array_values(array_intersect(match ($module) { 'audit' => ['module', 'action', 'record_id', 'reason'], 'scan-history' => ['mode', 'result', 'beneficiary_id', 'event_id'], default => ['title', 'subject', 'reason'] }, $columns));
                if ($keys) { $query->where(function ($q) use ($keys, $search): void { foreach ($keys as $key) { $q->orWhere($key, 'like', $search); } }); }
            }
        }
        if (in_array('created_at', $columns, true)) {
            foreach (['from' => '>=', 'to' => '<='] as $key => $operator) { if (isset($filter[$key])) { $query->whereDate($table.'.created_at', $operator, $filter[$key]); } }
        }
        $last = (clone $query)->max($table.'.id');
        $query->where($table.'.id', '<=', $last ?? 0);
        $count = (clone $query)->count();
        abort_if($count > config('association.export_row_limit'), 422, 'ضيّق فلاتر التقرير لتصدير الكشف على دفعات.');
        $columns = array_values(array_filter($columns, fn ($column) => ! preg_match('/password|token|secret|credential|api_key|private_key|device_hash|room_name|^room$/i', $column)));
        return ['columns' => $columns, 'count' => $count, 'last' => $last, 'rows' => function () use ($query, $table, $columns, $module, $account) {
            foreach ($query->orderBy($table.'.id')->cursor() as $record) {
                // Membership can be withdrawn while the stream is being read.
                if ($module === 'support' && ! app(AssociationConversations::class)->canRead($account, $record->id)) { continue; }
                yield array_intersect_key($record instanceof Model ? $record->attributesToArray() : (array) $record, array_flip($columns));
            }
        }];
    }
}
