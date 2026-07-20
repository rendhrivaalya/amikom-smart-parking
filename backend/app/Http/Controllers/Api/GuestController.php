<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GuestController extends Controller
{
    public function register(Request $request)
    {

        $request->validate([

            'name'=>'required|string',
            'phone'=>'required',
            'identity_number'=>'nullable',

            'plate_number'=>'required',
            'vehicle_type'=>'required|in:Motor,Mobil',

            'brand'=>'required',
            'vehicle_model'=>'nullable',
            'vehicle_year'=>'nullable',

            'color'=>'nullable',

            'purpose'=>'required',
            'destination'=>'required',

            'stnk_image'=>'required|image|max:4096'

        ]);


        // upload STNK
        $imagePath = $request->file('stnk_image')
            ->store('guest-stnk','public');


        // generate token tamu
        $token = 'GST-' . strtoupper(Str::random(8));


        $guest = Guest::create([

    'name'=>$request->name,
    'phone'=>$request->phone,
    'identity_number'=>$request->identity_number,

    'plate_number'=>$request->plate_number,
    'vehicle_type'=>$request->vehicle_type,

    'brand'=>$request->brand,
    'vehicle_model'=>$request->vehicle_model,
    'vehicle_year'=>$request->vehicle_year,

    'color'=>$request->color,

    'purpose'=>$request->purpose,
    'destination'=>$request->destination,

    'stnk_image'=>$imagePath,

    'qr_token'=>$token,
    'expired_at'=>now()->addDay(),

    'is_used'=>false,
    'used_at'=>null,

    'status'=>'approved'
]);


        $qr = base64_encode(
            QrCode::format('svg')
            ->size(300)
            ->generate($token)
        );


        return response()->json([

            'success'=>true,

            'message'=>'Registrasi tamu berhasil',

            'data'=>[

                'guest_id'=>$guest->id,

                'token'=>$token,

                'expired_at'=>$guest->expired_at,

                'qr_code'=>'data:image/svg+xml;base64,'.$qr

            ]

        ]);

    }
}