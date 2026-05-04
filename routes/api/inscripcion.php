<?php

use App\Http\Controllers\Api\InscripcionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:director'])->group(function () {

    Route::get('/periodos/{periodo}/inscripciones', [InscripcionController::class, 'index']);
    Route::get('/periodos/{periodo}/inscripciones/{id}', [InscripcionController::class, 'show']);
    Route::post('/periodos/{periodo}/inscripciones', [InscripcionController::class, 'store']);
    Route::put('/periodos/{periodo}/inscripciones/{id}', [InscripcionController::class, 'update']);
    Route::delete('/periodos/{periodo}/inscripciones/{id}', [InscripcionController::class, 'destroy']);

});


Route::middleware(['auth:sanctum', 'role:profesor'])->group(function () {
    Route::get(
        '/periodos/{periodo}/cursos/{curso}/paralelos/{paralelo}/estudiantes',
        [InscripcionController::class, 'estudiantesPorClase']
    );
});
