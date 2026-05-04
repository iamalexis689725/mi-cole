<?php

use App\Http\Controllers\Api\AsistenciaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:profesor'])->group(function () {

    Route::post(
        '/periodos/{periodo}/cursos/{curso}/paralelos/{paralelo}/asistencia',
        [AsistenciaController::class, 'store']
    );

    Route::get(
        '/periodos/{periodo}/cursos/{curso}/paralelos/{paralelo}/asistencia/{fecha}',
        [AsistenciaController::class, 'showByDate']
    );

});