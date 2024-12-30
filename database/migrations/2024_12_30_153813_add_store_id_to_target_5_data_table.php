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
        Schema::table('target5_data', function (Blueprint $table) {
            $table->integer("numeratore_ao")->default(null);
            $table->integer("denominatore_ao")->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target5_data', function (Blueprint $table) {
            //
        });
    }
};
