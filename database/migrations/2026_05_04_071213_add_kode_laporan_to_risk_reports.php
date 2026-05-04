<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('risk_reports', 'kode_laporan')) {
                $table->string('kode_laporan', 30)->unique()->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->dropColumn(['kode_laporan']);
        });
    }
};
