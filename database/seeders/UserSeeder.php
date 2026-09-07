<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::create([
            'name' => 'Administrator',
            'email' => 'admin@cbt.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Budi Guru',
            'email' => 'guru@cbt.test',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Siti Siswa',
            'email' => 'siswa@cbt.test',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'nis' => '2024001',
            'email_verified_at' => now(),
        ]);
    }
}
