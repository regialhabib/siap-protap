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
        Schema::table('komoditas', function (Blueprint $table): void {
            $table->unique('nama_komoditas');
        });

        Schema::table('opts', function (Blueprint $table): void {
            $table->unique('nama_opt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('komoditas', function (Blueprint $table): void {
            $table->dropUnique(['nama_komoditas']);
        });

        Schema::table('opts', function (Blueprint $table): void {
            $table->dropUnique(['nama_opt']);
        });
    }
};
