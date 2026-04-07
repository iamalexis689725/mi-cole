<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CursoController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/cursos', [CursoController::class, 'index']);
    Route::get('/cursos/{id}', [CursoController::class, 'show']);

    Route::post('/cursos', [CursoController::class, 'store'])
        ->middleware('role:director');

    Route::put('/cursos/{id}', [CursoController::class, 'update'])
        ->middleware('role:director');
    Route::delete('/cursos/{id}', [CursoController::class, 'destroy'])
        ->middleware('role:director');
});