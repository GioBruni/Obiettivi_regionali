<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target6_data extends Model
{
    use HasFactory;

   
    protected $table = 'target6_data';


    protected $fillable = [
        'totale_accertamenti',
        'numero_opposti',
        'totale_cornee',
        'anno',
        'structure_id',
        'created_at',
        'updated_at',
    ];
}
