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
        Schema::create('flows_m', function (Blueprint $table) {
            $table->id();
            $table->string("file_name");
            $table->unsignedBigInteger("structure_id");
            $table->integer("year");
            $table->integer("month");
            $table->integer("version")->nullable();
            $table->datetime("created_at")->useCurrent();
            $table->date("file_date");
            $table->integer("ob1_1")->nullable();
            $table->integer("ob5_4")->nullable();
            $table->integer("ia1_3")->nullable();
            $table->integer("ia1_4")->nullable();
            $table->integer("ia1_5")->nullable();
            $table->integer("ia1_6")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flows_m');
    }
};
