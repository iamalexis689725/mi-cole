<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircularUser extends Model
{
    use HasFactory;
    
    protected $table = 'circular_user';

    protected $fillable = [
        'circular_id',
        'user_id',
        'leido',
        'leido_at',
    ];
}
