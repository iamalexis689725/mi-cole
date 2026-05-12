<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnecdotarioController;

Route::middleware(['auth:sanctum', 'role:profesor'])
    ->group(function () {

        Route::get(
            '/anecdotarios',
            [AnecdotarioController::class, 'index']
        );

        Route::post(
            '/anecdotarios',
            [AnecdotarioController::class, 'store']
        );

        Route::get(
            '/anecdotarios/{anecdotario}',
            [AnecdotarioController::class, 'show']
        );

        Route::put(
            '/anecdotarios/{anecdotario}',
            [AnecdotarioController::class, 'update']
        );

        Route::delete(
            '/anecdotarios/{anecdotario}',
            [AnecdotarioController::class, 'destroy']
        );
    });