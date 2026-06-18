<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Asistencia;
use App\Models\AsistenciaDetalle;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function store(Request $request, int $periodoId, int $asignacionId)
    {
        $request->validate([
            'fecha' => 'required|date',
            'asistencias' => 'required|array',
            'asistencias.*.estudiante_id' => 'required|exists:estudiantes,id',
            'asistencias.*.estado' => 'required|in:presente,falta,justificado,tarde',
            'asistencias.*.observacion' => 'nullable|string'
        ]);

        $asignacion = AsignacionDocente::where('id', $asignacionId)
            ->where('academic_period_id', $periodoId)
            ->firstOrFail();

        $existe = Asistencia::where('asignacion_docente_id', $asignacion->id)
            ->where('fecha', $request->fecha)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Ya se registró asistencia para esta materia en esta fecha'
            ], 409);
        }

        $asistencia = Asistencia::create([
            'asignacion_docente_id' => $asignacion->id,
            'fecha' => $request->fecha,
        ]);

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

    public function show(int $periodoId, int $asignacionId, string $fecha)
    {
        $asignacion = AsignacionDocente::where('id', $asignacionId)
            ->where('academic_period_id', $periodoId)
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


    public function update(Request $request, int $id)
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

    public function index(int $periodoId, int $asignacionId)
    {
        $asignacion = AsignacionDocente::where('id', $asignacionId)
            ->where('academic_period_id', $periodoId)
            ->firstOrFail();

        $asistencias = Asistencia::where('asignacion_docente_id', $asignacion->id)
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json($asistencias);
    }
}
