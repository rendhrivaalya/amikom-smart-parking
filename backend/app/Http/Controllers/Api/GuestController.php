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

    'qr_token'=>null,
    'expired_at'=>null,

    'is_used'=>false,
    'used_at'=>null,

    'status'=>'waiting'

]);




        return response()->json([
    'success'=>true,
    'message'=>'Registrasi berhasil. Menunggu persetujuan petugas.',
    'data'=>$guest
],201);

    

    }

    public function index()
{
    $guests = Guest::latest()->get();

    return response()->json([

        'success' => true,

        'total' => $guests->count(),

        'data' => $guests->map(function ($guest) {

            return [

                'id' => $guest->id,

                'name' => $guest->name,

                'phone' => $guest->phone,

                'plate_number' => $guest->plate_number,

                'vehicle_type' => $guest->vehicle_type,

                'brand' => $guest->brand,

                'vehicle_model' => $guest->vehicle_model,

                'vehicle_year' => $guest->vehicle_year,

                'purpose' => $guest->purpose,

                'destination' => $guest->destination,

                'status' => $guest->status,

                'qr_token' => $guest->qr_token,

                'expired_at' => $guest->expired_at,

                'is_used' => $guest->is_used,

'parking_status' => match($guest->status){

    'waiting' => 'Menunggu Approval',

    'approved' => 'Menunggu Datang',

    'parking' => 'Sedang Parkir',

    'finished' => 'Selesai',

    'rejected' => 'Ditolak',

    default => '-'

},

                'created_at' => $guest->created_at,

            ];

        })

    ]);
}

public function pending()
{
    return response()->json([

        'success'=>true,

        'data'=>Guest::where('status','waiting')
                    ->latest()
                    ->get()

    ]);
}

public function approve(Guest $guest)
{
    if($guest->status!='waiting'){

        return response()->json([
            'message'=>'Guest sudah diproses'
        ],400);

    }

    $token='GST-'.strtoupper(Str::random(8));

    $guest->update([

        'status'=>'approved',

        'qr_token'=>$token,

        'expired_at' => now()->endOfDay(),

        'is_used'=>false,

        'used_at'=>null

    ]);

    $qr=base64_encode(

        QrCode::format('svg')
            ->size(300)
            ->generate($token)

    );

    return response()->json([

        'success'=>true,

        'message'=>'Guest berhasil disetujui',

        'data'=>[

            'guest'=>$guest,

            'token'=>$token,

            'expired_at'=>$guest->expired_at,

            'qr_code'=>'data:image/svg+xml;base64,'.$qr

        ]

    ]);
}

public function reject(Guest $guest)
{

    if($guest->status!='waiting'){

        return response()->json([
            'message'=>'Guest sudah diproses'
        ],400);

    }

    $guest->update([

        'status'=>'rejected'

    ]);

    return response()->json([

        'success'=>true,

        'message'=>'Guest ditolak'

    ]);

}

public function show(Guest $guest)
{

    return response()->json([

        'success'=>true,

        'data'=>[

            'id'=>$guest->id,

            'name'=>$guest->name,

            'phone'=>$guest->phone,

            'identity_number'=>$guest->identity_number,

            'plate_number'=>$guest->plate_number,

            'vehicle_type'=>$guest->vehicle_type,

            'brand'=>$guest->brand,

            'vehicle_model'=>$guest->vehicle_model,

            'vehicle_year'=>$guest->vehicle_year,

            'color'=>$guest->color,

            'purpose'=>$guest->purpose,

            'destination'=>$guest->destination,

            'status'=>$guest->status,

            'expired_at'=>$guest->expired_at,

            'created_at'=>$guest->created_at

        ]

    ]);

}

public function history()
{

    $guests=Guest::latest()->get();

    return response()->json([

        'success'=>true,

        'total'=>$guests->count(),

        'data'=>$guests->map(function($guest){

            return [

                'id'=>$guest->id,

                'name'=>$guest->name,

                'plate_number'=>$guest->plate_number,

                'vehicle_type'=>$guest->vehicle_type,

                'purpose'=>$guest->purpose,

                'destination'=>$guest->destination,

                'status'=>$guest->status,

                'expired_at'=>$guest->expired_at,

                'created_at'=>$guest->created_at

            ];

        })

    ]);

}

}