<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Association\AssociationAccess;
use App\Services\Association\AssociationImports;
use App\Services\Association\AssociationRecordRules;
use App\Services\Association\AssociationRecords;
use Illuminate\Http\Request;

final class AssociationExchangeController extends Controller
{
    public function preview(Request $request, string $module, AssociationImports $imports) { return response()->json(['data' => $imports->preview($request->user(), $module, $request->all())])->header('Cache-Control', 'no-store'); }
    public function apply(Request $request, string $module, AssociationImports $imports) { return response()->json(['data' => $imports->apply($request->user(), $module, $request->all())]); }

    private function cell(mixed $value): string
    {
        $value = is_array($value) || is_object($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) : (string) $value;
        return preg_match('/^[\s]*[=+\-@]/u', $value) ? "'".$value : $value;
    }

    public function export(Request $request, string $module, AssociationAccess $access, AssociationRecords $records)
    {
        $grant = $access->require($request->user(), $module, 'export');
        $rules = ['q' => 'nullable|string|max:120', 'status' => 'nullable|string|max:80', 'from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from', 'metadata' => 'sometimes|boolean'];
        $rules += array_fill_keys(array_keys(AssociationRecordRules::LOOKUPS), 'nullable|integer|min:1');
        $rules['form_id'] = 'nullable|integer|min:1';
        $filter = $request->validate($rules);
        $dataset = app(\App\Services\Association\AssociationExportRows::class)->dataset($request->user(), $module, $grant, $filter);
        $columns = $dataset['columns'];
        $filename = 'haq-'.$module.'-'.now()->format('Y-m-d-His').'.csv';
        if ($request->boolean('metadata')) {
            $sample = [];
            foreach (($dataset['rows'])() as $row) { $sample[] = $row; if (count($sample) >= 20) { break; } }
            return response()->json(['data' => ['name' => $filename, 'rows' => $dataset['count'], 'columns' => $columns, 'sample' => $sample]])->header('Cache-Control', 'private, no-store');
        }
        app(\App\Services\Association\AssociationOperations::class)->audit($request->user(), $grant, $module, null, 'export', null, ['filter' => $filter, 'last_record_id' => $dataset['last']]);

        return response()->streamDownload(function () use ($dataset, $columns): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $columns, ',', '"', '');
            foreach (($dataset['rows'])() as $values) {
                fputcsv($stream, array_map(fn ($column) => $this->cell($values[$column] ?? null), $columns), ',', '"', '');
            }
            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
