<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReligiousAccount;
use App\Services\Quran\QuranSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class ReligiousAccountController extends Controller
{
    public function register(Request $request)
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:255|unique:religious_accounts,email', 'password' => 'required|string|min:10|max:128']);

        return $this->session(ReligiousAccount::create($data), 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255', 'password' => 'required|string|max:128']);
        $user = ReligiousAccount::where('email', mb_strtolower(trim($data['email'])))->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'البريد أو كلمة المرور غير صحيحة']);
        }

        return $this->session($user);
    }

    private function session(ReligiousAccount $user, int $status = 200)
    {
        return response()->json(['data' => ['account' => $user->only('id', 'name', 'email'),
            'token' => $user->createToken('religious-app', ['quran:sync'], now()->addDays(90))->plainTextToken]], $status);
    }

    public function profile(Request $request)
    {
        return response()->json(['data' => $request->user()->only('id', 'name', 'email')]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['data' => ['logged_out' => true]]);
    }

    public function sync(Request $request, QuranSync $sync)
    {
        $data = $request->validate(['operations' => 'present|array|max:250', 'operations.*' => 'array:id,key,base_revision,deleted,value',
            'operations.*.id' => 'required|uuid|distinct', 'operations.*.key' => 'required|string|max:80',
            'operations.*.base_revision' => 'required|integer|min:0', 'operations.*.deleted' => 'required|boolean', 'operations.*.value' => 'present']);

        return response()->json(['data' => $sync->exchange($request->user(), $data['operations'])]);
    }
}
