<?php

use App\Http\Controllers\Api\CriterioController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'role:profesor'
])->group(function () {

    Route::get(
        '/criterios/asignacion/{asignacionId}',
        [CriterioController::class, 'index']
    );

    Route::get(
        '/periodos-evaluacion/{periodoId}/criterios',
        [CriterioController::class, 'criteriosPorPeriodo']
    );

    Route::get(
        '/periodos-evaluacion/{periodo}/mis-criterios',
        [CriterioController::class, 'misCriteriosPorPeriodo']
    );

    Route::get(
        '/asignaciones/{asignacion}/periodos-evaluacion',
        [CriterioController::class, 'periodosAsignacion']
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
