<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('parkings', function (Blueprint $table) {

        $table->id();

        // User yang parkir
        $table->foreignId('user_id')
              ->constrained()
              ->cascadeOnDelete();

        // Kendaraan yang dipakai
        $table->foreignId('vehicle_id')
              ->constrained()
              ->cascadeOnDelete();

        // Token yang digunakan untuk transaksi
        $table->foreignId('parking_token_id')
              ->nullable()
              ->constrained()
              ->nullOnDelete();

        // Waktu masuk
        $table->timestamp('check_in');

        // Waktu keluar
        $table->timestamp('check_out')->nullable();

        // Status parkir
        $table->enum('status', [
            'PARKING',
            'FINISHED'
        ])->default('PARKING');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parkings');
    }
};
