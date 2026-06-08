<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Criterio;
use App\Models\Inscripcion;
use App\Models\Nota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotaController extends Controller
{
    public function index(int $criterioId): JsonResponse
    {
        $criterio = Criterio::with('asignacionDocente')
            ->findOrFail($criterioId);

        $this->authorizeProfesor($criterio->asignacionDocente);

        $asignacion = $criterio->asignacionDocente;

        $inscripciones = Inscripcion::with('estudiante.user')
            ->where('curso_id', $asignacion->curso_id)
            ->where('paralelo_id', $asignacion->paralelo_id)
            ->where(
                'academic_period_id',
                $asignacion->academic_period_id
            )
            ->get();

        $resultado = $inscripciones->map(function ($inscripcion) use ($criterio) {

            $nota = Nota::where(
                'criterio_id',
                $criterio->id
            )
                ->where(
                    'estudiante_id',
                    $inscripcion->estudiante_id
                )
                ->first();

            return [
                'id' => $inscripcion->estudiante->id,
                'nombre' => $inscripcion->estudiante->user?->name,
                'nota_id' => $nota?->id,
                'nota' => $nota?->nota,
                'observacion' => $nota?->observacion,
            ];
        });

        return response()->json($resultado);
    }

    public function store(
        Request $request,
        int $criterioId
    ): JsonResponse {

        $request->validate([
            'notas' => 'required|array',
            'notas.*.estudiante_id' => 'required|exists:estudiantes,id',
            'notas.*.nota' => 'required|numeric|min:0|max:100',
            'notas.*.observacion' => 'nullable|string',
        ]);

        $criterio = Criterio::with('asignacionDocente')
            ->findOrFail($criterioId);

        $this->authorizeProfesor($criterio->asignacionDocente);

        foreach ($request->notas as $item) {

            Nota::updateOrCreate(
                [
                    'criterio_id' => $criterio->id,
                    'estudiante_id' => $item['estudiante_id'],
                ],
                [
                    'nota' => $item['nota'],
                    'observacion' => $item['observacion'] ?? null,
                    'tenant_id' => $criterio->tenant_id,
                ]
            );
        }

        return response()->json([
            'message' => 'Notas guardadas correctamente'
        ]);
    }

    public function libroCalificaciones(
        int $asignacionId
    ): JsonResponse {

        $asignacion = AsignacionDocente::findOrFail($asignacionId);

        $this->authorizeProfesor($asignacion);

        $criterios = Criterio::where(
            'asignacion_docente_id',
            $asignacionId
        )
            ->select(
                'id',
                'nombre',
                'porcentaje'
            )
            ->orderBy('id')
            ->get();

        $inscripciones = Inscripcion::with('estudiante.user')
            ->where('curso_id', $asignacion->curso_id)
            ->where('paralelo_id', $asignacion->paralelo_id)
            ->where(
                'academic_period_id',
                $asignacion->academic_period_id
            )
            ->get();

        $criterioIds = $criterios->pluck('id');

        $notas = Nota::whereIn(
            'criterio_id',
            $criterioIds
        )->get();

        $estudiantes = $inscripciones->map(
            function ($inscripcion) use (
                $criterios,
                $notas
            ) {

                $promedio = 0;

                $notasEstudiante = $criterios->map(
                    function ($criterio) use (
                        $inscripcion,
                        $notas,
                        &$promedio
                    ) {

                        $nota = $notas
                            ->where(
                                'criterio_id',
                                $criterio->id
                            )
                            ->where(
                                'estudiante_id',
                                $inscripcion->estudiante_id
                            )
                            ->first();

                        $valorNota = (float) (
                            $nota?->nota ?? 0
                        );

                        $promedio +=
                            $valorNota *
                            (
                                $criterio->porcentaje / 100
                            );

                        return [
                            'criterio_id' => $criterio->id,
                            'nota' => $valorNota,
                        ];
                    }
                );

                return [
                    'id' => $inscripcion->estudiante->id,
                    'nombre' => $inscripcion->estudiante->user?->name,
                    'notas' => $notasEstudiante,
                    'promedio' => round(
                        $promedio,
                        2
                    ),
                ];
            }
        );

        return response()->json([
            'criterios' => $criterios,
            'estudiantes' => $estudiantes,
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