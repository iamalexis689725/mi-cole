<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EstudianteAgendaController extends Controller
{
    public function pendientes()
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('user_id', $user->id)
            ->firstOrFail();
        $agendas = Agenda::with([
            'archivos',
            'asignacion.subject',
            'asignacion.profesor.user'
        ])
            ->whereIn('tipo', ['tarea', 'examen'])
            ->whereHas('asignacion.curso.inscripciones', function ($q) use ($estudiante) {
                $q->where('estudiante_id', $estudiante->id);
            })
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
                            'url' => Storage::url($archivo->archivo),
                        ];
                    }),
                ];
            })
        );
    }

    public function biblioteca()
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('user_id', $user->id)
            ->firstOrFail();
        $agendas = Agenda::with([
            'archivos',
            'asignacion.subject',
            'asignacion.profesor.user'
        ])
            ->where('tipo', 'recurso')
            ->whereHas('asignacion.curso.inscripciones', function ($q) use ($estudiante) {
                $q->where('estudiante_id', $estudiante->id);
            })
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
                            'url' => Storage::url($archivo->archivo),
                        ];
                    }),
                ];
            })
        );
    }
}
