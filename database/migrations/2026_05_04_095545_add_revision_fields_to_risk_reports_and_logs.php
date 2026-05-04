<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom revision_note di risk_reports
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->text('revision_note')->nullable()->after('approval_status');
        });

        // Tambah kolom old_data (JSON) di risk_report_logs untuk snapshot revisi
        Schema::table('risk_report_logs', function (Blueprint $table) {
            $table->json('old_data')->nullable()->after('status_after_note');
        });
    }

    public function down(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->dropColumn('revision_note');
        });

        Schema::table('risk_report_logs', function (Blueprint $table) {
            $table->dropColumn('old_data');
        });
    }
};
