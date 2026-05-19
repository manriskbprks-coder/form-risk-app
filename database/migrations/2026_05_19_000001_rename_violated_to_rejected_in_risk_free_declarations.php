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
     * Uses raw SQL for SQLite compatibility (SQLite cannot drop columns with FK constraints).
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->upSqlite();
        } else {
            $this->upMysql();
        }
    }

    /**
     * MySQL/MariaDB/PostgreSQL: Standard ALTER TABLE approach
     */
    protected function upMysql(): void
    {
        // Drop unique index first
        Schema::table('risk_free_declarations', function (Blueprint $table) {
            $table->dropIndex('unique_declaration_per_period');
        });

        // Drop old columns (MySQL supports DROP COLUMN with FK)
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

        // Update enum values
        DB::statement("ALTER TABLE risk_free_declarations MODIFY COLUMN status ENUM('active', 'rejected', 'cancelled') DEFAULT 'active'");
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
        } else {
            $this->downMysql();
        }
    }

    /**
     * MySQL/MariaDB/PostgreSQL: Reverse
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
