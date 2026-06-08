<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PeriodoEvaluacionController;

Route::middleware([
    'auth:sanctum',
    'role:director|profesor'
])->group(function () {

    Route::get(
        '/periodos/{periodo}/periodos-evaluacion',
        [PeriodoEvaluacionController::class, 'index']
    );

    Route::post(
        '/periodos/{periodo}/periodos-evaluacion',
        [PeriodoEvaluacionController::class, 'store']
    );
});