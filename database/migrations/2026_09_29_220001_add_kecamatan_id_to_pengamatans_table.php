<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengamatans', function (Blueprint $table) {
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengamatans', function (Blueprint $table) {
            $table->dropForeign(['kecamatan_id']);
            $table->dropColumn('kecamatan_id');
        });
    }
};
