<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CircularController;

Route::middleware(['auth:sanctum', 'module:circulares'])->group(function () {

    Route::get(
        '/circulares',
        [CircularController::class, 'index']
    );

    Route::post(
        '/circulares/{id}/leer',
        [CircularController::class, 'marcarLeido']
    );

    Route::get(
        '/circulares/{id}',
        [CircularController::class, 'show']
    );
});

Route::middleware(['auth:sanctum', 'role:director', 'module:circulares'])->group(function () {

    Route::post(
        '/circulares',
        [CircularController::class, 'store']
    );

    Route::get(
        '/circulares/{id}/stats',
        [CircularController::class, 'stats']
    );

    Route::put(
        '/circulares/{id}',
        [CircularController::class, 'update']
    );

    Route::delete(
        '/circulares/{id}',
        [CircularController::class, 'destroy']
    );
});