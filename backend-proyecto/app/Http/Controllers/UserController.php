<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // GET /api/users - Solo admin puede ver todos los usuarios
    public function index(Request $request)
    {
        // Solo admin tiene acceso
        abort_unless($request->user()->role === 'admin', 403, 'No autorizado');

        $query = User::query();

        // Filtros
        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        if ($request->filled('active')) {
            $query->where('active', $request->query('active') === '1');
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount('projects')
                      ->orderBy('created_at', 'desc')
                      ->paginate(15);

        return response()->json($users);
    }

    // PUT /api/users/{user}/toggle-status - Activar/desactivar usuario
    public function toggleStatus(Request $request, User $user)
    {
        abort_unless($request->user()->role === 'admin', 403, 'No autorizado');

        // No puede desactivarse a sí mismo
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'No puedes desactivarte a ti mismo'], 422);
        }

        $user->update(['active' => !$user->active]);

        return response()->json([
            'message' => $user->active ? 'Usuario activado' : 'Usuario desactivado',
            'user' => $user
        ]);
    }
    public function show(Request $request, User $user)
    {
        abort_unless($request->user()->role === 'admin', 403, 'No autorizado');
        
        // Cargar relaciones necesarias
        $user->load('projects:id,name,status');
        
        return response()->json([
            'user' => $user,
            'projects' => $user->projects
        ]);
    }

    // PUT /api/users/{user}/role - Cambiar rol de usuario
    public function updateRole(Request $request, User $user)
    {
        abort_unless($request->user()->role === 'admin', 403, 'No autorizado');

        $request->validate([
            'role' => ['required', Rule::in(['admin', 'colaborador'])]
        ]);

        // No puede cambiar su propio rol si es el único admin
        if ($user->id === $request->user()->id) {
            $adminCount = User::where('role', 'admin')->where('active', true)->count();
            if ($adminCount === 1 && $request->role !== 'admin') {
                return response()->json([
                    'message' => 'No puedes cambiar tu rol siendo el único administrador'
                ], 422);
            }
        }

        $user->update(['role' => $request->role]);

        return response()->json([
            'message' => 'Rol actualizado correctamente',
            'user' => $user
        ]);
    }

    // GET /api/users/{user}/projects - Obtener proyectos del usuario
    public function getUserProjects(Request $request, User $user)
    {
        abort_unless($request->user()->role === 'admin', 403, 'No autorizado');

        $projects = $user->projects()->select('id', 'name', 'status')->get();

        return response()->json(['projects' => $projects]);
    }

    // PUT /api/users/{user}/projects - Asignar proyectos al usuario
    public function assignProjects(Request $request, User $user)
    {
        abort_unless($request->user()->role === 'admin', 403, 'No autorizado');

        $request->validate([
            'project_ids' => ['array'],
            'project_ids.*' => ['integer', 'exists:projects,id']
        ]);

        $projectIds = $request->input('project_ids', []);
        $user->projects()->sync($projectIds);

        return response()->json([
            'message' => 'Proyectos asignados correctamente',
            'assigned_count' => count($projectIds)
        ]);
    }

    // GET /api/users/stats - Estadísticas para el dashboard
    public function getStats(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403, 'No autorizado');

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('active', true)->count(),
            'admins' => User::where('role', 'admin')->count(),
            'collaborators' => User::where('role', 'colaborador')->count(),
            'recent_users' => User::where('created_at', '>=', now()->subDays(7))->count()
        ];

        return response()->json($stats);
    }

    // POST /api/users - Crear nuevo usuario (admin)
    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403, 'No autorizado');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'colaborador'])],
            'active' => ['boolean']
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'active' => $request->boolean('active', true)
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'user' => $user
        ], 201);
    }

    // DELETE /api/users/{user} - Eliminar usuario (admin)
    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->role === 'admin', 403, 'No autorizado');

        // No puede eliminarse a sí mismo
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'No puedes eliminarte a ti mismo'], 422);
        }

        // No puede eliminar el último admin
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount === 1) {
                return response()->json([
                    'message' => 'No puedes eliminar el último administrador'
                ], 422);
            }
        }

        // Desasociar de proyectos antes de eliminar
        $user->projects()->detach();
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }
}