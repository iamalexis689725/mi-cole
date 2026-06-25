<?php

use App\Http\Controllers\Api\EstudianteAgendaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EstudianteController;

Route::middleware(['auth:sanctum', 'role:director'])->group(function () {

    Route::get('/estudiantes', [EstudianteController::class, 'index']);

    Route::post('/estudiantes', [EstudianteController::class, 'store']);

    Route::get('/estudiantes/{id}', [EstudianteController::class, 'show']);

    Route::put('/estudiantes/{id}', [EstudianteController::class, 'update']);

    Route::delete('/estudiantes/{id}', [EstudianteController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:estudiante', 'module:estudiantes'])->group(function () {

    Route::get(
        '/estudiante/pendientes',
        [EstudianteAgendaController::class, 'pendientes']
    );

    Route::get(
        '/estudiante/biblioteca',
        [EstudianteAgendaController::class, 'biblioteca']
    );

    Route::get(
        '/estudiante/materias',
        [EstudianteAgendaController::class, 'materias']
    );

    Route::get(
        '/estudiante/materias/{asignacionId}',
        [EstudianteAgendaController::class, 'detalleMateria']
    );

    Route::get(
        '/estudiante/horario',
        [EstudianteController::class, 'horario']
    );
});
