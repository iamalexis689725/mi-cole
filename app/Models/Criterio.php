<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criterio extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'criterios';

    protected $fillable = [
        'asignacion_docente_id',
        'periodo_evaluacion_id',
        'nombre',
        'porcentaje',
    ];

    protected $casts = [
        'porcentaje' => 'decimal:2',
    ];

    public function notas()
    {
        return $this->hasMany(Nota::class);
    }

    public function asignacionDocente()
    {
        return $this->belongsTo(AsignacionDocente::class);
    }

    public function periodoEvaluacion()
    {
        return $this->belongsTo(PeriodoEvaluacion::class);
    }
}
