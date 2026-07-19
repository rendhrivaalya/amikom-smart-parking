<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingLog;
use App\Models\ParkingSlot;
use App\Models\ParkingToken;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ParkingController extends Controller
{
    public function checkIn(Request $request)
    {
        try {

            // Pastikan user login
            if (!auth()->check()) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Validasi input
            $validator = Validator::make($request->all(), [
    'token' => 'required|string',
    'parking_slot_id' => 'required|exists:parking_slots,id',
]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }


            $parkingToken = ParkingToken::where('token', $request->token)
    ->where('type', 'IN')
    ->where('is_used', false)
    ->where('expired_at', '>', now())
    ->first();

if (!$parkingToken) {
    return response()->json([
        'message' => 'Token tidak valid atau sudah kedaluwarsa'
    ], 400);
}



$vehicle = Vehicle::find($parkingToken->vehicle_id);

if (!$vehicle) {
    return response()->json([
        'message' => 'Kendaraan tidak ditemukan'
    ],404);
}


if ($parkingToken->user_id != auth()->id()) {
    return response()->json([
        'message' => 'Token bukan milik user ini'
    ],403);
}


            // Cek kendaraan masih parkir
            $activeParking = ParkingLog::where('vehicle_id', $vehicle->id)
                ->whereNull('check_out')
                ->first();

            if ($activeParking) {
                return response()->json([
                    'message' => 'Kendaraan masih berada di area parkir'
                ], 400);
            }


            $slot = ParkingSlot::find($request->parking_slot_id);


            if ($slot->status === 'occupied') {
                return response()->json([
                    'message' => 'Slot parkir sudah terisi'
                ], 400);
            }


            DB::beginTransaction();


            $parking = ParkingLog::create([
    'vehicle_id' => $vehicle->id,
    'user_id' => $parkingToken->user_id,
                'parking_slot_id' => $slot->id,
                'check_in' => Carbon::now(),
                'check_out' => null,
                'status' => 'parking',
            ]);


            $slot->update([
                'status' => 'occupied'
            ]);

            $parkingToken->update([
    'is_used' => true,
    'used_at' => now()
]);


            DB::commit();


            return response()->json([
                'message' => 'Check In berhasil',
                'data' => $parking
            ], 201);


        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function checkOut(Request $request)
{
    try {

        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
    'token' => 'required|string',
]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }


        $parkingToken = ParkingToken::where('token', $request->token)
    ->where('type', 'OUT')
    ->where('is_used', false)
    ->where('expired_at', '>', now())
    ->first();

if (!$parkingToken) {
    return response()->json([
        'message' => 'Token tidak valid atau sudah kedaluwarsa'
    ], 400);
}

$vehicle = Vehicle::find($parkingToken->vehicle_id);

if (!$vehicle) {
    return response()->json([
        'message' => 'Kendaraan tidak ditemukan'
    ], 404);
}

if ($parkingToken->user_id != auth()->id()) {
    return response()->json([
        'message'=>'Token bukan milik user ini'
    ],403);
}

$parking = ParkingLog::where('vehicle_id', $vehicle->id)
    ->whereNull('check_out')
    ->first();

    if ($parking && $parking->user_id != auth()->id()) {
    return response()->json([
        'message'=>'Data parkir bukan milik user ini'
    ],403);
}


        if (!$parking) {
            return response()->json([
                'message' => 'Kendaraan tidak sedang parkir'
            ], 404);
        }




        DB::beginTransaction();


        $parking->update([
            'check_out' => Carbon::now(),
            'status' => 'completed'
        ]);


        $slot = ParkingSlot::find($parking->parking_slot_id);

        if ($slot) {
            $slot->update([
                'status' => 'available'
            ]);
        }

        $parkingToken->update([
    'is_used' => true,
    'used_at' => now()
]);


        DB::commit();


        return response()->json([
            'message' => 'Check Out berhasil',
            'data' => $parking
        ], 200);


    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => 'Terjadi kesalahan',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
}

public function history()
{
    $history = ParkingLog::where('user_id', auth()->id())
        ->with(['vehicle', 'parkingSlot'])
        ->get();

    return response()->json([
        'message' => 'Riwayat parkir',
        'data' => $history
    ]);
}
 
}
