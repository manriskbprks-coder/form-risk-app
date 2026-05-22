<?php

use App\Domain\Enums\RiskReportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom `status` ke tabel `risk_reports` sebagai
     * pengganti gabungan `approval_status` + `resolution_status`.
     *
     * Kolom lama TIDAK dihapus dulu — akan dihapus di migration terpisah
     * setelah semua kode sudah migrasi ke kolom `status`.
     */
    public function up(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->string('status', 50)->default('pending_kacab')->after('kode_laporan');
        });

        // Migrate data dari 2 kolom lama ke 1 kolom baru
        DB::table('risk_reports')->update([
            'status' => DB::raw("
                CASE
                    WHEN approval_status = 'pending_kacab' THEN 'pending_kacab'
                    WHEN approval_status = 'need_revision' THEN 'need_revision'
                    WHEN approval_status = 'pending_revision' THEN 'pending_revision'
                    WHEN approval_status = 'approved' AND resolution_status = 'open' THEN 'approved'
                    WHEN approval_status = 'approved' AND resolution_status = 'in_progress' THEN 'in_progress'
                    WHEN approval_status = 'approved' AND resolution_status = 'closed' THEN 'closed'
                    ELSE 'pending_kacab'
                END
            "),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
