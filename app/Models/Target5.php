<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Target5 extends Model
{
    protected $table = 'target5_data';


    protected $fillable = [
        'year',
        'month',
        'structure_id',
        'mammografico',
        'cercocarcinoma',
        'colonretto',
    ];
}
