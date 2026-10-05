<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    public function index()
    {
        return view('admin.index');
    }

    public function getManagers()
    {
        return response()->json(
            User::where(function ($q) {
                $q->whereHas('roles')
                    ->orWhereHas('roles.permissions');
            })
                ->with(['roles', 'permissions'])
                ->paginate(100),
            200
        );
    }

    public function getItems()
    {
        return response()->json(User::whereDoesntHave('permissions')
            ->with(['roles', 'permissions'])
            ->paginate(100), 200);
    }

    public function getAll()
    {
        return response()->json(User::get(['name', 'id', 'basic_salary']), 200);
    }

    public function getRolesPermissions()
    {
        $roles = Role::all(['id', 'name', 'display_name']);
        $permissions = Permission::all(['id', 'name', 'display_name']);
        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    public function getItem($id)
    {
        $user = User::with(['roles', 'permissions'])->findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'basic_salary'       => 'required',
            'password'    => 'required|string|min:6',
            'role'        => 'required|exists:roles,name',
            'permissions' => 'array'
        ]);

        $image = null;
        if ($request->filled('photo')) {
            $image = $this->saveBase64File($request->photo);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'basic_salary'    => $request->basic_salary,
            'photo'    => $image,
            'password' => Hash::make($request->password),
        ]);

        $user->addRole($request->role);

        // ربط الصلاحيات
        if (!empty($request->permissions)) {
            $user->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user
        ]);
    }

    protected function saveBase64File($base64String)
    {
        preg_match('/^data:(.*?);base64,/', $base64String, $matches);
        $mimeType = $matches[1] ?? null;

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            default => 'bin',
        };

        $fileData = preg_replace('/^data:(.*?);base64,/', '', $base64String);
        $fileData = base64_decode($fileData);
        $fileName = Str::random(20) . '.' . $extension;

        Storage::disk('public_upload')->put("users/{$fileName}", $fileData);
        return Storage::disk('public_upload')->url("users/{$fileName}");
    }
    
    function deleteByFullUrl(?string $url): void
    {
        if (!$url) {
            return;
        }
    
        // استخراج المسار من الـ URL
        $path = parse_url($url, PHP_URL_PATH); // /users/xxxxxxxx.png
    
        // إزالة أول /
        $path = ltrim($path, '/'); // users/xxxxxxxx.png
    
        if (Storage::disk('public_upload')->exists($path)) {
            Storage::disk('public_upload')->delete($path);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => "required|email|unique:users,email,{$user->id}",
            'basic_salary'       => 'required',
            'password'    => 'nullable|string|min:6',
            'role'        => 'required|exists:roles,name',
            'permissions' => 'array'
        ]);
        
        $image = null;
        if ($request->filled('photo')) {
            $this->deleteByFullUrl($user->photo);
            $image = $this->saveBase64File($request->photo);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->photo = $image;
        $user->basic_salary = $request->basic_salary;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // تحديث الدور
        $user->syncRoles([$request->role]);

        // تحديث الصلاحيات
        $user->syncPermissions($request->permissions ?? []);

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user
        ]);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(200);
    }
}
