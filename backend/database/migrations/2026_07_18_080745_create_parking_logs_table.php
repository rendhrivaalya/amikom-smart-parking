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
    Schema::create('parking_logs', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

    $table->foreignId('parking_slot_id')->constrained()->cascadeOnDelete();

    $table->timestamp('check_in');

    $table->timestamp('check_out')->nullable();

    $table->string('status')->default('parking');

    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_logs');
    }
};
