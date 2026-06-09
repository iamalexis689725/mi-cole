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

    public function libroCalificaciones(int $asignacionId, int $periodoId): JsonResponse
    {
        $asignacion = AsignacionDocente::findOrFail(
            $asignacionId
        );

        $this->authorizeProfesor(
            $asignacion
        );

        $criterios = Criterio::where(
            'asignacion_docente_id',
            $asignacionId
        )
            ->where(
                'periodo_evaluacion_id',
                $periodoId
            )
            ->orderBy('id')
            ->get([
                'id',
                'nombre',
                'porcentaje'
            ]);

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

        $estudiantes = $inscripciones->map(
            function ($inscripcion) use ($criterios) {
                $promedio = 0;
                $notas = [];
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
                    $valorNota = $nota?->nota ?? 0;
                    $notas[] = [
                        'criterio_id' => $criterio->id,
                        'criterio' => $criterio->nombre,
                        'porcentaje' => $criterio->porcentaje,
                        'nota' => $valorNota,
                    ];
                    $promedio +=
                        $valorNota *
                        ($criterio->porcentaje / 100);
                }
                return [
                    'estudiante_id' =>
                    $inscripcion->estudiante->id,
                    'estudiante' =>
                    $inscripcion->estudiante->user->name,
                    'notas' => $notas,
                    'promedio' =>
                    round($promedio, 2),
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
