<?php

use App\Http\Controllers\Api\AsistenciaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:profesor', 'module:asistencia'])->group(function () {

    Route::post(
        '/periodos/{periodo}/asignaciones/{asignacion}/asistencia',
        [AsistenciaController::class, 'store']
    );

    Route::get(
        '/periodos/{periodo}/asignaciones/{asignacion}/asistencia/{fecha}',
        [AsistenciaController::class, 'show']
    );

    Route::get(
        '/periodos/{periodo}/asignaciones/{asignacion}/asistencia',
        [AsistenciaController::class, 'index']
    );

    Route::put(
        '/asistencia/{id}',
        [AsistenciaController::class, 'update']
    );
});