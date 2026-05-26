<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Criterio;
use App\Models\Inscripcion;
use App\Models\Nota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibroCalificacionesController extends Controller
{
    public function index(int $asignacionId): JsonResponse
    {
        $asignacion = AsignacionDocente::findOrFail(
            $asignacionId
        );

        $this->authorizeProfesor($asignacion);

        // CRITERIOS
        $criterios = Criterio::where(
            'asignacion_docente_id',
            $asignacionId
        )
            ->orderBy('id')
            ->get();

        // ESTUDIANTES
        $inscripciones = Inscripcion::with(
            'estudiante.user'
        )
            ->where(
                'curso_id',
                $asignacion->curso_id
            )
            ->where(
                'paralelo_id',
                $asignacion->paralelo_id
            )
            ->where(
                'academic_period_id',
                $asignacion->academic_period_id
            )
            ->get();

        $resultado = $inscripciones->map(
            function ($inscripcion) use ($criterios) {

                $notas = [];

                $promedio = 0;

                foreach ($criterios as $criterio) {

                    $nota = Nota::where(
                        'criterio_id',
                        $criterio->id
                    )
                        ->where(
                            'estudiante_id',
                            $inscripcion->estudiante_id
                        )
                        ->first();

                    $valor = $nota?->nota ?? 0;

                    $notas[] = [
                        'criterio_id' => $criterio->id,
                        'criterio' => $criterio->nombre,
                        'porcentaje' => $criterio->porcentaje,
                        'nota' => $valor,
                    ];

                    // PROMEDIO PONDERADO
                    $promedio +=
                        ($valor * $criterio->porcentaje) / 100;
                }

                return [
                    'estudiante_id' =>
                        $inscripcion->estudiante->id,

                    'estudiante' =>
                        $inscripcion->estudiante->user->name,

                    'notas' => $notas,

                    'promedio' => round($promedio, 2),
                ];
            }
        );

        return response()->json([
            'criterios' => $criterios,
            'estudiantes' => $resultado,
        ]);
    }

    // GUARDAR NOTAS
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'notas' => 'required|array',
            'notas.*.criterio_id' => 'required|exists:criterios,id',
            'notas.*.estudiante_id' => 'required|exists:estudiantes,id',
            'notas.*.nota' => 'required|numeric|min:0|max:100',
        ]);

        foreach ($request->notas as $item) {

            $criterio = Criterio::with('asignacionDocente')
                ->findOrFail($item['criterio_id']);

            $this->authorizeProfesor(
                $criterio->asignacionDocente
            );

            Nota::updateOrCreate(
                [
                    'criterio_id' => $item['criterio_id'],
                    'estudiante_id' => $item['estudiante_id'],
                ],
                [
                    'nota' => $item['nota'],
                    'tenant_id' => $criterio->tenant_id,
                ]
            );
        }

        return response()->json([
            'message' => 'Libro actualizado correctamente'
        ]);
    }

    // AUTH
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