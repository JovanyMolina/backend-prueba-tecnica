<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;

class ProjectController extends Controller
{
    // GET /api/projects
    public function index(Request $request)
    {
        $user = $request->user();
        $q = Project::query();

        // Filtro de búsqueda
        if ($s = $request->query('search')) {
            $q->where('name', 'like', "%{$s}%");
        }

        // Control de acceso por rol
        if ($user->role === 'admin') {
            // Admin ve todos los proyectos
            $q->withCount('collaborators');
        } else {
            // Colaborador solo ve proyectos donde está asignado
            $q->whereHas('collaborators', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })->withCount('collaborators');
        }

        return ProjectResource::collection(
            $q->orderByDesc('id')->paginate(10)
        );
    }

    // GET /api/projects/{project}
    public function show(Request $request, Project $project)
    {
        $user = $request->user();

        // Verificar acceso: admin o colaborador del proyecto
        if ($user->role !== 'admin') {
            $isCollaborator = $project->collaborators()->where('users.id', $user->id)->exists();
            if (!$isCollaborator) {
                abort(403, 'No tienes acceso a este proyecto');
            }
        }

        // Cargar relaciones
        $project->load(['collaborators:id,name,email,role'])
                ->loadCount('collaborators');

        return new ProjectResource($project);
    }

    // POST /api/projects  (ADMIN)
    public function store(StoreProjectRequest $request)
    {
        // Solo admin puede crear proyectos
        abort_unless($request->user()->role === 'admin', 403, 'Solo administradores pueden crear proyectos');

        $data = $request->validated();
        $project = Project::create($data);

        // Asignación de colaboradores
        $collaboratorIds = $request->input('collaborators', []);
        $project->collaborators()->sync($collaboratorIds);

        $project->loadCount('collaborators');


    \Log::info('Creando proyecto:', $request->all());
    
   /*  $data = $request->validated(); */
    \Log::info('Datos validados:', $data);
    
   /*  $project = Project::create($data); */
    \Log::info('Proyecto creado:', $project->toArray());
    
        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    // PUT /api/projects/{project}  (ADMIN)
    public function update(UpdateProjectRequest $request, Project $project)
    {
        // Solo admin puede actualizar proyectos
        abort_unless($request->user()->role === 'admin', 403, 'Solo administradores pueden actualizar proyectos');

        $project->update($request->validated());

        if ($request->has('collaborators')) {
            $project->collaborators()->sync($request->input('collaborators', []));
        }

        $project->loadCount('collaborators');

        return new ProjectResource($project);
    }

    // DELETE /api/projects/{project}  (ADMIN)
    public function destroy(Request $request, Project $project)
    {
        // Solo admin puede eliminar proyectos
        abort_unless($request->user()->role === 'admin', 403, 'Solo administradores pueden eliminar proyectos');

        // Eliminar tareas relacionadas (si las hay)
        $project->tasks()->delete();
        
        // Desasociar colaboradores
        $project->collaborators()->detach();
        
        // Eliminar proyecto
        $project->delete();

        return response()->noContent();
    }

    // GET /api/projects/{project}/collaborators - Obtener colaboradores de un proyecto
    public function getCollaborators(Request $request, Project $project)
    {
        $user = $request->user();

        // Verificar acceso
        if ($user->role !== 'admin') {
            $isCollaborator = $project->collaborators()->where('users.id', $user->id)->exists();
            if (!$isCollaborator) {
                abort(403, 'No tienes acceso a este proyecto');
            }
        }

        return response()->json([
            'data' => $project->collaborators->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
            ])
        ]);
    }
}