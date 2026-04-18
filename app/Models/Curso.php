<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'nombre',
        'nivel',
        'descripcion',
        'estado',
        'academic_period_id'
    ];

    public function paralelos()
    {
        return $this->hasMany(Paralelo::class);
    }

    public function periodo()
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }
}
