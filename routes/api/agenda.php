<?php

use App\Http\Controllers\Api\AgendaController;
use Illuminate\Support\Facades\Route;

Route::prefix('agenda')->middleware('auth:sanctum')->group(function () {
    
    Route::get('/{asignacionId}', [AgendaController::class, 'index']);

    Route::middleware('role:profesor')->group(function () {
        Route::post('/', [AgendaController::class, 'store']);
        Route::delete('/{id}', [AgendaController::class, 'destroy']);
    });

});