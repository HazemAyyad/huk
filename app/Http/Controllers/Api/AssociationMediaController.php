<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppAccount;
use App\Models\User;
use App\Services\Association\AssociationAccess;
use App\Services\Association\AssociationModules;
use App\Services\Association\AssociationRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class AssociationMediaController extends Controller
{
    public function legacy(Request $request, string $module, int $id, string $field, AssociationAccess $access, AssociationRecords $records)
    {
        $grant = $access->require($request->user(), $module);
        $record = $records->query($module, $grant)->whereKey($id)->firstOrFail();
        $definition = collect($records->fields($module))->firstWhere('key', $field);
        abort_unless($definition && $definition['type'] === 'file', 404);
        $filter = $request->validate(['index' => 'sometimes|integer|min:0|max:200']);
        $index = (int) ($filter['index'] ?? 0);
        $value = $record->getAttribute($field);
        if (is_string($value) && str_starts_with($value, '[')) { $value = json_decode($value, true); }
        $value = is_array($value) ? ($value[$index] ?? null) : ($index === 0 ? $value : null);
        abort_unless(is_string($value) && $value !== '', 404);
        // Resolve only a stored, allowlisted attachment inside its known upload
        // roots. No URL fetching, directory traversal, or client supplied path.
        $path = rawurldecode(parse_url($value, PHP_URL_PATH) ?: '');
        $path = str_replace('\\', '/', $path);
        abort_if(str_contains($path, "\0") || preg_match('~(?:^|/)\.\.(?:/|$)~', $path), 404);
        $marker = str_contains('/'.ltrim($path, '/'), '/uploads/') ? 'uploads' : 'storage';
        $position = strpos('/'.ltrim($path, '/'), '/'.$marker.'/');
        abort_if($position === false, 404);
        $relative = substr('/'.ltrim($path, '/'), $position + strlen($marker) + 2);
        $root = realpath($marker === 'uploads' ? public_path('uploads') : storage_path('app/public'));
        $resolved = $root ? realpath($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative)) : false;
        abort_unless($resolved && is_file($resolved) && str_starts_with(strtolower($resolved), strtolower($root.DIRECTORY_SEPARATOR)), 404);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($resolved) ?: 'application/octet-stream';
        if ($request->boolean('metadata')) {
            return response()->json(['data' => ['name' => basename($resolved), 'mime' => $mime, 'bytes' => filesize($resolved), 'sha256' => hash_file('sha256', $resolved)]])->header('Cache-Control', 'no-store');
        }
        return response()->file($resolved, ['Content-Type' => in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true) ? $mime : 'application/octet-stream',
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff', 'Content-Disposition' => 'attachment; filename="'.basename($resolved).'"']);
    }

    public function show(Request $request, string $upload, AssociationAccess $access, AssociationRecords $records)
    {
        $row = DB::table('association_uploads')->where('id', $upload)->first() ?? abort(404);
        $actor = $request->user();
        abort_unless($actor instanceof AppAccount || $actor instanceof User, 401);
        $allowed = false;
        if ($actor instanceof AppAccount) {
            if ($row->record_id === null) {
                $allowed = (int) $row->account_id === $actor->id &&
                    ($row->context === 'portal' || $actor->currentAccessToken()?->can('app:admin'));
                if ($allowed && $row->context === 'admin') {
                    $allowed = $access->can($actor, $row->module, 'create') || $access->can($actor, $row->module, 'update');
                }
            } elseif (in_array($row->module, ['chat', 'support'], true)) {
                $allowed = app(\App\Services\Association\AssociationConversations::class)->canReadAttachment($actor, $row);
            } elseif ($row->module === 'submissions') {
                $allowed = app(\App\Services\Association\AssociationForms::class)->canReadSubmission($actor, (int) $row->record_id);
            } elseif ($row->module === 'notifications') {
                $allowed = app(\App\Services\Association\AssociationInbox::class)->canRead($actor, (int) $row->record_id);
                if (! $allowed && $actor->currentAccessToken()?->can('app:admin') && $access->can($actor, 'notifications')) {
                    try { app(\App\Services\Association\AssociationNotificationAdmin::class)->show($actor, (int) $row->record_id); $allowed = true; }
                    catch (\Symfony\Component\HttpKernel\Exception\HttpException) {}
                }
            } elseif ($row->module === 'profile') {
                $person = $actor->user_id ? User::whereKey($actor->user_id)->value('telegram_user_id') : null;
                $allowed = $person && (int) $person === (int) $row->record_id;
                if (! $allowed && $actor->currentAccessToken()?->can('app:admin') && $access->can($actor, 'beneficiaries')) {
                    $allowed = $records->query('beneficiaries', $access->require($actor, 'beneficiaries'))->whereKey($row->record_id)->exists();
                }
            } else {
                $owner = $actor->user_id ? User::find($actor->user_id) : null;
                $record = $records->model($row->module)->newQuery()->whereKey($row->record_id)->first();
                $allowed = $record && $owner && $this->owned($row->module, $record->getAttributes(), $owner);
                if (! $allowed && $actor->currentAccessToken()?->can('app:admin')) {
                    $grant = $access->require($actor, $row->module);
                    $allowed = $records->query($row->module, $grant)->whereKey($row->record_id)->exists();
                }
            }
        } else {
            if ($row->record_id === null) {
                $allowed = (int) $row->staff_id === $actor->id;
            } elseif (isset(AssociationModules::DEFINITIONS[$row->module]) && AssociationModules::DEFINITIONS[$row->module]['model']) {
                $record = $records->model($row->module)->newQuery()->whereKey($row->record_id)->first();
                if ($record) {
                    $roles = DB::table('roles')->join('role_user', 'roles.id', '=', 'role_user.role_id')
                        ->where('role_user.user_id', $actor->id)->where('user_type', $actor->getMorphClass())->pluck('roles.name')->all();
                    $allowed = $this->owned($row->module, $record->getAttributes(), $actor) ||
                        count(array_intersect($roles, explode('|', AssociationModules::DEFINITIONS[$row->module]['roles']))) > 0;
                }
            }
        }
        abort_unless($allowed && Storage::disk('local')->exists($row->path), 404);
        if ($request->boolean('metadata')) {
            return response()->json(['data' => ['id' => $row->id, 'name' => $row->name, 'mime' => $row->mime, 'bytes' => (int) $row->bytes, 'sha256' => $row->sha256]])->header('Cache-Control', 'no-store');
        }
        $inline = in_array($row->mime, ['image/png', 'image/jpeg', 'image/webp', 'image/gif'], true);
        $headers = ['Content-Type' => $inline ? $row->mime : 'application/octet-stream',
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'];

        return Storage::disk('local')->response($row->path, $row->name, $headers, $inline ? 'inline' : 'attachment');
    }

    private function owned(string $module, array $record, User $owner): bool
    {
        $beneficiary = (int) $owner->telegram_user_id;
        if (isset($record['user_id']) && (int) $record['user_id'] === $owner->id) {
            return true;
        }
        if ($beneficiary < 1) {
            return false;
        }
        if ($module === 'beneficiaries') {
            return (int) $record['id'] === $beneficiary;
        }
        if (isset($record['telegram_user_id'])) {
            return (int) $record['telegram_user_id'] === $beneficiary;
        }
        if (isset($record['family_member_id'])) {
            return DB::table('family_members')->where('id', $record['family_member_id'])->where('telegram_user_id', $beneficiary)->exists();
        }

        return false;
    }
}
