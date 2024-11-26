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
        Schema::create('target7_data', function (Blueprint $table) {
            $table->id();
            $table->integer("dimissioni_ospedaliere");
            $table->integer("dimissioni_ps");
            $table->integer("prestazioni_laboratorio");
            $table->integer("prestazioni_radiologia");
            $table->integer("prestazioni_ambulatoriali");
            $table->integer("vaccinati");
            $table->integer("certificati_indicizzati");
            $table->integer("documenti_indicizzati");
            $table->integer("documenti_cda2");
            $table->integer("documenti_indicizzati_cda2");
            $table->integer("documenti_pades");
            $table->integer("documenti_indicizzati_pades");
            $table->unsignedBigInteger("structure_id");
            $table->integer("anno");
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
        Schema::dropIfExists('target7_data');
    }
};
