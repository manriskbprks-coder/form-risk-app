<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rename violated → rejected columns and update enum values.
     * Handles MySQL, PostgreSQL, and SQLite differences.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->upSqlite();
        } elseif ($driver === 'pgsql') {
            $this->upPgsql();
        } else {
            $this->upMysql();
        }
    }

    /**
     * MySQL/MariaDB: Standard ALTER TABLE approach
     */
    protected function upMysql(): void
    {
        // Drop FK on branch_id first (it's part of the unique index, so MySQL blocks dropping the index)
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        // Drop unique index
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->dropIndex('unique_declaration_per_period');
        });

        // Drop old columns
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->dropColumn(['violated_at', 'violated_by']);
        });

        // Re-create with new names
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->timestamp('rejected_at')->nullable()->after('status');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->onDelete('set null');
        });

        // Recreate unique constraint
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->unique(['branch_id', 'periode', 'bulan', 'tahun'], 'unique_declaration_per_period');
        });

        // Re-add FK on branch_id
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });

        // Update enum values
        DB::statement("ALTER TABLE risk_free_declarations MODIFY COLUMN status ENUM('active', 'rejected', 'cancelled') DEFAULT 'active'");
        DB::statement("UPDATE risk_free_declarations SET status = 'rejected' WHERE status = 'violated'");
    }

    /**
     * PostgreSQL: Uses dropUnique() instead of dropIndex(), and CHECK constraints instead of ENUM.
     */
    protected function upPgsql(): void
    {
        // Step 1: Drop the unique constraint (PostgreSQL creates a constraint, not just an index)
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->dropUnique('unique_declaration_per_period');
        });

        // Step 2: Drop the CHECK constraint on status column
        // PostgreSQL auto-names CHECK constraints as {table}_{column}_check
        DB::statement('ALTER TABLE risk_free_declarations DROP CONSTRAINT IF EXISTS risk_free_declarations_status_check');

        // Step 3: Drop old columns
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->dropColumn(['violated_at', 'violated_by']);
        });

        // Step 4: Add new columns (PostgreSQL doesn't support ->after())
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
        });

        // Step 5: Recreate unique constraint
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->unique(['branch_id', 'periode', 'bulan', 'tahun'], 'unique_declaration_per_period');
        });

        // Step 6: Add new CHECK constraint for status with updated values
        DB::statement("ALTER TABLE risk_free_declarations ADD CONSTRAINT risk_free_declarations_status_check CHECK (status IN ('active', 'rejected', 'cancelled'))");

        // Step 7: Update data
        DB::statement("UPDATE risk_free_declarations SET status = 'rejected' WHERE status = 'violated'");
    }

    /**
     * SQLite: Recreate table approach (SQLite cannot drop columns with FK constraints)
     * Uses a temp table name to avoid index name conflicts (SQLite requires unique index names across DB).
     */
    protected function upSqlite(): void
    {
        $tempTable = 'risk_free_declarations_' . time();

        // Step 1: Create temp table with new schema (use anonymous unique index to avoid name conflicts)
        Schema::create($tempTable, function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('periode', ['1', '2']);
            $table->tinyInteger('bulan');
            $table->year('tahun');
            $table->text('statement_text');
            $table->enum('status', ['active', 'rejected', 'cancelled'])->default('active');
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Use anonymous unique index (no name) to avoid conflicts with existing index name
            $table->unique(['branch_id', 'periode', 'bulan', 'tahun']);
        });

        // Step 2: Copy data from old table to temp table
        DB::statement("INSERT INTO {$tempTable} (id, branch_id, user_id, periode, bulan, tahun, statement_text, status, rejected_at, rejected_by, created_at, updated_at)
            SELECT id, branch_id, user_id, periode, bulan, tahun, statement_text,
                CASE WHEN status = 'violated' THEN 'rejected' ELSE status END,
                violated_at, violated_by, created_at, updated_at
            FROM risk_free_declarations");

        // Step 3: Drop old table
        Schema::drop('risk_free_declarations');

        // Step 4: Rename temp table to original name
        Schema::rename($tempTable, 'risk_free_declarations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->downSqlite();
        } elseif ($driver === 'pgsql') {
            $this->downPgsql();
        } else {
            $this->downMysql();
        }
    }

    /**
     * MySQL/MariaDB: Reverse
     */
    protected function downMysql(): void
    {
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->dropIndex('unique_declaration_per_period');
        });

        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->dropColumn(['rejected_at', 'rejected_by']);
        });

        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->timestamp('violated_at')->nullable()->after('status');
            $table->foreignId('violated_by')->nullable()->after('violated_at')->constrained('users')->onDelete('set null');
        });

        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->unique(['branch_id', 'periode', 'bulan', 'tahun'], 'unique_declaration_per_period');
        });

        DB::statement("ALTER TABLE risk_free_declarations MODIFY COLUMN status ENUM('active', 'violated', 'cancelled') DEFAULT 'active'");
        DB::statement("UPDATE risk_free_declarations SET status = 'violated' WHERE status = 'rejected'");
    }

    /**
     * PostgreSQL: Reverse
     */
    protected function downPgsql(): void
    {
        // Drop unique constraint
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->dropUnique('unique_declaration_per_period');
        });

        // Drop CHECK constraint
        DB::statement('ALTER TABLE risk_free_declarations DROP CONSTRAINT IF EXISTS risk_free_declarations_status_check');

        // Drop new columns
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->dropColumn(['rejected_at', 'rejected_by']);
        });

        // Add old columns back
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->timestamp('violated_at')->nullable();
            $table->foreignId('violated_by')->nullable()->constrained('users')->onDelete('set null');
        });

        // Recreate unique constraint
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->unique(['branch_id', 'periode', 'bulan', 'tahun'], 'unique_declaration_per_period');
        });

        // Recreate CHECK constraint with old values
        DB::statement("ALTER TABLE risk_free_declarations ADD CONSTRAINT risk_free_declarations_status_check CHECK (status IN ('active', 'violated', 'cancelled'))");

        // Update data back
        DB::statement("UPDATE risk_free_declarations SET status = 'violated' WHERE status = 'rejected'");
    }

    /**
     * SQLite: Reverse
     */
    protected function downSqlite(): void
    {
        $tempTable = 'risk_free_declarations_' . time();

        // Create temp table with old schema (anonymous unique index)
        Schema::create($tempTable, function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('periode', ['1', '2']);
            $table->tinyInteger('bulan');
            $table->year('tahun');
            $table->text('statement_text');
            $table->enum('status', ['active', 'violated', 'cancelled'])->default('active');
            $table->timestamp('violated_at')->nullable();
            $table->foreignId('violated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Anonymous unique index
            $table->unique(['branch_id', 'periode', 'bulan', 'tahun']);
        });

        // Copy data
        DB::statement("INSERT INTO {$tempTable} (id, branch_id, user_id, periode, bulan, tahun, statement_text, status, violated_at, violated_by, created_at, updated_at)
            SELECT id, branch_id, user_id, periode, bulan, tahun, statement_text,
                CASE WHEN status = 'rejected' THEN 'violated' ELSE status END,
                rejected_at, rejected_by, created_at, updated_at
            FROM risk_free_declarations");

        // Drop old table
        Schema::drop('risk_free_declarations');

        // Rename
        Schema::rename($tempTable, 'risk_free_declarations');
    }
};
