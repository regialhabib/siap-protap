<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'popt'])->default('popt')->after('password');
            $table->foreignId('uppt_id')->nullable()->after('role')->constrained('uppts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['uppt_id']);
            $table->dropColumn(['role', 'uppt_id']);
        });
    }
};
