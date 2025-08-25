<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $user = auth()->user();
    $q = Project::with('users:id,name','tasks');
        if ($user->role !== 'admin') {
        $q->whereHas('users', fn($x)=>$x->where('users.id',$user->id));
        }
    return $q->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request) {
        $this->authorizeAdmin();
        $project = Project::create($request->validated());
        if ($ids = $request->input('user_ids')) $project->users()->sync($ids);
        return response()->json($project->load('users'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project) {
        $this->authorizeProjectAccess($project);
        return $project->load('users:id,name','tasks');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project) {
        $this->authorizeAdmin();
        $project->update($request->validated());
        if ($ids = $request->input('user_ids')) $project->users()->sync($ids);
        return $project->load('users','tasks');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project) {
        $this->authorizeAdmin();
        $project->delete();
        return response()->noContent();
    }

     private function authorizeAdmin(){ 
        abort_unless(auth()->user()?->role==='admin', 403); 
    }
    
    private function authorizeProjectAccess(Project $project){
        $u = auth()->user();
        if ($u->role==='admin') return;
        abort_unless($project->users()->whereKey($u->id)->exists(), 403);
    }
}
