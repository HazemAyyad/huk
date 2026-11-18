<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AssociationMedia
{
    public function store(AppAccount $account, string $module, string $field, UploadedFile $file, bool $portal = false): array
    {
        $rules = ['required', 'file'];
        if (config('association.attachment_max_kb') !== null) {
            $rules[] = 'max:'.config('association.attachment_max_kb');
        }
        Validator::make(['file' => $file], ['file' => $rules])->validate();
        abort_if(DB::table('association_uploads')->where('account_id', $account->id)->whereNull('record_id')->count() >= 100, 429, 'too_many_pending_attachments');
        $id = (string) Str::uuid();
        $path = 'association/uploads/'.$id.'.bin';
        $stream = fopen($file->getRealPath(), 'rb');
        try {
            if (! Storage::disk('local')->put($path, $stream)) {
                abort(503, 'attachment_storage_unavailable');
            }
        } finally {
            fclose($stream);
        }
        $name = mb_substr(preg_replace('/[\x00-\x1F\x7F\\\\\/]/u', '', $file->getClientOriginalName()) ?: 'attachment', 0, 200);
        $row = ['id' => $id, 'account_id' => $account->id, 'staff_id' => $account->user_id, 'module' => $module,
            'context' => $portal ? 'portal' : 'admin', 'field' => $field, 'path' => $path, 'name' => $name, 'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'bytes' => $file->getSize(), 'sha256' => hash_file('sha256', $file->getRealPath()), 'created_at' => now(), 'updated_at' => now()];
        try {
            DB::table('association_uploads')->insert($row);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return $this->describe((object) $row);
    }

    public function describe(object $upload): array
    {
        return ['id' => $upload->id, 'name' => $upload->name, 'mime' => $upload->mime,
            'bytes' => (int) $upload->bytes, 'sha256' => $upload->sha256, 'url' => url('/association-media/'.$upload->id)];
    }

    public function id(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return preg_match('~(?:^|/association-media/)([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$~i', $value, $match) ? mb_strtolower($match[1]) : null;
    }

    public function value(AppAccount $account, string $module, string $field, mixed $value, mixed $old = null, ?string $record = null): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value)) {
            $before = is_array($old) ? $old : (is_string($old) ? json_decode($old, true) : []);

            return array_map(fn ($item) => $this->value($account, $module, $field, $item, in_array($item, $before ?: [], true) ? $item : null, $record), $value);
        }
        if ($value === $old) {
            return $old;
        }
        $id = $this->id($value);
        $upload = $id ? DB::table('association_uploads')->where('id', $id)->lockForUpdate()->first() : null;
        if (! $upload || $upload->module !== $module || $upload->field !== $field ||
            ($upload->record_id !== null && (string) $upload->record_id !== $record) ||
            ($upload->record_id === null && (int) $upload->account_id !== $account->id)) {
            throw ValidationException::withMessages([$field => 'المرفق غير صالح لهذا الحقل أو السجل.']);
        }

        return url('/association-media/'.$id);
    }

    public function bind(AppAccount $account, string $module, string $record, array $values): void
    {
        foreach ($values as $field => $value) {
            if (is_string($value) && str_starts_with($value, '[')) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $value = $decoded;
                }
            }
            foreach (is_array($value) ? $value : [$value] as $item) {
                $id = $this->id($item);
                if ($id) {
                    DB::table('association_uploads')->where('id', $id)->where('module', $module)->where('field', $field)
                        ->where('account_id', $account->id)->whereNull('record_id')->update(['record_id' => $record, 'updated_at' => now()]);
                }
            }
        }
    }
}
