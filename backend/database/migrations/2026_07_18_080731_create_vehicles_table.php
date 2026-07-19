<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('vehicles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('plate_number')->unique();
        $table->string('vehicle_type');
        $table->string('brand');
        $table->string('color');
        $table->string('qr_code')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }

    public function generateQr(Vehicle $vehicle)
{
    if ($vehicle->user_id != auth()->id()) {
        return response()->json([
            'message' => 'Forbidden'
        ], 403);
    }

    $fileName = "vehicle_{$vehicle->id}.svg";
    $path = "qrcodes/$fileName";

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
};
