<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paralelo extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'curso_id',
        'nombre',
        'turno',
        'capacidad'
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }
}
