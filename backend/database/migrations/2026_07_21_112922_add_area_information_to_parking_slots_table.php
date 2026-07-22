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
    Schema::table('parking_slots', function (Blueprint $table) {

        $table->string('area_code')->after('id');

        $table->string('area_name')->after('area_code');

        $table->enum('allowed_vehicle', [
            'Motor',
            'Mobil',
            'Semua'
        ])->after('area_name');

        $table->string('allowed_role')
              ->default('all')
              ->after('allowed_vehicle');

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('parking_slots', function (Blueprint $table) {

        $table->dropColumn([
            'area_code',
            'area_name',
            'allowed_vehicle',
            'allowed_role'
        ]);

    });
}
};
