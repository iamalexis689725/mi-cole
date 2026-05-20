<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantModuleController extends Controller
{
    public function index(int $tenantId)
    {
        $tenant = Tenant::with('modules')
            ->findOrFail($tenantId);

        return response()->json(
            $tenant->modules->map(function ($module) {
                return [
                    'id' => $module->id,
                    'codigo' => $module->codigo,
                    'nombre' => $module->nombre,
                    'descripcion' => $module->descripcion,
                    'activo' => $module->pivot->activo,
                ];
            })
        );
    }

    public function assign(Request $request, int $tenantId)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
        ]);

        $tenant = Tenant::findOrFail($tenantId);

        $tenant->modules()->syncWithoutDetaching([
            $request->module_id => ['activo' => true],
        ]);

        return response()->json(['message' => 'Módulo asignado correctamente.']);
    }

    public function remove(int $tenantId, int $moduleId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->modules()->detach($moduleId);
        return response()->json(['message' => 'Módulo removido correctamente.']);
    }

    public function activate(
        int $tenantId,
        int $moduleId
    ) {
        $tenant = Tenant::findOrFail($tenantId);

        $tenant->modules()->syncWithoutDetaching([
            $moduleId => [
                'activo' => true
            ]
        ]);

        $tenant->modules()->updateExistingPivot(
            $moduleId,
            [
                'activo' => true
            ]
        );

        return response()->json([
            'message' => 'Módulo activado correctamente.'
        ]);
    }

    public function deactivate(
        int $tenantId,
        int $moduleId
    ) {
        $tenant = Tenant::findOrFail($tenantId);

        $tenant->modules()->updateExistingPivot(
            $moduleId,
            [
                'activo' => false
            ]
        );

        return response()->json([
            'message' => 'Módulo desactivado correctamente.'
        ]);
    }
}
