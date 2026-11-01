<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppAccount;
use App\Models\Permission;
use App\Models\User;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppLocalization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class AppLocalizationController extends Controller
{
    public function __construct(private AppLocalization $catalog, private AppAdminAccess $access) {}

    public function bootstrap(Request $request)
    {
        abort_unless(config('haq_app.enabled'), 503, 'localization_disabled');
        $request->validate(['include_content_only' => 'sometimes|boolean']);
        $includeContentOnly = $request->boolean('include_content_only');
        $rows = DB::table('app_languages')->where('enabled', true)->whereNotNull('release_id')->get();
        $registry = collect($this->catalog->languages())->keyBy('locale');
        $languages = [];
        foreach ($rows as $row) {
            $content = DB::table('app_content_releases')->where('id', $row->release_id)->where('locale', $row->locale)->first();
            if (! $content) {
                continue;
            }
            $interface = $row->ui_release_id ? DB::table('app_content_releases')->where('id', $row->ui_release_id)->where('locale', $row->locale)->first() : null;
            $uiSnapshot = $interface ? json_decode($interface->payload, true, 512, JSON_THROW_ON_ERROR) : null;
            if ($interface && (($uiSnapshot['ui_complete'] ?? true) !== true || ($includeContentOnly && ! $this->catalog->interfaceReady($uiSnapshot)))) {
                $interface = null;
            }
            // Existing clients discover only interface releases. New clients can
            // independently request the latest reviewed content in any locale.
            if (! $includeContentOnly && ! $interface) {
                continue;
            }
            $primary = $interface ?? $content;
            $snapshot = json_decode($primary->payload, true, 512, JSON_THROW_ON_ERROR);
            $languages[] = [...$registry[$row->locale], 'release_id' => $primary->id, 'version' => $snapshot['version'],
                'interface_release_id' => $interface?->id, 'content_release_id' => $content->id];
        }

        return response()->json(['data' => ['schema' => 1, 'languages' => $languages]])->header('Cache-Control', 'no-store');
    }

    public function release(int $release)
    {
        abort_unless(config('haq_app.enabled'), 503);
        $row = DB::table('app_content_releases')->where('id', $release)->first() ?? abort(404);

        // Exact bytes are signed; do not decode and re-encode the payload.
        return response()->json(['data' => ['release_id' => $row->id, 'payload' => $row->payload,
            'bytes' => strlen($row->payload), 'sha256' => $row->sha256, 'key_id' => $row->key_id, 'signature' => $row->signature]])
            ->header('Cache-Control', 'public, max-age=31536000, immutable');
    }

    public function languages(Request $request)
    {
        $grant = $this->access->require($request->user());
        $locales = json_decode($grant->locales, true);

        return response()->json(['data' => array_values(array_filter($this->catalog->summary(), fn ($row) => ! $locales || in_array($row['locale'], $locales, true)))])
            ->header('Cache-Control', 'no-store');
    }

    public function records(Request $request, string $locale)
    {
        $this->access->require($request->user(), null, $locale);
        $data = $request->validate(['kind' => 'nullable|in:ui,content', 'q' => 'nullable|string|max:100', 'page' => 'nullable|integer|min:1|max:10000']);

        return response()->json(['data' => $this->catalog->records($locale, $data['kind'] ?? 'ui', $data['q'] ?? '', (int) ($data['page'] ?? 1))])->header('Cache-Control', 'no-store');
    }

    public function save(Request $request, string $locale)
    {
        $data = $request->validate(['key' => 'required|string|max:190', 'value' => 'required', 'revision' => 'required|integer|min:0']);
        $this->access->require($request->user(), str_starts_with($data['key'], 'ui/') ? 'translations.edit' : 'content.edit', $locale);
        $this->catalog->language($locale);

        return response()->json(['data' => $this->catalog->save($locale, $data['key'], $data['value'], $data['revision'], $request->user()->id)]);
    }

    public function review(Request $request, string $locale)
    {
        $this->access->require($request->user(), 'review', $locale);
        $data = $request->validate(['key' => 'required|string|max:190', 'revision' => 'required|integer|min:1']);
        $this->catalog->language($locale);
        $this->catalog->review($locale, $data['key'], $data['revision'], $request->user()->id);

        return response()->json(['data' => ['reviewed' => true]]);
    }

    public function publish(Request $request, string $locale)
    {
        $this->access->require($request->user(), 'publish', $locale);
        $data = $request->validate(['revision' => 'required|integer|min:1', 'restore_release_id' => 'nullable|integer|min:1',
            'content_only' => ['sometimes', 'boolean', Rule::prohibitedIf($request->filled('restore_release_id'))]]);
        $this->catalog->language($locale);

        return response()->json(['data' => $this->catalog->publish($locale, $data['revision'], $request->user()->id,
            $data['restore_release_id'] ?? null, $data['content_only'] ?? false)]);
    }

    public function hide(Request $request, string $locale)
    {
        $this->access->require($request->user(), 'languages.manage', $locale);
        $data = $request->validate(['revision' => 'required|integer|min:1']);
        $this->catalog->language($locale);
        DB::transaction(function () use ($request, $locale, $data): void {
            $row = DB::table('app_languages')->where('locale', $locale)->lockForUpdate()->first();
            abort_unless($row->revision === $data['revision'], 409);
            // Bundled Arabic always remains available to clients, independently of this list.
            DB::table('app_languages')->where('locale', $locale)->update(['enabled' => false, 'revision' => $row->revision + 1, 'updated_at' => now()]);
            $this->access->audit($request->user()->id, 'language.hidden', $locale);
        });

        return response()->json(['data' => ['hidden' => true]]);
    }

    public function releases(Request $request, string $locale)
    {
        $this->access->require($request->user(), 'publish', $locale);

        return response()->json(['data' => DB::table('app_content_releases')->where('locale', $locale)->orderByDesc('id')->limit(50)->get(['id', 'sha256', 'created_at'])]);
    }

    public function audit(Request $request)
    {
        $this->access->require($request->user(), 'team.manage');

        return response()->json(['data' => DB::table('app_admin_audit')->orderByDesc('id')->paginate(50)]);
    }

    public function team(Request $request)
    {
        $this->access->require($request->user(), 'team.manage');

        return response()->json(['data' => DB::table('app_admin_memberships')->orderBy('id')->paginate(50)]);
    }

    public function updateMember(Request $request, int $member)
    {
        $actor = $this->access->require($request->user(), 'team.manage');
        $data = $request->validate(['active' => 'required|boolean', 'permissions' => 'required|array',
            'permissions.*' => ['required', Rule::in(array_keys(AppAdminAccess::PERMISSIONS))],
            'locales' => 'present|array', 'locales.*' => ['required', Rule::in(array_column($this->catalog->languages(), 'locale'))]]);
        $target = DB::table('app_admin_memberships')->where('id', $member)->first() ?? abort(404);
        abort_if($target->app_account_id === $request->user()->id, 403, 'cannot_edit_own_grant');
        abort_if(array_diff($data['permissions'], $actor->effective_permissions), 403, 'cannot_delegate_permission');
        $scope = json_decode($actor->locales, true);
        abort_if($scope && (! $data['locales'] || array_diff($data['locales'], $scope)), 403, 'cannot_delegate_locale');
        DB::transaction(function () use ($data, $target, $request): void {
            $staffId = AppAccount::findOrFail($target->app_account_id)->user_id;
            abort_unless($staffId, 422, 'staff_identity_required');
            $permissionIds = [];
            foreach ($data['permissions'] as $permission) {
                $permissionIds[] = Permission::firstOrCreate(['name' => AppAdminAccess::PERMISSIONS[$permission]])->id;
            }
            // Preserve every unrelated role/direct permission; the app grant is
            // the narrower gate for this unit, including permission removals.
            User::findOrFail($staffId)->permissions()->syncWithoutDetaching($permissionIds);
            DB::table('app_admin_memberships')->where('id', $target->id)->update(['active' => $data['active'],
                'permissions' => json_encode($data['permissions']), 'locales' => json_encode($data['locales']), 'updated_at' => now()]);
            $this->access->audit($request->user()->id, 'membership.updated', (string) $target->id, $data);
        });

        return response()->json(['data' => ['updated' => true]]);
    }
}
