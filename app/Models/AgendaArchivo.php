<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaArchivo extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'agenda_id',
        'archivo',
        'nombre_original',
    ];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }
}
