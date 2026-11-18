<?php

namespace App\Services\Association;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AssociationCaseCodes
{
    public function next(Model $model, string $prefix): string
    {
        if (! Schema::hasTable('association_sequences')) {
            $last = DB::table($model->getTable())->orderByDesc('id')->value('case_code');

            return $prefix.((int) substr((string) $last, strlen($prefix)) + 1);
        }

        return DB::transaction(function () use ($model, $prefix): string {
            $key = $model->getTable().':'.$prefix;
            DB::table('association_sequences')->insertOrIgnore(['module_key' => $key, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);
            $sequence = DB::table('association_sequences')->where('module_key', $key)->lockForUpdate()->first();
            $last = (int) $sequence->last_number;
            if ($last === 0) {
                foreach (DB::table($model->getTable())->select('case_code')->where('case_code', 'like', $prefix.'%')->cursor() as $existing) {
                    if (preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/D', (string) $existing->case_code, $match)) {
                        $last = max($last, (int) $match[1]);
                    }
                }
            }
            do {
                $code = $prefix.(++$last);
            } while (DB::table($model->getTable())->where('case_code', $code)->exists());
            DB::table('association_sequences')->where('module_key', $key)->update(['last_number' => $last, 'updated_at' => now()]);

            return $code;
        }, 3);
    }
}
