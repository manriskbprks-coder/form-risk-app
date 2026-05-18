<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration ini sudah TIDAK DIGUNAKAN karena kita revert kembali
     * ke role names spesifik (teller, ca, csr, security, kacab, korwil)
     * sebagai nilai role_target di tabel risk_items.
     *
     * Data sudah di-revert via migration baru:
     * 2026_05_12_120001_revert_role_target_to_specific_roles.php
     */
    public function up(): void
    {
        // No-op: migration ini sudah digantikan
    }

    public function down(): void
    {
        // No-op
    }
};
