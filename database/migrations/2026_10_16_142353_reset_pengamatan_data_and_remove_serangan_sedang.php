<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('pengamatans')->delete();

        Schema::table('pengamatans', function (Blueprint $table) {
            $table->dropColumn(['serangan_sedang', 'kondisi_serangan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengamatans', function (Blueprint $table) {
            $table->decimal('serangan_sedang', 10, 2)->default(0);
            $table->string('kondisi_serangan')->nullable();
        });
    }
};
