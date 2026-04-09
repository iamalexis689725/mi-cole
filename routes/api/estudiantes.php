<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EstudianteController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/estudiantes', [EstudianteController::class, 'index'])
        ->middleware('role:director');

    Route::post('/estudiantes', [EstudianteController::class, 'store'])
        ->middleware('role:director');

    Route::get('/estudiantes/{id}', [EstudianteController::class, 'show'])
        ->middleware('role:director');

    Route::put('/estudiantes/{id}', [EstudianteController::class, 'update'])
        ->middleware('role:director');

    Route::delete('/estudiantes/{id}', [EstudianteController::class, 'destroy'])
        ->middleware('role:director');
});