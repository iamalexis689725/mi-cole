<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AsignacionDocenteController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/periodos/{periodo}/asignaciones', [AsignacionDocenteController::class, 'index'])
        ->middleware('role:director');

    Route::get(
        '/periodos/{periodo}/asignaciones/materias',
        [AsignacionDocenteController::class, 'materias']
    )->middleware('role:director');

    Route::get(
        '/periodos/{periodo}/asignaciones/profesores',
        [AsignacionDocenteController::class, 'profesores']
    )->middleware('role:director');

    Route::get('/periodos/{periodo}/asignaciones/mis-clases', [AsignacionDocenteController::class, 'misClases'])
        ->middleware(['auth:sanctum', 'role:profesor']);

    Route::get('/periodos/{periodo}/asignaciones/{id}', [AsignacionDocenteController::class, 'show'])
        ->middleware('role:director');

    Route::post('/periodos/{periodo}/asignaciones', [AsignacionDocenteController::class, 'store'])
        ->middleware('role:director');

    Route::delete('/periodos/{periodo}/asignaciones/{id}', [AsignacionDocenteController::class, 'destroy'])
        ->middleware('role:director');

    Route::get('/periodos/{periodo}/cursos/{curso}/paralelos/{paralelo}/horario', [AsignacionDocenteController::class, 'horarioCurso'])
        ->middleware(['auth:sanctum', 'role:director']);

    Route::post('/asignaciones/{id}/horarios', [AsignacionDocenteController::class, 'agregarHorario'])
        ->middleware('role:director');
});
