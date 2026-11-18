<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/** Immutable, independently retryable parts. No total business size limit. */
final class AssociationTransfers
{
    public function __construct(private AssociationAccess $access, private AssociationRecords $records, private AssociationMedia $media) {}

    private function authorize(AppAccount $account, object $transfer): void
    {
        abort_unless((int) $transfer->account_id === $account->id, 404);
        if (($transfer->context ?? 'admin') === 'portal') {
            if ($transfer->module === 'profile') {
                abort_unless($transfer->field === 'personal_photo', 422, 'invalid_attachment_field');
                app(AssociationProfile::class)->attachmentContext($account, (int) $transfer->record_id);
                return;
            }
            if (in_array($transfer->module, ['chat', 'support'], true)) {
                app(AssociationConversations::class)->attachmentContext($account, (int) $transfer->record_id, $transfer->module);
                abort_unless($transfer->field === 'attachment', 422, 'invalid_attachment_field');
                return;
            }
            app(AssociationForms::class)->attachmentField($account, (int) $transfer->form_id, $transfer->field);
            return;
        }
        $token = $account->currentAccessToken();
        abort_if($token && ! $token->can('app:admin'), 403, 'admin_token_required');
        if ($transfer->field === '_import') {
            $this->access->require($account, $transfer->module, 'import');
            abort_unless(in_array($transfer->module, ['beneficiaries', 'embassy', 'ramadan-beneficiaries'], true) && ! $transfer->record_id, 422, 'invalid_import_target');
            return;
        }
        if (in_array($transfer->module, ['chat', 'support'], true)) {
            app(AssociationConversations::class)->attachmentContext($account, (int) $transfer->record_id, $transfer->module);
            abort_unless($transfer->field === 'attachment', 422, 'invalid_attachment_field');
            return;
        }
        if ($transfer->module === 'notifications') {
            $this->access->require($account, 'notifications', $transfer->record_id ? 'update' : 'create');
            if ($transfer->record_id) {
                $notification = app(AssociationNotificationAdmin::class)->show($account, (int) $transfer->record_id)['record'];
                abort_unless($notification['status'] === 'draft', 409, 'notification_already_queued');
            }
            abort_unless($transfer->field === 'image', 422, 'invalid_attachment_field');
            return;
        }
        if ($transfer->module === 'submissions' && $transfer->record_id) {
            $this->access->require($account, 'submissions', 'update');
            $submission = app(AssociationForms::class)->adminShow($account, 'submissions', (int) $transfer->record_id)['record'];
            $field = collect($submission['form_snapshot']['fields'])->firstWhere('key', $transfer->field);
            abort_unless($field && in_array($field['type'], ['image', 'file'], true), 422, 'invalid_attachment_field');
            return;
        }
        $grant = $this->access->require($account, $transfer->module, $transfer->record_id ? 'update' : 'create');
        if ($transfer->record_id) {
            $this->records->query($transfer->module, $grant)->whereKey($transfer->record_id)->firstOrFail();
        }
        $field = collect($this->records->fields($transfer->module))->firstWhere('key', $transfer->field);
        abort_unless($field && $field['type'] === 'file' && empty($field['readonly']), 422, 'invalid_attachment_field');
    }

    public function start(AppAccount $account, array $input, ?int $formId = null, bool $portalThread = false): array
    {
        $data = Validator::make($input, ['id' => 'required|uuid', 'module' => 'required|string|max:80',
            'field' => 'required|string|max:100', 'record_id' => 'nullable|integer|min:1', 'name' => 'required|string|max:200',
            'bytes' => 'required|integer|min:1', 'sha256' => 'required|string|regex:/^[a-f0-9]{64}$/D'])->validate();
        $data['record_id'] ??= null;
        $data['account_id'] = $account->id;
        $data['context'] = $formId === null && ! $portalThread ? 'admin' : 'portal';
        $data['form_id'] = $formId;
        if ($formId !== null) { $data['module'] = 'submissions'; $data['record_id'] = null; }
        $this->authorize($account, (object) $data);
        $maximum = config('association.attachment_max_kb');
        abort_if($maximum !== null && $data['bytes'] > $maximum * 1024, 422, 'attachment_too_large');
        DB::transaction(function () use ($data, $account): void {
            $existing = DB::table('association_upload_transfers')->where('id', $data['id'])->lockForUpdate()->first();
            if ($existing) {
                foreach ($data as $key => $value) {
                    abort_unless((string) $existing->$key === (string) $value, 409, 'upload_metadata_changed');
                }

                return;
            }
            abort_if(DB::table('association_upload_transfers')->where('account_id', $account->id)->whereIn('status', ['uploading', 'assembling', 'failed'])->count() >= 100, 429, 'too_many_pending_attachments');
            DB::table('association_upload_transfers')->insert([...$data, 'chunk_bytes' => config('association.upload_chunk_bytes'),
                'status' => 'uploading', 'created_at' => now(), 'updated_at' => now()]);
        });

        return $this->status($account, $data['id']);
    }

