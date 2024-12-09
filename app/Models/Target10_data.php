<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target10_data extends Model
{
    use HasFactory;

    protected $table = 'target10_data';


    protected $fillable = [
        'ob10_1_numeratore',
        'ob10_1_denominatore',
        'ob10_2_numeratore',
        'ob10_2_denominatore',
        'num_aziende_bovine_controllate',
        'num_aziende_bovine_totali',
        'num_aziende_ovicaprine_controllate',
        'num_aziende_ovicaprine_totali',
        'num_capi_ovicaprini_controllati',
        'num_capi_ovicaprini_totali',
        'num_aziende_suine_controllate',
        'num_aziende_suine_totali',
        'num_aziende_equine_controllate',
        'num_aziende_equine_totali',
        'num_allevamenti_apistici_controllati',
        'num_allevamenti_apistici_totali',
        'pnaa7_esecuzione',
        'pnaa7_esecuzione_totali',
        'controlli_farmacosorveglianza_veterinaria',
        'controlli_farmacosorveglianza_veterinaria_totali',




        'copertura_pnr_num',
        'copertura_pnr_den',
        'copertura_fitofarmaci_num',
        'copertura_fitofarmaci_den',
        'copertura_additivi_num',
        'copertura_additivi_den',


        'ob10_at_1_den',
        'ob10_at_2_den',


        'cia_1_num',
        'cia_1_den',
        'cia_2_num',
        'cia_2_den',
        'cia_3_num',
        'cia_3_den',

        'ob10_ao_4_num',
        'ob10_ao_4_den',


        'structure_id',
        'anno',
        'mese',
        'created_at',
        'updated_at',
    ];
}
