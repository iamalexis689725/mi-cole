<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Paralelo;
use Illuminate\Http\Request;

class ParaleloController extends Controller
{
    public function index()
    {
        return Paralelo::with('curso')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'nombre' => 'required|string|unique:paralelos,nombre,NULL,id,curso_id,' . $request->curso_id . ',tenant_id,' . auth()->user()->tenant_id,
            'turno' => 'nullable|string',
            'capacidad' => 'nullable|integer',
        ], [
            'nombre.unique' => 'Este paralelo ya existe en este curso'
        ]);

        // valido mi tenant
        $curso = Curso::where('id', $request->curso_id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$curso) {
            return response()->json([
                'message' => 'Curso no pertenece a tu colegio'
            ], 403);
        }

        $paralelo = Paralelo::create([
            'curso_id' => $request->curso_id,
            'nombre' => $request->nombre,
            'turno' => $request->turno,
            'capacidad' => $request->capacidad,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return response()->json($paralelo, 201);
    }

    public function show($id)
    {
        return Paralelo::with('curso')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $paralelo = Paralelo::findOrFail($id);

        $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'nombre' => 'required|string|unique:paralelos,nombre,' . $id . ',id,curso_id,' . $request->curso_id . ',tenant_id,' . auth()->user()->tenant_id,
            'turno' => 'nullable|string',
            'capacidad' => 'nullable|integer',
        ], [
            'nombre.unique' => 'Este paralelo ya existe en este curso'
        ]);

        // valido tenant
        $curso = Curso::where('id', $request->curso_id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$curso) {
            return response()->json([
                'message' => 'Curso no pertenece a tu colegio'
            ], 403);
        }

        $data = $request->only([
            'curso_id',
            'nombre',
            'turno',
            'capacidad'
        ]);

        $paralelo->update($data);

        return response()->json($paralelo);
    }

    public function destroy($id)
    {
        Paralelo::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Paralelo eliminado correctamente'
        ]);
    }
}
