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
        Schema::table('flows_sdo', function (Blueprint $table) {
            $table->string("file_name");
            $table->unsignedBigInteger("structure_id");
            $table->integer("year");
            $table->integer("month");
            $table->string("version")->nullable();
            $table->datetime("created_at");
            $table->date("file_date");
            $table->integer("ob2_1");
            $table->integer("ob2_2");
            $table->integer("ob2_3");
            $table->integer( "ob2_4");
            $table->integer( "ob6");
            $table->integer("ob7_1")->nullable();
            $table->integer("ob9_2")->nullable();
            $table->integer("ob10_1")->nullable();
            $table->integer("ob10_2")->nullable();
            $table->integer("ob10_3")->nullable();

            $table->foreign("structure_id", "20241108_flows_sdo_structure_id")->on("structures")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flows_sdo', function (Blueprint $table) {
            //
        });
    }
};
