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
use App\Http\Controllers\Api\ParkingMonitorController;
use App\Http\Controllers\Api\SlotRecommendationController;


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


    Route::post(
        '/logout',
        [AuthController::class,'logout']
    );


    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/profile',
        [ProfileController::class,'index']
    );


    Route::put(
        '/profile',
        [ProfileController::class,'update']
    );



    /*
    |--------------------------------------------------------------------------
    | Parking Slot
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/parking-slots',
        [ParkingSlotController::class,'index']
    );

    Route::get(
    '/parking-slots/areas',
    [ParkingSlotController::class,'areas']
);

Route::get(
    '/parking-slots/available',
    [ParkingSlotController::class,'available']
);


    /*
    |--------------------------------------------------------------------------
    | Parking Recommendation
    |--------------------------------------------------------------------------
    */


    Route::post(
        '/parking/recommend-slot',
        [SlotRecommendationController::class,'recommend']
    );



    /*
    |--------------------------------------------------------------------------
    | Parking History
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/parking-history',
        [ParkingController::class,'history']
    );

    Route::get(
    '/admin/parking-history/{id}',
    [ParkingController::class,'historyDetail']
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


});



    /*
    |--------------------------------------------------------------------------
    | Parking Transaction
    |--------------------------------------------------------------------------
    */


   









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
    '/parking-monitor',
    [ParkingMonitorController::class,'index']
);

Route::get(
    '/parking-monitor/detail',
    [ParkingMonitorController::class,'detail']
);


Route::get(
    '/parking-monitor/statistic',
    [ParkingMonitorController::class,'statistic']
);

Route::get(
    '/parking-monitor/{id}',
    [ParkingMonitorController::class, 'show']
);




Route::get(
    '/admin/guests',
    [GuestController::class, 'index']
);

Route::get(
    '/guest',
    [GuestController::class,'index']
);

Route::get(
    '/guest/waiting',
    [GuestController::class,'pending']
);

Route::post(
    '/guest/{guest}/approve',
    [GuestController::class,'approve']
);

Route::post(
    '/guest/{guest}/reject',
    [GuestController::class,'reject']
);

Route::get(
    '/guest/history',
    [GuestController::class,'history']
);


    /*
    |--------------------------------------------------------------------------
    | Dashboard Admin
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/dashboard',
        [DashboardController::class,'index']
    );

    Route::get(
    '/dashboard/chart',
    [DashboardController::class,'chart']
);

Route::get('/dashboard/realtime', [DashboardController::class,'realtime']);

    /*
    |--------------------------------------------------------------------------
    | Monitoring Parkir
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/parking-monitor',
        [ParkingMonitorController::class,'index']
    );


    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/admin/parking-history',
        [ParkingController::class,'adminHistory']
    );


    /*
    |--------------------------------------------------------------------------
    | User & Vehicle Management
    |--------------------------------------------------------------------------
    */


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


/*
|--------------------------------------------------------------------------
| USER PARKING
|--------------------------------------------------------------------------
*/


Route::middleware([
    'auth:sanctum',
    'role:mahasiswa,dosen,staff'
])->group(function(){


    Route::get(
        '/dashboard-user',
        [UserDashboardController::class,'index']
    );


});