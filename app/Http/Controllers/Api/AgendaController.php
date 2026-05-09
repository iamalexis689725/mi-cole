<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\AgendaArchivo;
use App\Models\AsignacionDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgendaController extends Controller
{
    public function index($periodoId, $asignacionId)
    {
        $asignacion = AsignacionDocente::where('id', $asignacionId)
            ->where('academic_period_id', $periodoId)
            ->firstOrFail();

        $agenda = Agenda::with([
            'archivos',
            'asignacion.profesor.user',
            'asignacion.subject',
            'asignacion.curso',
            'asignacion.paralelo'
        ])
            ->where(
                'asignacion_docente_id',
                $asignacion->id
            )
            ->latest()
            ->get();
        return response()->json(
            $agenda->map(function ($a) {
                return [
                    'id' => $a->id,
                    'titulo' => $a->titulo,
                    'descripcion' => $a->descripcion,
                    'tipo' => $a->tipo,
                    'fecha_entrega' => $a->fecha_entrega,
                    'materia' => $a->asignacion->subject->name,
                    'profesor' => $a->asignacion->profesor->user->name,
                    'curso' => $a->asignacion->curso->nombre,
                    'paralelo' => $a->asignacion->paralelo->nombre,
                    'archivos' => $a->archivos->map(function ($archivo) {
                        return [
                            'id' => $archivo->id,
                            'nombre_original' => $archivo->nombre_original,
                            'url' => Storage::url($archivo->archivo)
                        ];
                    }),
                ];
            })
        );
    }

    public function show($id)
    {
        $agenda = Agenda::findOrFail($id);

        return response()->json($agenda);
    }

    public function store(Request $request, $periodoId, $asignacionId)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:tarea,examen,recurso',
            'fecha_entrega' => 'nullable|date',
            'archivo' => 'nullable|string'
        ]);

        $asignacion = AsignacionDocente::where('id', $asignacionId)
            ->where('academic_period_id', $periodoId)
            ->firstOrFail();

        $agenda = Agenda::create([
            'asignacion_docente_id' => $asignacion->id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'tipo' => $request->tipo,
            'fecha_entrega' => $request->fecha_entrega,
            'archivo' => $request->archivo,
        ]);

        return response()->json([
            'message' => 'Agenda creada correctamente',
            'data' => $agenda
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:tarea,examen,recurso',
            'fecha_entrega' => 'nullable|date',
            'archivo' => 'nullable|string'
        ]);

        $agenda = Agenda::findOrFail($id);

        $agenda->update($request->all());

        return response()->json([
            'message' => 'Agenda actualizada',
            'data' => $agenda
        ]);
    }

    public function destroy($id)
    {
        $agenda = Agenda::findOrFail($id);

        $agenda->delete();

        return response()->json([
            'message' => 'Agenda eliminada correctamente'
        ]);
    }

    public function subirArchivo(Request $request, $id)
    {
        $request->validate([
            'archivos' => 'required|array',
            'archivos.*' => 'file|mimes:pdf,doc,docx,png,jpg,jpeg|max:10240'
        ]);

        $agenda = Agenda::findOrFail($id);

        $subidos = [];

        foreach ($request->file('archivos') as $archivo) {

            $nombre = time() . '_' . $archivo->getClientOriginalName();

            $path = $archivo->storeAs(
                'agendas',
                $nombre,
                'public'
            );

            $nuevo = AgendaArchivo::create([
                'agenda_id' => $agenda->id,
                'archivo' => $path,
                'nombre_original' => $archivo->getClientOriginalName(),
            ]);

            $subidos[] = [
                'id' => $nuevo->id,
                'archivo' => $nuevo->archivo,
                'nombre_original' => $nuevo->nombre_original,
                'url' => Storage::url($nuevo->archivo)
            ];
        }

        return response()->json([
            'message' => 'Archivos subidos correctamente',
            'archivos' => $subidos
        ]);
    }

    public function eliminarArchivo($id)
    {
        $archivo = AgendaArchivo::findOrFail($id);

        Storage::disk('public')->delete($archivo->archivo);

        $archivo->delete();

        return response()->json([
            'message' => 'Archivo eliminado correctamente'
        ]);
    }

    public function reemplazarArchivo(Request $request, $id)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:10240'
        ]);

        $archivo = AgendaArchivo::findOrFail($id);

        // eliminar archivo viejo
        Storage::disk('public')->delete($archivo->archivo);

        // nuevo archivo
        $nuevoArchivo = $request->file('archivo');

        $nombre = time() . '_' . $nuevoArchivo->getClientOriginalName();

        $path = $nuevoArchivo->storeAs(
            'agendas',
            $nombre,
            'public'
        );

        // actualizar BD
        $archivo->update([
            'archivo' => $path,
            'nombre_original' => $nuevoArchivo->getClientOriginalName(),
        ]);

        return response()->json([
            'message' => 'Archivo reemplazado correctamente',

            'data' => [
                'id' => $archivo->id,
                'nombre_original' => $archivo->nombre_original,
                'url' => Storage::url($archivo->archivo)
            ]
        ]);
    }
}
