<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration ini mengembalikan nilai role_target di tabel risk_items
     * dari role_category (maker, checker, viewer) kembali ke role names
     * spesifik (teller, ca, csr, security, kacab, korwil).
     *
     * Ini adalah REVERT dari migration 2026_05_12_120000_change_role_target_in_risk_items
     * yang sebelumnya mengubah data dari role names spesifik ke role_category.
     */
    public function up(): void
    {
        // Revert data: balikin dari role_category ke role names spesifik
        // Maker → teller (default, karena maker mencakup teller/ca/csr/security)
        DB::table('risk_items')
            ->where('role_target', 'maker')
            ->update(['role_target' => 'teller']);

        // Checker → kacab
        DB::table('risk_items')
            ->where('role_target', 'checker')
            ->update(['role_target' => 'kacab']);

        // Viewer → korwil
        DB::table('risk_items')
            ->where('role_target', 'viewer')
            ->update(['role_target' => 'korwil']);
    }

    public function down(): void
    {
        // Rollback: balikin ke role_category
        DB::table('risk_items')
            ->whereIn('role_target', ['teller', 'ca', 'csr', 'security'])
            ->update(['role_target' => 'maker']);

        DB::table('risk_items')
            ->where('role_target', 'kacab')
            ->update(['role_target' => 'checker']);

        DB::table('risk_items')
            ->where('role_target', 'korwil')
            ->update(['role_target' => 'viewer']);
    }
};
