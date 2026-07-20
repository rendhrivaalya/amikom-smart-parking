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

        if (!Schema::hasColumn('parking_logs', 'guest_id')) {
            $table->foreignId('guest_id')
                ->nullable()
                ->after('user_id')
                ->constrained('guests')
                ->nullOnDelete();
        }


        if (!Schema::hasColumn('parking_logs', 'parking_token_id')) {
            $table->foreignId('parking_token_id')
                ->nullable()
                ->after('parking_slot_id')
                ->constrained('parking_tokens')
                ->nullOnDelete();
        }

    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
