<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Paralelo;
use App\Models\Profesor;
use Illuminate\Http\Request;

class AsignacionDocenteController extends Controller
{

    public function index()
    {
        return AsignacionDocente::with([
            'profesor.user',
            'subject',
            'curso',
            'paralelo'
        ])->get();
    }

    public function show($id)
    {
        return AsignacionDocente::with([
            'profesor.user',
            'subject',
            'curso',
            'paralelo'
        ])->findOrFail($id);
    }

    public function destroy($id)
    {
        $asignacion = AsignacionDocente::findOrFail($id);
        $asignacion->delete();

        return response()->json([
            'message' => 'Asignación eliminada correctamente'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'profesor_id' => 'required|exists:profesores,id',
            'subject_id' => 'required|exists:subjects,id',
            'curso_id' => 'required|exists:cursos,id',
            'paralelo_id' => 'required|exists:paralelos,id',
            'dia' => 'required|string|in:Lunes,Martes,Miercoles,Jueves,Viernes,Sabado,Domingo',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
        ]);

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


        // ✅ Validar que el paralelo pertenece al curso enviado
        $paraleloValido = Paralelo::where('id', $request->paralelo_id)
            ->where('curso_id', $request->curso_id)
            ->exists();

        if (!$paraleloValido) {
            return response()->json([
                'message' => 'El paralelo no pertenece al curso seleccionado'
            ], 422);
        }


        // validacion de choque de horarios
        $existe = AsignacionDocente::where('profesor_id', $request->profesor_id)
            ->where('dia', $request->dia)
            ->where(function ($q) use ($request) {
                $q->where('hora_inicio', '<', $request->hora_fin)
                    ->where('hora_fin', '>', $request->hora_inicio);
            })
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'El profesor ya tiene una clase en ese horario'
            ], 422);
        }

        $asignacion = AsignacionDocente::create([
            'profesor_id' => $request->profesor_id,
            'subject_id' => $request->subject_id,
            'curso_id' => $request->curso_id,
            'paralelo_id' => $request->paralelo_id,
            'dia' => $request->dia,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return response()->json([
            'message' => 'Asignación creada correctamente',
            'data' => $asignacion->load([
                'profesor.user',
                'subject',
                'curso',
                'paralelo'
            ])
        ], 201);
    }
}
