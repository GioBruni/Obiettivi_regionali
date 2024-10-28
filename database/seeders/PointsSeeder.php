<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class PointsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("points")->insert([
            'target_number' => 1,
            'target' => "Liste di attesa",
            'sub_target' => null,
            'asp' => 10,
            'ao' => 10
        ]);

        DB::table("points")->insert([
            'target_number' => 2.1,
            'target' => "Esiti",
            'sub_target' => "Frattura femore",
            'asp' => 4,
            'ao' => 4
        ]);

        DB::table("points")->insert([
            'target_number' => 2.2,
            'target' => "Esiti",
            'sub_target' => "Parti cesarei",
            'asp' => 4,
            'ao' => 4
        ]);

        DB::table("points")->insert([
            'target_number' => 2.3,
            'target' => "Esiti",
            'sub_target' => "Temp PTCA",
            'asp' => 4,
            'ao' => 4
        ]);

        DB::table("points")->insert([
            'target_number' => 2.4,
            'target' => "Esiti",
            'sub_target' => "Colecistectomia",
            'asp' => 4,
            'ao' => 4
        ]);

        DB::table("points")->insert([
            'target_number' => 3,
            'target' => "Standard sicurezza punti nascita",
            'sub_target' => null,
            'asp' => 5,
            'ao' => 8
        ]);

        DB::table("points")->insert([
            'target_number' => 4,
            'target' => "PS sovraffollamento",
            'sub_target' => null,
            'asp' => 4,
            'ao' => 8
        ]);

        DB::table("points")->insert([
            'target_number' => 5.1,
            'target' => "Screening",
            'sub_target' => "tumore cervice uterina",
            'asp' => 3,
            'ao' => 1
        ]);

        DB::table("points")->insert([
            'target_number' => 5.2,
            'target' => "Screening",
            'sub_target' => "tumore mammella",
            'asp' => 3,
            'ao' => 1
        ]);

        DB::table("points")->insert([
            'target_number' => 5.3,
            'target' => "Screening",
            'sub_target' => "tumore colon retto",
            'asp' => 3,
            'ao' => 1
        ]);

        DB::table("points")->insert([
            'target_number' => 6,
            'target' => "Donazioni organi",
            'sub_target' => null,
            'asp' => 5,
            'ao' => 9
        ]);

        DB::table("points")->insert([
            'target_number' => 7,
            'target' => "FSE",
            'sub_target' => null,
            'asp' => 8,
            'ao' => 8
        ]);

        DB::table("points")->insert([
            'target_number' => 8,
            'target' => "Percorso certificabilità",
            'sub_target' => null,
            'asp' => 3,
            'ao' => 3
        ]);

        DB::table("points")->insert([
            'target_number' => 9,
            'target' => "Approvv farmaci e PCT",
            'sub_target' => null,
            'asp' => 5,
            'ao' => 5
        ]);

        DB::table("points")->insert([
            'target_number' => 10.1,
            'target' => "Area performance",
            'sub_target' => "Ospedaliera",
            'asp' => 8,
            'ao' => 18
        ]);

        DB::table("points")->insert([
            'target_number' => 10.2,
            'target' => "Area performance",
            'sub_target' => "Territoriale",
            'asp' => 12,
            'ao' => 12
        ]);

        DB::table("points")->insert([
            'target_number' => 10.3,
            'target' => "Area performance",
            'sub_target' => "Prevenzione",
            'asp' => 15,
            'ao' => 0
        ]);

    }
}
