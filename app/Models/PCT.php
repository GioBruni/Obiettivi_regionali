<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PCT extends Model
{
    protected $table = "target_PCT";
    
    protected $fillable = [
        'numerator',
        'denominator',
        'year',
        'begin_month',
        'end_month',
        'structure_id',
        'user_id',
        'uploated_file_id',
    ];

}
