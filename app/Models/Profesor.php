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

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'profesor_subject')
        ->withPivot('tenant_id')
        ->wherePivot('tenant_id', auth()->user()->tenant_id)
        ->where('subjects.tenant_id', auth()->user()->tenant_id);
    }
}
