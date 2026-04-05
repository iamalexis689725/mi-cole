<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        return Profesor::with('user')->get();
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('director')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'codigo_profesor' => 'required|unique:profesores',
            'especialidad' => 'nullable|string'
        ]);

        // 🔥 Crear USER (con tenant)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => auth()->user()->tenant_id
        ]);

        // 🔥 Asignar rol
        $user->assignRole('profesor');

        // 🔥 Crear PROFESOR (tenant automático por trait)
        $profesor = Profesor::create([
            'user_id' => $user->id,
            'codigo_profesor' => $request->codigo_profesor,
            'especialidad' => $request->especialidad
        ]);

        return response()->json([
            'message' => 'Profesor creado correctamente',
            'data' => $profesor->load('user')
        ], 201);
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

        // 🔥 VALIDACIÓN EXTRA (ANTI BUG)
        $profesor = Profesor::findOrFail($request->profesor_id);
        $subject = Subject::findOrFail($request->subject_id);

        if ($profesor->tenant_id !== $subject->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes relacionar datos de diferentes tenants'
            ], 403);
        }

        // 🔥 EVITAR DUPLICADOS
        $existe = ProfesorSubject::where('profesor_id', $request->profesor_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Esta materia ya fue asignada a este profesor'
            ], 409);
        }

        // 🔥 CREAR RELACIÓN
        $relacion = ProfesorSubject::create([
            'profesor_id' => $request->profesor_id,
            'subject_id' => $request->subject_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Materia asignada correctamente',
            'data' => $relacion
        ], 201);
    }
}
