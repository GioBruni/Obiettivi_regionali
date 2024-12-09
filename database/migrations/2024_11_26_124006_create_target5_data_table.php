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
        Schema::create('target5_data', function (Blueprint $table) {
            $table->id();
            $table->integer("year");
            $table->integer("month");
            $table->unsignedBigInteger( "structure_id");
            $table->integer("mammografico");
            $table->integer("cercocarcinoma");
            $table->integer("colonretto");
            $table->timestamps();

            $table->foreign("structure_id")->on(table: "structures")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target5_data');
    }
};
