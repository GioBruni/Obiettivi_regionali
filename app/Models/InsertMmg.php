<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsertMmg extends Model
{
    use HasFactory;

   
    protected $table = 'insert_mmg';


    protected $fillable = [
        'mmg_totale',
        'mmg_coinvolti',
        'year',
        'structure_id',
    ];
}

