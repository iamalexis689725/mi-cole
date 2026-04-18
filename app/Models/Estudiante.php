<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'estudiantes';

    protected $fillable = [
        'user_id',
        'codigo_estudiante',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function padres()
    {
        return $this->belongsToMany(PadreFamilia::class, 'padre_estudiante')
            ->withPivot('tenant_id', 'parentesco')
            ->wherePivot('tenant_id', auth()->user()->tenant_id);
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }
}
