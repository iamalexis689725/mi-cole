<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfesorSubject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfesorSubjectController extends Controller
{
    public function index()
    {
        $data = ProfesorSubject::with(['profesor.user', 'subject'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $relacion = ProfesorSubject::with(['profesor.user', 'subject'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        return response()->json([
            'data' => $relacion
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'profesor_id' => [
                'required',
                Rule::exists('profesores', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
        ]);

        $existe = ProfesorSubject::where('profesor_id', $request->profesor_id)
            ->where('subject_id', $request->subject_id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Ya existe esta relación'
            ], 409);
        }

        $relacion = ProfesorSubject::create([
            'profesor_id' => $request->profesor_id,
            'subject_id' => $request->subject_id,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return response()->json([
            'message' => 'Relación creada correctamente',
            'data' => $relacion
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $relacion = ProfesorSubject::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $request->validate([
            'profesor_id' => [
                'sometimes',
                Rule::exists('profesores', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
            'subject_id' => [
                'sometimes',
                Rule::exists('subjects', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
        ]);

        $relacion->update($request->only([
            'profesor_id',
            'subject_id'
        ]));

        return response()->json([
            'message' => 'Relación actualizada',
            'data' => $relacion
        ]);
    }

    public function destroy($id)
    {
        $relacion = ProfesorSubject::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $relacion->delete();

        return response()->json([
            'message' => 'Relación eliminada correctamente'
        ]);
    }
}