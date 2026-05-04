<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'asignacion_docente_id',
        'fecha',
        'tenant_id'
    ];

    public function asignacion()
    {
        return $this->belongsTo(AsignacionDocente::class, 'asignacion_docente_id');
    }

    public function detalles()
    {
        return $this->hasMany(AsistenciaDetalle::class);
    }
}
