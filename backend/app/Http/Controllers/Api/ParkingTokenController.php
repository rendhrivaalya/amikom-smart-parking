<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingToken;
use App\Models\Vehicle;
use App\Models\ParkingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ParkingTokenController extends Controller
{

    public function generate(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:IN,OUT'
        ]);


        $vehicle = Vehicle::where('id', $request->vehicle_id)
            ->where('user_id', auth()->id())
            ->first();


        if (!$vehicle) {
            return response()->json([
                'success'=>false,
                'message'=>'Kendaraan tidak ditemukan'
            ],404);
        }


        // Cek status parkir
        $activeParking = ParkingLog::where('vehicle_id',$vehicle->id)
            ->whereNull('check_out')
            ->first();


        // Kalau generate IN tapi masih parkir
        if ($request->type == 'IN' && $activeParking) {
            return response()->json([
                'success'=>false,
                'message'=>'Kendaraan masih berada di area parkir'
            ],400);
        }


        // Kalau generate OUT tapi belum parkir
        if ($request->type == 'OUT' && !$activeParking) {
            return response()->json([
                'success'=>false,
                'message'=>'Kendaraan belum berada di area parkir'
            ],400);
        }



        // hapus token lama
        ParkingToken::where('vehicle_id',$vehicle->id)
            ->where('is_used',false)
            ->delete();



        $token = ParkingToken::create([
            'user_id'=>auth()->id(),
            'vehicle_id'=>$vehicle->id,
            'token'=>$request->type.'-'.strtoupper(Str::random(8)),
            'type'=>$request->type,
            'expired_at'=>now()->addMinutes(5),
            'is_used'=>false
        ]);



        $qr = base64_encode(
            QrCode::format('svg')
            ->size(300)
            ->generate($token->token)
        );



        return response()->json([
            'success'=>true,
            'message'=>'QR Token berhasil dibuat',
            'data'=>[
                'token'=>$token->token,
                'type'=>$token->type,
                'expired_at'=>$token->expired_at,
                'expires_in'=>300,
                'qr_code'=>'data:image/svg+xml;base64,'.$qr
            ]
        ]);

    }



    public function generateIn()
    {
        $vehicle = Vehicle::where('user_id',auth()->id())
            ->where('status','active')
            ->first();


        if(!$vehicle){
            return response()->json([
                'success'=>false,
                'message'=>'Kendaraan tidak ditemukan'
            ],404);
        }


        return $this->generate(
            new Request([
                'vehicle_id'=>$vehicle->id,
                'type'=>'IN'
            ])
        );
    }




    public function generateOut()
    {

        $vehicle = Vehicle::where('user_id',auth()->id())
            ->where('status','active')
            ->first();


        if(!$vehicle){
            return response()->json([
                'success'=>false,
                'message'=>'Kendaraan tidak ditemukan'
            ],404);
        }


        return $this->generate(
            new Request([
                'vehicle_id'=>$vehicle->id,
                'type'=>'OUT'
            ])
        );

    }

}