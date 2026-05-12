<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anecdotario extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'estudiante_id',
        'profesor_id',
        'asignacion_docente_id',
        'academic_period_id',
        'tipo',
        'titulo',
        'descripcion',
        'fecha',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class , 'estudiante_id');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class , 'profesor_id');
    }

    public function asignacionDocente()
    {
        return $this->belongsTo(AsignacionDocente::class , 'asignacion_docente_id');
    }

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class , 'academic_period_id');
    }
}
