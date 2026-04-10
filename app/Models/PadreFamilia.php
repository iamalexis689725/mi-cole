<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PadreFamilia extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'padre_familias';

    protected $fillable = [
        'user_id',
        'telefono',
        'ocupacion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'padre_estudiante')
            ->withPivot('tenant_id', 'parentesco')
            ->wherePivot('tenant_id', auth()->user()->tenant_id);
    }
}
