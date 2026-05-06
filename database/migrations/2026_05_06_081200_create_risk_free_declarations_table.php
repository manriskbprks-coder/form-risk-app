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
        Schema::create('risk_free_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('periode', ['1', '2']); // 1 = tgl 1-14, 2 = tgl 15-akhir
            $table->tinyInteger('bulan'); // 1-12
            $table->year('tahun');
            $table->text('statement_text');
            $table->enum('status', ['active', 'violated', 'cancelled'])->default('active');
            $table->timestamp('violated_at')->nullable();
            $table->foreignId('violated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Unique constraint: 1 branch hanya boleh 1 deklarasi per periode per bulan per tahun
            $table->unique(['branch_id', 'periode', 'bulan', 'tahun'], 'unique_declaration_per_period');
        });

        Schema::create('risk_free_declaration_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_free_declaration_id')->constrained('risk_free_declarations')->onDelete('cascade');
            $table->string('jabatan'); // Teller, CA, CS, Security, Kacab
            $table->boolean('is_clean')->default(true); // true = nihil
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_free_declaration_details');
        Schema::dropIfExists('risk_free_declarations');
    }
};
