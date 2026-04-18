<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "nombre",
        "fecha_inicio",
        "fecha_fin",
        "activo",
    ];

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }
}
