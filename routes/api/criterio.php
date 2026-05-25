<?php

use App\Http\Controllers\Api\CriterioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:director|profesor'])->group(function () {

    Route::get(
        '/criterios/asignacion/{asignacionId}',
        [CriterioController::class, 'index']
    );

    Route::post(
        '/criterios/asignacion/{asignacionId}',
        [CriterioController::class, 'store']
    );

    Route::put(
        '/criterios/{criterioId}',
        [CriterioController::class, 'update']
    );

    Route::delete(
        '/criterios/{criterioId}',
        [CriterioController::class, 'destroy']
    );
});