<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'criterio_id',
        'estudiante_id',
        'nota',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function criterio()
    {
        return $this->belongsTo(Criterio::class);
    }
}
