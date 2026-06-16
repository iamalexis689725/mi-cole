<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfesorController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/profesores', [ProfesorController::class, 'index'])
        ->middleware('role:director');

    Route::post('/profesores', [ProfesorController::class, 'store'])
        ->middleware('role:director');

    Route::get('/profesores/{id}', [ProfesorController::class, 'show'])
        ->middleware('role:director');

    Route::put('/profesores/{id}', [ProfesorController::class, 'update'])
        ->middleware('role:director');

    Route::delete('/profesores/{id}', [ProfesorController::class, 'destroy'])
        ->middleware('role:director');

    Route::post('/profesores/asignar-materia', [ProfesorController::class, 'asignarMateria'])
        ->middleware('role:director');

    Route::get('/profesores/{id}/subjects', [ProfesorController::class, 'subjects'])
        ->middleware('role:director');

    Route::get('/profesores/{id}/horario', [ProfesorController::class, 'horario'])
        ->middleware('role:director');

    Route::post('/profesores/quitar-materia', [ProfesorController::class, 'quitarMateria'])
        ->middleware('role:director');
});
