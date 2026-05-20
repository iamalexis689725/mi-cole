<?php

use App\Http\Controllers\Api\AgendaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:profesor', 'module:agenda'])->group(function () {

    Route::get(
        '/periodos/{periodo}/asignaciones/{asignacion}/agenda',
        [AgendaController::class, 'index']
    );

    Route::post(
        '/periodos/{periodo}/asignaciones/{asignacion}/agenda',
        [AgendaController::class, 'store']
    );

    Route::get(
        '/agenda/{id}',
        [AgendaController::class, 'show']
    );

    Route::put(
        '/agenda/{id}',
        [AgendaController::class, 'update']
    );

    Route::delete(
        '/agenda/{id}',
        [AgendaController::class, 'destroy']
    );

    Route::post(
        '/agenda/{id}/subir-archivo',
        [AgendaController::class, 'subirArchivo']
    );

    Route::delete(
        '/agenda-archivos/{id}',
        [AgendaController::class, 'eliminarArchivo']
    );

    Route::post(
        '/agenda-archivos/{id}/reemplazar',
        [AgendaController::class, 'reemplazarArchivo']
    );
});
