<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class Generic extends Model
{
    
    protected $fillable = [
        'id',
        'description',
        'enable',
    ];

    
}
