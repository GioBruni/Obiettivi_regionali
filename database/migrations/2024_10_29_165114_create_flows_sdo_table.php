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
        Schema::create('flows_sdo', function (Blueprint $table) {
            $table->id();
            $table->string("file_name");
            $table->unsignedBigInteger("structure_id");
            $table->integer("year");
            $table->integer("month");
            $table->integer("version")->nullable();
            $table->datetime("created_at")->useCurrent();
            $table->date("file_date");
            $table->integer("ob2_1_numeratore")->nullable();
            $table->integer("ob2_1_denominatore")->nullable();
            $table->integer("ob2_2_minore_mille_numeratore")->nullable();
            $table->integer("ob2_2_minore_mille_denominatore")->nullable();
            $table->integer("ob2_2_maggiore_mille_numeratore")->nullable();
            $table->integer("ob2_2_maggiore_mille_denominatore")->nullable();

            $table->integer("ob2_3_numeratore")->nullable();
            $table->integer("ob2_3_denominatore")->nullable();
            $table->integer("ob2_4_numeratore")->nullable();
            $table->integer("ob2_4_denominatore")->nullable();
            $table->integer("ob6")->nullable();
            $table->integer("ob7_1")->nullable();
            $table->integer("ob9_2")->nullable();

            $table->integer("ob10_at_1_num")->nullable();
            $table->integer("ob10_at_2_num")->nullable();
            $table->integer("ob10_ao_1_num")->nullable();
            $table->integer("ob10_ao_1_den")->nullable();
            $table->integer("ob10_ao_2_num")->nullable();
            $table->integer("ob10_ao_2_den")->nullable();

            $table->integer("ob10_ao_3_num")->nullable();
            $table->integer("ob10_ao_3_den")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flows_sdo');
    }
};