    private function transfer(AppAccount $account, string $id, bool $lock = false): object
    {
        $query = DB::table('association_upload_transfers')->where('id', $id)->where('account_id', $account->id);
        $row = ($lock ? $query->lockForUpdate() : $query)->first() ?? abort(404);
        $this->authorize($account, $row);

        return $row;
    }

    public function status(AppAccount $account, string $id): array
    {
        $row = $this->transfer($account, $id);
        $parts = DB::table('association_upload_chunks')->where('transfer_id', $id);
        $next = 0;
        foreach ((clone $parts)->orderBy('part')->select('part')->cursor() as $part) {
            if ((int) $part->part !== $next) { break; }
            $next++;
        }
        $upload = $row->upload_id ? DB::table('association_uploads')->where('id', $row->upload_id)->first() : null;

        return ['id' => $id, 'status' => $row->status, 'bytes' => (int) $row->bytes, 'chunk_bytes' => (int) $row->chunk_bytes,
            'received_bytes' => (int) (clone $parts)->sum('bytes'), 'next_part' => $next,
            'upload' => $upload ? $this->media->describe($upload) : null, 'error' => $row->error];
    }

    public function part(AppAccount $account, string $id, int $part, UploadedFile $file): array
    {
        Validator::make(['file' => $file, 'part' => $part], ['file' => 'required|file', 'part' => 'integer|min:0'])->validate();
        DB::transaction(function () use ($account, $id, $part, $file): void {
            $row = $this->transfer($account, $id, true);
            abort_unless($row->status === 'uploading', 409, 'upload_not_accepting_parts');
            $count = intdiv((int) $row->bytes - 1, (int) $row->chunk_bytes) + 1;
            abort_unless($part < $count, 422, 'invalid_part_number');
            $expected = $part === $count - 1 ? (int) $row->bytes - ($part * (int) $row->chunk_bytes) : (int) $row->chunk_bytes;
            abort_unless($file->getSize() === $expected, 422, 'invalid_part_size');
            $hash = hash_file('sha256', $file->getRealPath());
            $existing = DB::table('association_upload_chunks')->where('transfer_id', $id)->where('part', $part)->first();
            if ($existing) {
                abort_unless(hash_equals($existing->sha256, $hash), 409, 'part_content_changed');

                return;
            }
            $path = 'association/transfers/'.$id.'/'.$part.'.part';
            $stream = fopen($file->getRealPath(), 'rb');
            try {
                abort_unless(Storage::disk('local')->put($path, $stream), 503, 'attachment_storage_unavailable');
            } finally {
                fclose($stream);
            }
            DB::table('association_upload_chunks')->insert(['transfer_id' => $id, 'part' => $part, 'bytes' => $expected, 'sha256' => $hash, 'path' => $path]);
            DB::table('association_upload_transfers')->where('id', $id)->update(['updated_at' => now()]);
        });

        return $this->status($account, $id);
    }

    public function finish(AppAccount $account, string $id): array
    {
        DB::transaction(function () use ($account, $id): void {
            $row = $this->transfer($account, $id, true);
            if ($row->status === 'complete') {
                return;
            }
            abort_if($row->status === 'cancelled', 409, 'upload_cancelled');
            $parts = DB::table('association_upload_chunks')->where('transfer_id', $id);
            abort_unless((int) (clone $parts)->sum('bytes') === (int) $row->bytes && $parts->count() === intdiv((int) $row->bytes - 1, (int) $row->chunk_bytes) + 1, 409, 'upload_parts_missing');
            DB::table('association_upload_transfers')->where('id', $id)->update(['status' => 'assembling', 'error' => null, 'updated_at' => now()]);
        });

        return $this->status($account, $id);
    }

