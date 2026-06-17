<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\PadreFamilia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PadreAgendaController extends Controller
{

    public function agendasPorHijo(int $estudianteId)
    {
        $user = Auth::user();

        $padre = PadreFamilia::where('user_id', $user->id)
            ->firstOrFail();

        $esHijo = $padre->estudiantes()
            ->where('estudiantes.id', $estudianteId)
            ->exists();

        if (!$esHijo) {
            return response()->json([
                'message' => 'Este estudiante no pertenece al padre autenticado'
            ], 403);
        }

        $estudiante = $padre->estudiantes()
            ->with('user')
            ->findOrFail($estudianteId);

        $agendas = Agenda::with([
            'asignacion.subject',
            'archivos'
        ])
            ->whereHas('asignacion', function ($q) use ($estudianteId) {
                $q->whereHas('curso.inscripciones', function ($sub) use ($estudianteId) {
                    $sub->where('estudiante_id', $estudianteId);
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'estudiante' => [
                'id' => $estudiante->id,
                'nombre' => $estudiante->user->name,
                'codigo_estudiante' => $estudiante->codigo_estudiante,
            ],

            'agendas' => $agendas->map(function ($agenda) {
                return [
                    'id' => $agenda->id,
                    'titulo' => $agenda->titulo,
                    'descripcion' => $agenda->descripcion,
                    'tipo' => $agenda->tipo,
                    'fecha_entrega' => $agenda->fecha_entrega,
                    'materia' => $agenda->asignacion->subject->name,

                    'archivos' => $agenda->archivos->map(function ($archivo) {
                        return [
                            'id' => $archivo->id,
                            'nombre_original' => $archivo->nombre_original,
                            'url' => Storage::url($archivo->archivo),
                        ];
                    }),
                ];
            }),
        ]);
    }

    public function tareasPendientes()
    {
        $user = Auth::user();

        $padre = PadreFamilia::where('user_id', $user->id)
            ->firstOrFail();

        $estudiantes = $padre->estudiantes()
            ->with([
                'user',
                'inscripciones.curso',
                'inscripciones.paralelo',
            ])
            ->get();

        $resultado = [];

        foreach ($estudiantes as $estudiante) {

            $agendas = Agenda::with([
                'asignacion.subject',
                'archivos'
            ])
                ->whereHas('asignacion', function ($q) use ($estudiante) {

                    $q->whereHas('curso.inscripciones', function ($sub) use ($estudiante) {

                        $sub->where('estudiante_id', $estudiante->id);
                    });
                })
                ->latest()
                ->get();

            $resultado[] = [
                'estudiante' => [
                    'id' => $estudiante->id,
                    'nombre' => $estudiante->user->name,
                    'codigo_estudiante' => $estudiante->codigo_estudiante,
                ],

                'agendas' => $agendas->map(function ($agenda) {

                    return [
                        'id' => $agenda->id,
                        'titulo' => $agenda->titulo,
                        'descripcion' => $agenda->descripcion,
                        'tipo' => $agenda->tipo,
                        'fecha_entrega' => $agenda->fecha_entrega,

                        'materia' => $agenda->asignacion->subject->name,

                        'archivos' => $agenda->archivos->map(function ($archivo) {

                            return [
                                'id' => $archivo->id,
                                'nombre_original' => $archivo->nombre_original,
                                'url' => Storage::url($archivo->archivo),
                            ];
                        }),
                    ];
                }),
            ];
        }

        return response()->json($resultado);
    }
}
