<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use Illuminate\Http\Request;

class AcademicPeriodController extends Controller
{

    public function index()
    {
        return AcademicPeriod::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|unique:academic_periods,nombre',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'nullable|boolean',
        ]);

        $isActivo = $request->activo ?? false;

        if ($isActivo) {
            AcademicPeriod::where('tenant_id', auth()->user()->tenant_id)
                ->update(['activo' => false]);
        }

        $periodo = AcademicPeriod::create([
            'nombre' => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $isActivo,
        ]);

        return response()->json($periodo, 201);
    }

    public function show($id)
    {
        return AcademicPeriod::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $periodo = AcademicPeriod::findOrFail($id);

        if ($request->has('activo') && count($request->all()) === 1) {

            if ($request->activo) {
                AcademicPeriod::where('tenant_id', auth()->user()->tenant_id)
                    ->update(['activo' => false]);
            }

            $periodo->update([
                'activo' => $request->activo
            ]);

            return response()->json($periodo);
        }


        $request->validate([
            'nombre' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'nullable|boolean',
        ]);

        $isActivo = $request->activo ?? $periodo->activo;

        if ($isActivo) {
            AcademicPeriod::where('tenant_id', auth()->user()->tenant_id)
                ->where('id', '!=', $id)
                ->update(['activo' => false]);
        }

        $periodo->update([
            'nombre' => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $isActivo,
        ]);

        return response()->json($periodo);
    }

    public function destroy($id)
    {
        AcademicPeriod::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Periodo eliminado correctamente'
        ]);
    }

    public function activo()
    {
        $periodo = AcademicPeriod::where('activo', true)->first();

        if (!$periodo) {
            return response()->json([
                'message' => 'No hay periodo activo'
            ], 404);
        }

        return response()->json($periodo);
    }

    public function activar($id)
    {
        $periodo = AcademicPeriod::findOrFail($id);
        
        AcademicPeriod::where('tenant_id', auth()->user()->tenant_id)
            ->update(['activo' => false]);

        $periodo->update([
            'activo' => true
        ]);

        return response()->json([
            'message' => 'Periodo activado correctamente',
            'data' => $periodo
        ]);
    }
}
