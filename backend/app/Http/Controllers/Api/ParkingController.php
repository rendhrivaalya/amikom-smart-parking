<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingLog;
use App\Models\ParkingSlot;
use App\Models\ParkingToken;
use App\Models\Vehicle;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ParkingController extends Controller
{
    public function checkIn(Request $request)
{

    try{


        $request->validate([

            'token'=>'required|string',

            'parking_slot_id'=>'required|exists:parking_slots,id'

        ]);



        /*
        |--------------------------------------------------------------------------
        | CEK GUEST TOKEN
        |--------------------------------------------------------------------------
        */


        $guest = Guest::where(
                'qr_token',
                $request->token
            )
            ->where('is_used',false)
            ->where(
                'expired_at',
                '>',
                now()
            )
            ->first();



        if($guest){


            $slot = ParkingSlot::find(
                $request->parking_slot_id
            );



            if($slot->status=='occupied'){

                return response()->json([

                    'message'=>'Slot sudah terisi'

                ],400);

            }



            DB::beginTransaction();


            $parking = ParkingLog::create([


                'user_id'=>null,


                'guest_id'=>$guest->id,


                'vehicle_id'=>null,


                'parking_slot_id'=>$slot->id,


                'parking_token_id'=>null,


                'check_in'=>now(),


                'status'=>'parking'


            ]);



            $slot->update([

                'status'=>'occupied'

            ]);



            $guest->update([

                'is_used'=>true,

                'used_at'=>now()

            ]);



            DB::commit();



            return response()->json([

                'message'=>'Guest berhasil check in',

                'data'=>$parking

            ],201);


        }



        /*
        |--------------------------------------------------------------------------
        | CEK MAHASISWA
        |--------------------------------------------------------------------------
        */


        $user = auth('sanctum')->user();



        if(!$user){


            return response()->json([

                'message'=>'Mahasiswa harus login'

            ],401);


        }




        $parkingToken = ParkingToken::where(

                'token',

                $request->token

            )

            ->where(
                'type',
                'IN'
            )

            ->where(
                'is_used',
                false
            )

            ->where(
                'expired_at',
                '>',
                now()
            )

            ->first();



        if(!$parkingToken){


            return response()->json([

                'message'=>'Token parkir tidak valid'

            ],400);


        }




        if($parkingToken->user_id != $user->id){


            return response()->json([

                'message'=>'Token bukan milik user'

            ],403);


        }




        $vehicle = Vehicle::find(
            $parkingToken->vehicle_id
        );



        if(!$vehicle){


            return response()->json([

                'message'=>'Kendaraan tidak ditemukan'

            ],404);


        }



        $slot = ParkingSlot::find(
            $request->parking_slot_id
        );



        if($slot->status=='occupied'){


            return response()->json([

                'message'=>'Slot sudah terisi'

            ],400);


        }




        DB::beginTransaction();



        $parking = ParkingLog::create([


            'user_id'=>$user->id,


            'guest_id'=>null,


            'vehicle_id'=>$vehicle->id,


            'parking_slot_id'=>$slot->id,


            'parking_token_id'=>$parkingToken->id,


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

            'message'=>'Mahasiswa berhasil check in',

            'data'=>$parking

        ],201);



    }
    catch(\Exception $e){


        DB::rollBack();


        return response()->json([

            'message'=>$e->getMessage()

        ],500);


    }

}

    public function checkOut(Request $request)
{

    try {


        $request->validate([

            'token'=>'required|string'

        ]);



        /*
        |--------------------------------------------------------------------------
        | CHECK GUEST CHECKOUT
        |--------------------------------------------------------------------------
        */


        $guest = Guest::where(
                'qr_token',
                $request->token
            )
            ->first();



        if($guest){


            $parking = ParkingLog::where(
                    'guest_id',
                    $guest->id
                )
                ->whereNull('check_out')
                ->first();



            if(!$parking){

                return response()->json([

                    'message'=>'Guest tidak sedang parkir'

                ],404);

            }



            DB::beginTransaction();



            $parking->update([

                'check_out'=>now(),

                'status'=>'completed'

            ]);



            $slot = ParkingSlot::find(
                $parking->parking_slot_id
            );


            if($slot){

                $slot->update([

                    'status'=>'available'

                ]);

            }



            DB::commit();



            return response()->json([

                'message'=>'Guest berhasil check out',

                'data'=>$parking

            ],200);



        }




        /*
        |--------------------------------------------------------------------------
        | CHECK MAHASISWA
        |--------------------------------------------------------------------------
        */


        $user = auth('sanctum')->user();



        if(!$user){


            return response()->json([

                'message'=>'Mahasiswa harus login'

            ],401);


        }





        $parkingToken = ParkingToken::where(

                'token',

                $request->token

            )

            ->where(
                'type',
                'OUT'
            )

            ->where(
                'is_used',
                false
            )

            ->where(
                'expired_at',
                '>',
                now()
            )

            ->first();





        if(!$parkingToken){


            return response()->json([

                'message'=>'Token checkout tidak valid'

            ],400);


        }





        if($parkingToken->user_id != $user->id){


            return response()->json([

                'message'=>'Token bukan milik user'

            ],403);


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




        $parking->update([

            'check_out'=>now(),

            'status'=>'completed'

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

            'message'=>'Mahasiswa berhasil check out',

            'data'=>$parking

        ],200);



    }
    catch(\Exception $e){


        DB::rollBack();



        return response()->json([

            'message'=>$e->getMessage(),

            'line'=>$e->getLine()

        ],500);


    }

}
}
