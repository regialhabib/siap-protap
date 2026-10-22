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
        Schema::table('pengamatans', function (Blueprint $table): void {
            $table->index('tanggal_pengamatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengamatans', function (Blueprint $table): void {
            $table->dropIndex(['tanggal_pengamatan']);
        });
    }
};
