<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menghapus kolom lama `approval_status` dan `resolution_status`
     * dari tabel `risk_reports` karena sudah digantikan oleh kolom `status`.
     *
     * Migration ini HARUS dijalankan SETELAH semua kode sudah migrasi
     * ke kolom `status` (lihat migration 2026_05_21_000001).
     */
    public function up(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'resolution_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Mengembalikan kolom lama jika rollback diperlukan.
     * Data tidak bisa dikembalikan secara otomatis — perlu migrasi manual
     * dari kolom `status` ke 2 kolom lama.
     */
    public function down(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->string('approval_status', 50)->default('pending_kacab')->after('kode_laporan');
            $table->string('resolution_status', 50)->default('open')->after('approval_status');
        });
    }
};
