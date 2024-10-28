<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationsUsers extends Model
{
    
    protected $table = "users_structures";
    
    protected $fillable = [
        'user_id',
        'structure_id',
    ];

}
