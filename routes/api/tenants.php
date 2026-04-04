<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/tenants', [TenantController::class, 'store'])
        ->middleware('role:super-admin');

});