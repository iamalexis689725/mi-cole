<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\Paralelo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InscripcionController extends Controller
{
    public function index()
    {
        $enrollments = Inscripcion::with([
            'estudiante.user',
            'curso',
            'paralelo',
            'periodo'
        ])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->get();

        return response()->json([
            'data' => $enrollments
        ]);
    }


    public function show($id)
    {
        $enrollment = Inscripcion::with([
            'estudiante.user',
            'curso',
            'paralelo',
            'periodo'
        ])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        return response()->json([
            'data' => $enrollment
        ]);
    }


    public function store(Request $request)
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
            'academic_period_id' => [
                'required',
                Rule::exists('academic_periods', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
        ]);


        $paralelo = Paralelo::findOrFail($request->paralelo_id);

        if ($paralelo->curso_id != $request->curso_id) {
            return response()->json([
                'message' => 'El paralelo no pertenece al curso seleccionado'
            ], 422);
        }

        $existe = Inscripcion::where('estudiante_id', $request->estudiante_id)
            ->where('academic_period_id', $request->academic_period_id)
            ->where('tenant_id', auth()->user()->tenant_id)
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
            'academic_period_id' => $request->academic_period_id,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return response()->json([
            'message' => 'Estudiante inscrito correctamente',
            'data' => $inscripcion->load([
                'estudiante.user',
                'curso',
                'paralelo',
                'periodo'
            ])
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $enrollment = Inscripcion::where('tenant_id', auth()->user()->tenant_id)
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
            'academic_period_id' => [
                'sometimes',
                Rule::exists('academic_periods', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
        ]);
        
        $curso_id = $request->curso_id ?? $enrollment->curso_id;
        $paralelo_id = $request->paralelo_id ?? $enrollment->paralelo_id;

        $existeRelacion = Paralelo::where('id', $paralelo_id)
            ->where('curso_id', $curso_id)
            ->exists();

        if (!$existeRelacion) {
            return response()->json([
                'message' => 'El paralelo no pertenece al curso seleccionado'
            ], 422);
        }

        if ($request->has('estudiante_id') || $request->has('academic_period_id')) {
            $existe = Inscripcion::where('estudiante_id', $request->estudiante_id ?? $enrollment->estudiante_id)
                ->where('academic_period_id', $request->academic_period_id ?? $enrollment->academic_period_id)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->where('id', '!=', $enrollment->id)
                ->exists();

            if ($existe) {
                return response()->json([
                    'message' => 'El estudiante ya está inscrito en este periodo'
                ], 409);
            }
        }

        $enrollment->update($request->only([
            'estudiante_id',
            'curso_id',
            'paralelo_id',
            'academic_period_id',
        ]));

        return response()->json([
            'message' => 'Inscripción actualizada correctamente',
            'data' => $enrollment->load([
                'estudiante.user',
                'curso',
                'paralelo',
                'periodo'
            ])
        ]);
    }


    public function destroy($id)
    {
        $enrollment = Inscripcion::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $enrollment->delete();

        return response()->json([
            'message' => 'Inscripción eliminada correctamente'
        ]);
    }
}
