<?php

use App\Http\Controllers\Api\ProfesorSubjectController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:director'])->group(function () {

    Route::get('/profesor-subjects', [ProfesorSubjectController::class, 'index']);
    Route::get('/profesor-subjects/{id}', [ProfesorSubjectController::class, 'show']);
    Route::post('/profesor-subjects', [ProfesorSubjectController::class, 'store']);
    Route::put('/profesor-subjects/{id}', [ProfesorSubjectController::class, 'update']);
    Route::delete('/profesor-subjects/{id}', [ProfesorSubjectController::class, 'destroy']);

});