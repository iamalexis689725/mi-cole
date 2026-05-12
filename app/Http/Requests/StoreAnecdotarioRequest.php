<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnecdotarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'estudiante_id' => 'required|exists:estudiantes,id',
            'asignacion_docente_id' => 'required|exists:asignaciones_docente,id',
            'academic_period_id' => 'required|exists:academic_periods,id',
            'tipo' => 'required|in:conducta,merito,observacion',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha' => 'required|date',
        ];
    }
}
