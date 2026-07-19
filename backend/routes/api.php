<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\ParkingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ParkingTokenController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/test', function () {
    return response()->json([
        'message' => 'API OK'
    ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Vehicle
    Route::apiResource('vehicles', VehicleController::class);
    Route::post('/vehicles/{vehicle}/generate-qr', [VehicleController::class, 'generateQr']);

    // Parking Token
Route::post('/parking/generate-token', 
    [ParkingTokenController::class, 'generate']
);


Route::post('/parking/generate-in',
    [ParkingTokenController::class, 'generateIn']
);


Route::post('/parking/generate-out',
    [ParkingTokenController::class, 'generateOut']
);

    // Parking
    Route::post('/check-in', [ParkingController::class, 'checkIn']);
    Route::post('/check-out', [ParkingController::class, 'checkOut']);
    Route::get('/parking-history', [ParkingController::class, 'history']);
});

/*
|--------------------------------------------------------------------------
| Admin & Petugas
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin,petugas'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Admin melihat semua user
    Route::get('/admin/users', [ProfileController::class, 'allUsers']);

     Route::get('/admin/vehicles', [VehicleController::class, 'allVehicles']);

});

/*
|--------------------------------------------------------------------------
| Mahasiswa
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:mahasiswa'])->group(function () {

    Route::get('/dashboard-user', function () {
        return response()->json([
            'message' => 'Dashboard Mahasiswa',
            'user' => auth()->user()
        ]);
    });

});

