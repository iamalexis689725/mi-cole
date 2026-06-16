<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\User;
use App\Models\Profesor;
use App\Models\ProfesorSubject;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfesorController extends Controller
{

    public function index()
    {
        return Profesor::with(['user', 'subjects'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->get();
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'codigo_profesor' => 'required|string|unique:profesores,codigo_profesor,NULL,id,tenant_id,' . auth()->user()->tenant_id,
            'especialidad' => 'nullable|string'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => auth()->user()->tenant_id
        ]);

        $user->assignRole('profesor');

        $profesor = Profesor::create([
            'user_id' => $user->id,
            'codigo_profesor' => $request->codigo_profesor,
            'especialidad' => $request->especialidad,
            'tenant_id' => auth()->user()->tenant_id
        ]);

        return response()->json([
            'message' => 'Profesor creado correctamente',
            'data' => $profesor->load('user')
        ], 201);
    }


    public function show($id)
    {
        return Profesor::with([
            'user',
            'subjects' => function ($query) {
                $query->where('subjects.tenant_id', auth()->user()->tenant_id);
            }
        ])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $profesor = Profesor::with('user')->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $profesor->user->id,
            'password' => 'nullable|min:6',
            'codigo_profesor' => 'sometimes|string|unique:profesores,codigo_profesor,' . $id . ',id,tenant_id,' . auth()->user()->tenant_id,
            'especialidad' => 'nullable|string'
        ]);

        // actualizo usuario
        $userData = $request->only(['name', 'email']);

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if (!empty($userData)) {
            $profesor->user->update($userData);
        }

        //actualizo profesor
        $profesorData = $request->only([
            'codigo_profesor',
            'especialidad'
        ]);

        if (!empty($profesorData)) {
            $profesor->update($profesorData);
        }

        return response()->json(
            $profesor->load('user')
        );
    }


    public function destroy($id)
    {
        $profesor = Profesor::with('user')->findOrFail($id);
        ProfesorSubject::where('profesor_id', $id)->delete();
        $user = $profesor->user;
        $profesor->delete();
        $user->delete();

        return response()->json([
            'message' => 'Profesor eliminado correctamente'
        ]);
    }


    public function asignarMateria(Request $request)
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

        $profesor = Profesor::findOrFail($request->profesor_id);
        $subject = Subject::findOrFail($request->subject_id);

        if ($profesor->tenant_id !== $subject->tenant_id) {
            return response()->json([
                'message' => 'No puedes relacionar datos de diferentes tenants'
            ], 403);
        }

        $existe = ProfesorSubject::where('profesor_id', $request->profesor_id)
            ->where('subject_id', $request->subject_id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Esta materia ya fue asignada a este profesor'
            ], 409);
        }

        $relacion = ProfesorSubject::create([
            'profesor_id' => $request->profesor_id,
            'subject_id' => $request->subject_id,
            'tenant_id' => auth()->user()->tenant_id
        ]);

        return response()->json([
            'message' => 'Materia asignada correctamente',
            'data' => $relacion
        ], 201);
    }

    public function quitarMateria(Request $request)
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

        $deleted = ProfesorSubject::where('profesor_id', $request->profesor_id)
            ->where('subject_id', $request->subject_id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'La relación no existe'
            ], 404);
        }

        return response()->json([
            'message' => 'Materia eliminada correctamente'
        ]);
    }


    public function subjects(int $id)
    {
        $profesor = Profesor::with('subjects')->findOrFail($id);

        return response()->json([
            'data' => $profesor->subjects
        ]);
    }

    public function horario($id)
    {
        $profesor = Profesor::with('user')->findOrFail($id);

        $asignaciones = AsignacionDocente::with(['subject', 'curso', 'paralelo'])
            ->where('profesor_id', $id)
            ->orderBy('hora_inicio')
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'dia'         => $a->dia,
                'hora_inicio' => $a->hora_inicio,
                'hora_fin'    => $a->hora_fin,
                'materia'     => $a->subject->name,
                'curso'       => $a->curso->nombre,
                'paralelo'    => $a->paralelo->nombre,
            ])
            ->groupBy('dia');

        $orden = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

        $horario = collect($orden)
            ->filter(fn($dia) => $asignaciones->has($dia))
            ->mapWithKeys(fn($dia) => [$dia => $asignaciones[$dia]]);

        return response()->json([
            'profesor' => [
                'id'     => $profesor->id,
                'nombre' => $profesor->user->name,
                'codigo' => $profesor->codigo_profesor,
            ],
            'horario' => $horario
        ]);
    }
}
