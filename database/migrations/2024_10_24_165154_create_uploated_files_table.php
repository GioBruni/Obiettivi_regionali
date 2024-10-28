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
        Schema::create('uploated_files', function (Blueprint $table) {
            $table->id();
            $table->string("filename");
            $table->string("path");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("structure_id");
            $table->string("notes")->nullable();
            $table->integer("target_number");
            $table->unsignedBigInteger( "target_category_id")->nullable();
            $table->timestamps();    

            $table->foreign("user_id", "fk_user_id_20241024")->on("users")->references("id");
            $table->foreign("structure_id", "fk_structure_id_20241024")->on("structures")->references("id");
            $table->foreign("target_category_id", "fk_target_category_id_20241024")->on("target_categories")->references("id");

        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('updated_files');
    }
};
