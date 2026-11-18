<?php

namespace App\Services\Association;

use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

final class AssociationSheetRows
{
    public function read(string $path, string $name, int $limit): array
    {
        $extension = mb_strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (! in_array($extension, ['csv', 'xlsx', 'xls'], true)) { throw ValidationException::withMessages(['file' => 'اختر ملف CSV أو Excel صالحًا.']); }
        if ($extension === 'csv') {
            $stream = fopen($path, 'rb');
            abort_unless($stream, 503, 'import_file_unavailable');
            $rows = [];
            try {
                while (($row = fgetcsv($stream, 1024 * 1024, ',', '"', '')) !== false) {
                    if ($row === [null]) { continue; }
                    $rows[] = $row;
                    if (count($rows) > $limit + 1) { throw ValidationException::withMessages(['file' => 'قسّم الكشف إلى ملفات بحد أقصى '.$limit.' صف بيانات لكل معاينة.']); }
                    if (count($row) > 200) { throw ValidationException::withMessages(['file' => 'عدد أعمدة الكشف كبير جدًا.']); }
                }
            } finally { fclose($stream); }
            return $rows;
        }
        abort_if($extension === 'xlsx' && ! class_exists(\ZipArchive::class), 503, 'excel_reader_requires_zip_extension');
        try {
            $reader = IOFactory::createReaderForFile($path, [$extension === 'xls' ? 'Xls' : 'Xlsx']);
            $info = $reader->listWorksheetInfo($path);
            abort_unless($info && $info[0]['totalRows'] <= $limit + 1 && $info[0]['totalColumns'] <= 200, 422, 'import_sheet_dimensions_exceeded');
            $reader->setReadDataOnly(false);
            $reader->setLoadSheetsOnly([$info[0]['worksheetName']]);
            $reader->setReadFilter(new class($limit) implements IReadFilter {
                public function __construct(private int $limit) {}
                public function readCell($columnAddress, $row, $worksheetName = '') { return $row <= $this->limit + 1; }
            });
            $book = $reader->load($path);
            try {
                $sheet = $book->getSheet(0);
                $rows = [];
                foreach ($sheet->getRowIterator(1, $info[0]['totalRows']) as $row) {
                    $values = [];
                    $cells = $row->getCellIterator('A', $info[0]['lastColumnLetter']);
                    $cells->setIterateOnlyExistingCells(false);
                    foreach ($cells as $cell) {
                        abort_if($cell->isFormula(), 422, 'import_formulas_not_supported');
                        $values[] = $cell->getFormattedValue();
                    }
                    $rows[] = $values;
                }
                return $rows;
            } finally { $book->disconnectWorksheets(); }
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { throw $exception; }
        catch (\Throwable) { throw ValidationException::withMessages(['file' => 'تعذّرت قراءة ملف Excel. تحقق من الملف والأعمدة.']); }
    }
}
