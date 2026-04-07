<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ParaleloController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/paralelos', [ParaleloController::class, 'index']);
    Route::get('/paralelos/{id}', [ParaleloController::class, 'show']);

    Route::post('/paralelos', [ParaleloController::class, 'store'])
        ->middleware('role:director');

    Route::put('/paralelos/{id}', [ParaleloController::class, 'update']);
    Route::delete('/paralelos/{id}', [ParaleloController::class, 'destroy']);
});