<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('parking_tokens', function (Blueprint $table) {

        $table->id();

        // pemilik kendaraan
        $table->foreignId('user_id')
              ->constrained()
              ->cascadeOnDelete();

        // kendaraan yang digunakan
        $table->foreignId('vehicle_id')
              ->constrained()
              ->cascadeOnDelete();

        // token QR dinamis
        $table->string('token')->unique();

        // IN = masuk, OUT = keluar
        $table->enum('type', [
            'IN',
            'OUT'
        ]);

        // waktu kadaluarsa
        $table->timestamp('expired_at');

        // apakah sudah digunakan scanner
        $table->boolean('is_used')
              ->default(false);

        $table->timestamp('used_at')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_tokens');
    }
};
