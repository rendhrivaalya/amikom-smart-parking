<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VehicleController extends Controller
{
    // Menampilkan semua kendaraan milik user yang login
    public function index()
{
    $vehicles = Vehicle::where('user_id', auth()->id())->get();

    return response()->json([
        'success' => true,
        'data' => $vehicles
    ]);
}

    // Menampilkan semua kendaraan untuk admin
public function allVehicles()
{
    $vehicles = Vehicle::all();

    return response()->json([
        'success' => true,
        'data' => $vehicles
    ]);
}


    // Menambahkan kendaraan
    public function store(Request $request)
    {
        $request->validate([
            'owner_name' => 'required|string|max:255',
            'stnk_number' => 'required|unique:vehicles,stnk_number',
            'plate_number' => 'required|unique:vehicles,plate_number',
            'vehicle_type' => 'required',
            'brand' => 'required',
            'vehicle_model' => 'nullable',
            'vehicle_year' => 'nullable|integer',
            'color' => 'required',
        ]);

        $vehicle = Vehicle::create([
            'user_id' => auth()->id(),
            'owner_name' => $request->owner_name,
            'stnk_number' => $request->stnk_number,
            'plate_number' => $request->plate_number,
            'vehicle_type' => $request->vehicle_type,
            'brand' => $request->brand,
            'vehicle_model' => $request->vehicle_model,
            'vehicle_year' => $request->vehicle_year,
            'color' => $request->color,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Kendaraan berhasil ditambahkan',
            'data' => $vehicle
        ], 201);
    }


    // Detail kendaraan
    public function show(Vehicle $vehicle)
    {
        if ($vehicle->user_id != auth()->id()) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        return response()->json([
            'data' => $vehicle
        ]);
    }


    // Update kendaraan
    public function update(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id != auth()->id()) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $request->validate([
            'owner_name' => 'required|string|max:255',
            'stnk_number' => 'required|unique:vehicles,stnk_number,' . $vehicle->id,
            'plate_number' => 'required|unique:vehicles,plate_number,' . $vehicle->id,
            'vehicle_type' => 'required',
            'brand' => 'required',
            'vehicle_model' => 'nullable',
            'vehicle_year' => 'nullable|integer',
            'color' => 'required',
            'status' => 'nullable'
        ]);


        $vehicle->update([
    'owner_name' => $request->owner_name,
    'stnk_number' => $request->stnk_number,
    'plate_number' => $request->plate_number,
    'vehicle_type' => $request->vehicle_type,
    'brand' => $request->brand,
    'vehicle_model' => $request->vehicle_model,
    'vehicle_year' => $request->vehicle_year,
    'color' => $request->color,
]);


        return response()->json([
            'message' => 'Data kendaraan berhasil diubah',
            'data' => $vehicle
        ]);
    }


    // Hapus kendaraan
    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->user_id != auth()->id()) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $vehicle->delete();

        return response()->json([
            'message' => 'Kendaraan berhasil dihapus'
        ]);
    }


    // Generate QR Code
    public function generateQr(Vehicle $vehicle)
    {
        if ($vehicle->user_id != auth()->id()) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }


        $fileName = "vehicle_{$vehicle->id}.svg";
        $path = "qrcodes/" . $fileName;


        Storage::disk('public')->put(
            $path,
            QrCode::format('svg')
                ->size(300)
                ->generate($vehicle->plate_number)
        );


        $vehicle->update([
            'qr_code' => $path
        ]);


        return response()->json([
            'message' => 'QR Code berhasil dibuat',
            'qr_code' => asset("storage/$path")
        ]);
    }
}