<?php

namespace App\Traits;

use App\Services\Association\AssociationModules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Keep native-app deletions consistent in existing Eloquent web screens. */
trait AssociationTombstones
{
    protected static function bootAssociationTombstones(): void
    {
        static::addGlobalScope('association_deleted', function (Builder $query): void {
            if (! Schema::hasTable('association_deleted_records')) { return; }
            $short = class_basename($query->getModel());
            $modules = array_keys(array_filter(AssociationModules::DEFINITIONS, fn ($definition) => $definition['model'] === $short));
            if ($modules) {
                $query->whereNotIn($query->getModel()->qualifyColumn($query->getModel()->getKeyName()),
                    DB::table('association_deleted_records')->whereIn('module', $modules)->select('record_id'));
            }
        });
    }
}
