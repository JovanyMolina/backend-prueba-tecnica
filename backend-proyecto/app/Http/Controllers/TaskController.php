<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $req) {
    $this->ensureAdmin();
    $task = Task::create($req->validated());
        return response()->json($task->load('project','assignee:id,name'),201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $req, Task $task)
    {
    $u = auth()->user();
    $data = $req->validated();
        // si intenta cambiar state y no es el asignado, 403
        if (array_key_exists('state',$data) && $u->id !== $task->assigned_to && $u->role!=='admin') {
            abort(403);
        }
        // si no es admin, bloquea cambios “sensibles”
        if ($u->role!=='admin') {
            unset($data['project_id'], $data['assigned_to'], $data['priority'], $data['due_date'], $data['title'], $data['description']);
        }
        $task->update($data);
        return $task->load('project','assignee:id,name');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
