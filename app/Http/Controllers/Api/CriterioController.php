<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Criterio;
use App\Models\PeriodoEvaluacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CriterioController extends Controller
{
    public function index(int $asignacionId): JsonResponse
    {
        $asignacion = AsignacionDocente::findOrFail($asignacionId);

        $this->authorizeProfesor($asignacion);

        $criterios = Criterio::with('periodoEvaluacion')
            ->where('asignacion_docente_id', $asignacionId)
            ->orderBy('id')
            ->get();

        return response()->json($criterios);
    }

    public function periodosAsignacion(
        int $asignacionId
    ): JsonResponse {

        $asignacion = AsignacionDocente::findOrFail(
            $asignacionId
        );

        $this->authorizeProfesor(
            $asignacion
        );

        $periodos = PeriodoEvaluacion::whereHas(
            'criterios',
            function ($query) use ($asignacionId) {
                $query->where(
                    'asignacion_docente_id',
                    $asignacionId
                );
            }
        )
            ->orderBy('orden')
            ->get([
                'id',
                'nombre',
                'orden'
            ]);

        return response()->json(
            $periodos
        );
    }

    public function criteriosPorPeriodo(
        int $periodoId
    ): JsonResponse {

        $criterios = Criterio::with('periodoEvaluacion')
            ->where('periodo_evaluacion_id', $periodoId)
            ->orderBy('id')
            ->get();

        return response()->json($criterios);
    }


    public function misCriteriosPorPeriodo(
        int $periodoId
    ): JsonResponse {

        $profesor = auth()->user()->profesor;

        if (!$profesor) {
            return response()->json([
                'message' => 'Profesor no encontrado'
            ], 403);
        }

        $criterios = Criterio::with([
            'periodoEvaluacion',
            'asignacionDocente.subject:id,name',
            'asignacionDocente.curso:id,nombre',
            'asignacionDocente.paralelo:id,nombre',
        ])
            ->where(
                'periodo_evaluacion_id',
                $periodoId
            )
            ->whereHas(
                'asignacionDocente',
                function ($query) use ($profesor) {
                    $query->where(
                        'profesor_id',
                        $profesor->id
                    );
                }
            )
            ->orderBy('id')
            ->get();

        return response()->json($criterios);
    }

    public function store(Request $request, int $asignacionId): JsonResponse
    {
        $request->validate([
            'periodo_evaluacion_id' => 'required|exists:periodos_evaluacion,id',
            'nombre' => 'required|string|max:255',
            'porcentaje' => 'required|numeric|min:1|max:100',
        ]);

        $asignacion = AsignacionDocente::findOrFail($asignacionId);

        $this->authorizeProfesor($asignacion);

        $total = Criterio::where(
            'periodo_evaluacion_id',
            $request->periodo_evaluacion_id
        )
            ->where(
                'asignacion_docente_id',
                $asignacionId
            )
            ->sum('porcentaje');

        if (($total + $request->porcentaje) > 100) {
            return response()->json([
                'message' => 'La suma de porcentajes para esta materia en este periodo no puede superar 100%'
            ], 422);
        }

        $criterio = Criterio::create([
            'asignacion_docente_id' => $asignacionId,
            'periodo_evaluacion_id' => $request->periodo_evaluacion_id,
            'nombre' => $request->nombre,
            'porcentaje' => $request->porcentaje,
            'tenant_id' => $asignacion->tenant_id,
        ]);

        return response()->json($criterio, 201);
    }

    public function update(Request $request, int $criterioId): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'porcentaje' => 'required|numeric|min:1|max:100',
        ]);

        $criterio = Criterio::findOrFail($criterioId);

        $asignacion = $criterio->asignacionDocente;

        $this->authorizeProfesor($asignacion);

        $total = Criterio::where(
            'periodo_evaluacion_id',
            $criterio->periodo_evaluacion_id
        )
            ->where(
                'asignacion_docente_id',
                $criterio->asignacion_docente_id
            )
            ->where(
                'id',
                '!=',
                $criterio->id
            )
            ->sum('porcentaje');

        if (($total + $request->porcentaje) > 100) {
            return response()->json([
                'message' => 'La suma de porcentajes para esta materia en este periodo no puede superar 100%'
            ], 422);
        }

        $criterio->update([
            'nombre' => $request->nombre,
            'porcentaje' => $request->porcentaje,
        ]);

        return response()->json($criterio);
    }

    public function destroy(
        int $criterioId
    ): JsonResponse {

        $criterio = Criterio::findOrFail(
            $criterioId
        );

        $asignacion = $criterio->asignacionDocente;

        $this->authorizeProfesor(
            $asignacion
        );

        $criterio->delete();

        return response()->json([
            'message' =>
            'Criterio eliminado correctamente'
        ]);
    }

    private function authorizeProfesor(
        AsignacionDocente $asignacion
    ): void {

        $profesor = auth()->user()->profesor;

        if (
            !$profesor ||
            $profesor->id !== $asignacion->profesor_id
        ) {
            abort(403, 'No autorizado');
        }
    }
}
