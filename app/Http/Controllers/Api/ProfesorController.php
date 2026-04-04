<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
}