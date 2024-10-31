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
        Schema::create('target_PCT', function (Blueprint $table) {
            $table->id();
            $table->integer("numerator");
            $table->integer( "denominator")->nullable();
            $table->integer("year");
            $table->integer("begin_month");
            $table->integer("end_month");
            $table->unsignedBigInteger( "structure_id");
            $table->unsignedBigInteger( "user_id");
            $table->unsignedBigInteger("uploated_file_id")->nullable();
            $table->timestamps();

            $table->foreign("structure_id")->on("structures")->references("id");
            $table->foreign("user_id")->on("users")->references("id");
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
