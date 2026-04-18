<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfesorSubject extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'profesor_subject';

    protected $fillable = [
        'profesor_id',
        'subject_id',
    ];

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
