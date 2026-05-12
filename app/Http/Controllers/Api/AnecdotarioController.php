<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnecdotarioRequest;
use App\Models\Anecdotario;
use Illuminate\Http\Request;

class AnecdotarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Anecdotario::with([
            'estudiante.user',
            'profesor.user',
            'asignacionDocente.subject',
            'academicPeriod'
        ]);

        if ($request->estudiante_id) {
            $query->where('estudiante_id', $request->estudiante_id);
        }

        if ($request->academic_period_id) {
            $query->where('academic_period_id', $request->academic_period_id);
        }

        if ($request->asignacion_docente_id) {
            $query->where(
                'asignacion_docente_id',
                $request->asignacion_docente_id
            );
        }

        return response()->json(
            $query->latest()->get()
        );
    }

    public function store(StoreAnecdotarioRequest $request)
    {
        $profesor = auth()->user()->profesor;

        $anecdota = Anecdotario::create([
            'tenant_id' => auth()->user()->tenant_id,
            'estudiante_id' => $request->estudiante_id,
            'profesor_id' => $profesor->id,
            'asignacion_docente_id' => $request->asignacion_docente_id,
            'academic_period_id' => $request->academic_period_id,
            'tipo' => $request->tipo,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha' => $request->fecha,
        ]);

        return response()->json([
            'message' => 'Anecdotario creado exitosamente',
            'data' => $anecdota->load([
                'estudiante.user',
                'profesor.user',
                'asignacionDocente.subject',
                'academicPeriod'
            ])
        ], 201);
    }

    public function show(Anecdotario $anecdotario)
    {
        return response()->json(
            $anecdotario->load([
                'estudiante.user',
                'profesor.user',
                'asignacionDocente.subject',
                'academicPeriod'
            ])
        );
    }

    public function update(
        StoreAnecdotarioRequest $request,
        Anecdotario $anecdotario
    ) {
        $anecdotario->update([
            'estudiante_id' => $request->estudiante_id,
            'asignacion_docente_id' => $request->asignacion_docente_id,
            'academic_period_id' => $request->academic_period_id,
            'tipo' => $request->tipo,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha' => $request->fecha,
        ]);

        return response()->json([
            'message' => 'Anecdotario actualizado',
            'data' => $anecdotario
        ]);
    }



    public function destroy(Anecdotario $anecdotario)
    {
        $anecdotario->delete();

        return response()->json([
            'message' => 'Anecdotario eliminado exitosamente'
        ]);
    }
}
