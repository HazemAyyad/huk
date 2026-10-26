<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReligiousAccount;
use App\Services\Religious\GoogleIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class ReligiousIdentityController extends Controller
{
    public static function account(ReligiousAccount $user): array
    {
        return [...$user->only('id', 'name', 'email', 'username'),
            'google_linked' => $user->google_subject !== null,
            'password_enabled' => $user->getAttribute('password_enabled') ?? true];
    }

    public function capabilities()
    {
        return response()->json(['data' => [
            'google' => config('religious_account.google_client_id') !== '',
            'recovery' => config('religious_account.recovery_enabled'),
        ]]);
    }

    private function username(Request $request, ?int $ignore = null): array
    {
        $request->merge(['username' => strtolower(trim((string) $request->input('username')))]);

        return $request->validate(['username' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]{6,29}$/', Rule::unique('religious_accounts')->ignore($ignore)]]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $data = $this->username($request, $user->id);
        if ($request->has('name')) {
            $data += $request->validate(['name' => 'required|string|max:100']);
        }
        $user->fill($data)->save();

        return response()->json(['data' => self::account($user)]);
    }

    public function google(Request $request, GoogleIdentity $identity)
    {
        $data = $request->validate(['id_token' => 'required|string|max:10000']);
        $claims = $identity->verify($data['id_token']);
        $user = ReligiousAccount::where('google_subject', $claims['sub'])->first();
        if (! $user) {
            $email = mb_strtolower($claims['email']);
            if (ReligiousAccount::where('email', $email)->exists()) {
                throw ValidationException::withMessages(['email' => 'البريد مرتبط بحساب قائم؛ ادخل بكلمة المرور ثم اربط Google من إعدادات الحساب']);
            }
            $username = $this->username($request);
            $user = DB::transaction(function () use ($claims, $email, $username) {
                $account = new ReligiousAccount([...$username, 'name' => mb_substr($claims['name'] ?? $username['username'], 0, 100), 'email' => $email, 'password' => Str::random(64)]);
                $account->google_subject = $claims['sub'];
                $account->password_enabled = false;
                $account->save();

                return $account;
            });
        }

        return response()->json(['data' => ['account' => self::account($user),
            'token' => $user->createToken('religious-app', ['quran:sync'], now()->addDays(90))->plainTextToken]]);
    }

    public function link(Request $request, GoogleIdentity $identity)
    {
        $data = $request->validate(['id_token' => 'required|string|max:10000']);
        $claims = $identity->verify($data['id_token']);
        $user = $request->user();
        if (mb_strtolower($claims['email']) !== $user->email ||
            ($user->google_subject !== null && $user->google_subject !== $claims['sub']) ||
            ReligiousAccount::where('google_subject', $claims['sub'])->where('id', '!=', $user->id)->exists()) {
            throw ValidationException::withMessages(['id_token' => 'اختر حساب Google الذي يطابق بريد حساب حق']);
        }
        $user->google_subject = $claims['sub'];
        $user->save();

        return response()->json(['data' => self::account($user)]);
    }

    public function forgot(Request $request)
    {
        abort_unless(config('religious_account.recovery_enabled'), 503, 'استعادة البريد غير مفعّلة بعد');
        $data = $request->validate(['email' => 'required|email|max:255']);
        $data['email'] = mb_strtolower(trim($data['email']));
        try {
            Password::broker('religious')->sendResetLink($data);
        } catch (\Throwable) {
            abort(503, 'تعذّر إرسال رسالة الاستعادة');
        }

        return response()->json(['data' => ['message' => 'إذا كان البريد مسجلًا فستصلك رسالة استعادة. يمكنك طلب رسالة أخرى بعد دقيقة.']]);
    }

    public function reset(Request $request)
    {
        abort_unless(config('religious_account.recovery_enabled'), 503);
        $data = $request->validate(['email' => 'required|email|max:255', 'token' => 'required|string|max:255', 'password' => 'required|string|min:10|max:128|confirmed']);
        $data['email'] = mb_strtolower(trim($data['email']));
        $status = Password::broker('religious')->reset($data, function (ReligiousAccount $user, string $password): void {
            DB::transaction(function () use ($user, $password): void {
                $user->password = $password;
                $user->password_enabled = true;
                $user->save();
                $user->tokens()->delete();
            });
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['token' => 'رمز الاستعادة غير صحيح أو منتهي الصلاحية']);
        }

        return response()->json(['data' => ['reset' => true]]);
    }

    public function destroy(Request $request, GoogleIdentity $identity)
    {
        $data = $request->validate(['confirmation' => 'required|in:DELETE', 'password' => 'nullable|string|max:128', 'id_token' => 'nullable|string|max:10000']);
        $user = $request->user();
        $verified = ($user->getAttribute('password_enabled') ?? true) && Hash::check($data['password'] ?? '', $user->password);
        if (! $verified && ! empty($data['id_token'])) {
            $claims = $identity->verify($data['id_token']);
            $verified = $user->google_subject !== null && $claims['sub'] === $user->google_subject && ($claims['iat'] ?? 0) >= time() - 300;
        }
        if (! $verified) {
            throw ValidationException::withMessages(['password' => 'أكّد هويتك بكلمة المرور أو بحساب Google المرتبط']);
        }
        DB::transaction(function () use ($user): void {
            $user->tokens()->delete();
            DB::table('religious_password_reset_tokens')->where('email', $user->email)->delete();
            $user->delete(); // All Quran/content/personal/activity rows have cascade foreign keys.
        });

        return response()->json(['data' => ['deleted' => true]]);
    }
}
