<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Amministratore',
            'email' => 'giovanni.bruni.ext@asp.sr.it',
            'password' => bcrypt('123QWEasd!"£'),
        ]);
    
        // Assegna il ruolo di "uploader" e "user manager"
        $user->assignRole(['uploader', 'user manager', 'controller']);   
    }
}
