<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registro de usuario
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|exists:roles,name'
        ]);

        // Crear usuario
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Asignar rol
        $user->assignRole($validated['role']);

        // Crear token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'token' => $token
        ], 201);
    }

    /**
     * Login de usuario
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Intento de autenticación
        if (!Auth::attempt($validated)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas'],
            ]);
        }

        $user = $request->user();

        // Revocar tokens anteriores (opcional pero recomendado)
        $user->tokens()->delete();

        // Crear nuevo token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'token' => $token
        ]);
    }

    /**
     * Logout (cerrar sesión)
     */
    public function logout(Request $request)
    {
        // Elimina solo el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout correcto'
        ]);
    }

    /**
     * Perfil del usuario autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames()
        ]);
    }
}
