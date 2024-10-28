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
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->integer('region_code');
            $table->integer('company_code'); 
            $table->integer('structure_code'); 
            $table->integer('type'); 
            $table->string('name'); 
            $table->string('address')->nullable(); 
            $table->integer('zip_code')->nullable(); 
            $table->string('phone')->nullable(); 
            $table->string('email')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            //
        });
    }
};
