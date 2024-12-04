<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermessiERuoliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea i permessi
        Permission::create(['name' => 'upload files']);
        Permission::create(['name' => 'create users']);

        // Crea i ruoli e assegna i permessi
        $uploader = Role::create(['name' => 'uploader']);
        $uploader->givePermissionTo('upload files');

        $userManager = Role::create(['name' => 'user_manager']);
        $userManager->givePermissionTo('create users');
    }
}
