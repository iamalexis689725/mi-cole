<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Asistencia;
use App\Models\AsistenciaDetalle;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function store(Request $request, $periodoId, $cursoId, $paraleloId)
    {
        $request->validate([
            'fecha' => 'required|date',
            'asistencias' => 'required|array',
            'asistencias.*.estudiante_id' => 'required|exists:estudiantes,id',
            'asistencias.*.estado' => 'required|in:presente,falta,justificado,tarde',
            'asistencias.*.observacion' => 'nullable|string'
        ]);

        // 🔥 1. Obtener asignación (CLAVE DEL SISTEMA)
        $asignacion = AsignacionDocente::where('academic_period_id', $periodoId)
            ->where('curso_id', $cursoId)
            ->where('paralelo_id', $paraleloId)
            ->firstOrFail();

        // 🔥 2. Evitar duplicado de asistencia
        $existe = Asistencia::where('asignacion_docente_id', $asignacion->id)
            ->where('fecha', $request->fecha)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Ya se registró asistencia para esta fecha'
            ], 409);
        }

        // 🔥 3. Crear asistencia
        $asistencia = Asistencia::create([
            'asignacion_docente_id' => $asignacion->id,
            'fecha' => $request->fecha,
        ]);

        // 🔥 4. Guardar detalles
        foreach ($request->asistencias as $item) {
            AsistenciaDetalle::create([
                'asistencia_id' => $asistencia->id,
                'estudiante_id' => $item['estudiante_id'],
                'estado' => $item['estado'],
                'observacion' => $item['observacion'] ?? null
            ]);
        }

        return response()->json([
            'message' => 'Asistencia registrada correctamente',
            'data' => $asistencia->load('detalles.estudiante.user')
        ], 201);
    }

    public function show($periodoId, $cursoId, $paraleloId, $fecha)
    {
        $asignacion = AsignacionDocente::where('academic_period_id', $periodoId)
            ->where('curso_id', $cursoId)
            ->where('paralelo_id', $paraleloId)
            ->firstOrFail();

        $asistencia = Asistencia::with('detalles.estudiante.user')
            ->where('asignacion_docente_id', $asignacion->id)
            ->where('fecha', $fecha)
            ->first();

        if (!$asistencia) {
            return response()->json([
                'message' => 'No hay asistencia registrada'
            ], 404);
        }

        return response()->json($asistencia);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'asistencias' => 'required|array',
            'asistencias.*.estudiante_id' => 'required|exists:estudiantes,id',
            'asistencias.*.estado' => 'required|in:presente,falta,justificado,tarde',
            'asistencias.*.observacion' => 'nullable|string'
        ]);

        $asistencia = Asistencia::findOrFail($id);

        // 🔥 borrar detalles antiguos
        $asistencia->detalles()->delete();

        // 🔥 guardar nuevos
        foreach ($request->asistencias as $item) {
            AsistenciaDetalle::create([
                'asistencia_id' => $asistencia->id,
                'estudiante_id' => $item['estudiante_id'],
                'estado' => $item['estado'],
                'observacion' => $item['observacion'] ?? null
            ]);
        }

        return response()->json([
            'message' => 'Asistencia actualizada',
            'data' => $asistencia->load('detalles.estudiante.user')
        ]);
    }

    public function index($periodoId, $cursoId, $paraleloId)
    {
        $asignacion = AsignacionDocente::where('academic_period_id', $periodoId)
            ->where('curso_id', $cursoId)
            ->where('paralelo_id', $paraleloId)
            ->firstOrFail();

        $asistencias = Asistencia::where('asignacion_docente_id', $asignacion->id)
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json($asistencias);
    }
}
