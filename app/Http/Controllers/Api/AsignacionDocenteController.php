<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Paralelo;
use App\Models\Profesor;
use App\Models\Curso;
use App\Models\HorarioAsignacion;
use Illuminate\Http\Request;

class AsignacionDocenteController extends Controller
{
    public function index(int $periodoId)
    {
        return AsignacionDocente::with([
            'profesor.user',
            'subject',
            'curso',
            'paralelo',
            'horarios'
        ])
            ->where('academic_period_id', $periodoId)
            ->get();
    }

    //endpoint para solo profesor
    public function misClases(int $periodoId)
    {
        $user = auth()->user();

        $profesor = Profesor::where('user_id', $user->id)->first();

        if (!$profesor) {
            return response()->json([
                'message' => 'No eres un profesor'
            ], 403);
        }

        $asignaciones = AsignacionDocente::with([
            'subject',
            'curso',
            'paralelo'
        ])
            ->where('profesor_id', $profesor->id)
            ->where('academic_period_id', $periodoId)
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'curso' => $a->curso->nombre,
                    'paralelo' => $a->paralelo->nombre,
                    'materia' => $a->subject->name,
                    'curso_id' => $a->curso_id,
                    'paralelo_id' => $a->paralelo_id,
                    'subject_id' => $a->subject_id,
                ];
            });

        return response()->json([
            'asignaciones' => $asignaciones
        ]);
    }

    public function show(int $periodoId, int $id)
    {
        return AsignacionDocente::with([
            'profesor.user',
            'subject',
            'curso',
            'paralelo',
            'horarios'
        ])
            ->where('academic_period_id', $periodoId)
            ->findOrFail($id);
    }

    public function destroy(int $periodoId, int $id)
    {
        $asignacion = AsignacionDocente::where('academic_period_id', $periodoId)
            ->findOrFail($id);

        $asignacion->delete();

        return response()->json([
            'message' => 'Asignación eliminada correctamente'
        ]);
    }

    public function store(Request $request, int $periodoId)
    {
        $request->validate([
            'profesor_id' => 'required|exists:profesores,id',
            'subject_id' => 'required|exists:subjects,id',
            'curso_id' => 'required|exists:cursos,id',
            'paralelo_id' => 'required|exists:paralelos,id',

            'horarios' => 'required|array|min:1',

            'horarios.*.dia' =>
            'required|string|in:Lunes,Martes,Miercoles,Jueves,Viernes,Sabado,Domingo',

            'horarios.*.hora_inicio' => 'required',

            'horarios.*.hora_fin' =>
            'required',
        ]);

        $curso = Curso::where('id', $request->curso_id)
            ->where('academic_period_id', $periodoId)
            ->first();

        if (!$curso) {
            return response()->json([
                'message' => 'El curso no pertenece al periodo académico'
            ], 422);
        }

        $subjectValido = Profesor::where('id', $request->profesor_id)
            ->whereHas('subjects', function ($q) use ($request) {
                $q->where('subjects.id', $request->subject_id);
            })
            ->exists();

        if (!$subjectValido) {
            return response()->json([
                'message' => 'La materia no está asignada a este profesor'
            ], 422);
        }

        $paraleloValido = Paralelo::where('id', $request->paralelo_id)
            ->where('curso_id', $request->curso_id)
            ->exists();

        if (!$paraleloValido) {
            return response()->json([
                'message' => 'El paralelo no pertenece al curso seleccionado'
            ], 422);
        }

        $asignacionExistente = AsignacionDocente::where([
            'profesor_id' => $request->profesor_id,
            'subject_id' => $request->subject_id,
            'curso_id' => $request->curso_id,
            'paralelo_id' => $request->paralelo_id,
            'academic_period_id' => $periodoId,
        ])->first();

        if ($asignacionExistente) {
            return response()->json([
                'message' => 'La asignación ya existe'
            ], 422);
        }

        foreach ($request->horarios as $horario) {

            $conflictoCurso = HorarioAsignacion::where('dia', $horario['dia'])
                ->whereHas('asignacion', function ($q) use ($request, $periodoId) {
                    $q->where('curso_id', $request->curso_id)
                        ->where('paralelo_id', $request->paralelo_id)
                        ->where('academic_period_id', $periodoId);
                })
                ->where(function ($q) use ($horario) {
                    $q->where('hora_inicio', '<', $horario['hora_fin'])
                        ->where('hora_fin', '>', $horario['hora_inicio']);
                })
                ->exists();

            if ($conflictoCurso) {
                return response()->json([
                    'message' => 'El curso ya tiene una materia en ese horario'
                ], 422);
            }

            $conflictoProfesor = HorarioAsignacion::where('dia', $horario['dia'])
                ->whereHas('asignacion', function ($q) use ($request, $periodoId) {
                    $q->where('profesor_id', $request->profesor_id)
                        ->where('academic_period_id', $periodoId);
                })
                ->where(function ($q) use ($horario) {
                    $q->where('hora_inicio', '<', $horario['hora_fin'])
                        ->where('hora_fin', '>', $horario['hora_inicio']);
                })
                ->exists();

            if ($conflictoProfesor) {
                return response()->json([
                    'message' => 'El profesor ya tiene una clase en ese horario'
                ], 422);
            }
        }

        $asignacion = AsignacionDocente::create([
            'profesor_id' => $request->profesor_id,
            'subject_id' => $request->subject_id,
            'curso_id' => $request->curso_id,
            'paralelo_id' => $request->paralelo_id,
            'academic_period_id' => $periodoId,
        ]);

        foreach ($request->horarios as $horario) {

            HorarioAsignacion::create([
                'asignacion_docente_id' => $asignacion->id,
                'dia' => $horario['dia'],
                'hora_inicio' => $horario['hora_inicio'],
                'hora_fin' => $horario['hora_fin'],
            ]);
        }

        return response()->json([
            'message' => 'Asignación creada correctamente',
            'data' => $asignacion->load([
                'profesor.user',
                'subject',
                'curso',
                'paralelo',
                'horarios'
            ])
        ], 201);
    }

    public function horarioCurso(
        int $periodoId,
        int $cursoId,
        int $paraleloId
    ) {
        $horarios = HorarioAsignacion::with([
            'asignacion.profesor.user',
            'asignacion.subject'
        ])
            ->whereHas('asignacion', function ($q) use (
                $periodoId,
                $cursoId,
                $paraleloId
            ) {
                $q->where('academic_period_id', $periodoId)
                    ->where('curso_id', $cursoId)
                    ->where('paralelo_id', $paraleloId);
            })
            ->orderBy('hora_inicio')
            ->get()
            ->map(fn($h) => [
                'id' => $h->id,
                'dia' => $h->dia,
                'hora_inicio' => $h->hora_inicio,
                'hora_fin' => $h->hora_fin,
                'materia' => $h->asignacion->subject->name,
                'profesor' => $h->asignacion->profesor->user->name,
            ])
            ->groupBy('dia');

        $orden = [
            'Lunes',
            'Martes',
            'Miercoles',
            'Jueves',
            'Viernes',
            'Sabado'
        ];

        $horario = collect($orden)
            ->filter(fn($dia) => $horarios->has($dia))
            ->mapWithKeys(fn($dia) => [
                $dia => $horarios[$dia]
            ]);

        return response()->json([
            'curso_id' => $cursoId,
            'paralelo_id' => $paraleloId,
            'periodo_id' => $periodoId,
            'horario' => $horario
        ]);
    }
}
