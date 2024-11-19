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
        Schema::create('target9_PCT', function (Blueprint $table) {
            $table->id();
            $table->integer("numerator");
            $table->integer("begin_month");
            $table->integer("end_month");
            $table->integer("year");
            $table->unsignedBigInteger( "structure_id");
            $table->unsignedBigInteger("uploated_file_id")->nullable();
            $table->timestamps();

            $table->foreign("structure_id")->on(table: "structures")->references("id");
            $table->foreign("uploated_file_id")->on("uploated_files")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_PCT');
    }
};
