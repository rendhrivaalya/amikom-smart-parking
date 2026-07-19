<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {

            $table->string('owner_name')->after('user_id');
            $table->string('stnk_number')->unique()->after('owner_name');
            $table->string('vehicle_model')->nullable()->after('brand');
            $table->year('vehicle_year')->nullable()->after('vehicle_model');
            $table->enum('status', ['active', 'inactive'])->default('active');

        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {

            $table->dropColumn([
                'owner_name',
                'stnk_number',
                'vehicle_model',
                'vehicle_year',
                'status'
            ]);

        });
    }
};