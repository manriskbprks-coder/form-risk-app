<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tujuan: Nambah kolom sumber_risiko di tabel risk_reports
     * - nullable: karena data lama (existing reports) ga punya nilai ini
     * - string: biar fleksibel, ga perlu ENUM constraint di DB
     * - after('kategori'): biar urutan kolom rapi
     */
    public function up(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->string('sumber_risiko')->nullable()->after('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->dropColumn('sumber_risiko');
        });
    }
};
