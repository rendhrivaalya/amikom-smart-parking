<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingLog;
use App\Models\ParkingSlot;
use App\Models\ParkingToken;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScannerController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | PETUGAS SCAN CHECK IN
    |--------------------------------------------------------------------------
    */

    public function checkIn(Request $request)
    {

        $request->validate([

            'token'=>'required|string',

            'parking_slot_id'=>'required|exists:parking_slots,id'

        ]);


        $petugas = auth()->user();



        // cek token masuk

        $parkingToken = ParkingToken::where(
                'token',
                $request->token
            )
            ->where('type','IN')
            ->where('is_used',false)
            ->where(
                'expired_at',
                '>',
                now()
            )
            ->first();



        if(!$parkingToken){

            return response()->json([

                'message'=>'Token masuk tidak valid'

            ],400);

        }



        // cek slot

        $slot = ParkingSlot::findOrFail(
            $request->parking_slot_id
        );


        if($slot->status == 'occupied'){

            return response()->json([

                'message'=>'Slot sudah terisi'

            ],400);

        }



        // kendaraan

        $vehicle = Vehicle::findOrFail(
            $parkingToken->vehicle_id
        );



        /*
        |--------------------------------------------------------------------------
        | VALIDASI AREA
        |--------------------------------------------------------------------------
        */


        if(
            $slot->allowed_vehicle != 'Semua'
            &&
            $slot->allowed_vehicle != $vehicle->vehicle_type
        ){

            return response()->json([

                'message'=>'Kendaraan tidak sesuai area parkir'

            ],403);

        }



        if(
            $slot->allowed_role != 'Semua'
            &&
            $slot->allowed_role != $parkingToken->user->role
        ){

            return response()->json([

                'message'=>'Role tidak diperbolehkan masuk area ini'

            ],403);

        }




        DB::beginTransaction();


        try{


            $parking = ParkingLog::create([

                'user_id'=>$parkingToken->user_id,

                'guest_id'=>null,

                'vehicle_id'=>$vehicle->id,

                'vehicle_category'=>$vehicle->vehicle_category,

                'parking_slot_id'=>$slot->id,

                'parking_token_id'=>$parkingToken->id,

                'checked_by'=>$petugas->id,

                'check_in'=>now(),

                'status'=>'parking'

            ]);



            $slot->update([

                'status'=>'occupied'

            ]);



            $parkingToken->update([

                'is_used'=>true,

                'used_at'=>now()

            ]);



            DB::commit();



            return response()->json([

                'success'=>true,

                'message'=>'Check in berhasil',

                'data'=>$parking

            ],201);



        }catch(\Exception $e){


            DB::rollBack();


            return response()->json([

                'message'=>$e->getMessage()

            ],500);


        }


    }




    /*
    |--------------------------------------------------------------------------
    | PETUGAS SCAN CHECK OUT
    |--------------------------------------------------------------------------
    */

    public function checkOut(Request $request)
    {


        $request->validate([

            'token'=>'required|string'

        ]);



        $petugas = auth()->user();



        $parkingToken = ParkingToken::where(
                'token',
                $request->token
            )
            ->where('type','OUT')
            ->where('is_used',false)
            ->where(
                'expired_at',
                '>',
                now()
            )
            ->first();



        if(!$parkingToken){

            return response()->json([

                'message'=>'Token keluar tidak valid'

            ],400);

        }




        $parking = ParkingLog::where(
                'vehicle_id',
                $parkingToken->vehicle_id
            )
            ->whereNull('check_out')
            ->first();



        if(!$parking){

            return response()->json([

                'message'=>'Kendaraan tidak sedang parkir'

            ],404);

        }




        DB::beginTransaction();


        try{


            $parking->update([

                'check_out'=>now(),

                'status'=>'completed',

                'checked_by'=>$petugas->id

            ]);



            $slot = ParkingSlot::find(
                $parking->parking_slot_id
            );


            if($slot){

                $slot->update([

                    'status'=>'available'

                ]);

            }



            $parkingToken->update([

                'is_used'=>true,

                'used_at'=>now()

            ]);



            DB::commit();



            return response()->json([

                'success'=>true,

                'message'=>'Check out berhasil',

                'data'=>$parking

            ]);



        }catch(\Exception $e){


            DB::rollBack();


            return response()->json([

                'message'=>$e->getMessage()

            ],500);

        }


    }

}