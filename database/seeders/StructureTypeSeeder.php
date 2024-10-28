<?php

namespace Database\Seeders;

use App\Models\Generic;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StructureTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("structure_type")->insert([
            'code' => '0',
            'description' => "Azienda Ospedaliera",
            'column_points' => "ao"
        ]);
        DB::table("structure_type")->insert([
            'code' => '1',
            'description' => "Ospedale a gestione diretta presidio A.S.L.",
            'column_points' => "asp"
        ]);
        DB::table("structure_type")->insert([
            'code' => '2',
            'description' => "Azienda Ospedaliera-Universitaria e Policlinico",
            'column_points' => "ao"
        ]);
        DB::table("structure_type")->insert([
            'code' => '3',
            'description' => "Istituto di ricovero e cura a carattere scientifico",
            'column_points' => "ao"
        ]);
        DB::table("structure_type")->insert([
            'code' => '4',
            'description' => "Ospedale Classificato o Assimilato ai sensi dell'art. 1, ultimo comma, della legge 132/1968",
            'column_points' => "ao"
        ]);
        DB::table("structure_type")->insert([
            'code' => '5',
            'description' => "Casa di cura privata",
            'column_points' => "ao"
        ]);
        DB::table("structure_type")->insert([
            'code' => '8',
            'description' => "Istituto qualificato presidio della A.S.L.",
            'column_points' => "ao"
        ]);
        DB::table("structure_type")->insert([
            'code' => '9',
            'description' => "Ente di ricerca",
            'column_points' => "ao"
        ]);
    }
}
