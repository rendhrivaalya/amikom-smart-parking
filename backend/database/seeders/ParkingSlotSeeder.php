<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingSlot;

class ParkingSlotSeeder extends Seeder
{
    public function run(): void
    {

        $areas = [

            [
                'code'=>'DA',
                'name'=>'Dome Atas',
                'vehicle'=>'Motor',
                'role'=>'Semua',
                'total'=>30
            ],

            [
                'code'=>'DB',
                'name'=>'Dome Bawah',
                'vehicle'=>'Motor',
                'role'=>'Dosen, Staff',
                'total'=>20
            ],

            [
                'code'=>'G7A1',
                'name'=>'Gedung 7 Area 1',
                'vehicle'=>'Semua',
                'role'=>'Semua',
                'total'=>50
            ],

            [
                'code'=>'G7A2',
                'name'=>'Gedung 7 Area 2',
                'vehicle'=>'Motor',
                'role'=>'Semua',
                'total'=>30
            ],

            [
                'code'=>'UG6',
                'name'=>'Utara Gedung 6',
                'vehicle'=>'Motor',
                'role'=>'Semua',
                'total'=>20
            ],

            [
                'code'=>'BG6A1',
                'name'=>'Basement G6 Area 1',
                'vehicle'=>'Motor',
                'role'=>'Semua',
                'total'=>15
            ],

            [
                'code'=>'BG6A2',
                'name'=>'Basement G6 Area 2',
                'vehicle'=>'Motor',
                'role'=>'Semua',
                'total'=>15
            ],

            [
                'code'=>'G1',
                'name'=>'Gedung 1',
                'vehicle'=>'Mobil',
                'role'=>'Semua',
                'total'=>10
            ],

            [
                'code'=>'G2',
                'name'=>'Gedung 2',
                'vehicle'=>'Mobil',
                'role'=>'Semua',
                'total'=>10
            ],

        ];


        foreach($areas as $area){

            for($i=1; $i <= $area['total']; $i++){

                ParkingSlot::create([

                    'slot_code'=> $area['code'].'-'.str_pad($i,3,'0',STR_PAD_LEFT),

                    'area_code'=>$area['code'],

                    'area_name'=>$area['name'],

                    'allowed_vehicle'=>$area['vehicle'],

                    'allowed_role'=>$area['role'],

                ]);

            }

        }

    }
}