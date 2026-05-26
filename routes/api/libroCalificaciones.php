<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LibroCalificacionesController;

Route::middleware([
    'auth:sanctum',
    'role:profesor'
])->group(function () {

    Route::get(
        '/asignaciones/{asignacion}/libro-calificaciones',
        [LibroCalificacionesController::class, 'index']
    );

    Route::post(
        '/libro-calificaciones',
        [LibroCalificacionesController::class, 'store']
    );
});