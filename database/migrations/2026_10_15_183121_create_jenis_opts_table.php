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
        Schema::create('jenis_opts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opt_id')->constrained()->cascadeOnDelete();
            $table->string('nama_jenis_opt');
            $table->timestamps();
            $table->unique(['opt_id', 'nama_jenis_opt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_opts');
    }
};
