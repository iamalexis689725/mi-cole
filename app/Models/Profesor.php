<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'profesores';

    protected $fillable = [
        'user_id',
        'codigo_profesor',
        'especialidad',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
