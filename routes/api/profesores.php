<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfesorController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/profesores', [ProfesorController::class, 'index'])
        ->middleware('role:director');

    Route::post('/profesores', [ProfesorController::class, 'store'])
        ->middleware('role:director');

});