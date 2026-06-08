<?php

use App\Http\Controllers\Api\PromedioController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'role:profesor|director'
])->group(function () {

    Route::get(
        '/periodos-evaluacion/{periodoId}/promedio',
        [PromedioController::class, 'promedioPeriodo']
    );

    Route::get(
        '/estudiantes/{estudiante}/promedio-final',
        [PromedioController::class, 'promedioFinal']
    );

    Route::get(
        '/estudiantes/{estudiante}/boletin',
        [PromedioController::class, 'boletin']
    );
});
