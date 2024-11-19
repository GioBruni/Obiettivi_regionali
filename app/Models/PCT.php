<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PCT extends Model
{
    protected $table = "target9_PCT";
    
    protected $fillable = [
        'numerator',
        'begin_month',
        'end_month',
        'year',
        'structure_id',
        'uploated_file_id',
    ];

}
