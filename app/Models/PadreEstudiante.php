<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PadreEstudiante extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'padre_estudiante';

    protected $fillable = [
        'padre_familia_id',
        'estudiante_id',
        'parentesco',
    ];
}
