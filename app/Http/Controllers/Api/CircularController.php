<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Circular;
use App\Models\CircularUser;
use App\Models\User;
use Illuminate\Http\Request;

class CircularController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = Circular::where('tenant_id', $user->tenant_id);

        if ($user->roles->contains('name', 'director')) {
            return $query->with('creator')->latest()->get();
        }

        // 👤 USUARIOS NORMALES (padre, estudiante, etc.)
        return $query
            ->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['creator', 'users' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'contenido' => 'required|string',
            'target' => 'required|in:all,padres,profesores,estudiantes',
        ]);

        $user = auth()->user();

        $circular = Circular::create([
            'titulo' => $request->titulo,
            'contenido' => $request->contenido,
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'target' => $request->target,
            'published_at' => now(),
        ]);

        $query = User::where('tenant_id', $user->tenant_id);

        if ($request->target !== 'all') {

            $roleMap = [
                'padres' => 'padre',
                'profesores' => 'profesor',
                'estudiantes' => 'estudiante',
            ];

            $roleName = $roleMap[$request->target] ?? null;

            if ($roleName) {
                $query->whereHas('roles', function ($q) use ($roleName) {
                    $q->where('name', $roleName);
                });
            }
        }

        $users = $query->pluck('id');

        $data = $users->map(fn($userId) => [
            'circular_id' => $circular->id,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        CircularUser::insert($data);

        return response()->json([
            'message' => 'Circular creada',
            'data' => $circular
        ], 201);
    }

    public function marcarLeido($id)
    {
        $user = auth()->user();

        $rel = CircularUser::where('circular_id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$rel->leido) {
            $rel->update([
                'leido' => true,
                'leido_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Marcado como leído'
        ]);
    }

    public function show($id)
    {
        $user = auth()->user();

        $circular = Circular::where('tenant_id', $user->tenant_id)
            ->with(['creator', 'users' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->findOrFail($id);

        if ($user->roles->contains('name', 'director')) {
            return $circular;
        }

        $tieneAcceso = CircularUser::where('circular_id', $id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$tieneAcceso) {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }

        return $circular;
    }

    public function stats($id)
    {
        $total = CircularUser::where('circular_id', $id)->count();
        $leidos = CircularUser::where('circular_id', $id)
            ->where('leido', true)
            ->count();

        return response()->json([
            'total' => $total,
            'leidos' => $leidos,
            'pendientes' => $total - $leidos
        ]);
    }

    public function update(Request $request, $id)
    {
        $circular = Circular::findOrFail($id);

        if ($circular->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($circular->users()->where('leido', true)->exists()) {
            return response()->json([
                'message' => 'No se puede editar, ya fue leída'
            ], 400);
        }

        $request->validate([
            'titulo' => 'sometimes|string',
            'contenido' => 'sometimes|string',
        ]);

        $circular->update($request->only(['titulo', 'contenido']));

        return response()->json([
            'message' => 'Circular actualizada',
            'data' => $circular
        ]);
    }

    public function destroy($id)
    {
        $circular = Circular::findOrFail($id);

        if ($circular->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $circular->delete();

        return response()->json([
            'message' => 'Circular eliminada'
        ]);
    }
}
