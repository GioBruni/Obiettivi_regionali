<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CUPTarget1 extends Model
{

    protected $table = "cup_model_target1";
    
    protected $fillable = [
        'user_id',
        'structure_id',
        'provision_date',       // data erogazione
        'amount',               // quantità
        'doctor_code',          // codice prescrittore
        'nomenclator_code',     // codice nomenclatore
    ];
}
