<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('risk_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_risiko');
            // TAMBAHIN BARIS INI:
            $table->enum('kategori', ['finansial', 'non-finansial'])->default('finansial');
            $table->string('role_target');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_items');
    }
};
