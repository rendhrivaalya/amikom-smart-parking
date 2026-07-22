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
    Schema::create('parking_tokens', function (Blueprint $table) {

        $table->id();

        $table->foreignId('user_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('vehicle_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('token')->unique();

        $table->enum('type', [
            'IN',
            'OUT'
        ]);

        $table->timestamp('expired_at');

        $table->boolean('is_used')->default(false);

        $table->timestamp('used_at')->nullable();

        $table->timestamps();
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
