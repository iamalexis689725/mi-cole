<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'asistencia_id',
        'estudiante_id',
        'estado',
        'observacion'
    ];

    public function asistencia()
    {
        return $this->belongsTo(Asistencia::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }
}
