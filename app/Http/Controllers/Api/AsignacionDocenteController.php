<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Paralelo;
use App\Models\Profesor;
use App\Models\Curso;
use Illuminate\Http\Request;

class AsignacionDocenteController extends Controller
{
    public function index($periodoId)
    {
        return AsignacionDocente::with([
            'profesor.user',
            'subject',
            'curso',
            'paralelo'
        ])
        ->where('academic_period_id', $periodoId)
        ->get();
    }

    public function show($periodoId, $id)
    {
        return AsignacionDocente::with([
            'profesor.user',
            'subject',
            'curso',
            'paralelo'
        ])
        ->where('academic_period_id', $periodoId)
        ->findOrFail($id);
    }

    public function destroy($periodoId, $id)
    {
        $asignacion = AsignacionDocente::where('academic_period_id', $periodoId)
            ->findOrFail($id);

        $asignacion->delete();

        return response()->json([
            'message' => 'Asignación eliminada correctamente'
        ]);
    }

    public function store(Request $request, $periodoId)
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

        // ✅ Validar que el paralelo pertenece al curso
        $paraleloValido = Paralelo::where('id', $request->paralelo_id)
            ->where('curso_id', $request->curso_id)
            ->exists();

        if (!$paraleloValido) {
            return response()->json([
                'message' => 'El paralelo no pertenece al curso seleccionado'
            ], 422);
        }

        
        $existe = AsignacionDocente::where('profesor_id', $request->profesor_id)
            ->where('dia', $request->dia)
            ->where('academic_period_id', $periodoId)
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
            'academic_period_id' => $periodoId,
            'dia' => $request->dia,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
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