<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403, 'Forbidden');
    }

    // GET /api/users?search=&role=&per_page=
    public function index(Request $request)
    {
  $this->ensureAdmin($request);

    $q = User::query()
        ->select('id','name','email','role')
        ->when($request->filled('role'), fn($qq) => $qq->where('role', $request->role))
        ->orderBy('name');

   
    if ($request->boolean('paginate', true)) {
        $per = (int) $request->query('per_page', 20);
        return $q->paginate($per);
    }

    return $q->get();
 }

    // POST /api/users  (solo admin)
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

    // GET /api/users/{user} (solo admin)
    public function show(Request $request, User $user)
    {
        $this->ensureAdmin($request);
        return $user;
    }

    // PUT /api/users/{user} (solo admin)
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

    // DELETE /api/users/{user} (solo admin)
    public function destroy(Request $request, User $user)
    {
        $this->ensureAdmin($request);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta.'], 422);
        }

        $user->delete();
        return response()->noContent();
    }

    // PATCH /api/users/{user}/role (solo admin)
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
