<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gare extends Model
{
    protected $table = "target9_gare";
    
    protected $fillable = [
        'year',
        'data_appalto',
        'anno_gara',
        'structure_id',
        'uploated_file_gara_id',
        'uploated_file_delibera_id',
        'numero_decreto',
        'protocollo_decreto',
        'data_protocollo_decreto',
        'numero_delibera',
        'data_delibera',
        'anno_delibera',
    ];
}
