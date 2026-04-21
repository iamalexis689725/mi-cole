<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CircularController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/circulares', [CircularController::class, 'index']);

    Route::post('/circulares', [CircularController::class, 'store'])
        ->middleware('role:director');

    Route::post('/circulares/{id}/leer', [CircularController::class, 'marcarLeido']);

    Route::get('/circulares/{id}/stats', [CircularController::class, 'stats'])
        ->middleware('role:director');

    Route::put('/circulares/{id}', [CircularController::class, 'update'])
        ->middleware('role:director');

    Route::delete('/circulares/{id}', [CircularController::class, 'destroy'])
        ->middleware('role:director');

    Route::get('/circulares/{id}', [CircularController::class, 'show'])
        ->middleware('auth:sanctum');
});
