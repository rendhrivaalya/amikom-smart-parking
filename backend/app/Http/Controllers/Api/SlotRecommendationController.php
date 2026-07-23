<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingSlot;
use App\Models\Vehicle;
use Illuminate\Http\Request;


class SlotRecommendationController extends Controller
{


    public function recommend(Request $request)
    {

        $request->validate([

            'vehicle_id'=>'required|exists:vehicles,id'

        ]);



        $user = auth()->user();



        $vehicle = Vehicle::where(
                'id',
                $request->vehicle_id
            )
            ->where(
                'user_id',
                $user->id
            )
            ->first();



        if(!$vehicle){

            return response()->json([

                'message'=>'Kendaraan tidak ditemukan'

            ],404);

        }



        $slot = ParkingSlot::where(
                'status',
                'available'
            )
            ->where(function($q) use($vehicle){

                $q->where(
                    'allowed_vehicle',
                    $vehicle->vehicle_type
                )
                ->orWhere(
                    'allowed_vehicle',
                    'Semua'
                );

            })
            ->where(function($q) use($user){

                $q->where(
                    'allowed_role',
                    $user->role
                )
                ->orWhere(
                    'allowed_role',
                    'Semua'
                );

            })
            ->first();



        if(!$slot){

            return response()->json([

                'message'=>'Tidak ada slot tersedia'

            ],404);

        }




        return response()->json([

            'success'=>true,

            'recommendation'=>[

                'slot_id'=>$slot->id,

                'slot_code'=>$slot->slot_code,

                'area_code'=>$slot->area_code,

                'area_name'=>$slot->area_name,

                'vehicle'=>$vehicle->vehicle_type

            ]

        ]);

    }

}