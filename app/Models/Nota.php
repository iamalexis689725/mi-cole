<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'notas';

    protected $fillable = [
        'criterio_id',
        'estudiante_id',
        'nota',
        'observacion',
    ];

    protected $casts = [
        'nota' => 'decimal:2',
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
