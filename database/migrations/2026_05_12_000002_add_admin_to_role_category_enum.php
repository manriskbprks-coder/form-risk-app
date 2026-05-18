<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite (testing) doesn't support MODIFY COLUMN, skip for SQLite
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role_category ENUM('maker', 'checker', 'viewer', 'admin') DEFAULT 'maker'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role_category ENUM('maker', 'checker', 'viewer') DEFAULT 'maker'");
        }
    }
};
