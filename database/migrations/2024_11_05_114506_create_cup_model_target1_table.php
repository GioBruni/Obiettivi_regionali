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
        Schema::create('cup_model_target1', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("structure_id");
            $table->unsignedBigInteger("user_id");
            $table->date("provision_date");
            $table->integer("amount");
            $table->string("doctor_code");
            $table->string("nomenclator_code");
            $table->timestamps();

            $table->foreign("structure_id")->on("structures")->references("id");
            $table->foreign("user_id")->on("users")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cup_model_target1');
    }
};
