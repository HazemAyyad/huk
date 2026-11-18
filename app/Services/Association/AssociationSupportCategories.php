<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class AssociationSupportCategories
{
    public function __construct(private AssociationAccess $access, private AssociationOperations $operations) {}
    private function describe(object $row): array
    {
        return [...(array) $row, 'staff_accounts' => json_decode($row->staff_accounts, true), '_label' => $row->name, '_revision' => $this->operations->revision($row)];
    }
    public function index(AppAccount $account): array
    {
        $grant = $this->access->require($account, 'support-categories');
        $items = array_map(fn ($row) => $this->describe($row), DB::table('association_support_categories')->orderBy('id')->get()->all());
        return ['title' => 'فئات الدعم الفني', 'items' => $items, 'total' => count($items),
            'fields' => [['key' => 'name', 'label' => 'اسم الفئة', 'type' => 'text', 'required' => true], ['key' => 'name_en', 'label' => 'الاسم بالإنجليزية', 'type' => 'text'],
                ['key' => 'staff_accounts', 'label' => 'الموظفون المختصون', 'type' => 'lookup_many', 'lookup' => 'support-staff', 'required' => true], ['key' => 'active', 'label' => 'مفعّلة', 'type' => 'bool', 'default' => true]],
            'can_create' => $this->access->allows($grant, 'support-categories', 'create'), 'can_edit' => $this->access->allows($grant, 'support-categories', 'update'),
            'actions' => [], 'has_more' => false, 'page' => 1];
    }
    public function show(AppAccount $account, int $id): array
    {
        $metadata = $this->index($account);
        return [...$metadata, 'record' => $this->describe(DB::table('association_support_categories')->where('id', $id)->first() ?? abort(404))];
    }
    public function save(AppAccount $account, array $input, ?int $id): array
    {
        $grant = $this->access->require($account, 'support-categories', $id ? 'update' : 'create');
        return $this->operations->run($account, 'support-categories.'.($id ?? 'new').'.save', $input, function () use ($account, $grant, $input, $id): array {
            $old = $id ? DB::table('association_support_categories')->where('id', $id)->lockForUpdate()->first() ?? abort(404) : null;
            if ($old) { $this->operations->expect($old, $input); }
            $record = Validator::make($input, ['record' => 'required|array:name,name_en,staff_accounts,active'])->validate()['record'];
            $data = Validator::make([...($old ? ['name' => $old->name, 'name_en' => $old->name_en, 'staff_accounts' => json_decode($old->staff_accounts, true), 'active' => (bool) $old->active] : ['active' => true]), ...$record], ['name' => 'required|string|max:255', 'name_en' => 'nullable|string|max:255', 'staff_accounts' => 'required|array|min:1|max:100',
                'staff_accounts.*' => 'integer|distinct|min:1', 'active' => 'required|boolean'])->validate();
            foreach ($data['staff_accounts'] as $peer) { $staff = AppAccount::whereKey($peer)->where('active', true)->firstOrFail(); $this->access->require($staff, 'support'); }
            $data['staff_accounts'] = json_encode($data['staff_accounts']); $data['updated_at'] = now();
            if ($id) { DB::table('association_support_categories')->where('id', $id)->update($data); }
            else { $id = DB::table('association_support_categories')->insertGetId([...$data, 'created_at' => now()]); }
            $new = DB::table('association_support_categories')->where('id', $id)->first();
            $this->operations->audit($account, $grant, 'support-categories', $id, $old ? 'update' : 'create', $old, $new);

            return ['record' => $this->describe($new)];
        });
    }
    public function portal(): array
    {
        $items = [];
        foreach (DB::table('association_support_categories')->where('active', true)->orderBy('id')->cursor() as $row) {
            $staff = array_filter(json_decode($row->staff_accounts, true), function ($id): bool { $account = AppAccount::find($id); return $account && $this->access->can($account, 'support'); });
            if ($staff) { $items[] = ['id' => $row->id, 'name' => $row->name, 'name_en' => $row->name_en]; }
        }
        return ['items' => $items];
    }
}
