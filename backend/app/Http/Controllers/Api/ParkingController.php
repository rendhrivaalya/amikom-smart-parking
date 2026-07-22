<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingLog;
use App\Models\ParkingSlot;
use App\Models\ParkingToken;
use App\Models\Vehicle;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ParkingController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | CHECK IN
    |--------------------------------------------------------------------------
    */

    public function checkIn(Request $request)
    {

        try {


            $request->validate([

                'token'=>'required|string',

                'parking_slot_id'=>'required|exists:parking_slots,id'

            ]);



            /*
            |--------------------------------------------------------------------------
            | CEK GUEST
            |--------------------------------------------------------------------------
            */


            $guest = Guest::where(
                    'qr_token',
                    $request->token
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



            if($guest){


                $slot = ParkingSlot::findOrFail(
                    $request->parking_slot_id
                );



                if($slot->status == 'occupied'){

                    return response()->json([
                        'message'=>'Slot sudah terisi'
                    ],400);

                }



                // Role guest hanya area umum
                if(
                    $slot->allowed_role != 'Semua'
                ){

                    return response()->json([
                        'message'=>'Area parkir hanya untuk dosen dan staff.'
                    ],403);

                }



                if(
                    $slot->allowed_vehicle != 'Semua'
                    &&
                    strtolower($slot->allowed_vehicle)
                    !=
                    strtolower($guest->vehicle_type)
                ){

                    return response()->json([
                        'message'=>'Area parkir tidak sesuai kendaraan.'
                    ],403);

                }



                DB::beginTransaction();



                $parking = ParkingLog::create([

                    'user_id'=>null,

                    'guest_id'=>$guest->id,

                    'vehicle_id'=>null,

                    'vehicle_category'=>$guest->vehicle_category,

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
            | CEK MAHASISWA / USER
            |--------------------------------------------------------------------------
            */


            $user = auth('sanctum')->user();



            if(!$user){

                return response()->json([

                    'message'=>'User harus login'

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




            $slot = ParkingSlot::findOrFail(
                $request->parking_slot_id
            );




            if($slot->status == 'occupied'){

                return response()->json([

                    'message'=>'Slot sudah terisi'

                ],400);

            }




            /*
            |--------------------------------------------------------------------------
            | VALIDASI JENIS KENDARAAN
            |--------------------------------------------------------------------------
            */


            if(
                $slot->allowed_vehicle != 'Semua'
                &&
                strtolower($slot->allowed_vehicle)
                !=
                strtolower($vehicle->vehicle_type)
            ){

                return response()->json([

                    'message'=>'Area parkir tidak sesuai kendaraan.'

                ],403);

            }




            /*
            |--------------------------------------------------------------------------
            | VALIDASI ROLE
            |--------------------------------------------------------------------------
            */


            if(
                $slot->allowed_role != 'Semua'
                &&
                strtolower($slot->allowed_role)
                !=
                strtolower($user->role)
            ){

                return response()->json([

                    'message'=>'Area parkir tidak diperbolehkan.'

                ],403);

            }




            DB::beginTransaction();



            $parking = ParkingLog::create([


                'user_id'=>$user->id,


                'guest_id'=>null,


                'vehicle_id'=>$vehicle->id,


                'vehicle_category'=>$vehicle->vehicle_category,


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

                'message'=>$e->getMessage(),

                'line'=>$e->getLine()

            ],500);


        }


    }

        /*
    |--------------------------------------------------------------------------
    | CHECK OUT
    |--------------------------------------------------------------------------
    */

    public function checkOut(Request $request)
    {

        try {


            $request->validate([

                'token'=>'required|string'

            ]);



            /*
            |--------------------------------------------------------------------------
            | CHECKOUT GUEST
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

                ]);

            }





            /*
            |--------------------------------------------------------------------------
            | CHECKOUT USER / MAHASISWA
            |--------------------------------------------------------------------------
            */


            $user = auth('sanctum')->user();



            if(!$user){

                return response()->json([

                    'message'=>'User harus login'

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




            if($parking->user_id != $user->id){

                return response()->json([

                    'message'=>'Data parkir bukan milik user'

                ],403);

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

            ]);



        }
        catch(\Exception $e){


            DB::rollBack();


            return response()->json([

                'message'=>$e->getMessage(),

                'line'=>$e->getLine()

            ],500);

        }


    }




    /*
    |--------------------------------------------------------------------------
    | HISTORY MAHASISWA
    |--------------------------------------------------------------------------
    */

    public function history()
    {

        $logs = ParkingLog::with([

            'vehicle',

            'parkingSlot'

        ])
        ->where(
            'user_id',
            auth()->id()
        )
        ->latest()
        ->get();



        return response()->json([

            'success'=>true,

            'data'=>$logs->map(function($log){


                return [

                    'id'=>$log->id,

                    'vehicle'=>
                        $log->vehicle
                        ?
                        $log->vehicle->plate_number
                        :
                        'Guest',


                    'vehicle_category'=>$log->vehicle_category,


                    'area'=>
                        $log->parkingSlot
                        ?
                        $log->parkingSlot->area_name
                        :
                        null,


                    'slot'=>
                        $log->parkingSlot
                        ?
                        $log->parkingSlot->slot_code
                        :
                        null,


                    'check_in' => optional($log->check_in)->format('Y-m-d H:i:s'),
'check_out' => optional($log->check_out)->format('Y-m-d H:i:s'),


                    'status'=>$log->status

                ];


            })

        ]);

    }





    /*
    |--------------------------------------------------------------------------
    | HISTORY ADMIN
    |--------------------------------------------------------------------------
    */

    public function adminHistory()
    {


        $logs = ParkingLog::with([

            'user',

            'vehicle',

            'parkingSlot',

            'guest'

        ])
        ->latest()
        ->get();




        return response()->json([


            'success'=>true,


            'data'=>$logs->map(function($log){


                return [


                    'user'=>
                        $log->user
                        ?
                        $log->user->name
                        :
                        'Guest',



                    'role'=>
                        $log->user
                        ?
                        $log->user->role
                        :
                        'guest',



                    'vehicle'=>
                        $log->vehicle
                        ?
                        $log->vehicle->plate_number
                        :
                        '-',



                    'category'=>$log->vehicle_category,



                    'area'=>
                        $log->parkingSlot
                        ?
                        $log->parkingSlot->area_name
                        :
                        null,



                    'slot'=>
                        $log->parkingSlot
                        ?
                        $log->parkingSlot->slot_code
                        :
                        null,


'check_in' => optional($log->check_in)->format('Y-m-d H:i:s'),
'check_out' => optional($log->check_out)->format('Y-m-d H:i:s'),


                    'status'=>$log->status


                ];


            })


        ]);

    }


}


