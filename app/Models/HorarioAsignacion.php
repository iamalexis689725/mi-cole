<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioAsignacion extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'horarios_asignacion';

    protected $fillable = [
        'asignacion_docente_id',
        'dia',
        'hora_inicio',
        'hora_fin'
    ];

    public function asignacion()
    {
        return $this->belongsTo(AsignacionDocente::class, 'asignacion_docente_id');
    }
}
