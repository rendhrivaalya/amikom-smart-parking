<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingLog;
use App\Models\ParkingSlot;

class DashboardController extends Controller
{

    public function index()
    {
        return response()->json([

            'kendaraan_parkir_sekarang' =>
                ParkingLog::where('status','parking')
                ->count(),

            'slot_kosong' =>
                ParkingSlot::whereDoesntHave(
                    'parkingLogs',
                    function($q){
                        $q->where('status','parking');
                    }
                )->count(),

            'slot_terisi' =>
                ParkingLog::where('status','parking')
                ->count(),

            'total_mahasiswa_hari_ini' =>
                ParkingLog::whereDate(
                    'created_at',
                    today()
                )
                ->whereHas(
                    'user',
                    fn($q)=>$q->where('role','mahasiswa')
                )
                ->count(),

            'total_guest_hari_ini' =>
                ParkingLog::whereDate(
                    'created_at',
                    today()
                )
                ->whereNotNull('guest_id')
                ->count(),

            'roda_2' =>
                ParkingLog::where(
                    'vehicle_category',
                    'Roda 2'
                )->count(),

            'roda_4' =>
                ParkingLog::where(
                    'vehicle_category',
                    'Roda 4'
                )->count(),

        ]);
    }

}