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
            $table->integer("dimissioni_ospedaliere")->nullable();
            $table->integer("dimissioni_ps")->nullable();
            $table->integer("prestazioni_laboratorio")->nullable();
            $table->integer("prestazioni_radiologia")->nullable();
            $table->integer("prestazioni_ambulatoriali")->nullable();
            $table->integer("vaccinati")->nullable();
            $table->integer("certificati_indicizzati")->nullable();
            $table->integer("documenti_indicizzati")->nullable();
            $table->integer("documenti_cda2")->nullable();
            $table->integer("documenti_indicizzati_cda2")->nullable();
            $table->integer("documenti_pades")->nullable();
            $table->integer("documenti_indicizzati_pades")->nullable();
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
