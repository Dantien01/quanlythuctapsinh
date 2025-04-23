<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Mentor',
            'email' => 'mentor@example.com',
            'password' => Hash::make('password'),
            'role' => 'mentor',
        ]);

        User::create([
            'name' => 'Intern',
            'email' => 'intern@example.com',
            'password' => Hash::make('password'),
            'role' => 'intern',
        ]);
    }
}