<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\AsignacionDocente;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EstudianteAgendaController extends Controller
{
    public function pendientes(Request $request)
    {
        $user = Auth::user();

        $estudiante = Estudiante::where(
            'user_id',
            $user->id
        )->firstOrFail();

        $query = Agenda::with([
            'archivos',
            'asignacion.subject',
            'asignacion.profesor.user'
        ])
            ->whereIn('tipo', [
                'tarea',
                'examen'
            ]);

        // filtrar por tipo
        if ($request->filled('tipo')) {

            $query->where(
                'tipo',
                $request->tipo
            );
        }

        // filtrar por materia
        if ($request->filled('asignacion_id')) {

            $query->where(
                'asignacion_docente_id',
                $request->asignacion_id
            );
        }

        $agendas = $query
            ->whereHas(
                'asignacion.curso.inscripciones',
                function ($q) use ($estudiante) {

                    $q->where(
                        'estudiante_id',
                        $estudiante->id
                    );
                }
            )
            ->orderBy('fecha_entrega', 'asc')
            ->get();

        return response()->json(
            $agendas->map(function ($agenda) {

                return [
                    'id' => $agenda->id,
                    'titulo' => $agenda->titulo,
                    'descripcion' => $agenda->descripcion,
                    'tipo' => $agenda->tipo,
                    'fecha_entrega' => $agenda->fecha_entrega,

                    'materia' => $agenda->asignacion->subject->name,

                    'profesor' => $agenda->asignacion->profesor->user->name,

                    'archivos' => $agenda->archivos->map(function ($archivo) {

                        return [
                            'id' => $archivo->id,
                            'nombre_original' => $archivo->nombre_original,
                            'url' => Storage::url(
                                $archivo->archivo
                            ),
                        ];
                    }),
                ];
            })
        );
    }

    public function biblioteca(Request $request)
    {
        $user = Auth::user();

        $estudiante = Estudiante::where(
            'user_id',
            $user->id
        )->firstOrFail();

        $query = Agenda::with([
            'archivos',
            'asignacion.subject',
            'asignacion.profesor.user'
        ])
            ->where('tipo', 'recurso');

        // filtrar por materia
        if ($request->filled('asignacion_id')) {

            $query->where(
                'asignacion_docente_id',
                $request->asignacion_id
            );
        }

        $agendas = $query
            ->whereHas(
                'asignacion.curso.inscripciones',
                function ($q) use ($estudiante) {

                    $q->where(
                        'estudiante_id',
                        $estudiante->id
                    );
                }
            )
            ->latest()
            ->get();

        return response()->json(
            $agendas->map(function ($agenda) {

                return [
                    'id' => $agenda->id,
                    'titulo' => $agenda->titulo,
                    'descripcion' => $agenda->descripcion,

                    'materia' => $agenda->asignacion->subject->name,

                    'profesor' => $agenda->asignacion->profesor->user->name,

                    'created_at' => $agenda->created_at,

                    'archivos' => $agenda->archivos->map(function ($archivo) {

                        return [
                            'id' => $archivo->id,
                            'nombre_original' => $archivo->nombre_original,
                            'url' => Storage::url(
                                $archivo->archivo
                            ),
                        ];
                    }),
                ];
            })
        );
    }

    public function materias()
    {
        $user = auth()->user();

        $estudiante = Estudiante::where(
            'user_id',
            $user->id
        )->firstOrFail();

        $materias = AsignacionDocente::with([
            'subject',
            'profesor.user',
            'horarios'
        ])
            ->whereHas(
                'curso.inscripciones',
                fn($q) =>
                $q->where(
                    'estudiante_id',
                    $estudiante->id
                )
            )
            ->get();

        return response()->json(
            $materias->map(fn($a) => [
                'asignacion_id' => $a->id,
                'materia' => $a->subject->name,
                'profesor' => $a->profesor->user->name,
            ])
        );
    }
    
    public function detalleMateria(int $asignacionId)
    {
        $user = auth()->user();

        $estudiante = Estudiante::where(
            'user_id',
            $user->id
        )->firstOrFail();

        $asignacion = AsignacionDocente::with([
            'subject',
            'profesor.user',
            'horarios'
        ])
            ->where('id', $asignacionId)
            ->whereHas(
                'curso.inscripciones',
                fn($q) =>
                $q->where(
                    'estudiante_id',
                    $estudiante->id
                )
            )
            ->firstOrFail();

        return response()->json([
            'asignacion_id' => $asignacion->id,

            'materia' => $asignacion->subject->name,

            'profesor' => $asignacion->profesor->user->name,

            'horarios' => $asignacion->horarios->map(
                fn($h) => [
                    'dia' => $h->dia,
                    'hora_inicio' => $h->hora_inicio,
                    'hora_fin' => $h->hora_fin,
                ]
            ),
        ]);
    }
}
