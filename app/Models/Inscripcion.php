<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'estudiante_id',
        'curso_id',
        'paralelo_id',
        'academic_period_id',
        'tenant_id'
    ];

    protected $table = 'inscripciones';

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
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
