<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Criterio;
use App\Models\Estudiante;
use App\Models\Nota;
use App\Models\PadreFamilia;
use App\Models\PeriodoEvaluacion;
use Illuminate\Support\Facades\Auth;

class PadreNotaController extends Controller
{
    public function boleta(int $estudianteId)
    {
        $user = Auth::user();

        $padre = PadreFamilia::where(
            'user_id',
            $user->id
        )->firstOrFail();

        $esHijo = $padre->estudiantes()
            ->where('estudiantes.id', $estudianteId)
            ->exists();

        if (!$esHijo) {
            abort(403);
        }

        $estudiante = Estudiante::with('user')
            ->findOrFail($estudianteId);

        $periodos = PeriodoEvaluacion::orderBy('orden')
            ->get();

        $resultado = [];

        foreach ($periodos as $periodo) {

            $asignaciones = AsignacionDocente::with(
                'subject'
            )
                ->whereHas('curso.inscripciones', function ($q) use ($estudianteId) {
                    $q->where(
                        'estudiante_id',
                        $estudianteId
                    );
                })
                ->get();

            $materias = [];

            $promedioGeneral = 0;
            $cantidadMaterias = 0;

            foreach ($asignaciones as $asignacion) {

                $criterios = Criterio::where(
                    'asignacion_docente_id',
                    $asignacion->id
                )
                    ->where(
                        'periodo_evaluacion_id',
                        $periodo->id
                    )
                    ->get();

                if ($criterios->isEmpty()) {
                    continue;
                }

                $promedioMateria = 0;

                foreach ($criterios as $criterio) {

                    $nota = Nota::where(
                        'criterio_id',
                        $criterio->id
                    )
                        ->where(
                            'estudiante_id',
                            $estudianteId
                        )
                        ->first();

                    $valorNota = $nota?->nota ?? 0;

                    $promedioMateria +=
                        $valorNota *
                        ($criterio->porcentaje / 100);
                }

                $promedioMateria =
                    round($promedioMateria, 2);

                $materias[] = [
                    'materia' =>
                    $asignacion->subject->name,

                    'promedio' =>
                    $promedioMateria,
                ];

                $promedioGeneral +=
                    $promedioMateria;

                $cantidadMaterias++;
            }

            $resultado[] = [
                'periodo' => $periodo->nombre,

                'materias' => $materias,

                'promedio_general' =>
                $cantidadMaterias > 0
                    ? round(
                        $promedioGeneral /
                            $cantidadMaterias,
                        2
                    )
                    : 0,
            ];
        }

        return response()->json([
            'estudiante' => [
                'id' => $estudiante->id,
                'nombre' => $estudiante->user->name,
            ],

            'periodos' => $resultado,
        ]);
    }
}