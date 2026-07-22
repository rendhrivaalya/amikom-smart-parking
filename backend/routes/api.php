<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\ParkingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ParkingTokenController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\UserDashboardController;
use App\Http\Controllers\Api\ParkingSlotController;
use App\Http\Controllers\Api\ScannerController;


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/


Route::get('/test', function () {

    return response()->json([
        'message'=>'API OK'
    ]);

});



/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/


Route::post('/register',
    [AuthController::class,'register']
);


Route::post('/login',
    [AuthController::class,'login']
);



/*
|--------------------------------------------------------------------------
| GUEST REGISTRATION
|--------------------------------------------------------------------------
*/


Route::post('/guest/register',
    [GuestController::class,'register']
);



/*
|--------------------------------------------------------------------------
| PARKING CHECK IN
|--------------------------------------------------------------------------
|
| Digunakan oleh:
|
| Guest       : GST-XXXXXX
| Mahasiswa   : IN-XXXXXX
|
| Tidak memakai auth middleware
|
*/


Route::post('/check-in',
    [ParkingController::class,'checkIn']
);


 Route::post(
        '/check-out',
        [ParkingController::class,'checkOut']
    );




/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER
|--------------------------------------------------------------------------
*/


Route::middleware('auth:sanctum')->group(function(){


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */



    Route::get(
    '/parking-slots',
    [ParkingSlotController::class,'index']
);

    Route::post('/logout',
        [AuthController::class,'logout']
    );



    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */


    Route::get('/profile',
        [ProfileController::class,'index']
    );


    Route::put('/profile',
        [ProfileController::class,'update']
    );

    Route::get(
    '/parking-history',
    [ParkingController::class,'history']
);


    /*
    |--------------------------------------------------------------------------
    | Vehicle
    |--------------------------------------------------------------------------
    */


    Route::apiResource(
        'vehicles',
        VehicleController::class
    );


    Route::post(
        '/vehicles/{vehicle}/generate-qr',
        [VehicleController::class,'generateQr']
    );



    /*
    |--------------------------------------------------------------------------
    | Parking Token
    |--------------------------------------------------------------------------
    */


    Route::post(
        '/parking/generate-token',
        [ParkingTokenController::class,'generate']
    );


    Route::post(
        '/parking/generate-in',
        [ParkingTokenController::class,'generateIn']
    );


    Route::post(
        '/parking/generate-out',
        [ParkingTokenController::class,'generateOut']
    );



    /*
    |--------------------------------------------------------------------------
    | Parking Transaction
    |--------------------------------------------------------------------------
    */


   



});





/*
|--------------------------------------------------------------------------
| ADMIN & PETUGAS
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| ADMIN & PETUGAS
|--------------------------------------------------------------------------
*/


Route::middleware([
    'auth:sanctum',
    'role:admin,petugas'
])->group(function(){


    Route::get(
        '/dashboard',
        [DashboardController::class,'index']
    );


    Route::get(
        '/admin/parking-history',
        [ParkingController::class,'adminHistory']
    );


    Route::get(
        '/admin/users',
        [ProfileController::class,'allUsers']
    );


    Route::get(
        '/admin/vehicles',
        [VehicleController::class,'allVehicles']
    );


    /*
    |--------------------------------------------------------------------------
    | PETUGAS SCANNER
    |--------------------------------------------------------------------------
    */


    Route::post(
        '/scanner/check-in',
        [ScannerController::class,'checkIn']
    );


    Route::post(
        '/scanner/check-out',
        [ScannerController::class,'checkOut']
    );


});





/*
|--------------------------------------------------------------------------
| MAHASISWA
|--------------------------------------------------------------------------
*/


Route::middleware([
    'auth:sanctum',
    'role:mahasiswa'
])->group(function(){


    Route::get(
        '/dashboard-user',
        [UserDashboardController::class,'index']
    );


});