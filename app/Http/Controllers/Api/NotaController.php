<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Criterio;
use App\Models\Nota;
use Illuminate\Http\Request;

class NotaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'criterio_id' => 'required|exists:criterios,id',
            'estudiante_id' => 'required|exists:estudiantes,id',
            'nota' => 'required|numeric|min:0|max:100',
        ]);

        return Nota::updateOrCreate(
            [
                'criterio_id' => $data['criterio_id'],
                'estudiante_id' => $data['estudiante_id'],
            ],
            ['nota' => $data['nota']]
        );
    }

    public function promedio($estudianteId, $asignacionId)
    {
        $criterios = Criterio::where('asignacion_docente_id', $asignacionId)->get();

        $total = 0;

        foreach ($criterios as $criterio) {
            $nota = Nota::where('criterio_id', $criterio->id)
                ->where('estudiante_id', $estudianteId)
                ->value('nota') ?? 0;

            $total += ($nota * $criterio->porcentaje) / 100;
        }

        return response()->json([
            'promedio' => round($total, 2)
        ]);
    }
}
