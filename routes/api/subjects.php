<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SubjectController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/subjects', [SubjectController::class, 'index'])
        ->middleware('role:director|profesor');

    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])
        ->middleware('role:director|profesor');

    Route::post('/subjects', [SubjectController::class, 'store'])
        ->middleware('role:director');

    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])
        ->middleware('role:director');

    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])
        ->middleware('role:director');

    Route::get(
        '/subjects/{id}/profesores',
        [SubjectController::class, 'profesores']
    )->middleware('role:director');
});
