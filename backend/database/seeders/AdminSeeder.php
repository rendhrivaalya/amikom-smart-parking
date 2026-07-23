<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class AdminSeeder extends Seeder
{

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
        | PETUGAS
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



        /*
        |--------------------------------------------------------------------------
        | DOSEN
        |--------------------------------------------------------------------------
        */


        for ($i = 1; $i <= 5; $i++) {


            User::updateOrCreate(

                [
                    'email' => "dosen{$i}@amikom.ac.id"
                ],

                [
                    'name' => "Dosen {$i}",
                    'nim' => null,
                    'password' => Hash::make('dosen123'),
                    'role' => 'dosen',
                ]

            );


        }



        /*
        |--------------------------------------------------------------------------
        | STAFF
        |--------------------------------------------------------------------------
        */


        for ($i = 1; $i <= 5; $i++) {


            User::updateOrCreate(

                [
                    'email' => "staff{$i}@amikom.ac.id"
                ],

                [
                    'name' => "Staff {$i}",
                    'nim' => null,
                    'password' => Hash::make('staff123'),
                    'role' => 'staff',
                ]

            );


        }


    }

}