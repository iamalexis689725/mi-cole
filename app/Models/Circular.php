<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Circular extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'circulares';

    protected $fillable = [
        'titulo',
        'contenido',
        'tenant_id',
        'created_by',
        'target',
        'published_at',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('leido', 'leido_at')
            ->withTimestamps();
    }
}
