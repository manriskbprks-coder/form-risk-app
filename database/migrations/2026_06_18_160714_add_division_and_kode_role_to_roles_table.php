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
        Schema::table('roles', function (Blueprint $table) {
            $table->uuid('division_id')->nullable()->after('role_category');
            $table->string('kode_role', 5)->nullable()->after('division_id');

            $table->foreign('division_id')
                  ->references('id')
                  ->on('divisions')
                  ->nullOnDelete();
        });

        // Buat Divisi Operasional sebagai divisi default
        $divisiOperasional = \App\Models\Division::firstOrCreate(
            ['kode_divisi' => 'OP'],
            ['nama_divisi' => 'Operasional']
        );

        // Seed mapping: role_name => kode_role
        // Semua role yang ada saat ini masuk ke Divisi Operasional
        $roleMapping = [
            'teller'   => 'TL',
            'ca'       => 'CA',
            'csr'      => 'CSR',
            'security' => 'SC',
            'kacab'    => 'KC',
            'korwil'   => 'KW',
            'manrisk'  => 'MR',
        ];

        foreach ($roleMapping as $roleName => $kodeRole) {
            \Illuminate\Support\Facades\DB::table('roles')
                ->where('name', $roleName)
                ->update([
                    'division_id' => $divisiOperasional->id,
                    'kode_role'   => $kodeRole,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn(['division_id', 'kode_role']);
        });
    }
};
