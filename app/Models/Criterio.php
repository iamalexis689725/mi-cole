<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criterio extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'asignacion_docente_id',
        'nombre',
        'porcentaje',
    ];

    public function notas()
    {
        return $this->hasMany(Nota::class);
    }
}
