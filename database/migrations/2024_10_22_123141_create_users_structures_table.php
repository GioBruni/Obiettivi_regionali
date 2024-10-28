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
        Schema::create('users_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("structure_id");
            $table->timestamps();

            $table->foreign("user_id", "fk_user_id_20241022")->on("users")->references("id");
            $table->foreign("structure_id", "fk_structure_id_20241022")->on("structures")->references("id");

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_structures', function (Blueprint $table) {
            //
        });
    }
};
