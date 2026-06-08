<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah pending_kacab menjadi pending_atasan
        DB::table('risk_reports')->where('status', 'pending_kacab')->update(['status' => 'pending_atasan']);

        // 2. Gabungkan approved dan in_progress menjadi approved_in_progress
        DB::table('risk_reports')->whereIn('status', ['approved', 'in_progress'])->update(['status' => 'approved_in_progress']);

        // Update di risk_report_logs untuk status_after_note
        DB::table('risk_report_logs')->where('status_after_note', 'pending_kacab')->update(['status_after_note' => 'pending_atasan']);
        DB::table('risk_report_logs')->whereIn('status_after_note', ['approved', 'in_progress'])->update(['status_after_note' => 'approved_in_progress']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback log
        DB::table('risk_report_logs')->where('status_after_note', 'pending_atasan')->update(['status_after_note' => 'pending_kacab']);
        // Tidak bisa dibedakan lagi mana yang approved mana yang in_progress dengan mudah, jadi rollback nya cuma balikin ke approved aja
        DB::table('risk_report_logs')->where('status_after_note', 'approved_in_progress')->update(['status_after_note' => 'approved']);

        // Rollback report
        DB::table('risk_reports')->where('status', 'pending_atasan')->update(['status' => 'pending_kacab']);
        DB::table('risk_reports')->where('status', 'approved_in_progress')->update(['status' => 'approved']);
    }
};
