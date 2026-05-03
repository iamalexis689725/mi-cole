<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index($asignacionId)
    {
        return Agenda::where('asignacion_docente_id', $asignacionId)
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asignacion_docente_id' => 'required|exists:asignacion_docentes,id',
            'titulo' => 'required|string',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:tarea,examen,recurso',
            'fecha_entrega' => 'nullable|date',
            'archivo' => 'nullable|file'
        ]);

        if ($request->hasFile('archivo')) {
            $data['archivo'] = $request->file('archivo')->store('agendas');
        }

        return Agenda::create($data);
    }

    public function destroy($id)
    {
        $agenda = Agenda::findOrFail($id);
        $agenda->delete();

        return response()->json(['message' => 'Agenda eliminada correctamente']);
    }


}
