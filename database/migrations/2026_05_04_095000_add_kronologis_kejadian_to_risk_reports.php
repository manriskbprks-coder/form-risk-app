<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->text('kronologis_kejadian')->nullable()->after('other_cause_description');
        });
    }

    public function down(): void
    {
        Schema::table('risk_reports', function (Blueprint $table) {
            $table->dropColumn('kronologis_kejadian');
        });
    }
};
