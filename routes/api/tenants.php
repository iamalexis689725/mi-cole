<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantController;

Route::middleware(['auth:sanctum'])->group(function () {

    
    Route::post('/tenants', [TenantController::class, 'store'])->middleware('role:super-admin');
    Route::get('/tenants', [TenantController::class, 'index'])->middleware('role:super-admin');
    Route::get('/tenants/{id}', [TenantController::class, 'show'])->middleware('role:super-admin');
    Route::delete('/tenants/{id}', [TenantController::class, 'destroy'])->middleware('role:super-admin');

    
    Route::get('/my-tenant', [TenantController::class, 'myTenant'])->middleware('role:director');
    Route::post('/tenants/logo', [TenantController::class, 'uploadLogo'])->middleware('role:director');
    Route::put('/tenants', [TenantController::class, 'update'])->middleware('role:director');
});