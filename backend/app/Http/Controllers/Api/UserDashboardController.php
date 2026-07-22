<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParkingLog;

class UserDashboardController extends Controller
{

    public function index(Request $request)
    {

        $user = auth()->user();


        $parking = ParkingLog::with([
            'vehicle',
            'parkingSlot'
        ])
        ->where('user_id',$user->id)
        ->where('status','parking')
        ->first();



        $history = ParkingLog::with([
            'vehicle',
            'parkingSlot'
        ])
        ->where('user_id',$user->id)
        ->latest()
        ->limit(5)
        ->get();



        return response()->json([

            'success'=>true,

            'data'=>[

                'nama'=>$user->name,

                'nim'=>$user->nim,


                'kendaraan'=>[

                    'total'=>$user->vehicles()->count(),

                    'data'=>$user->vehicles()
                    ->select(
                        'plate_number',
                        'vehicle_type',
                        'brand'
                    )
                    ->get()

                ],


                'status_parkir'=> $parking ? [

                    'sedang_parkir'=>true,

                    'area'=>$parking->parkingSlot->area_name,

                    'slot'=>$parking->parkingSlot->slot_code,

                    'check_in'=>$parking->check_in

                ] : [

                    'sedang_parkir'=>false

                ],


                'riwayat_terakhir'=>$history->map(function($log){

                    return [

                        'vehicle'=>$log->vehicle->plate_number,

                        'area'=>$log->parkingSlot->area_name,

                        'slot'=>$log->parkingSlot->slot_code,

                        'check_in'=>$log->check_in,

                        'check_out'=>$log->check_out,

                        'status'=>$log->status

                    ];

                })


            ]

        ]);

    }

}