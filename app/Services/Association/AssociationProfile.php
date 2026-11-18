<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class AssociationProfile
{
    public const PUBLIC_FIELDS = ['full_name', 'national_id', 'birth_date', 'gender', 'address', 'whatsapp', 'egypt_number', 'telegram_number', 'governorate', 'city',
        'marital_status', 'housing_status', 'entry_date', 'entry_method', 'rent_value', 'landlord_phone', 'personal_photo'];
    public const DIRECT_FIELDS = ['address', 'whatsapp', 'egypt_number', 'telegram_number'];

    public function __construct(private AssociationPortal $portal, private AssociationAccess $access, private AssociationRecords $records,
        private AssociationOperations $operations, private AssociationRecordRules $rules, private AssociationMedia $media, private AssociationIdentities $identities) {}

    private function settings(int $id): ?object { return DB::table('association_profile_settings')->where('beneficiary_id', $id)->first(); }
    private function revision(TelegramUser $person, ?object $settings): array { return ['record' => $person->getAttributes(), 'profile' => (array) $settings]; }
    public function fields(): array
    {
        $fields = array_values(array_filter($this->records->fields('beneficiaries'), fn ($field) => in_array($field['key'], self::PUBLIC_FIELDS, true)));
        if (! in_array('personal_photo', array_column($fields, 'key'), true)) { $fields[] = ['key' => 'personal_photo', 'label' => 'الصورة الشخصية', 'label_en' => 'Personal photo', 'type' => 'file']; }

        return $fields;
    }
    private function editable(?object $settings): array
    {
        $extra = $settings && (! $settings->editable_until || \Carbon\CarbonImmutable::parse($settings->editable_until)->isFuture()) ? json_decode($settings->editable_fields ?? '[]', true) : [];
        return array_values(array_unique([...self::DIRECT_FIELDS, ...($extra ?: [])]));
    }
    public function show(AppAccount $account): array
    {
        $person = $this->portal->person($account);
        $settings = $this->settings($person->id);
        $values = array_intersect_key($person->getAttributes(), array_flip(self::PUBLIC_FIELDS));
        $values['personal_photo'] = $settings?->personal_photo;
        return ['record' => $values, 'id' => $person->id, 'access_code' => $person->access_code, 'qr_payload' => $person->national_id, 'fields' => $this->fields(),
            'editable_fields' => $this->editable($settings), 'editable_until' => $settings?->editable_until, '_revision' => $this->operations->revision($this->revision($person, $settings))];
    }
    public function openFields(AppAccount $account, int $id, array $input): array
    {
        $grant = $this->access->require($account, 'beneficiaries', 'update');
        $this->records->query('beneficiaries', $grant)->whereKey($id)->firstOrFail();
        $data = Validator::make($input, ['fields' => 'present|array|max:30', 'fields.*' => ['string', 'distinct', \Illuminate\Validation\Rule::in(array_column($this->fields(), 'key'))],
            'until' => 'nullable|date|after:now', 'reason' => 'required|string|min:3|max:2000'])->validate();
        return $this->operations->run($account, 'profiles.'.$id.'.open_fields', $input, function () use ($account, $grant, $id, $data): array {
            $this->records->query('beneficiaries', $grant)->whereKey($id)->lockForUpdate()->firstOrFail();
            $before = $this->settings($id);
            DB::table('association_profile_settings')->updateOrInsert(['beneficiary_id' => $id], ['editable_fields' => json_encode($data['fields']),
                'editable_until' => empty($data['until']) ? null : \Carbon\CarbonImmutable::parse($data['until'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                'opened_by' => $grant->staff_id, 'revision' => ($before->revision ?? 0) + 1, 'updated_at' => now()]);
            $this->operations->audit($account, $grant, 'beneficiaries', $id, 'profile_fields_opened', $before, $this->settings($id), $data['reason']);
            app(AssociationNotifications::class)->beneficiary($id, $grant->staff_id, 'تحديث ملفك في الجمعية', $data['fields'] ? 'سمحت الإدارة بتصحيح حقول محددة في ملفك. افتح ملفي لدى الجمعية لمراجعتها.' : 'انتهى تفويض تصحيح الحقول الإضافية.', ['type' => 'profile']);

            return ['updated' => true, 'fields' => $data['fields']];
        }, fn () => $this->records->query('beneficiaries', $grant)->whereKey($id)->firstOrFail());
    }
    public function update(AppAccount $account, array $input): array
    {
        $person = $this->portal->person($account);
        Validator::make($input, ['record' => 'required|array|max:30'])->validate();
        return $this->operations->run($account, 'profile.update', $input, function () use ($account, $person, $input): array {
            $person = TelegramUser::whereKey($person->id)->lockForUpdate()->firstOrFail();
            $settings = $this->settings($person->id);
            $this->operations->expect($this->revision($person, $settings), $input);
            abort_if(array_diff(array_keys($input['record']), $this->editable($settings)), 422, 'profile_fields_not_open_for_edit');
            $values = $input['record'];
            $photoChanged = array_key_exists('personal_photo', $values);
            $photo = $values['personal_photo'] ?? null; unset($values['personal_photo']);
            $grant = (object) ['is_superadmin' => false, 'association_global' => false, 'association_categories' => json_encode($person->category_id ? [(int) $person->category_id] : []), 'staff_id' => $account->user_id];
            $validated = $this->rules->validate($account, $grant, 'beneficiaries', $values, $person);
            if (isset($validated['national_id'])) {
                $validated['national_id'] = $this->identities->normalize($validated['national_id']);
                $this->identities->lock([$person->national_id, $validated['national_id']]);
                $this->identities->available($validated['national_id'], $person->id);
            }
            $before = $this->revision($person, $settings);
            $person->forceFill([...$validated, 'updated_by' => $account->user_id])->save();
            $profile = ['editable_fields' => '[]', 'editable_until' => null, 'revision' => ($settings->revision ?? 0) + 1, 'updated_at' => now()];
            if ($photoChanged) {
                $photo = $this->media->value($account, 'profile', 'personal_photo', $photo, $settings?->personal_photo, (string) $person->id);
                if ($photo) {
                    $mime = DB::table('association_uploads')->where('id', $this->media->id($photo))->value('mime');
                    abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true), 422, 'profile_photo_must_be_image');
                }
                $profile['personal_photo'] = $photo;
                $this->media->bind($account, 'profile', (string) $person->id, ['personal_photo' => $photo]);
            }
            // Consume only fields the beneficiary actually corrected. Opening
            // unrelated fields remains an explicit staff decision.
            $opened = array_values(array_diff(json_decode($settings->editable_fields ?? '[]', true) ?: [], array_keys($input['record'])));
            $profile['editable_fields'] = json_encode($opened);
            $profile['editable_until'] = $settings?->editable_until;
            DB::table('association_profile_settings')->updateOrInsert(['beneficiary_id' => $person->id], $profile);
            $this->operations->audit($account, (object) ['staff_id' => null], 'beneficiaries', $person->id, 'beneficiary_profile_update', $before,
                ['changed_fields' => array_keys($input['record']), 'record' => $person->getAttributes()]);

            return $this->show($account);
        });
    }
    public function attachmentContext(AppAccount $account, int $id): void
    {
        $person = $this->portal->person($account);
        abort_unless($person->id === $id && in_array('personal_photo', $this->editable($this->settings($id)), true), 403, 'profile_photo_not_open_for_edit');
    }
}
