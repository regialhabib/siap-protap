<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengamatans', function (Blueprint $table) {
            $table->dropColumn('kecamatan');
        });
    }

    public function down(): void
    {
        Schema::table('pengamatans', function (Blueprint $table) {
            $table->string('kecamatan')->nullable();
        });
    }
};
