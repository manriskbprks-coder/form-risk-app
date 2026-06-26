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
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            // Drop foreign key first because MySQL might be using the unique index for this foreign key constraint
            $table->dropForeign(['branch_id']);
            $table->dropUnique('unique_declaration_per_period');
            
            $table->foreignUuid('division_id')->nullable()->after('branch_id')->constrained('divisions')->onDelete('cascade');
            
            $table->unique(['branch_id', 'division_id', 'periode', 'bulan', 'tahun'], 'unique_declaration_per_period');
            // Re-add foreign key
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropUnique('unique_declaration_per_period');
            
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
            
            $table->unique(['branch_id', 'periode', 'bulan', 'tahun'], 'unique_declaration_per_period');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }
};
