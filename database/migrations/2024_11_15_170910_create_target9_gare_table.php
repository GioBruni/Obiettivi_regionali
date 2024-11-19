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
        Schema::create('target9_gare', function (Blueprint $table) {
            $table->id();
            $table->date("data_appalto");
            $table->integer("year");
            $table->unsignedBigInteger( "structure_id");
            $table->unsignedBigInteger("uploated_file_gara_id");
            $table->unsignedBigInteger("uploated_file_delibera_id")->nullable();
            $table->string("numero_decreto");
            $table->string("protocollo_decreto");
            $table->date("data_protocollo_decreto");
            $table->string("numero_delibera")->nullable();
            $table->date("data_delibera")->nullable();
            $table->integer("anno_delibera")->nullable();
            $table->timestamps();

            $table->foreign("structure_id")->on(table: "structures")->references("id");
            $table->foreign("uploated_file_gara_id")->on("uploated_files")->references("id");
            $table->foreign("uploated_file_delibera_id")->on("uploated_files")->references("id");
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target9_gare');
    }
};
