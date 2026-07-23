<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ParkingSlot;
use App\Models\ParkingLog;




class ParkingMonitorController extends Controller
{

    public function index(Request $request)
    {


        $areas = ParkingSlot::select(
                'area_code',
                'area_name'
            )
            ->groupBy(
                'area_code',
                'area_name'
            )
            ->get();



       $data = $areas->map(function($area) use ($request){


            $query = ParkingSlot::where(
    'area_code',
    $area->area_code
    );

    if ($request->filled('status')) {

    $query->where(
        'status',
        $request->status
    );

}
    




$slots = $query->get();



            return [

            'occupancy_percentage' =>

    round(

        (
            $slots
            ->where(
                'status',
                'occupied'
            )
            ->count()

            /

            $slots->count()

        ) * 100,

        2

    ),

                'area_code'=>$area->area_code,

                'area_name'=>$area->area_name,


                'total_slot'=>$slots->count(),



                'slot_tersedia'=>
                    $slots
                    ->where(
                        'status',
                        'available'
                    )
                    ->count(),



                'slot_terisi'=>
                    $slots
                    ->where(
                        'status',
                        'occupied'
                    )
                    ->count(),



                'slots'=>$slots->map(function($slot) use ($area,$slots){

                    return [

    'area_code' => $area->area_code,

    'area_name' => $area->area_name,

    'total_slot' => $slots->count(),

    'slot_tersedia' => $slots
        ->where('status','available')
        ->count(),

    'slot_terisi' => $slots
        ->where('status','occupied')
        ->count(),

    'occupancy_percentage' =>

        $slots->count() > 0

        ? round(

            ($slots->where('status','occupied')->count() / $slots->count()) * 100,

            2

        )

        : 0,

    'slots'=>$slots->map(function($slot){

    return [

        'id'=>$slot->id,

        'slot_code'=>$slot->slot_code,

        'status'=>$slot->status,

        'allowed_vehicle'=>$slot->allowed_vehicle,

        'allowed_role'=>$slot->allowed_role

    ];

})

];

                })

            ];

        });



        return response()->json([

            'success'=>true,

            'data'=>$data

        ]);

    }

    public function detail(Request $request)
{
    $parking = ParkingLog::with([
        'user',
        'guest',
        'vehicle',
        'parkingSlot'
    ])
    ->where('status', 'parking');

    /*
    |--------------------------------------------------------------------------
    | Filter Area
    |--------------------------------------------------------------------------
    */

    if ($request->filled('area')) {

        $parking->whereHas('parkingSlot', function ($q) use ($request) {

            $q->where('area_code', $request->area);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Filter User Role
    |--------------------------------------------------------------------------
    */

    if ($request->filled('role')) {

        $parking->whereHas('user', function ($q) use ($request) {

            $q->where('role', $request->role);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Filter Guest/User
    |--------------------------------------------------------------------------
    */

    if ($request->filled('type')) {

        if ($request->type == 'Guest') {

            $parking->whereNotNull('guest_id');

        } elseif ($request->type == 'User') {

            $parking->whereNull('guest_id');

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    if ($request->filled('keyword')) {

        $keyword = $request->keyword;

        $parking->where(function ($query) use ($keyword) {

            $query->whereHas('user', function ($q) use ($keyword) {

                $q->where('name', 'like', "%{$keyword}%");

            })

            ->orWhereHas('guest', function ($q) use ($keyword) {

                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('plate_number', 'like', "%{$keyword}%");

            })

            ->orWhereHas('vehicle', function ($q) use ($keyword) {

                $q->where('plate_number', 'like', "%{$keyword}%")
                  ->orWhere('brand', 'like', "%{$keyword}%")
                  ->orWhere('vehicle_model', 'like', "%{$keyword}%");

            });

        });

    }

    $parking = $parking
        ->latest()
        ->get();

    return response()->json([

        'success' => true,

        'total' => $parking->count(),

        'data' => $parking->map(function ($item) {

            return [

                'parking_id' => $item->id,

                'type' => $item->guest_id ? 'Guest' : 'User',

                'name' => $item->guest
                    ? $item->guest->name
                    : $item->user?->name,

                'role' => $item->guest
                    ? 'Guest'
                    : $item->user?->role,

                'plate_number' => $item->guest
                    ? $item->guest->plate_number
                    : $item->vehicle?->plate_number,

                'vehicle' => $item->guest
                    ? trim($item->guest->brand . ' ' . $item->guest->vehicle_model)
                    : trim($item->vehicle?->brand . ' ' . $item->vehicle?->vehicle_model),

                'slot' => $item->parkingSlot?->slot_code,

                'area' => $item->parkingSlot?->area_name,

                'check_in' => $item->check_in,

                'duration' => now()->diffForHumans(
                    $item->check_in,
                    true
                )

            ];

        })

    ]);

}

public function show($id)
{
    $parking = \App\Models\ParkingLog::with([
        'user',
        'guest',
        'vehicle',
        'parkingSlot'
    ])->find($id);

    if (!$parking) {

        return response()->json([
            'success' => false,
            'message' => 'Data parkir tidak ditemukan'
        ], 404);

    }

    return response()->json([

        'success' => true,

        'message' => 'Detail parkir berhasil diambil',

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

            'status' => $parking->status
        ]

    ]);
}

public function statistic()
{

    $areas = ParkingSlot::select(
        'area_code',
        'area_name'
    )
    ->groupBy(
        'area_code',
        'area_name'
    )
    ->get();

    return response()->json([

        'success'=>true,

        'data'=>$areas->map(function($area){

            $slots = ParkingSlot::where(
                'area_code',
                $area->area_code
            )->get();

            return [

                'area_code'=>$area->area_code,

                'area_name'=>$area->area_name,

                'total'=>$slots->count(),

                'available'=>$slots
                    ->where('status','available')
                    ->count(),

                'occupied'=>$slots
                    ->where('status','occupied')
                    ->count()

            ];

        })

    ]);

}

}