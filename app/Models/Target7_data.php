<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target7_data extends Model
{
    use HasFactory;

   
    protected $table = 'target7_data';


    protected $fillable = [
        'dimissioni_ospedaliere',
        'dimissioni_ps',
        'prestazioni_laboratorio',
        'prestazioni_radiologia',
        'prestazioni_ambulatoriali',
        'vaccinati',
        'certificati_indicizzati',
        'documenti_indicizzati',
        'documenti_cda2',
        'documenti_indicizzati_cda2',
        'documenti_pades',
        'documenti_indicizzati_pades',
        'structure_id',
        'anno',
        'created_at',
        'updated_at',
    ];
}
