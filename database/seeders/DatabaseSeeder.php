<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,      // 1. Bangun kantor dulu
            RoleSeeder::class,        // 2. Tentukan jabatan
            UserSeeder::class,        // 3. Rekrut orangnya
            RiskMasterSeeder::class,  // 4. Kasih buku panduan risiko
            DummyRiskReportSeeder::class, // 5. Data dummy laporan risiko
        ]);
    }
}