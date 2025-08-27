<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;

class TaskController extends Controller
{
    // GET /api/tasks?project_id=
    public function index(Request $request)
    {
        $user = $request->user();
        $q = Task::query()->with(['project:id,name', 'assignees:id,name,email']);

        if ($request->filled('project_id')) {
            $q->where('project_id', (int)$request->query('project_id'));
        }

        // Control de acceso por rol
        if ($user->role !== 'admin') {
            // Colaborador solo ve tareas donde está asignado
            $q->whereHas('assignees', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            });
        }

        $tasks = $q->orderByDesc('id')->paginate((int)$request->query('per_page', 10));

        // Transformar datos para compatibilidad con frontend
        $tasks->getCollection()->transform(function ($task) {
            $task->assignee = $task->assignees->first(); // Para compatibilidad
            return $task;
        });

        return $tasks;
    }

    // POST /api/projects/{project}/tasks
    public function store(StoreTaskRequest $request, Project $project)
    {
        \Log::info('=== CREANDO TAREA ===');
        \Log::info('Project ID:', ['id' => $project->id]);
        \Log::info('Request Data:', $request->all());
        
        $data = $request->validated();
        
        // Verificar que los usuarios asignados sean colaboradores del proyecto
        $assignedUserIds = $data['assigned_users'] ?? [];
        foreach ($assignedUserIds as $userId) {
            $isCollaborator = $project->collaborators()->where('users.id', $userId)->exists();
            if (!$isCollaborator) {
                return response()->json([
                    'message' => 'Uno o más usuarios no son colaboradores del proyecto'
                ], 422);
            }
        }

        // Crear la tarea (sin assigned_users en fillable)
        $taskData = collect($data)->except('assigned_users')->toArray();
        $task = $project->tasks()->create($taskData);
        
        // Asignar usuarios a la tarea
        $task->assignees()->sync($assignedUserIds);
        
        \Log::info('Tarea creada exitosamente:', $task->toArray());
        \Log::info('Usuarios asignados:', $assignedUserIds);

        return response()->json(
            $task->load(['project:id,name', 'assignees:id,name,email']),
            201
        );
    }

    // PUT /api/tasks/{task}
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $user = $request->user();
        $data = $request->validated();

        // Control de acceso
        if ($user->role !== 'admin') {
            // Verificar que el usuario esté asignado a la tarea
            $isAssigned = $task->assignees()->where('users.id', $user->id)->exists();
            if (!$isAssigned) {
                abort(403, 'No autorizado');
            }
            // Colaborador solo puede cambiar ciertos campos
            $data = collect($data)->only(['state', 'description'])->toArray();
        }

        // Actualizar campos básicos
        $taskData = collect($data)->except('assigned_users')->toArray();
        $task->update($taskData);

        // Si admin re-asigna usuarios
        if (isset($data['assigned_users']) && $user->role === 'admin') {
            // Verificar que todos sean colaboradores del proyecto
            foreach ($data['assigned_users'] as $userId) {
                $isCollaborator = $task->project->collaborators()->where('users.id', $userId)->exists();
                if (!$isCollaborator) {
                    return response()->json([
                        'message' => 'Uno o más usuarios no son colaboradores del proyecto'
                    ], 422);
                }
            }
            $task->assignees()->sync($data['assigned_users']);
        }

        return $task->load(['project:id,name', 'assignees:id,name,email']);
    }

    // DELETE /api/tasks/{task}
    public function destroy(Request $request, Task $task)
    {
        abort_unless($request->user()->role === 'admin', 403, 'No autorizado');
        
        // Desasociar usuarios antes de eliminar
        $task->assignees()->detach();
        $task->delete();
        
        return response()->json(['message' => 'Tarea eliminada']);
    }
}