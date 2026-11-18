<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

final class AssociationOfflinePermits
{
    public function __construct(private AssociationAccess $access, private AssociationEntities $entities) {}

    public function issue(AppAccount $account, object $grant, Model $event): ?string
    {
        if (! $this->entities->eventActive($event)) {
            return null;
        }
        $modes = [];
        foreach (['attendance' => 'confirm', 'deliveries' => 'confirm'] as $module => $action) {
            if ($this->access->allows($grant, $module, $action)) {
                $modes[] = $module.'.'.$action;
            }
        }
        if ($this->access->allows($grant, 'deliveries', 'override')) {
            $modes[] = 'deliveries.override';
        }

        return Crypt::encryptString(json_encode(['purpose' => 'association.offline.v1', 'account' => $account->id,
            'event' => $event->getKey(), 'issued' => now()->timestamp, 'until' => $this->entities->eventEnd($event)->timestamp,
            'modes' => $modes], JSON_THROW_ON_ERROR));
    }

    public function time(AppAccount $account, Model $event, string $mode, array $input): CarbonImmutable
    {
        abort_if($event->is_archived, 409, 'event_inactive');
        if (empty($input['occurred_at'])) {
            abort_unless($this->entities->eventActive($event), 409, 'event_inactive');

            return CarbonImmutable::now(config('app.timezone'));
        }
        try {
            $permit = json_decode(Crypt::decryptString((string) ($input['offline_permit'] ?? '')), true, 16, JSON_THROW_ON_ERROR);
            $time = CarbonImmutable::parse($input['occurred_at'])->setTimezone(config('app.timezone'));
            $valid = ($permit['purpose'] ?? null) === 'association.offline.v1' && (int) ($permit['account'] ?? 0) === $account->id &&
                (int) ($permit['event'] ?? 0) === (int) $event->getKey() && in_array($mode.'.confirm', $permit['modes'] ?? [], true) &&
                $time->timestamp >= (int) $permit['issued'] - 300 && $time->timestamp <= min((int) $permit['until'], $this->entities->eventEnd($event)->timestamp) &&
                $time->timestamp >= $this->entities->eventStart($event)->timestamp &&
                $time->timestamp <= now()->addMinute()->timestamp;
            if (! empty($input['override'])) {
                $valid = $valid && in_array('deliveries.override', $permit['modes'] ?? [], true);
            }
        } catch (\Throwable) {
            $valid = false;
        }
        if (! $valid) {
            throw ValidationException::withMessages(['offline_permit' => 'تفويض العملية دون اتصال غير صالح. حدّث بيانات الفعالية وراجع العملية المعلقة.']);
        }

        return $time;
    }
}
