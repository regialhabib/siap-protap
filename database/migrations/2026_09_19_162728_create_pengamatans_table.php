<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengamatans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_pengamatan');
            $table->foreignId('uppt_id')->constrained('uppts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('kecamatan');
            $table->foreignId('komoditas_id')->constrained('komoditas')->cascadeOnDelete();
            $table->decimal('luas_komoditi_ha', 10, 2);
            $table->foreignId('opt_id')->constrained('opts')->cascadeOnDelete();
            $table->decimal('serangan_ringan', 10, 2)->default(0);
            $table->decimal('serangan_sedang', 10, 2)->default(0);
            $table->decimal('serangan_berat', 10, 2)->default(0);
            $table->decimal('serangan_jumlah', 10, 2)->default(0);
            $table->decimal('kendali_apbd_kab', 10, 2)->default(0);
            $table->decimal('kendali_apbd_prov', 10, 2)->default(0);
            $table->decimal('kendali_masyarakat', 10, 2)->default(0);
            $table->decimal('kendali_apbn', 10, 2)->default(0);
            $table->string('kondisi_serangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengamatans');
    }
};
