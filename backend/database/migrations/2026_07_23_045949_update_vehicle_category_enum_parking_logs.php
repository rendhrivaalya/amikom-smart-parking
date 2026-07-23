<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {

        DB::statement("
            UPDATE parking_logs
            SET vehicle_category = 'motor'
            WHERE vehicle_category = 'Roda 2'
        ");

        DB::statement("
            UPDATE parking_logs
            SET vehicle_category = 'mobil'
            WHERE vehicle_category = 'Roda 4'
        ");


        DB::statement("
            ALTER TABLE parking_logs
            MODIFY vehicle_category
            ENUM('motor','mobil')
            NOT NULL
        ");

    }


    public function down(): void
    {

        DB::statement("
            ALTER TABLE parking_logs
            MODIFY vehicle_category
            ENUM('Roda 2','Roda 4')
            NOT NULL
        ");

    }

};