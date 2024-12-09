<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('target10_data', function (Blueprint $table) {
            $table->id();
            $table->integer("ob10_1_numeratore")->nullable();
            $table->integer("ob10_1_denominatore")->nullable();

            $table->integer("ob10_2_numeratore")->nullable();
            $table->integer("ob10_2_denominatore")->nullable();
            
            $table->integer("num_aziende_bovine_controllate")->nullable();
            $table->integer("num_aziende_bovine_totali")->nullable();

            $table->integer("num_aziende_ovicaprine_controllate")->nullable();
            $table->integer("num_aziende_ovicaprine_totali")->nullable();

            $table->integer("num_capi_ovicaprini_controllati")->nullable();
            $table->integer("num_capi_ovicaprini_totali")->nullable();

            $table->integer("num_aziende_suine_controllate")->nullable();
            $table->integer("num_aziende_suine_totali")->nullable();

            $table->integer("num_aziende_equine_controllate")->nullable();
            $table->integer("num_aziende_equine_totali")->nullable();

            $table->integer("num_allevamenti_apistici_controllati")->nullable();
            $table->integer("num_allevamenti_apistici_totali")->nullable();

            $table->integer("pnaa7_esecuzione")->nullable();
            $table->integer("pnaa7_esecuzione_totali")->nullable();

            $table->integer("controlli_farmacosorveglianza_veterinaria")->nullable();
            $table->integer("controlli_farmacosorveglianza_veterinaria_totali")->nullable();

            $table->integer("copertura_pnr_num")->nullable();
            $table->integer("copertura_pnr_den")->nullable();

            $table->integer("copertura_fitofarmaci_num")->nullable();
            $table->integer("copertura_fitofarmaci_den")->nullable();

            $table->integer("copertura_additivi_num")->nullable();
            $table->integer("copertura_additivi_den")->nullable();

            $table->integer("ob10_at_1_den")->nullable();
            $table->integer("ob10_at_2_den")->nullable();

            $table->integer("cia_1_num")->nullable();
            $table->integer("cia_1_den")->nullable();

            $table->integer("cia_2_num")->nullable();
            $table->integer("cia_2_den")->nullable();

            $table->integer("cia_3_num")->nullable();
            $table->integer("cia_3_den")->nullable();

            $table->integer("ob10_ao_4_num")->nullable();
            $table->integer("ob10_ao_4_den")->nullable();


            $table->unsignedBigInteger("structure_id");
            $table->integer("anno");
            $table->integer("mese");
            $table->datetime("created_at")->useCurrent();
            $table->datetime("updated_at")->useCurrent();

            $table->foreign("structure_id")->on("structures")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target10_data');
    }
};
