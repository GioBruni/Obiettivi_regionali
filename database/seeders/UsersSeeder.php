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

        $user = User::create([
            'name' => 'Amministratore',
            'email' => 'sebastiano.ortisi.ext@asp.sr.it',
            'password' => bcrypt('ciao'),
        ]);

        $user = User::create([
            'name' => 'utente',
            'email' => 'utente@hotmail.it',
            'password' => bcrypt('ciao'),
        ]);
    
        // Assegna il ruolo di "uploader" e "user manager"
        $user->assignRole(['uploader', 'user manager', 'controller']);   
    }
}
