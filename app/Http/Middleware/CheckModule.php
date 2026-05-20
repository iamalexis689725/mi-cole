<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModule
{
    public function handle(
        Request $request, 
        Closure $next, 
        string $moduleKey
    ): Response {
        $user = auth()->user();
        
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'message' => 'Acceso denegado: usuario sin tenant asignado o tenant no encontrado.',
            ], 404);
        }

        $module = Module::where('codigo', $moduleKey)
            ->first();

        if (!$module) {
            return response()->json([
                'message' => 'Módulo no encontrado o no existe.',
            ], 404);
        }

        $activo = $tenant->modules()
            ->where('module_id', $module->id)
            ->wherePivot('activo', true)
            ->exists();

        if (!$activo) {
            return response()->json([
                'message' => 'Acceso denegado: módulo no activo o no asignado.',
            ], 403);
        }

        return $next($request);
    }
}
