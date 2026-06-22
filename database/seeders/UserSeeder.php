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
            ['username' => 'manrisk_hq'],
            ['name' => 'HQ Manrisk', 'password' => $password, 'branch_id' => $branch1]
        );
        $manrisk->assignRole('manrisk');

        // 2. CHECKER TIER 2 (Korwil)
        $korwil = User::firstOrCreate(
            ['username' => 'korwil_jabar'],
            ['name' => 'Bapak Korwil', 'password' => $password, 'branch_id' => $branch1]
        );
        $korwil->assignRole('korwil');

        // 3. CHECKER TIER 1 (Kepala Cabang)
        $kacab = User::firstOrCreate(
            ['username' => 'kacab_sudirman'],
            ['name' => 'Kacab Sudirman', 'password' => $password, 'branch_id' => $branch2]
        );
        $kacab->assignRole('kacab');

        // 4. THE MAKERS (Customer Assistant, Teller, CSR, Security)
        $ca = User::firstOrCreate(
            ['username' => 'ca_sudirman'],
            ['name' => 'Customer Assistant', 'password' => $password, 'branch_id' => $branch2]
        );
        $ca->assignRole('ca');

        $teller = User::firstOrCreate(
            ['username' => 'teller_sudirman'],
            ['name' => 'Akun Teller', 'password' => $password, 'branch_id' => $branch2]
        );
        $teller->assignRole('teller');

        $csr = User::firstOrCreate(
            ['username' => 'csr_sudirman'],
            ['name' => 'Akun CSR', 'password' => $password, 'branch_id' => $branch2]
        );
        $csr->assignRole('csr');

        $security = User::firstOrCreate(
            ['username' => 'sec_sudirman'],
            ['name' => 'Akun Security', 'password' => $password, 'branch_id' => $branch2]
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
