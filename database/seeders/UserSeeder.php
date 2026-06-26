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
        $branchPusat = Branch::where('kode_cabang', '000')->first()->id ?? Branch::first()->id;
        $branchDaerah = Branch::where('kode_cabang', '!=', '000')->first()->id ?? Branch::skip(1)->first()->id ?? $branchPusat;

        // Ambil UUID Divisi
        $divisiCompliance = \App\Models\Division::where('nama_divisi', 'COMPLIANCE')->first()->id ?? null;
        $divisiRegional = \App\Models\Division::where('nama_divisi', 'REGIONAL')->first()->id ?? null;

        // 1. DIVISI COMPLIANCE (Admin) - Biasanya di Pusat
        $manrisk = User::firstOrCreate(
            ['username' => 'ManRisk'],
            ['name' => 'HQ Risk Management', 'password' => $password, 'branch_id' => $branchPusat, 'division_id' => $divisiCompliance]
        );
        $manrisk->assignRole('RISK MANAGEMENT');

        // 2. DIVISI REGIONAL (Viewer & Checker)
        $regionalHead = User::firstOrCreate(
            ['username' => 'RH_dummy'],
            ['name' => 'Regional Head', 'password' => $password, 'branch_id' => $branchPusat, 'division_id' => $divisiRegional]
        );
        $regionalHead->assignRole('REGIONAL HEAD');

        $kacab = User::firstOrCreate(
            ['username' => 'BM_dummy'],
            ['name' => 'Branch Manager', 'password' => $password, 'branch_id' => $branchDaerah, 'division_id' => $divisiRegional]
        );
        $kacab->assignRole('BRANCH MANAGER');

        $kaop = User::firstOrCreate(
            ['username' => 'BSM_dummy'],
            ['name' => 'Branch Service Manager', 'password' => $password, 'branch_id' => $branchDaerah, 'division_id' => $divisiRegional]
        );
        $kaop->assignRole('BRANCH SERVICE MANAGER');

        // 3. STAFF / MAKERS
        $ca = User::firstOrCreate(
            ['username' => 'CA_dummy'],
            ['name' => 'Customer Assistant', 'password' => $password, 'branch_id' => $branchDaerah, 'division_id' => $divisiRegional]
        );
        $ca->assignRole('CUSTOMER ASSISTANT');

        $teller = User::firstOrCreate(
            ['username' => 'Teller_dummy'],
            ['name' => 'Akun Teller', 'password' => $password, 'branch_id' => $branchDaerah, 'division_id' => $divisiRegional]
        );
        $teller->assignRole('TELLER');

        $csr = User::firstOrCreate(
            ['username' => 'CSR_dummy'],
            ['name' => 'Akun CSR', 'password' => $password, 'branch_id' => $branchDaerah, 'division_id' => $divisiRegional]
        );
        $csr->assignRole('CUSTOMER SERVICE REPRESENTATIVE');

        $security = User::firstOrCreate(
            ['username' => 'Security_dummy'],
            ['name' => 'Akun Security', 'password' => $password, 'branch_id' => $branchDaerah, 'division_id' => $divisiRegional]
        );
        $security->assignRole('SECURITY');

        // --- ASSIGNMENT REGIONAL HEAD KE CABANG ---
        $cabangDaerah = Branch::find($branchDaerah);
        if ($cabangDaerah) {
            $cabangDaerah->update(['korwil_id' => $regionalHead->id]);
        }

        $this->command->info('Data Karyawan dan Assignment Korwil berhasil disuntik!');
    }
}
