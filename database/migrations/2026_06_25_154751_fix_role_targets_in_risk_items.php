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
        // Update risk items role_target to match new uppercase role names
        \Illuminate\Support\Facades\DB::table('risk_items')->where('role_target', 'ca')->update(['role_target' => 'CUSTOMER ASSISTANT']);
        \Illuminate\Support\Facades\DB::table('risk_items')->where('role_target', 'csr')->update(['role_target' => 'CUSTOMER SERVICE REPRESENTATIVE']);
        \Illuminate\Support\Facades\DB::table('risk_items')->where('role_target', 'kacab')->update(['role_target' => 'BRANCH MANAGER']);
        \Illuminate\Support\Facades\DB::table('risk_items')->where('role_target', 'teller')->update(['role_target' => 'TELLER']);
        \Illuminate\Support\Facades\DB::table('risk_items')->where('role_target', 'security')->update(['role_target' => 'SECURITY']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('risk_items')->where('role_target', 'CUSTOMER ASSISTANT')->update(['role_target' => 'ca']);
        \Illuminate\Support\Facades\DB::table('risk_items')->where('role_target', 'CUSTOMER SERVICE REPRESENTATIVE')->update(['role_target' => 'csr']);
        \Illuminate\Support\Facades\DB::table('risk_items')->where('role_target', 'BRANCH MANAGER')->update(['role_target' => 'kacab']);
        \Illuminate\Support\Facades\DB::table('risk_items')->where('role_target', 'TELLER')->update(['role_target' => 'teller']);
        \Illuminate\Support\Facades\DB::table('risk_items')->where('role_target', 'SECURITY')->update(['role_target' => 'security']);
    }
};
