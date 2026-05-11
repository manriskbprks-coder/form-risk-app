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

        // 1. THE GOD TIER (System Admin / Manajemen Risiko) — Viewer
        $manrisk = User::firstOrCreate(
            ['email' => 'manrisk@bpr.com'],
            ['name' => 'HQ Manrisk', 'username' => 'manrisk_hq', 'password' => $password, 'branch_id' => 1, 'role_category' => 'viewer']
        );
        $manrisk->assignRole('manrisk');

        // 2. CHECKER TIER 2 (Korwil) — Viewer
        $korwil = User::firstOrCreate(
            ['email' => 'korwil@bpr.com'],
            ['name' => 'Bapak Korwil', 'username' => 'korwil_jabar', 'password' => $password, 'branch_id' => 1, 'role_category' => 'viewer']
        );
        $korwil->assignRole('korwil');

        // 3. CHECKER TIER 1 & MAKER (Kepala Cabang) — Checker
        $kacab = User::firstOrCreate(
            ['email' => 'kacab@bpr.com'],
            ['name' => 'Kacab Sudirman', 'username' => 'kacab_sudirman', 'password' => $password, 'branch_id' => 2, 'role_category' => 'checker']
        );
        $kacab->assignRole('kacab');

        // 4. THE MAKERS (Customer Assistant, Teller, CSR, Security) — Maker
        $ca = User::firstOrCreate(
            ['email' => 'ca@bpr.com'],
            ['name' => 'Customer Assistant', 'username' => 'ca_sudirman', 'password' => $password, 'branch_id' => 2, 'role_category' => 'maker']
        );
        $ca->assignRole('ca');

        $teller = User::firstOrCreate(
            ['email' => 'teller@bpr.com'],
            ['name' => 'Akun Teller', 'username' => 'teller_sudirman', 'password' => $password, 'branch_id' => 2, 'role_category' => 'maker']
        );
        $teller->assignRole('teller');

        $csr = User::firstOrCreate(
            ['email' => 'csr@bpr.com'],
            ['name' => 'Akun CSR', 'username' => 'csr_sudirman', 'password' => $password, 'branch_id' => 2, 'role_category' => 'maker']
        );
        $csr->assignRole('csr');

        $security = User::firstOrCreate(
            ['email' => 'security@bpr.com'],
            ['name' => 'Akun Security', 'username' => 'sec_sudirman', 'password' => $password, 'branch_id' => 2, 'role_category' => 'maker']
        );
        $security->assignRole('security');

        // --- ASSIGNMENT KORWIL KE CABANG ---
        // Kita set Bapak Korwil (ID yang baru kita bikin di atas) untuk mengawasi Cabang Sudirman (ID 2)
        $cabangSudirman = Branch::find(2);
        if ($cabangSudirman) {
            $cabangSudirman->update(['korwil_id' => $korwil->id]);
        }
        
        // Contoh kalau lu punya cabang lain (misal ID 3) yang mau dipegang Korwil ini juga:
        // $cabangCianjur = Branch::find(3);
        // if ($cabangCianjur) {
        //     $cabangCianjur->update(['korwil_id' => $korwil->id]);
        // }
        // -----------------------------------

        $this->command->info('Data Karyawan dan Assignment Korwil berhasil disuntik!');
    }
}