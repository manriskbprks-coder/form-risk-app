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
    Schema::create('risk_causes', function (Blueprint $table) {
        $table->uuid('id')->primary();
        // FK yang nyambung ke tabel risk_items
        $table->foreignUuid('risk_item_id')->constrained('risk_items')->cascadeOnDelete();
        $table->string('penyebab'); // Contoh: Lalai, Itikad buruk, Other
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_causes');
    }
};
