<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionDocente extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'asignaciones_docente';

    protected $fillable = [
        'profesor_id',
        'subject_id',
        'curso_id',
        'paralelo_id',
        'academic_period_id',
    ];

    public function horarios()
    {
        return $this->hasMany(
            HorarioAsignacion::class,
            'asignacion_docente_id'
        );
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function paralelo()
    {
        return $this->belongsTo(Paralelo::class);
    }

    public function periodo()
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }
}
