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
        Schema::create('insert_mmg', function (Blueprint $table) {
            $table->id();
            $table->integer("mmg_totale");
            $table->integer("mmg_coinvolti");
            $table->integer("year");
            $table->datetime("created_at")->useCurrent();
            $table->datetime("updated_at")->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insert_mmg');
    }
};
