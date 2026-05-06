<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah kolom is_active dari boolean ke smallInteger
     * biar kompatibel sama PostgreSQL + PDO::ATTR_EMULATE_PREPARES
     */
    public function up(): void
    {
        // Ubah di tabel branches
        Schema::table('branches', function (Blueprint $table) {
            $table->smallInteger('is_active')->default(1)->change();
        });

        // Ubah di tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->smallInteger('is_active')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->change();
        });
    }
};
