<?php

use App\Http\Controllers\Api\NotaController;
use Illuminate\Support\Facades\Route;

Route::post('/notas', [NotaController::class, 'store']);
Route::get('/promedio/{estudiante}/{asignacion}', [NotaController::class, 'promedio']);