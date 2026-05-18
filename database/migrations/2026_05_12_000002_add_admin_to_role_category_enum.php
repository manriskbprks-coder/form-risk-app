<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // SQLite (testing) doesn't support ALTER COLUMN, skip for SQLite
        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'pgsql') {
            // PostgreSQL: Laravel $table->enum() creates a CHECK constraint named like:
            // "users_role_category_check" — drop it, then recreate with 'admin' included
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_category_check");
            DB::statement("ALTER TABLE users ALTER COLUMN role_category SET DEFAULT 'maker'");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_category_check CHECK (role_category::TEXT IN ('maker', 'checker', 'viewer', 'admin'))");
        } else {
            // MySQL / MariaDB
            DB::statement("ALTER TABLE users MODIFY COLUMN role_category ENUM('maker', 'checker', 'viewer', 'admin') DEFAULT 'maker'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'pgsql') {
            // Revert back to original 3 values
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_category_check");
            DB::statement("ALTER TABLE users ALTER COLUMN role_category SET DEFAULT 'maker'");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_category_check CHECK (role_category::TEXT IN ('maker', 'checker', 'viewer'))");
        } else {
            // MySQL / MariaDB
            DB::statement("ALTER TABLE users MODIFY COLUMN role_category ENUM('maker', 'checker', 'viewer') DEFAULT 'maker'");
        }
    }
};
