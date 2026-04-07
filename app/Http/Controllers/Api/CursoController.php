<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index()
    {
        return Curso::all();
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'nombre' => 'required|string',
            'nivel' => 'required|string',
            'descripcion' => 'nullable|string',
        ]);

        $curso = Curso::create([
            'nombre' => $request->nombre,
            'nivel' => $request->nivel,
            'descripcion' => $request->descripcion,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return response()->json($curso, 201);
    }

    public function show($id)
    {
        return Curso::with('paralelos')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $curso = Curso::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string',
            'nivel' => 'required|string',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|boolean',
        ]);

        $data = $request->only([
            'nombre',
            'nivel',
            'descripcion',
            'estado'
        ]);

        $curso->update($data);

        return response()->json($curso);
    }

    public function destroy($id)
    {
        Curso::findOrFail($id)->delete();

        return response()->json(['message' => 'Curso eliminado correctamente']);
    }

    public function paralelos($id)
    {
        $curso = Curso::with('paralelos')->findOrFail($id);

        return response()->json([
            'data' => $curso->paralelos
        ]);
    }
}
