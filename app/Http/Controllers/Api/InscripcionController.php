<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Inscripcion;
use App\Models\Paralelo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InscripcionController extends Controller
{
    public function index($periodoId)
    {
        return Inscripcion::with([
            'estudiante.user',
            'curso',
            'paralelo',
            'periodo'
        ])
            ->where('academic_period_id', $periodoId)
            ->get();
    }

    public function show($periodoId, $id)
    {
        return Inscripcion::with([
            'estudiante.user',
            'curso',
            'paralelo',
            'periodo'
        ])
            ->where('academic_period_id', $periodoId)
            ->findOrFail($id);
    }

    public function store(Request $request, $periodoId)
    {
        $request->validate([
            'estudiante_id' => [
                'required',
                Rule::exists('estudiantes', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
            'curso_id' => [
                'required',
                Rule::exists('cursos', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
            'paralelo_id' => [
                'required',
                Rule::exists('paralelos', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
        ]);

        // validar periodo pertenece al tenant
        $periodo = AcademicPeriod::where('id', $periodoId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        if (!$periodo->activo) {
            return response()->json([
                'message' => 'No se pueden hacer inscripciones en un periodo inactivo'
            ], 403);
        }

        // validar relación curso - paralelo
        $paralelo = Paralelo::findOrFail($request->paralelo_id);

        if ($paralelo->curso_id != $request->curso_id) {
            return response()->json([
                'message' => 'El paralelo no pertenece al curso seleccionado'
            ], 422);
        }

        // validar duplicado
        $existe = Inscripcion::where('estudiante_id', $request->estudiante_id)
            ->where('academic_period_id', $periodoId)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'El estudiante ya está inscrito en este periodo'
            ], 409);
        }

        $inscripcion = Inscripcion::create([
            'estudiante_id' => $request->estudiante_id,
            'curso_id' => $request->curso_id,
            'paralelo_id' => $request->paralelo_id,
            'academic_period_id' => $periodoId,
        ]);

        return response()->json([
            'message' => 'Inscripción creada',
            'data' => $inscripcion->load([
                'estudiante.user',
                'curso',
                'paralelo',
                'periodo'
            ])
        ], 201);
    }

    public function update(Request $request, $periodoId, $id)
    {
        $inscripcion = Inscripcion::where('academic_period_id', $periodoId)
            ->findOrFail($id);

        $request->validate([
            'estudiante_id' => [
                'sometimes',
                Rule::exists('estudiantes', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
            'curso_id' => [
                'sometimes',
                Rule::exists('cursos', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
            'paralelo_id' => [
                'sometimes',
                Rule::exists('paralelos', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
        ]);

        $curso_id = $request->curso_id ?? $inscripcion->curso_id;
        $paralelo_id = $request->paralelo_id ?? $inscripcion->paralelo_id;

        $existeRelacion = Paralelo::where('id', $paralelo_id)
            ->where('curso_id', $curso_id)
            ->exists();

        if (!$existeRelacion) {
            return response()->json([
                'message' => 'El paralelo no pertenece al curso seleccionado'
            ], 422);
        }

        if ($request->has('estudiante_id')) {
            $existe = Inscripcion::where('estudiante_id', $request->estudiante_id)
                ->where('academic_period_id', $periodoId)
                ->where('id', '!=', $inscripcion->id)
                ->exists();

            if ($existe) {
                return response()->json([
                    'message' => 'El estudiante ya está inscrito en este periodo'
                ], 409);
            }
        }

        $inscripcion->update($request->only([
            'estudiante_id',
            'curso_id',
            'paralelo_id',
        ]));

        return response()->json([
            'message' => 'Inscripción actualizada',
            'data' => $inscripcion->load([
                'estudiante.user',
                'curso',
                'paralelo',
                'periodo'
            ])
        ]);
    }

    public function destroy($periodoId, $id)
    {
        Inscripcion::where('academic_period_id', $periodoId)
            ->findOrFail($id)
            ->delete();

        return response()->json([
            'message' => 'Inscripción eliminada'
        ]);
    }

    // PROFESOR (se queda igual)
    public function estudiantesPorClase($periodoId, $cursoId, $paraleloId)
    {
        $estudiantes = Inscripcion::with('estudiante.user')
            ->where('academic_period_id', $periodoId)
            ->where('curso_id', $cursoId)
            ->where('paralelo_id', $paraleloId)
            ->orderBy('id')
            ->get()
            ->map(fn($i) => [
                'id' => $i->estudiante->id,
                'nombre' => $i->estudiante->user->name,
                'email' => $i->estudiante->user->email,
            ])
            ->values();

        return response()->json([
            'periodo_id' => $periodoId,
            'curso_id' => $cursoId,
            'paralelo_id' => $paraleloId,
            'estudiantes' => $estudiantes
        ]);
    }
}
