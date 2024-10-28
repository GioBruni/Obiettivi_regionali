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
        Schema::create('flows_EMUR', function (Blueprint $table) {
            $table->id();
            $table->string("file_name");
            $table->unsignedBigInteger("structure_id");
            $table->integer("year");
            $table->integer("month");
            $table->integer("tmp");
            $table->integer("boarding");
            $table->integer("version")->nullable();
            $table->datetime("created_at")->useCurrent();
            $table->date("file_date");

            $table->foreign("structure_id", "fk_structure_id")->on("structures")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flows_EMUR', function (Blueprint $table) {
            //
        });
    }
};
