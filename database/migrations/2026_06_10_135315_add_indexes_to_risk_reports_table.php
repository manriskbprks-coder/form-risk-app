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
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->index(['branch_id', 'status'], 'idx_branch_status');
            $table->index(['user_id', 'status'], 'idx_user_status');
            $table->index('created_at', 'idx_created_at');
            $table->index('kategori', 'idx_kategori');
            $table->index('tanggal_kejadian', 'idx_tanggal_kejadian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->dropIndex('idx_branch_status');
            $table->dropIndex('idx_user_status');
            $table->dropIndex('idx_created_at');
            $table->dropIndex('idx_kategori');
            $table->dropIndex('idx_tanggal_kejadian');
        });
    }
};
