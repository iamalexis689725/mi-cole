<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoEvaluacion extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'periodos_evaluacion';

    protected $fillable = [
        'academic_period_id',
        'nombre',
        'orden',        
    ];

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function criterios()
    {
        return $this->hasMany(Criterio::class);
    }
}
