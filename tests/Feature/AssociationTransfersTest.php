<?php

namespace Tests\Feature;

use App\Services\Association\AssociationTransfers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationTransfersTest extends AssociationTestCase
{
    public function test_resumable_parts_are_immutable_and_assembled_with_end_to_end_integrity(): void
    {
        Storage::fake('local');
        config(['association.upload_chunk_bytes' => 5]);
        [$account] = $this->actor();
        $this->withToken($this->token($account));
        $id = (string) Str::uuid();
        $url = '/api/v2/admin/association/transfers';
        $input = ['id' => $id, 'module' => 'beneficiaries', 'field' => 'personal_photo', 'name' => 'anything.bin', 'bytes' => 11, 'sha256' => hash('sha256', 'hello world')];
        $this->postJson($url, $input)->assertOk()->assertJsonPath('data.next_part', 0);
        $this->postJson($url, $input)->assertOk();
        $this->postJson($url, [...$input, 'bytes' => 12])->assertConflict();
        $this->post($url.'/'.$id.'/parts/1', ['file' => UploadedFile::fake()->createWithContent('part', ' worl')])->assertOk()->assertJsonPath('data.next_part', 0);
        $this->postJson($url.'/'.$id.'/finish')->assertConflict();
        $this->post($url.'/'.$id.'/parts/0', ['file' => UploadedFile::fake()->createWithContent('part', 'hello')])->assertOk()->assertJsonPath('data.next_part', 2);
        $this->post($url.'/'.$id.'/parts/0', ['file' => UploadedFile::fake()->createWithContent('part', 'hello')])->assertOk();
        $this->post($url.'/'.$id.'/parts/0', ['file' => UploadedFile::fake()->createWithContent('part', 'other')])->assertConflict();
        $this->post($url.'/'.$id.'/parts/2', ['file' => UploadedFile::fake()->createWithContent('part', 'd')])->assertOk();
        $this->postJson($url.'/'.$id.'/finish')->assertAccepted()->assertJsonPath('data.status', 'assembling');
        app(AssociationTransfers::class)->assemble($id);
        $saved = $this->getJson($url.'/'.$id)->assertOk()->assertJsonPath('data.status', 'complete')->json('data.upload');
        $this->assertSame(11, $saved['bytes']);
        $this->assertSame(hash('sha256', 'hello world'), $saved['sha256']);
        $path = DB::table('association_uploads')->where('id', $saved['id'])->value('path');
        $this->assertSame('hello world', Storage::disk('local')->get($path));
        app(AssociationTransfers::class)->assemble($id);
        $this->assertSame(1, DB::table('association_uploads')->count());
    }

    public function test_scope_revocation_blocks_upload_resume_and_corrupt_full_hash_never_creates_media(): void
    {
        Storage::fake('local');
        config(['association.upload_chunk_bytes' => 5]);
        $category = $this->category();
        $person = $this->beneficiary($category);
        [$account] = $this->actor(['admin.enter', 'association.enter', 'association.beneficiaries.update'], 'scoped', false, [$category]);
        $service = app(AssociationTransfers::class);
        $id = (string) Str::uuid();
        $service->start($account, ['id' => $id, 'module' => 'beneficiaries', 'record_id' => $person, 'field' => 'personal_photo', 'name' => 'test', 'bytes' => 5, 'sha256' => str_repeat('0', 64)]);
        $service->part($account, $id, 0, UploadedFile::fake()->createWithContent('part', 'hello'));
        $service->finish($account, $id);
        $service->assemble($id);
        $this->assertSame('failed', $service->status($account, $id)['status']);
        $this->assertSame(0, DB::table('association_uploads')->count());
        DB::table('app_admin_memberships')->where('app_account_id', $account->id)->update(['association_categories' => '[]']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->status($account, $id);
    }
}
