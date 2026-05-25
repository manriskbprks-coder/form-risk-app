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
    Schema::create('risk_mitigations', function (Blueprint $table) {
        $table->uuid('id')->primary();
        // FK yang nyambung ke tabel risk_causes
        $table->foreignUuid('risk_cause_id')->constrained('risk_causes')->cascadeOnDelete();
        $table->text('mitigasi'); // Teks mitigasi standar dari lu (Manrisk)
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_mitigations');
    }
};
