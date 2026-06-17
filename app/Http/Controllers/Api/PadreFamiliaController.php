<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\PadreEstudiante;
use App\Models\PadreFamilia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PadreFamiliaController extends Controller
{
    public function index()
    {
        return PadreFamilia::with(['user', 'estudiantes.user'])->get();
    }


    public function misHijos()
    {
        $user = auth()->user();

        $padre = PadreFamilia::where('user_id', $user->id)
            ->with(['estudiantes.user'])
            ->firstOrFail();

        return response()->json([
            'padre' => [
                'id'     => $padre->id,
                'nombre' => $padre->user->name,
            ],
            'estudiantes' => $padre->estudiantes->map(fn($e) => [
                'id'                => $e->id,
                'nombre'            => $e->user->name,
                'codigo_estudiante' => $e->codigo_estudiante,
                'parentesco'        => $e->pivot->parentesco,
            ])
        ]);
    }

    public function show(int $id)
    {
        return PadreFamilia::with(['user', 'estudiantes.user'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'telefono' => 'nullable|string',
            'ocupacion' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        $user->assignRole('padre');

        $padre = PadreFamilia::create([
            'user_id'   => $user->id,
            'telefono'  => $request->telefono,
            'ocupacion' => $request->ocupacion,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return response()->json([
            'message' => 'Padre de familia creado correctamente',
            'data'    => $padre->load('user')
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $padre = PadreFamilia::with('user')->findOrFail($id);

        $request->validate([
            'name'      => 'sometimes|string',
            'email'     => 'sometimes|email|unique:users,email,' . $padre->user->id,
            'password'  => 'nullable|min:6',
            'telefono'  => 'nullable|string',
            'ocupacion' => 'nullable|string',
        ]);

        $userData = $request->only(['name', 'email']);

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if (!empty($userData)) {
            $padre->user->update($userData);
        }

        $padreData = $request->only(['telefono', 'ocupacion']);

        if (!empty($padreData)) {
            $padre->update($padreData);
        }

        return response()->json([
            'message' => 'Padre de familia actualizado correctamente',
            'data'    => $padre->load('user')
        ]);
    }

    public function destroy(int $id)
    {
        $padre = PadreFamilia::with('user')->findOrFail($id);

        PadreEstudiante::where('padre_familia_id', $id)->delete();

        $padre->user->syncRoles([]);
        $padre->user->delete();
        $padre->delete();

        return response()->json([
            'message' => 'Padre de familia eliminado correctamente'
        ]);
    }

    public function asignarEstudiante(Request $request)
    {
        $request->validate([
            'padre_familia_id' => [
                'required',
                Rule::exists('padre_familias', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
            'estudiante_id' => [
                'required',
                Rule::exists('estudiantes', 'id')
                    ->where('tenant_id', auth()->user()->tenant_id),
            ],
            'parentesco' => 'nullable|string|in:padre,madre,tutor,abuelo,otro',
        ]);

        $padre      = PadreFamilia::findOrFail($request->padre_familia_id);
        $estudiante = Estudiante::findOrFail($request->estudiante_id);

        if ($padre->tenant_id !== $estudiante->tenant_id) {
            return response()->json([
                'message' => 'No puedes relacionar datos de diferentes tenants'
            ], 403);
        }

        $existe = PadreEstudiante::where('padre_familia_id', $request->padre_familia_id)
            ->where('estudiante_id', $request->estudiante_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Este estudiante ya está asignado a este padre'
            ], 409);
        }

        $relacion = PadreEstudiante::create([
            'padre_familia_id' => $request->padre_familia_id,
            'estudiante_id'    => $request->estudiante_id,
            'parentesco'       => $request->parentesco,
        ]);

        return response()->json([
            'message' => 'Estudiante asignado correctamente',
            'data'    => $relacion
        ], 201);
    }

    public function estudiantes(int $id)
    {
        $padre = PadreFamilia::with(['user', 'estudiantes.user'])->findOrFail($id);

        return response()->json([
            'padre' => [
                'id'     => $padre->id,
                'nombre' => $padre->user->name,
            ],
            'estudiantes' => $padre->estudiantes->map(fn($e) => [
                'id'                => $e->id,
                'nombre'            => $e->user->name,
                'codigo_estudiante' => $e->codigo_estudiante,
                'parentesco'        => $e->pivot->parentesco,
            ])
        ]);
    }
}