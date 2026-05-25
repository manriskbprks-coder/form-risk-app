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
        Schema::create('risk_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users'); // Siapa yang lapor
            $table->foreignUuid('branch_id')->constrained('branches'); // Di cabang mana

            $table->date('tanggal_kejadian');
            $table->date('tanggal_diketahui');

            $table->foreignUuid('risk_item_id')->constrained('risk_items');
            $table->foreignUuid('risk_cause_id')->nullable()->constrained('risk_causes');

            // Kalau milih cause "Other", dia ngisi ini
            $table->string('other_cause_description')->nullable();

            // Mitigasi tambahan yang diketik manual (Point a3)
            $table->text('mitigasi_tambahan')->nullable();

            // Kolom ini asalnya nggak boleh kosong, sekarang kita kasih nullable()
            $table->decimal('dampak_finansial', 15, 2)->nullable();

            // TAMBAHIN KOLOM BARU INI:
            $table->text('dampak_non_finansial')->nullable();

            $table->string('skala_dampak')->nullable();

            // TAMBAHIN KOLOM KATEGORI JUGA BIAR JELAS LAPORANNYA APA
            $table->enum('kategori', ['finansial', 'non-finansial'])->default('finansial');

            // TAMBAHIN DUA BARIS INI:
            $table->string('approval_status')->default('pending_kacab');
            $table->enum('resolution_status', ['open', 'in_progress', 'closed'])->default('open');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_reports');
    }
};
