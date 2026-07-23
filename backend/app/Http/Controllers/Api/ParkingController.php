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

    public function adminHistory(Request $request)
{

    $query = ParkingLog::with([
        'user',
        'vehicle',
        'parkingSlot',
        'guest'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Filter Area
    |--------------------------------------------------------------------------
    */

    if ($request->filled('area')) {

        $query->whereHas('parkingSlot', function ($q) use ($request) {

            $q->where('area_code', $request->area);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Filter Status
    |--------------------------------------------------------------------------
    */

    if ($request->filled('status')) {

        $query->where('status', $request->status);

    }

    /*
    |--------------------------------------------------------------------------
    | Filter Role
    |--------------------------------------------------------------------------
    */

    if ($request->filled('role')) {

        $query->whereHas('user', function ($q) use ($request) {

            $q->where('role', $request->role);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Filter Guest / User
    |--------------------------------------------------------------------------
    */

    if ($request->filled('type')) {

        if ($request->type == 'Guest') {

            $query->whereNotNull('guest_id');

        }

        if ($request->type == 'User') {

            $query->whereNull('guest_id');

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Filter Tanggal
    |--------------------------------------------------------------------------
    */

    if ($request->filled('date')) {

        $query->whereDate(
            'check_in',
            $request->date
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    if ($request->filled('keyword')) {

        $keyword = $request->keyword;

        $query->where(function ($q) use ($keyword) {

            $q->whereHas('user', function ($u) use ($keyword) {

                $u->where('name', 'like', "%{$keyword}%");

            })

            ->orWhereHas('guest', function ($g) use ($keyword) {

                $g->where('name', 'like', "%{$keyword}%")
                  ->orWhere('plate_number', 'like', "%{$keyword}%");

            })

            ->orWhereHas('vehicle', function ($v) use ($keyword) {

                $v->where('plate_number', 'like', "%{$keyword}%")
                  ->orWhere('brand', 'like', "%{$keyword}%")
                  ->orWhere('vehicle_model', 'like', "%{$keyword}%");

            });

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */

    $sort = strtolower($request->get('sort', 'desc'));

    $query->orderBy(
        'check_in',
        $sort == 'asc' ? 'asc' : 'desc'
    );

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $perPage = $request->get('per_page', 10);

    $logs = $query->paginate($perPage);

    return response()->json([

        'success' => true,

        'message' => 'History parkir berhasil diambil',

        'pagination' => [

            'current_page' => $logs->currentPage(),

            'last_page' => $logs->lastPage(),

            'per_page' => $logs->perPage(),

            'total_data' => $logs->total()

        ],

        'data' => collect($logs->items())->map(function ($log) {

            return [

                'id' => $log->id,

                'type' => $log->guest ? 'Guest' : 'User',

                'name' => $log->guest
                    ? $log->guest->name
                    : $log->user?->name,

                'role' => $log->guest
                    ? 'guest'
                    : $log->user?->role,

                'plate_number' => $log->guest
                    ? $log->guest->plate_number
                    : $log->vehicle?->plate_number,

                'vehicle' => $log->guest
                    ? trim($log->guest->brand . ' ' . $log->guest->vehicle_model)
                    : trim($log->vehicle?->brand . ' ' . $log->vehicle?->vehicle_model),

                'vehicle_category' => $log->vehicle_category,

                'area' => $log->parkingSlot?->area_name,

                'area_code' => $log->parkingSlot?->area_code,

                'slot' => $log->parkingSlot?->slot_code,

                'check_in' => optional($log->check_in)->format('Y-m-d H:i:s'),

                'check_out' => optional($log->check_out)->format('Y-m-d H:i:s'),

                'duration' => $log->check_out
                    ? $log->check_in->diffForHumans($log->check_out, true)
                    : $log->check_in->diffForHumans(now(), true),

                'status' => $log->status

            ];

        })

    ]);

}

public function historyDetail($id)
{

    $parking = ParkingLog::with([
        'user',
        'guest',
        'vehicle',
        'parkingSlot'
    ])->find($id);

    if (!$parking) {

        return response()->json([
            'success' => false,
            'message' => 'Riwayat parkir tidak ditemukan'
        ], 404);

    }

    return response()->json([

        'success' => true,

        'message' => 'Detail riwayat parkir berhasil diambil',

        'data' => [

            'id' => $parking->id,

            'type' => $parking->guest ? 'Guest' : 'User',

            'name' => $parking->guest
                ? $parking->guest->name
                : $parking->user?->name,

            'role' => $parking->guest
                ? 'guest'
                : $parking->user?->role,

            'plate_number' => $parking->guest
                ? $parking->guest->plate_number
                : $parking->vehicle?->plate_number,

            'brand' => $parking->guest
                ? $parking->guest->brand
                : $parking->vehicle?->brand,

            'vehicle_model' => $parking->guest
                ? $parking->guest->vehicle_model
                : $parking->vehicle?->vehicle_model,

            'vehicle_category' => $parking->vehicle_category,

            'area' => $parking->parkingSlot?->area_name,

            'area_code' => $parking->parkingSlot?->area_code,

            'slot' => $parking->parkingSlot?->slot_code,

            'check_in' => optional($parking->check_in)->format('Y-m-d H:i:s'),

            'check_out' => optional($parking->check_out)->format('Y-m-d H:i:s'),

            'duration' => $parking->check_out
                ? $parking->check_in->diffForHumans($parking->check_out, true)
                : $parking->check_in->diffForHumans(now(), true),

            'status' => $parking->status,

            'checked_by' => $parking->checked_by

        ]

    ]);

}


}


