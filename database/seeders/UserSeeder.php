<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        \App\Models\User::create([
            'name' => 'Admin Darsana',
            'email' => 'admin@darsana.com',
            'password' => bcrypt('password123'),
        ]);
    }
}
