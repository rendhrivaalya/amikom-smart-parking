<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\ParkingLog;
use App\Models\ParkingSlot;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_vehicles' => Vehicle::count(),
            'total_slots' => ParkingSlot::count(),
            'occupied_slots' => ParkingSlot::where('status', 'occupied')->count(),
            'available_slots' => ParkingSlot::where('status', 'available')->count(),
            'parking_today' => ParkingLog::whereDate('created_at', today())->count(),
        ]);
    }
}