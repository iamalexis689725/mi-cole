<?php

use App\Http\Controllers\Api\PadreAgendaController;
use App\Http\Controllers\Api\PadreAnecdotarioController;
use App\Http\Controllers\Api\PadreAsistenciaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PadreFamiliaController;
use App\Http\Controllers\Api\PadreNotaController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/padre-familias', [PadreFamiliaController::class, 'index'])
        ->middleware('role:director');

    Route::post('/padre-familias', [PadreFamiliaController::class, 'store'])
        ->middleware('role:director');

    Route::get('/padre-familias/{id}', [PadreFamiliaController::class, 'show'])
        ->middleware('role:director');

    Route::put('/padre-familias/{id}', [PadreFamiliaController::class, 'update'])
        ->middleware('role:director');

    Route::delete('/padre-familias/{id}', [PadreFamiliaController::class, 'destroy'])
        ->middleware('role:director');

    Route::post('/padre-familias/asignar-estudiante', [PadreFamiliaController::class, 'asignarEstudiante'])
        ->middleware('role:director');

    Route::get('/padre-familias/{id}/estudiantes', [PadreFamiliaController::class, 'estudiantes'])
        ->middleware('role:director');

    Route::get('/padre/mis-hijos', [PadreFamiliaController::class, 'misHijos'])
        ->middleware('role:padre');

    Route::get(
        '/padre/mis-hijos/agendas',
        [PadreAgendaController::class, 'tareasPendientes']
    )->middleware('role:padre');

    Route::get(
        '/padre/mis-hijos/{estudianteId}/agendas',
        [PadreAgendaController::class, 'agendasPorHijo']
    )->middleware('role:padre');


    Route::get(
        '/padre/mis-hijos/{estudiante}/notas',
        [PadreNotaController::class, 'boleta']
    )->middleware('role:padre');

    Route::get(
        '/padre/mis-hijos/{estudiante}/anecdotarios',
        [PadreAnecdotarioController::class, 'index']
    )->middleware('role:padre');

    Route::get(
        '/padre/hijos/{estudiante}/asistencias',
        [PadreAsistenciaController::class, 'index']
    )->middleware('role:padre');
});
