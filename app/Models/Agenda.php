<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'asignacion_docente_id',
        'titulo',
        'descripcion',
        'tipo',
        'fecha_entrega',
        'archivo',
    ];

    public function asignacion()
    {
        return $this->belongsTo(
            AsignacionDocente::class,
            'asignacion_docente_id'
        );
    }

    public function archivos()
    {
        return $this->hasMany(AgendaArchivo::class);
    }
}
