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

    // RUTAS ESPECÍFICAS DE USUARIOS (ANTES QUE LAS GENÉRICAS)
    Route::get('/users/stats', [UserController::class, 'getStats']);
    Route::get('/users/{user}/projects', [UserController::class, 'getUserProjects']);
    Route::put('/users/{user}/projects', [UserController::class, 'assignProjects']);
    Route::put('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
    
    // RUTAS GENÉRICAS DE USUARIOS
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Proyectos
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    // Tareas
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);

    // ----- SOLO ADMIN -----
    Route::middleware('role:admin')->group(function () {
        // CRUD de proyectos
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

        // Tareas: crear/eliminar
        Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    });
});
