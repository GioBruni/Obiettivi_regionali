<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Target1 extends Model
{
    protected $table = 'target1_data';


    protected $fillable = [
        'year',
        'structure_id',
        'uploated_file_id',
        'numero_agende',
        'prestazioni_specialista_riferimento',
        'prestazioni_specialista_precedente',
        'prestazioni_MMG_riferimento',
        'prestazioni_MMG_precedente',
    ];

}
