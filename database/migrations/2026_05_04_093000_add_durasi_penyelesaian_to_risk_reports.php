<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->string('durasi_penyelesaian')->nullable()->after('mitigasi_tambahan');
        });
    }

    public function down(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->dropColumn('durasi_penyelesaian');
        });
    }
};
