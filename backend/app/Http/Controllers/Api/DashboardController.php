<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingLog;
use App\Models\ParkingSlot;
use App\Models\User;
use App\Models\Guest;


class DashboardController extends Controller
{

    public function index()
    {

        return response()->json([

        'total_slot' =>
    ParkingSlot::count(),

'total_dosen_hari_ini' =>

    ParkingLog::whereDate(
        'check_in',
        today()
    )
    ->whereHas(
        'user',
        fn($q)=>
        $q->where(
            'role',
            'dosen'
        )
    )
    ->count(),

'total_staff_hari_ini' =>

    ParkingLog::whereDate(
        'check_in',
        today()
    )
    ->whereHas(
        'user',
        fn($q)=>
        $q->where(
            'role',
            'staff'
        )
    )
    ->count(),

'guest_menunggu' =>

    Guest::where(
        'status',
        'waiting'
    )->count(),

'guest_sedang_parkir' =>

    Guest::where(
        'status',
        'parking'
    )->count(),

'total_mahasiswa' =>

    User::where(
        'role',
        'mahasiswa'
    )->count(),

'total_dosen' =>

    User::where(
        'role',
        'dosen'
    )->count(),

'total_staff' =>

    User::where(
        'role',
        'staff'
    )->count(),


            /*
            |--------------------------------------------------------------------------
            | Kendaraan sedang parkir
            |--------------------------------------------------------------------------
            */

            'kendaraan_parkir_sekarang' =>
                ParkingLog::where(
                    'status',
                    'parking'
                )
                ->count(),



            /*
            |--------------------------------------------------------------------------
            | Slot
            |--------------------------------------------------------------------------
            */


            'slot_kosong' =>
                ParkingSlot::where(
                    'status',
                    'available'
                )
                ->count(),



            'slot_terisi' =>
                ParkingSlot::where(
                    'status',
                    'occupied'
                )
                ->count(),



            /*
            |--------------------------------------------------------------------------
            | Statistik hari ini
            |--------------------------------------------------------------------------
            */


            'total_mahasiswa_hari_ini' =>

                ParkingLog::whereDate(
                    'check_in',
                    today()
                )
                ->whereHas(
                    'user',
                    fn($q)=>
                    $q->where(
                        'role',
                        'mahasiswa'
                    )
                )
                ->count(),




            'total_guest_hari_ini' =>

                ParkingLog::whereDate(
                    'check_in',
                    today()
                )
                ->whereNotNull(
                    'guest_id'
                )
                ->count(),




            /*
            |--------------------------------------------------------------------------
            | Kendaraan yang masih parkir
            |--------------------------------------------------------------------------
            */


            'motor' =>

                ParkingLog::where(
                    'vehicle_category',
                    'motor'
                )
                ->where(
                    'status',
                    'parking'
                )
                ->count(),




            'mobil' =>

                ParkingLog::where(
                    'vehicle_category',
                    'mobil'
                )
                ->where(
                    'status',
                    'parking'
                )
                ->count(),



        ]);

    }

    public function chart()
{

    return response()->json([

        'success'=>true,

        'data'=>[

            /*
            |--------------------------------------------------------------
            | User
            |--------------------------------------------------------------
            */

            'user'=>[

                'mahasiswa'=>User::where(
                    'role',
                    'mahasiswa'
                )->count(),

                'dosen'=>User::where(
                    'role',
                    'dosen'
                )->count(),

                'staff'=>User::where(
                    'role',
                    'staff'
                )->count(),

            ],

            /*
            |--------------------------------------------------------------
            | Guest
            |--------------------------------------------------------------
            */

            'guest'=>[

                'waiting'=>Guest::where(
                    'status',
                    'waiting'
                )->count(),

                'approved'=>Guest::where(
                    'status',
                    'approved'
                )->count(),

                'parking'=>Guest::where(
                    'status',
                    'parking'
                )->count(),

                'finished'=>Guest::where(
                    'status',
                    'finished'
                )->count(),

                'rejected'=>Guest::where(
                    'status',
                    'rejected'
                )->count(),

            ],

            /*
            |--------------------------------------------------------------
            | Slot
            |--------------------------------------------------------------
            */

            'slot'=>[

                'available'=>ParkingSlot::where(
                    'status',
                    'available'
                )->count(),

                'occupied'=>ParkingSlot::where(
                    'status',
                    'occupied'
                )->count()

            ],

            /*
            |--------------------------------------------------------------
            | Kendaraan sedang parkir
            |--------------------------------------------------------------
            */

            'parking'=>[

                'motor'=>ParkingLog::where(
                    'status',
                    'parking'
                )
                ->where(
                    'vehicle_category',
                    'Motor'
                )
                ->count(),

                'mobil'=>ParkingLog::where(
                    'status',
                    'parking'
                )
                ->where(
                    'vehicle_category',
                    'Mobil'
                )
                ->count()

            ]

        ]

    ]);


}

public function realtime()
{
    return response()->json([

        'success' => true,

        'last_update' => now()->format('Y-m-d H:i:s'),

        'data' => [

            'parking_now' => ParkingLog::where('status','parking')->count(),

            'available_slot' => ParkingSlot::where('status','available')->count(),

            'occupied_slot' => ParkingSlot::where('status','occupied')->count(),

            'waiting_guest' => Guest::where('status','waiting')->count()

        ]

    ]);
}


}