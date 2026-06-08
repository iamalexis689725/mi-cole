<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nota;
use Illuminate\Http\JsonResponse;

class PromedioController extends Controller
{
    public function promedioPeriodo(
        int $periodoId
    ): JsonResponse {

        $notas = Nota::with([
            'criterio',
            'estudiante.user'
        ])
            ->whereHas(
                'criterio',
                function ($query) use ($periodoId) {
                    $query->where(
                        'periodo_evaluacion_id',
                        $periodoId
                    );
                }
            )
            ->get();

        $resultado = [];

        foreach ($notas->groupBy('estudiante_id') as $estudianteId => $items) {

            $promedio = 0;

            foreach ($items as $nota) {

                $promedio +=
                    $nota->nota *
                    ($nota->criterio->porcentaje / 100);
            }

            $resultado[] = [
                'estudiante_id' => $estudianteId,
                'nombre' => $items->first()->estudiante->user->name,
                'promedio' => round($promedio, 2),
            ];
        }

        return response()->json($resultado);
    }


    public function promedioFinal(
        int $estudianteId
    ): JsonResponse {

        $notas = Nota::with('criterio')
            ->where(
                'estudiante_id',
                $estudianteId
            )
            ->get();

        $periodos = [];

        foreach ($notas as $nota) {

            $periodoId =
                $nota->criterio->periodo_evaluacion_id;

            if (!isset($periodos[$periodoId])) {
                $periodos[$periodoId] = 0;
            }

            $periodos[$periodoId] +=
                $nota->nota *
                ($nota->criterio->porcentaje / 100);
        }

        $promedioFinal =
            count($periodos)
            ? array_sum($periodos) / count($periodos)
            : 0;

        return response()->json([
            'estudiante_id' => $estudianteId,
            'promedio_final' => round(
                $promedioFinal,
                2
            ),
        ]);
    }

    public function boletin(
        int $estudianteId
    ): JsonResponse {

        $notas = Nota::with([
            'criterio.periodoEvaluacion',
            'estudiante.user'
        ])
            ->where(
                'estudiante_id',
                $estudianteId
            )
            ->get();

        $periodos = [];

        foreach ($notas as $nota) {

            $periodo =
                $nota->criterio
                ->periodoEvaluacion;

            $id = $periodo->id;

            if (!isset($periodos[$id])) {

                $periodos[$id] = [
                    'periodo' => $periodo->nombre,
                    'promedio' => 0,
                ];
            }

            $periodos[$id]['promedio'] +=
                $nota->nota *
                ($nota->criterio->porcentaje / 100);
        }

        $lista = array_values($periodos);

        $promedioFinal =
            count($lista)
            ? collect($lista)->avg('promedio')
            : 0;

        return response()->json([
            'estudiante' =>
            $notas->first()?->estudiante?->user?->name,
            'periodos' => $lista,
            'promedio_final' => round(
                $promedioFinal,
                2
            ),
        ]);
    }
}
