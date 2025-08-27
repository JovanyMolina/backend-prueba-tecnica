<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;

// ----- PÚBLICO -----
Route::post('/login',    [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);

// ----- AUTENTICADO -----
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/me', fn (Request $request) => $request->user());

    // Proyectos: lectura para cualquier autenticado
    Route::get('/projects',           [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    // Tareas: lectura/actualización (admin ve todas; colaborador solo las suyas)
    Route::get('/tasks',        [TaskController::class, 'index']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);

    // ----- SOLO ADMIN -----
    Route::middleware('role:admin')->group(function () {

        // CRUD de proyectos
        Route::post('/projects',                 [ProjectController::class, 'store']);
        Route::put('/projects/{project}',        [ProjectController::class, 'update']);
        Route::delete('/projects/{project}',     [ProjectController::class, 'destroy']);

        // Tareas: crear/eliminar
        Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
        Route::delete('/tasks/{task}',           [TaskController::class, 'destroy']);

        // Usuarios (para selects de colaboradores, gestión)
        Route::apiResource('users', UserController::class)->except(['create','edit']);
        Route::patch('users/{user}/role', [UserController::class, 'updateRole']);
    });
});
