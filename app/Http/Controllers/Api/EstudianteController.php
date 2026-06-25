<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EstudianteController extends Controller
{
    public function index()
    {
        return Estudiante::with('user')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->get();
    }

    public function show(int $id)
    {
        return Estudiante::with('user')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'codigo_estudiante' => 'required|string|unique:estudiantes,codigo_estudiante,NULL,id,tenant_id,' . auth()->user()->tenant_id,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        $user->assignRole('estudiante');

        $estudiante = Estudiante::create([
            'user_id'           => $user->id,
            'codigo_estudiante' => $request->codigo_estudiante,
            'tenant_id'         => auth()->user()->tenant_id,
        ]);

        return response()->json([
            'message' => 'Estudiante creado correctamente',
            'data'    => $estudiante->load('user')
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $estudiante = Estudiante::with('user')->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $estudiante->user->id,
            'password' => 'nullable|min:6',
            'codigo_estudiante' => 'sometimes|string|unique:estudiantes,codigo_estudiante,' . $id . ',id,tenant_id,' . auth()->user()->tenant_id,
        ]);

        $userData = $request->only(['name', 'email']);

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if (!empty($userData)) {
            $estudiante->user->update($userData);
        }

        $estudianteData = $request->only(['codigo_estudiante']);

        if (!empty($estudianteData)) {
            $estudiante->update($estudianteData);
        }

        return response()->json([
            'message' => 'Estudiante actualizado correctamente',
            'data'    => $estudiante->load('user')
        ]);
    }

    public function destroy(int $id)
    {
        $estudiante = Estudiante::with('user')->findOrFail($id);

        $user = $estudiante->user;
        $user->syncRoles([]);
        $estudiante->delete();
        $user->delete();

        return response()->json([
            'message' => 'Estudiante eliminado correctamente'
        ]);
    }

    public function horario()
{
    $user = auth()->user();

    $estudiante = Estudiante::where(
        'user_id',
        $user->id
    )->firstOrFail();

    $asignaciones = AsignacionDocente::with([
        'subject',
        'profesor.user',
        'horarios'
    ])
    ->whereHas(
        'curso.inscripciones',
        fn($q) =>
        $q->where(
            'estudiante_id',
            $estudiante->id
        )
    )
    ->get();

    $horarios = [];

    foreach ($asignaciones as $asignacion) {

        foreach ($asignacion->horarios as $horario) {

            $horarios[] = [

                'asignacion_id' => $asignacion->id,

                'dia' => strtolower($horario->dia),

                'hora_inicio' =>
                    substr($horario->hora_inicio, 0, 5),

                'hora_fin' =>
                    substr($horario->hora_fin, 0, 5),

                'materia' =>
                    $asignacion->subject->name,

                'profesor' =>
                    $asignacion->profesor->user->name,
            ];
        }
    }

    return response()->json($horarios);
}
}
