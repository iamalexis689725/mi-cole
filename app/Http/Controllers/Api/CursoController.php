<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Curso;
use Illuminate\Http\Request;

class CursoController extends Controller
{

    public function index($periodoId)
    {
        return Curso::with('periodo', 'paralelos')
            ->where('academic_period_id', $periodoId)
            ->get();
    }

    public function store(Request $request, $periodoId)
    {
        $request->validate([
            'nombre' => 'required|string',
            'nivel' => 'required|string',
            'descripcion' => 'nullable|string',
        ]);

        $periodo = AcademicPeriod::where('id', $periodoId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        if (!$periodo->activo) {
            return response()->json([
                'message' => 'No se pueden crear cursos en un periodo inactivo'
            ], 403);
        }

        $curso = Curso::create([
            'nombre' => $request->nombre,
            'nivel' => $request->nivel,
            'descripcion' => $request->descripcion,
            'academic_period_id' => $periodoId,
        ]);

        $curso->load('periodo');

        return response()->json($curso, 201);
    }

    public function show($periodoId, $id)
    {
        return Curso::where('academic_period_id', $periodoId)
            ->with('paralelos')
            ->findOrFail($id);
    }

    public function update(Request $request, $periodoId, $id)
    {
        $curso = Curso::where('academic_period_id', $periodoId)
            ->findOrFail($id);

        $curso->update($request->only([
            'nombre',
            'nivel',
            'descripcion',
            'estado'
        ]));

        return response()->json($curso);
    }

    public function destroy($periodoId, $id)
    {
        Curso::where('academic_period_id', $periodoId)
            ->findOrFail($id)
            ->delete();

        return response()->json(['message' => 'Curso eliminado']);
    }

    public function paralelos($periodoId, $id)
    {
        $curso = Curso::where('academic_period_id', $periodoId)
            ->with('paralelos')
            ->findOrFail($id);

        return response()->json([
            'data' => $curso->paralelos
        ]);
    }
}
