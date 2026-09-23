<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat atau update akun admin utama Anda
        User::updateOrCreate(
            ['email' => 'nursyaidr@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // (Opsional) Akun test bawaan laravel
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );
    }
}
