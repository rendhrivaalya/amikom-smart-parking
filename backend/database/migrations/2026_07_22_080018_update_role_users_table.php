<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

    public function up(): void
    {

        Schema::table('users', function (Blueprint $table) {

            $table->enum(
                'role',
                [
                    'mahasiswa',
                    'dosen',
                    'staff',
                    'petugas',
                    'admin'
                ]
            )
            ->default('mahasiswa')
            ->change();

        });

    }


    public function down(): void
    {

        Schema::table('users', function (Blueprint $table) {

            $table->enum(
                'role',
                [
                    'mahasiswa',
                    'petugas',
                    'admin'
                ]
            )
            ->default('mahasiswa')
            ->change();

        });

    }

};