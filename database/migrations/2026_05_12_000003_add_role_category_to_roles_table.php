<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add role_category to roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->enum('role_category', ['maker', 'checker', 'viewer', 'admin'])
                  ->default('maker')
                  ->after('guard_name');
        });

        // 2. Seed existing roles with their categories
        $mapping = [
            'manrisk' => 'admin',
            'korwil' => 'viewer',
            'kacab' => 'checker',
            'teller' => 'maker',
            'ca' => 'maker',
            'csr' => 'maker',
            'security' => 'maker',
        ];

        foreach ($mapping as $roleName => $category) {
            DB::table('roles')->where('name', $roleName)->update(['role_category' => $category]);
        }

        // 3. Remove role_category from users table
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_category')) {
                $table->dropColumn('role_category');
            }
        });
    }

    public function down(): void
    {
        // 1. Add back role_category to users
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role_category', ['maker', 'checker', 'viewer', 'admin'])
                  ->default('maker')
                  ->after('branch_id');
        });

        // 2. Remove role_category from roles
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('role_category');
        });
    }
};
