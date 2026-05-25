<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // Ambil UUID Cabang secara dinamis
        $branch1 = Branch::first()->id;
        $branch2 = Branch::skip(1)->first()->id ?? $branch1;

        // 1. THE GOD TIER (System Admin / Manajemen Risiko)
        $manrisk = User::firstOrCreate(
            ['email' => 'manrisk@bpr.com'],
            ['name' => 'HQ Manrisk', 'username' => 'manrisk_hq', 'password' => $password, 'branch_id' => $branch1]
        );
        $manrisk->assignRole('manrisk');

        // 2. CHECKER TIER 2 (Korwil)
        $korwil = User::firstOrCreate(
            ['email' => 'korwil@bpr.com'],
            ['name' => 'Bapak Korwil', 'username' => 'korwil_jabar', 'password' => $password, 'branch_id' => $branch1]
        );
        $korwil->assignRole('korwil');

        // 3. CHECKER TIER 1 (Kepala Cabang)
        $kacab = User::firstOrCreate(
            ['email' => 'kacab@bpr.com'],
            ['name' => 'Kacab Sudirman', 'username' => 'kacab_sudirman', 'password' => $password, 'branch_id' => $branch2]
        );
        $kacab->assignRole('kacab');

        // 4. THE MAKERS (Customer Assistant, Teller, CSR, Security)
        $ca = User::firstOrCreate(
            ['email' => 'ca@bpr.com'],
            ['name' => 'Customer Assistant', 'username' => 'ca_sudirman', 'password' => $password, 'branch_id' => $branch2]
        );
        $ca->assignRole('ca');

        $teller = User::firstOrCreate(
            ['email' => 'teller@bpr.com'],
            ['name' => 'Akun Teller', 'username' => 'teller_sudirman', 'password' => $password, 'branch_id' => $branch2]
        );
        $teller->assignRole('teller');

        $csr = User::firstOrCreate(
            ['email' => 'csr@bpr.com'],
            ['name' => 'Akun CSR', 'username' => 'csr_sudirman', 'password' => $password, 'branch_id' => $branch2]
        );
        $csr->assignRole('csr');

        $security = User::firstOrCreate(
            ['email' => 'security@bpr.com'],
            ['name' => 'Akun Security', 'username' => 'sec_sudirman', 'password' => $password, 'branch_id' => $branch2]
        );
        $security->assignRole('security');

        // --- ASSIGNMENT KORWIL KE CABANG ---
        $cabangSudirman = Branch::find($branch2);
        if ($cabangSudirman) {
            $cabangSudirman->update(['korwil_id' => $korwil->id]);
        }

        $this->command->info('Data Karyawan dan Assignment Korwil berhasil disuntik!');
    }
}
