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
    Schema::table('parking_logs', function (Blueprint $table) {

        $table->enum('vehicle_category',[
            'Roda 2',
            'Roda 4'
        ])->after('vehicle_id');

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_logs', function (Blueprint $table) {
            //
        });
    }
};
