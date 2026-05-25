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
        Schema::create('risk_report_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('risk_report_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained(); // Siapa yang nulis note
            $table->text('note'); // Isi progressnya
            $table->string('status_after_note'); // Status setelah note ini dibuat
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_report_logs');
    }
};
