<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\PeriodoEvaluacion;
use Illuminate\Http\Request;

class PeriodoEvaluacionController extends Controller
{
    public function index(int $periodoId)
    {
        $periodoEvaluacion = PeriodoEvaluacion::with('academicPeriod')
            ->where('academic_period_id', $periodoId)
            ->orderBy('orden')
            ->get();

        return response()->json($periodoEvaluacion);
    }

    public function store(
        Request $request,
        int $periodoId
    ) {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'orden' => 'required|integer|min:1',
        ]);

        $periodoAcademico =
            AcademicPeriod::findOrFail($periodoId);

        $periodo = PeriodoEvaluacion::create([
            'academic_period_id' => $periodoAcademico->id,
            'nombre' => $request->nombre,
            'orden' => $request->orden,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return response()->json(
            $periodo,
            201
        );
    }
}
