<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotaController;

Route::middleware([
    'auth:sanctum',
    'role:profesor'
])->group(function () {

    Route::get(
        '/criterios/{criterio}/notas',
        [NotaController::class, 'index']
    );

    Route::post(
        '/criterios/{criterio}/notas',
        [NotaController::class, 'store']
    );
});