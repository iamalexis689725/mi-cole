<?php

use App\Http\Controllers\Api\InscripcionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:director'])->group(function () {

    Route::get('/inscripciones', [InscripcionController::class, 'index']);
    Route::get('/inscripciones/{id}', [InscripcionController::class, 'show']);
    Route::post('/inscripciones', [InscripcionController::class, 'store']);
    Route::put('/inscripciones/{id}', [InscripcionController::class, 'update']);
    Route::delete('/inscripciones/{id}', [InscripcionController::class, 'destroy']);

});