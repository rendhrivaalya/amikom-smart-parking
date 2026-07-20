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
    Schema::create('guests', function (Blueprint $table) {

        $table->id();

        $table->string('name');

        $table->string('phone',20);

        $table->string('identity_number')->nullable();

        $table->string('plate_number');

        $table->enum('vehicle_type',[
            'Motor',
            'Mobil'
        ]);

        $table->string('brand');

        $table->string('vehicle_model')->nullable();

        $table->year('vehicle_year')->nullable();

        $table->string('color')->nullable();

        $table->string('purpose');

        $table->string('destination');

        // lokasi upload foto STNK
        $table->string('stnk_image');

        $table->string('qr_token')->unique();

$table->timestamp('expired_at')->nullable();

$table->boolean('is_used')->default(false);

$table->timestamp('used_at')->nullable();


        $table->enum('status', [
    'waiting',
    'approved',
    'parking',
    'finished',
    'rejected'
])->default('waiting');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
