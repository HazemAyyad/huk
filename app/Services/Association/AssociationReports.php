<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Illuminate\Support\Facades\DB;

final class AssociationReports
{
    public function __construct(private AssociationAccess $access) {}
    public function usage(AppAccount $account): array
    {
        $grant = $this->access->require($account, 'usage');
        $accounts = DB::table('app_accounts')->where('active', true)->whereNotIn('id', DB::table('association_account_policies')->where('app_enabled', false)->select('account_id'));
        $devices = DB::table('association_devices')->whereNull('revoked_at')->whereIn('account_id', (clone $accounts)->select('id'));
        $active = (clone $devices)->where('last_seen_at', '>=', now()->subMinutes(config('association.presence_minutes')))->distinct()->count('account_id');
        $daily = DB::table('association_usage_days')->where('day', '>=', now()->subDays(13)->toDateString())->whereIn('account_id', (clone $accounts)->select('id'))->selectRaw('day, COUNT(*) as accounts')->groupBy('day')->pluck('accounts', 'day');
        $history = [];
        for ($i = 13; $i >= 0; $i--) { $day = now()->subDays($i)->toDateString(); $history[] = ['day' => $day, 'accounts' => (int) ($daily[$day] ?? 0)]; }
        return ['as_of' => now()->toIso8601String(), 'presence_window_minutes' => config('association.presence_minutes'), 'daily' => $history,
            'can_export' => $this->access->allows($grant, 'usage', 'export'), 'metrics' => [
            ['key' => 'accounts', 'label' => 'حسابات التطبيق النشطة', 'value' => (clone $accounts)->count()],
            ['key' => 'association_accounts', 'label' => 'حسابات مرتبطة بالجمعية', 'value' => (clone $accounts)->where('association_enabled', true)->whereNotNull('user_id')->count()],
            ['key' => 'religious_accounts', 'label' => 'حسابات مرتبطة بالمحتوى الديني', 'value' => (clone $accounts)->where('religious_enabled', true)->whereNotNull('religious_account_id')->count()],
            ['key' => 'linked_accounts', 'label' => 'حسابات تجمع الجمعية والمحتوى الديني', 'value' => (clone $accounts)->where('association_enabled', true)->where('religious_enabled', true)->whereNotNull('user_id')->whereNotNull('religious_account_id')->count()],
            ['key' => 'active_now', 'label' => 'نشط خلال نافذة الاتصال الحالية', 'value' => $active],
            ['key' => 'active_today', 'label' => 'حسابات استخدمت التطبيق اليوم', 'value' => (int) ($daily[now()->toDateString()] ?? 0)],
            ['key' => 'registered_devices', 'label' => 'أجهزة مرتبطة غير مفصولة', 'value' => (clone $devices)->count()],
            ['key' => 'store_downloads', 'label' => 'تنزيلات متاجر التطبيقات', 'value' => null, 'note' => 'لم يُربط مصدر قياس المتاجر. عدد الحسابات والأجهزة لا يمثل عدد التنزيلات.'],
        ]];
    }

    public function settings(AppAccount $account): array
    {
        $grant = $this->access->require($account, 'settings');
        $preferences = [];
        foreach (DB::table('association_preferences')->where('key', 'like', 'system.%')->get() as $row) { $preferences[substr($row->key, 7)] = json_decode($row->value, true); }

        return ['record' => $preferences, 'revision' => app(AssociationOperations::class)->revision($preferences), 'can_edit' => $this->access->allows($grant, 'settings', 'update'), 'services' => [
            ['key' => 'push', 'label' => 'ربط إشعارات الهاتف', 'ready' => app(AssociationPush::class)->ready()],
            ['key' => 'upload_worker', 'label' => 'آخر معالجة للمرفقات', 'last_run' => DB::table('association_preferences')->where('key', 'runtime.upload_worker')->value('value')],
            ['key' => 'push_worker', 'label' => 'آخر معالجة للإشعارات', 'last_run' => DB::table('association_preferences')->where('key', 'runtime.push_worker')->value('value')],
        ], 'attachment_max_bytes' => config('association.attachment_max_kb') === null ? null : config('association.attachment_max_kb') * 1024,
            'beneficiary_device_limit' => 1, 'event_duration_hours' => config('association.event_duration_hours')];
    }

    public function saveSettings(AppAccount $account, array $input): array
    {
        $grant = $this->access->require($account, 'settings', 'update');
        $data = \Illuminate\Support\Facades\Validator::make($input, ['expected_revision' => 'required|string|size:64', 'record' => 'required|array:contact_phone,contact_email,support_instructions,support_instructions_en', 'record.contact_phone' => 'nullable|string|max:100',
            'record.contact_email' => 'nullable|email|max:255', 'record.support_instructions' => 'nullable|string|max:5000', 'record.support_instructions_en' => 'nullable|string|max:5000'])->validate()['record'];
        abort_if(array_diff(array_keys($input['record']), ['contact_phone', 'contact_email', 'support_instructions', 'support_instructions_en']), 422, 'unsupported_setting');
        return app(AssociationOperations::class)->run($account, 'settings.save', $input, function () use ($account, $grant, $input, $data): array {
            // The singleton lock also serializes creation of previously absent keys.
            DB::table('association_preferences')->insertOrIgnore(['key' => 'runtime.settings_lock', 'value' => '1', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('association_preferences')->where('key', 'runtime.settings_lock')->lockForUpdate()->first();
            $current = $this->settings($account);
            abort_unless(hash_equals($current['revision'], $input['expected_revision'] ?? ''), 409, 'settings_changed');
            foreach ($data as $key => $value) { DB::table('association_preferences')->updateOrInsert(['key' => 'system.'.$key], ['value' => json_encode($value, JSON_THROW_ON_ERROR), 'updated_by' => $grant->staff_id, 'updated_at' => now()]); }
            app(AssociationOperations::class)->audit($account, $grant, 'settings', null, 'update', $current['record'], $data);

            return $this->settings($account);
        });
    }

    public function preferences(AppAccount $account, ?array $data = null): array
    {
        $this->access->workspace($account);
        $key = 'staff.'.$account->id.'.copy_sent_text';
        if ($data !== null) {
            $validated = \Illuminate\Support\Facades\Validator::make($data, ['copy_sent_text' => 'required|boolean'])->validate();
            DB::table('association_preferences')->updateOrInsert(['key' => $key], ['value' => json_encode($validated['copy_sent_text']), 'updated_by' => $account->user_id, 'updated_at' => now()]);
        }
        return ['copy_sent_text' => json_decode(DB::table('association_preferences')->where('key', $key)->value('value') ?? 'false', true) === true];
    }
}
