<?php

namespace Tests\Feature;

use App\Services\Association\AssociationMedia;
use App\Services\Association\AssociationSheetRows;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Support\AssociationTestCase;

final class AssociationExchangeTest extends AssociationTestCase
{
    private const BASE = '/api/v2/admin/association/';

    private function sheet($account, string $csv): string
    {
        return app(AssociationMedia::class)->store($account, 'beneficiaries', '_import', UploadedFile::fake()->createWithContent('beneficiaries.csv', $csv))['id'];
    }

    public function test_preview_never_writes_people_and_confirmed_import_is_replay_safe(): void
    {
        Storage::fake('local');
        $category = $this->category('Imported');
        [$account] = $this->actor();
        $this->withToken($this->token($account));
        $upload = $this->sheet($account, "الاسم,رقم الهوية\nالأول,١٢٣٤٥٦٧٨٩\nالثاني,987654321\n");
        $preview = $this->postJson(self::BASE.'imports/beneficiaries/preview', ['upload_id' => $upload, 'mode' => 'create', 'category_ids' => [$category]])
            ->assertOk()->assertJsonPath('data.can_apply', true)->assertJsonPath('data.creates', 2)->json('data.preview_id');
        $this->assertSame(0, DB::table('telegram_users')->count());
        $input = ['operation_id' => (string) Str::uuid(), 'preview_id' => $preview];
        $this->postJson(self::BASE.'imports/beneficiaries/apply', $input)->assertOk()->assertJsonPath('data.imported', 2);
        $this->postJson(self::BASE.'imports/beneficiaries/apply', $input)->assertOk()->assertJsonPath('data.imported', 2);
        $this->assertSame(2, DB::table('telegram_users')->count());
        $this->assertSame(2, DB::table('categories')->value('last_number'));
        $this->assertSame(['123456789', '987654321'], DB::table('telegram_users')->orderBy('id')->pluck('national_id')->all());
        $this->postJson(self::BASE.'imports/beneficiaries/apply', ['operation_id' => (string) Str::uuid(), 'preview_id' => $preview])->assertConflict();
    }

    public function test_duplicates_invalid_rows_and_another_owners_upload_cannot_be_imported(): void
    {
        Storage::fake('local');
        $category = $this->category();
        [$account] = $this->actor();
        [$other] = $this->actor();
        $upload = $this->sheet($account, "full_name,national_id\nOne,123456789\nDuplicate,123456789\n");
        $otherUpload = $this->sheet($other, "full_name,national_id\nOther,987654321\n");
        $this->withToken($this->token($account));
        $this->postJson(self::BASE.'imports/beneficiaries/preview', ['upload_id' => $otherUpload, 'mode' => 'create'])->assertNotFound();
        $preview = $this->postJson(self::BASE.'imports/beneficiaries/preview', ['upload_id' => $upload, 'mode' => 'create', 'category_ids' => [$category]])
            ->assertOk()->assertJsonPath('data.can_apply', false)->assertJsonCount(1, 'data.errors')->json('data.preview_id');
        $this->postJson(self::BASE.'imports/beneficiaries/apply', ['operation_id' => (string) Str::uuid(), 'preview_id' => $preview])->assertUnprocessable();
        $this->assertSame(0, DB::table('telegram_users')->count());
    }

    public function test_changes_after_preview_roll_back_the_entire_import_including_issued_codes(): void
    {
        Storage::fake('local');
        $category = $this->category('Import');
        $old = $this->beneficiary($category, '987654321');
        [$account] = $this->actor();
        $upload = $this->sheet($account, "full_name,national_id\nNew,123456789\nUpdated,987654321\n");
        $this->withToken($this->token($account));
        $preview = $this->postJson(self::BASE.'imports/beneficiaries/preview', ['upload_id' => $upload, 'mode' => 'upsert', 'category_ids' => [$category]])
            ->assertOk()->assertJsonPath('data.can_apply', true)->json('data.preview_id');
        DB::table('telegram_users')->where('id', $old)->update(['address' => 'Changed after preview']);
        $this->postJson(self::BASE.'imports/beneficiaries/apply', ['operation_id' => (string) Str::uuid(), 'preview_id' => $preview])->assertConflict();
        $this->assertSame(1, DB::table('telegram_users')->count());
        $this->assertSame(0, DB::table('categories')->value('last_number'));
        $this->assertSame(0, DB::table('association_operations')->count());
    }

    public function test_export_applies_category_scope_and_protects_spreadsheet_formula_cells(): void
    {
        $allowed = $this->category('Allowed');
        $hidden = $this->category('Hidden');
        $one = $this->beneficiary($allowed, '123456789');
        $this->beneficiary($hidden, '987654321');
        DB::table('telegram_users')->where('id', $one)->update(['full_name' => '=HYPERLINK("https://example.test")']);
        [$account] = $this->actor(['admin.enter', 'association.enter', 'association.beneficiaries.export'], 'exporter', false, [$allowed]);
        $this->withToken($this->token($account));
        $response = $this->get(self::BASE.'exports/beneficiaries')->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('123456789', $csv);
        $this->assertStringNotContainsString('987654321', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertSame(1, DB::table('association_audit')->where('action', 'export')->count());
        $this->get(self::BASE.'exports/staff')->assertForbidden();
    }

    public function test_excel_preserves_formatted_identity_and_refuses_formula_execution(): void
    {
        Storage::fake('local');
        $path = Storage::disk('local')->path('sheet.xlsx');
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->fromArray([['full_name', 'national_id'], ['First', 12345678]]);
        $sheet->getStyle('B2')->getNumberFormat()->setFormatCode('000000000');
        (new Xlsx($book))->save($path);
        $rows = app(AssociationSheetRows::class)->read($path, 'sheet.xlsx', 5000);
        $this->assertSame('012345678', $rows[1][1]);
        $sheet->setCellValue('A2', '=1+1');
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(AssociationSheetRows::class)->read($path, 'sheet.xlsx', 5000);
    }

    public function test_ramadan_preview_reports_a_deleted_identity_as_a_row_error_before_any_write(): void
    {
        Storage::fake('local');
        Schema::create('ramadan_beneficiaries', function (Blueprint $t): void {
            $t->id(); $t->string('full_name'); $t->string('national_id')->unique(); $t->integer('daily_meals'); $t->timestamps(); $t->softDeletes();
        });
        DB::table('ramadan_beneficiaries')->insert(['full_name' => 'Old', 'national_id' => '123456789', 'daily_meals' => 1, 'deleted_at' => now()]);
        [$account] = $this->actor();
        $upload = app(AssociationMedia::class)->store($account, 'ramadan-beneficiaries', '_import', UploadedFile::fake()->createWithContent('ramadan.csv', "full_name,national_id,daily_meals\nNew,١٢٣٤٥٦٧٨٩,2\n"))['id'];
        $this->withToken($this->token($account))->postJson(self::BASE.'imports/ramadan-beneficiaries/preview', ['upload_id' => $upload, 'mode' => 'create'])
            ->assertOk()->assertJsonPath('data.can_apply', false)->assertJsonCount(1, 'data.errors');
        $this->assertSame(1, DB::table('ramadan_beneficiaries')->count());
    }
}
