<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anecdotario;
use App\Models\PadreFamilia;
use Illuminate\Support\Facades\Auth;

class PadreAnecdotarioController extends Controller
{
    public function index(int $estudianteId)
    {
        $user = Auth::user();

        $padre = PadreFamilia::where(
            'user_id',
            $user->id
        )->firstOrFail();

        $esHijo = $padre->estudiantes()
            ->where('estudiantes.id', $estudianteId)
            ->exists();

        if (!$esHijo) {
            abort(403);
        }

        $anecdotarios = Anecdotario::with([
            'profesor.user',
            'asignacionDocente.subject',
            'academicPeriod'
        ])
            ->where('estudiante_id', $estudianteId)
            ->latest('fecha')
            ->get();

        return response()->json($anecdotarios);
    }
}