    /** Run by the background command; flock serializes assembly without a long DB transaction. */
    public function assemble(string $id): void
    {
        $disk = Storage::disk('local');
        $directory = 'association/transfers/'.$id;
        $disk->makeDirectory($directory);
        $lock = fopen($disk->path($directory.'/assembly.lock'), 'c');
        if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if ($lock) { fclose($lock); }
            return;
        }
        $assembled = $directory.'/assembled.bin';
        try {
            $row = DB::table('association_upload_transfers')->where('id', $id)->first();
            if (! $row || $row->status !== 'assembling') {
                return;
            }
            $account = AppAccount::findOrFail($row->account_id);
            $this->authorize($account, $row);
            $output = fopen($disk->path($assembled), 'wb');
            abort_unless($output, 503, 'attachment_storage_unavailable');
            $hash = hash_init('sha256');
            $bytes = 0;
            try {
                foreach (DB::table('association_upload_chunks')->where('transfer_id', $id)->orderBy('part')->cursor() as $part) {
                    $stream = $disk->readStream($part->path);
                    abort_unless(is_resource($stream), 409, 'upload_part_missing');
                    $partHash = hash_init('sha256');
                    $partBytes = 0;
                    try {
                        while (! feof($stream)) {
                            $buffer = fread($stream, 64 * 1024);
                            abort_if($buffer === false, 503, 'attachment_storage_unavailable');
                            hash_update($hash, $buffer);
                            hash_update($partHash, $buffer);
                            $partBytes += strlen($buffer);
                            $written = 0;
                            while ($written < strlen($buffer)) {
                                $size = fwrite($output, substr($buffer, $written));
                                abort_unless($size, 503, 'attachment_storage_unavailable');
                                $written += $size;
                            }
                        }
                    } finally {
                        fclose($stream);
                    }
                    abort_unless($partBytes === (int) $part->bytes && hash_equals($part->sha256, hash_final($partHash)), 409, 'upload_part_corrupt');
                    $bytes += $partBytes;
                }
            } finally {
                fclose($output);
            }
            abort_unless($bytes === (int) $row->bytes && hash_equals($row->sha256, hash_final($hash)), 409, 'upload_checksum_mismatch');
            DB::transaction(function () use ($account, $id, $row, $disk, $assembled): void {
                $current = DB::table('association_upload_transfers')->where('id', $id)->lockForUpdate()->first();
                abort_unless($current->status === 'assembling', 409, 'upload_state_changed');
                $this->authorize($account, $current);
                $file = new UploadedFile($disk->path($assembled), $row->name, null, null, true);
                if (in_array($row->module, ['chat', 'support'], true)) {
                    abort_if(str_starts_with($file->getMimeType() ?: '', 'audio/'), 422, 'voice_messages_are_disabled');
                }
                $upload = $this->media->store($account, $row->module, $row->field, $file, $row->context === 'portal');
                if (in_array($row->module, ['chat', 'support'], true)) {
                    DB::table('association_uploads')->where('id', $upload['id'])->update(['record_id' => (string) $row->record_id, 'updated_at' => now()]);
                }
                DB::table('association_upload_transfers')->where('id', $id)->update(['status' => 'complete', 'upload_id' => $upload['id'], 'error' => null, 'updated_at' => now()]);
            });
            foreach (DB::table('association_upload_chunks')->where('transfer_id', $id)->cursor() as $part) {
                $disk->delete($part->path);
            }
        } catch (\Throwable $exception) {
            DB::table('association_upload_transfers')->where('id', $id)->where('status', 'assembling')->update([
                'status' => 'failed', 'error' => $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException ? $exception->getMessage() : 'assembly_failed', 'updated_at' => now()]);
            report($exception);
        } finally {
            $disk->delete($assembled);
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
