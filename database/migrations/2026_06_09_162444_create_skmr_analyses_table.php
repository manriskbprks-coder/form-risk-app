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
        Schema::create('skmr_analyses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('risk_report_id')->constrained('risk_reports')->cascadeOnDelete();
            $table->text('catatan_skmr')->nullable();
            $table->string('ketersediaan_kebijakan')->nullable();
            $table->string('kesesuaian_sop')->nullable();
            $table->text('rekomendasi_1')->nullable();
            $table->text('rekomendasi_2')->nullable();
            $table->text('dampak_rekomendasi_1')->nullable();
            $table->text('dampak_rekomendasi_2')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skmr_analyses');
    }
};
