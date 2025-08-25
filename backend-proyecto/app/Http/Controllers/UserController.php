<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Si no pones el middleware 'role:admin' en rutas,
    // este guard simple mantiene el control dentro del controlador.
    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403, 'Forbidden');
    }

    // GET /api/users?search=&role=&per_page=
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $q = User::query();

        if ($s = $request->query('search')) {
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                   ->orWhere('email', 'like', "%{$s}%");
            });
        }
        if ($role = $request->query('role')) {
            $q->where('role', $role);
        }

        $per = (int) $request->query('per_page', 15);
        return $q->orderByDesc('id')->paginate($per);
    }

    // POST /api/users
    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'role'     => ['required','in:admin,colaborador'],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        return response()->json($user, 201);
    }

    // GET /api/users/{user}
    public function show(Request $request, User $user)
    {
        $this->ensureAdmin($request);
        return $user;
    }

    // PUT/PATCH /api/users/{user}
    public function update(Request $request, User $user)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'name'     => ['sometimes','string','max:255'],
            'email'    => ['sometimes','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['sometimes','nullable','string','min:8','confirmed'],
            'role'     => ['sometimes','in:admin,colaborador'],
        ]);

        if ($user->id === $request->user()->id && isset($data['role']) && $data['role'] !== $user->role) {
            return response()->json(['message' => 'No puedes cambiar tu propio rol.'], 422);
        }

        if (array_key_exists('password', $data) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return $user->fresh();
    }

    // DELETE /api/users/{user}
    public function destroy(Request $request, User $user)
    {
        $this->ensureAdmin($request);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta.'], 422);
        }

        $user->delete();
        return response()->noContent();
    }

    // Opcional: PATCH /api/users/{user}/role  (si quieres un endpoint específico)
    public function updateRole(Request $request, User $user)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'role' => ['required','in:admin,colaborador'],
        ]);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'No puedes cambiar tu propio rol.'], 422);
        }

        $user->update(['role' => $data['role']]);
        return $user->fresh();
    }
}
