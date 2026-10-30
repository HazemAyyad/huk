<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppAccount;
use App\Models\ReligiousAccount;
use App\Models\User;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppSessionProof;
use App\Services\Religious\GoogleIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AppSessionController extends Controller
{
    public function login(Request $request, AppAdminAccess $access)
    {
        abort_unless(config('haq_app.enabled'), 503, 'app_accounts_disabled');
        $data = $request->validate(['email' => 'required|email|max:255', 'password' => 'required|string|max:128', 'provider' => 'required|in:association,religious']);
        $class = $data['provider'] === 'association' ? User::class : ReligiousAccount::class;
        $owner = $class::where('email', mb_strtolower(trim($data['email'])))->first();
        if (! $owner || $owner->getAttribute('password_enabled') === false || ! Hash::check($data['password'], $owner->password) || $owner->getAttribute('deleted_by')) {
            throw ValidationException::withMessages(['email' => 'invalid_credentials']);
        }
        $field = $data['provider'] === 'association' ? 'user_id' : 'religious_account_id';
        $account = AppAccount::firstOrCreate([$field => $owner->id], [
            'association_enabled' => $field === 'user_id', 'religious_enabled' => $field === 'religious_account_id',
        ]);
        abort_unless($account->active && in_array($data['provider'], $account->areas(), true), 403, 'area_denied');

        return $this->issue($account, $access, $data['provider']);
    }

    // Existing verified religious login/Google sessions can enter the aggregate
    // without creating any association identity or matching an employee email.
    public function exchange(Request $request, AppAdminAccess $access)
    {
        abort_unless(config('haq_app.enabled'), 503);
        $owner = $request->user();
        abort_unless($owner instanceof ReligiousAccount && $owner->tokenCan('quran:sync'), 403);
        $account = AppAccount::firstOrCreate(['religious_account_id' => $owner->id], ['religious_enabled' => true]);
        abort_unless($account->active && $account->religious_enabled, 403);

        return $this->issue($account, $access, 'religious', false);
    }

    private function profile(AppAccount $account, AppAdminAccess $access): array
    {
        $areas = $account->areas();
        $owner = $account->user_id ? User::find($account->user_id) : ReligiousAccount::find($account->religious_account_id);
        $roles = $account->user_id ? DB::table('roles')->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('user_id', $account->user_id)->where('user_type', (new User)->getMorphClass())->pluck('name')->all() : [];

        return ['id' => $account->id, 'name' => $owner?->name, 'areas' => $areas, 'religious_account_id' => $account->religious_account_id,
            'account_type' => count($areas) === 2 ? 'association_and_religious' : ($areas[0] ?? 'none'),
            'roles' => $roles, ...$access->session($account)];
    }

    private function issue(AppAccount $account, AppAdminAccess $access, string $provider, bool $religiousSession = true)
    {
        $data = ['account' => $this->profile($account, $access),
            'token' => $account->createToken('app-session', app(AppSessionProof::class)->abilities($account, $provider), now()->addDays(30))->plainTextToken];
        if ($religiousSession && $account->religious_enabled && $account->religious_account_id) {
            $religious = ReligiousAccount::findOrFail($account->religious_account_id);
            $data['religious_session'] = ['account' => ReligiousIdentityController::account($religious),
                'token' => $religious->createToken('religious-app', ['quran:sync'], now()->addDays(90))->plainTextToken];
        }

        return response()->json(['data' => $data]);
    }

    public function current(Request $request, AppAdminAccess $access)
    {
        return response()->json(['data' => $this->profile($request->user(), $access)]);
    }

    public function link(Request $request, AppAdminAccess $access, GoogleIdentity $google)
    {
        $data = $request->validate(['provider' => 'required|in:association,religious', 'email' => 'required|email|max:255',
            'password' => 'required|string|max:128', 'current_password' => 'nullable|string|max:128', 'current_id_token' => 'nullable|string|max:10000']);
        $account = $request->user();
        abort_unless(count($account->areas()) === 1 && ! in_array($data['provider'], $account->areas(), true), 409, 'already_linked');
        $source = $account->areas()[0];
        $owner = $source === 'association' ? User::findOrFail($account->user_id) : ReligiousAccount::findOrFail($account->religious_account_id);
        $verified = $owner->getAttribute('password_enabled') !== false && Hash::check($data['current_password'] ?? '', $owner->password);
        if (! $verified && $owner instanceof ReligiousAccount && ! empty($data['current_id_token'])) {
            $claims = $google->verify($data['current_id_token']);
            $verified = $owner->google_subject !== null && $owner->google_subject === $claims['sub'] && ($claims['iat'] ?? 0) >= time() - 300;
        }
        if (! $verified) {
            throw ValidationException::withMessages(['current_password' => 'reauthentication_required']);
        }
        $class = $data['provider'] === 'association' ? User::class : ReligiousAccount::class;
        $target = $class::where('email', mb_strtolower(trim($data['email'])))->first();
        if (! $target || $target->getAttribute('password_enabled') === false || ! Hash::check($data['password'], $target->password) || $target->getAttribute('deleted_by')) {
            throw ValidationException::withMessages(['email' => 'invalid_credentials']);
        }
        $field = $data['provider'] === 'association' ? 'user_id' : 'religious_account_id';
        $other = AppAccount::firstOrCreate([$field => $target->id], ['association_enabled' => $field === 'user_id', 'religious_enabled' => $field === 'religious_account_id']);
        $primary = DB::transaction(function () use ($account, $other, $access) {
            $locked = AppAccount::whereIn('id', [$account->id, $other->id])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $first = $locked[$account->id];
            $second = $locked[$other->id];
            abort_unless($first->active && $second->active && count($first->areas()) === 1 && count($second->areas()) === 1 && $first->areas() !== $second->areas(), 409, 'identity_link_conflict');
            // Retain the association aggregate and its existing grant; the other
            // aggregate has no staff identity and cannot contribute admin grants.
            $primary = $first->user_id ? $first : $second;
            $secondary = $first->user_id ? $second : $first;
            $religiousId = $secondary->religious_account_id;
            $secondary->update(['religious_account_id' => null, 'religious_enabled' => false, 'active' => false]);
            $primary->update(['religious_account_id' => $religiousId, 'religious_enabled' => true]);
            $primary->tokens()->delete();
            $secondary->tokens()->delete();
            $access->audit($primary->id, 'identity.linked_with_reauthentication', (string) $primary->id,
                ['previous_aggregate' => $secondary->id, 'religious_account_id' => $religiousId]);

            return $primary;
        });

        return $this->issue($primary, $access, $source);
    }

    public function admin(Request $request, AppAdminAccess $access)
    {
        $account = $request->user();
        $access->require($account);
        $name = 'app-admin:'.$account->currentAccessToken()->id;
        // Repeated exchanges replace the previous child, not all device sessions.
        $account->tokens()->where('name', $name)->delete();
        $expires = now()->addMinutes(config('haq_app.admin_minutes'));

        return response()->json(['data' => ['token' => $account->createToken($name, ['app:admin'], $expires)->plainTextToken,
            'expires_at' => $expires->toIso8601String(), ...$access->session($account)]]);
    }

    public function logout(Request $request)
    {
        $account = $request->user();
        $account->tokens()->where('name', 'app-admin:'.$account->currentAccessToken()->id)->delete();
        $account->currentAccessToken()->delete();

        return response()->json(['data' => ['logged_out' => true]]);
    }
}
