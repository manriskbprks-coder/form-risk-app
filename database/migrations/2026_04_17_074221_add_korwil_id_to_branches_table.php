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
        Schema::table('branches', function (Blueprint $table) {
            // Tambahin korwil_id. Harus nullable karena pas pertama kali cabang dibangun, belum tentu ada Korwil-nya.
            $table->uuid('korwil_id')->nullable()->after('nama_cabang');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('korwil_id');
        });
    }
};
