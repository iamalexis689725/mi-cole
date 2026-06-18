<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaDetalle;
use App\Models\PadreFamilia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PadreAsistenciaController extends Controller
{
    public function index(Request $request, int $estudianteId)
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

        $query = AsistenciaDetalle::with([
            'asistencia',
            'asistencia.asignacion.subject'
        ])
            ->where(
                'estudiante_id',
                $estudianteId
            )
            ->where(
                'estado',
                'falta'
            );

        // filtro por materia
        if ($request->filled('materia')) {

            $query->whereHas(
                'asistencia.asignacion.subject',
                function ($q) use ($request) {

                    $q->where(
                        'name',
                        $request->materia
                    );
                }
            );
        }

        $faltas = $query
            ->orderByDesc('id')
            ->get();

        // materias disponibles para el combo
        $materias = AsistenciaDetalle::with([
            'asistencia.asignacion.subject'
        ])
            ->where(
                'estudiante_id',
                $estudianteId
            )
            ->where(
                'estado',
                'falta'
            )
            ->get()
            ->pluck(
                'asistencia.asignacion.subject.name'
            )
            ->unique()
            ->values();

        return response()->json([
            'materias' => $materias,

            'total_faltas' => $faltas->count(),

            'faltas' => $faltas->map(function ($item) {

                return [

                    'fecha' =>
                    $item->asistencia->fecha,

                    'materia' =>
                    $item->asistencia
                        ->asignacion
                        ->subject
                        ->name,
                ];
            }),
        ]);
    }
}