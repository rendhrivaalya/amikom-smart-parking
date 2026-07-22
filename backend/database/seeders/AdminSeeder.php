<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        User::updateOrCreate(
            [
                'email' => 'admin@amikom.ac.id'
            ],
            [
                'name' => 'Administrator',
                'nim' => null,
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PETUGAS (15 AKUN)
        |--------------------------------------------------------------------------
        */

        for ($i = 1; $i <= 15; $i++) {

            User::updateOrCreate(
                [
                    'email' => "petugas{$i}@amikom.ac.id"
                ],
                [
                    'name' => "Petugas {$i}",
                    'nim' => null,
                    'password' => Hash::make('petugas123'),
                    'role' => 'petugas',
                ]
            );

        }
    }
}