<?php

use App\Http\Controllers\Api\TenantModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'role:super-admin'
])->group(function () {

    Route::get(
        '/tenants/{tenantId}/modules',
        [TenantModuleController::class, 'index']
    );

    Route::post(
        '/tenants/{tenantId}/modules',
        [TenantModuleController::class, 'assign']
    );

    Route::delete(
        '/tenants/{tenantId}/modules/{moduleId}',
        [TenantModuleController::class, 'remove']
    );

    Route::patch(
        '/tenants/{tenantId}/modules/{moduleId}/activate',
        [TenantModuleController::class, 'activate']
    );

    Route::patch(
        '/tenants/{tenantId}/modules/{moduleId}/deactivate',
        [TenantModuleController::class, 'deactivate']
    );
});
