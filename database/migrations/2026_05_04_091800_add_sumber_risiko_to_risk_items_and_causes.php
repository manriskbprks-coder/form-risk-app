<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom sumber_risiko ke risk_items
        Schema::table('risk_items', function (Blueprint $table) {
            $table->enum('sumber_risiko', [
                'manusia',
                'proses_internal',
                'sistem_teknologi',
                'faktor_eksternal'
            ])->default('manusia');
        });

        // Tambah kolom sumber_risiko ke risk_causes
        Schema::table('risk_causes', function (Blueprint $table) {
            $table->enum('sumber_risiko', [
                'manusia',
                'proses_internal',
                'sistem_teknologi',
                'faktor_eksternal'
            ])->default('manusia');
        });
    }

    public function down(): void
    {
        Schema::table('risk_items', function (Blueprint $table) {
            $table->dropColumn('sumber_risiko');
        });

        Schema::table('risk_causes', function (Blueprint $table) {
            $table->dropColumn('sumber_risiko');
        });
    }
};
