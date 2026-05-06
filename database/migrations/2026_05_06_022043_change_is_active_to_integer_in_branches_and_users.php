<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
        DB::statement('ALTER TABLE branches ALTER COLUMN is_active TYPE smallint USING is_active::smallint');
        DB::statement('ALTER TABLE branches ALTER COLUMN is_active SET DEFAULT 1');
        DB::statement('ALTER TABLE branches ALTER COLUMN is_active SET NOT NULL');

        // Ubah di tabel users
        DB::statement('ALTER TABLE users ALTER COLUMN is_active TYPE smallint USING is_active::smallint');
        DB::statement('ALTER TABLE users ALTER COLUMN is_active SET DEFAULT 1');
        DB::statement('ALTER TABLE users ALTER COLUMN is_active SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE branches ALTER COLUMN is_active TYPE boolean USING is_active::boolean');
        DB::statement('ALTER TABLE users ALTER COLUMN is_active TYPE boolean USING is_active::boolean');
    }
};
