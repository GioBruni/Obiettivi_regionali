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
        Schema::create('result_target3', function (Blueprint $table) {
            $table->id();
            $table->integer("numerator")->nullable();
            $table->integer("denominator")->nullable();
            $table->unsignedBigInteger("uploated_file_id");
            $table->timestamps();

            $table->foreign("uploated_file_id")->on("uploated_files")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_target3');
    }
};
