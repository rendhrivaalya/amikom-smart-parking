<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingSlot;
use Illuminate\Http\Request;

class ParkingSlotController extends Controller
{

    public function index(Request $request)
    {

        $query = ParkingSlot::query();


        /*
        |--------------------------------------------------------------------------
        | FILTER KENDARAAN
        |--------------------------------------------------------------------------
        */

        if($request->vehicle){

            $query->where(function($q) use ($request){

                $q->where(
                    'allowed_vehicle',
                    $request->vehicle
                )
                ->orWhere(
                    'allowed_vehicle',
                    'Semua'
                );

            });

        }



        $slots = $query
            ->orderBy('area_code')
            ->orderBy('slot_code')
            ->get();



        /*
        |--------------------------------------------------------------------------
        | GROUP BERDASARKAN AREA
        |--------------------------------------------------------------------------
        */


        $data = $slots
            ->groupBy('area_code')
            ->map(function($items){


                return [

                    'area_code'=>$items->first()->area_code,

                    'area_name'=>$items->first()->area_name,

                    'allowed_vehicle'=>$items->first()->allowed_vehicle,

                    'allowed_role'=>$items->first()->allowed_role,


                    'total_slot'=>$items->count(),


                    'available'=>$items
                        ->where('status','available')
                        ->count(),


                    'occupied'=>$items
                        ->where('status','occupied')
                        ->count(),


                    'slots'=>$items->map(function($slot){

                        return [

                            'id'=>$slot->id,

                            'slot_code'=>$slot->slot_code,

                            'status'=>$slot->status

                        ];

                    })

                ];

            })
            ->values();



        return response()->json([

            'success'=>true,

            'data'=>$data

        ]);

    }

}