<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotaController;

Route::middleware([
    'auth:sanctum',
    'role:profesor'
])->group(function () {

    Route::get(
        '/criterios/{criterio}/notas',
        [NotaController::class, 'index']
    );

    Route::post(
        '/criterios/{criterio}/notas',
        [NotaController::class, 'store']
    );

    /* Route::get(
        '/asignaciones/{asignacion}/libro-calificaciones',
        [NotaController::class, 'libroCalificaciones']
    ); */

    Route::get(
        '/asignaciones/{asignacion}/periodos-evaluacion/{periodo}/libro-calificaciones',
        [NotaController::class, 'libroCalificaciones']
    );
});
