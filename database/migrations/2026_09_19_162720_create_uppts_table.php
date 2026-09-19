<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uppts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kabupaten_id')->constrained('kabupatens')->cascadeOnDelete();
            $table->string('nama_uppt');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uppts');
    }
};
