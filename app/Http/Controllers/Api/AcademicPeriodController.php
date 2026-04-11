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
            'nombre' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'nullable|boolean',
        ]);

        if ($request->activo) {
            AcademicPeriod::where('tenant_id', auth()->user()->tenant_id)
                ->update(['activo' => false]);
        }

        $periodo = AcademicPeriod::create([
            'nombre' => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $request->activo ?? true,
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

        $request->validate([
            'nombre' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'nullable|boolean',
        ]);


        if ($request->activo) {
            AcademicPeriod::where('tenant_id', auth()->user()->tenant_id)
                ->update(['activo' => false]);
        }

        $periodo->update($request->only([
            'nombre',
            'fecha_inicio',
            'fecha_fin',
            'activo'
        ]));

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
        return AcademicPeriod::where('activo', true)->first();
    }
}