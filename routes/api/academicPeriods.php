<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AcademicPeriodController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/periodos', [AcademicPeriodController::class, 'index'])
        ->middleware('role:director|profesor');

    Route::post('/periodos', [AcademicPeriodController::class, 'store'])
        ->middleware('role:director');

    Route::get('/periodos/activo', [AcademicPeriodController::class, 'activo'])
        ->middleware('role:director|profesor');

    Route::get('/periodos/{id}', [AcademicPeriodController::class, 'show'])
        ->middleware('role:director');

    Route::put('/periodos/{id}', [AcademicPeriodController::class, 'update'])
        ->middleware('role:director');

    Route::delete('/periodos/{id}', [AcademicPeriodController::class, 'destroy'])
        ->middleware('role:director');

    Route::patch('/periodos/{id}/activar', [AcademicPeriodController::class, 'activar'])
        ->middleware('role:director');
});
