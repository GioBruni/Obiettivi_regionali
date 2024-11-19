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
        Schema::create('target6_data', function (Blueprint $table) {
            $table->id();
            $table->integer("totale_accertamenti");
            $table->integer("numero_opposti");
            $table->integer("totale_cornee");
            $table->integer("anno");
            $table->unsignedBigInteger("structure_id");
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
        Schema::dropIfExists('target6_data');
    }
};
