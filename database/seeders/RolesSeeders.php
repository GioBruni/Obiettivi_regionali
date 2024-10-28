<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeders extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'uploader',
            'guard_name' => "web"
        ]);
        Role::create([
            'name' => 'user manager',
            'guard_name' => "web"
        ]);
        Role::create([
            'name' => 'controller',
            'guard_name' => "web"
        ]);
    }
}
