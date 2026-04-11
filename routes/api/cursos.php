<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CursoController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/periodos/{periodo}/cursos', [CursoController::class, 'index']);
    
    Route::get('/periodos/{periodo}/cursos/{id}', [CursoController::class, 'show']);

    Route::post('/periodos/{periodo}/cursos', [CursoController::class, 'store'])
        ->middleware('role:director');

    Route::put('/periodos/{periodo}/cursos/{id}', [CursoController::class, 'update'])
        ->middleware('role:director');

    Route::delete('/periodos/{periodo}/cursos/{id}', [CursoController::class, 'destroy'])
        ->middleware('role:director');

    Route::get('/periodos/{periodo}/cursos/{id}/paralelos', [CursoController::class, 'paralelos'])
        ->middleware('role:director');
});