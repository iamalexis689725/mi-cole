<?php

use App\Http\Controllers\Api\AsignacionDocenteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/asignaciones', [AsignacionDocenteController::class, 'index']);
    Route::post('/asignaciones', [AsignacionDocenteController::class, 'store']);
    Route::get('/asignaciones/{id}', [AsignacionDocenteController::class, 'show']);
    Route::delete('/asignaciones/{id}', [AsignacionDocenteController::class, 'destroy']);
});
