<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $divisiRegional = Division::firstOrCreate(['kode_divisi' => 'REG', 'nama_divisi' => 'REGIONAL']);
        $divisiCompliance = Division::firstOrCreate(['kode_divisi' => 'COMP', 'nama_divisi' => 'COMPLIANCE']);

        // Mapping role name -> [category, kode, division_id]
        $roles = [
            'TELLER'                          => ['category' => 'maker',   'kode' => 'TL',  'divisi' => $divisiRegional->id],
            'CUSTOMER SERVICE REPRESENTATIVE' => ['category' => 'maker',   'kode' => 'CSR', 'divisi' => $divisiRegional->id],
            'CUSTOMER ASSISTANT'              => ['category' => 'maker',   'kode' => 'CA',  'divisi' => $divisiRegional->id],
            'BRANCH MANAGER'                  => ['category' => 'checker', 'kode' => 'BM',  'divisi' => $divisiRegional->id],
            'BRANCH SERVICE MANAGER'          => ['category' => 'checker', 'kode' => 'BSM', 'divisi' => $divisiRegional->id],
            'SECURITY'                        => ['category' => 'maker',   'kode' => 'SC',  'divisi' => $divisiRegional->id],
            
            // Peran Pusat & Pengawas
            'RISK MANAGEMENT'                 => ['category' => 'admin',   'kode' => 'RM',  'divisi' => $divisiCompliance->id],
            'REGIONAL HEAD'                   => ['category' => 'viewer',  'kode' => 'RH',  'divisi' => $divisiRegional->id],
        ];

        foreach ($roles as $name => $data) {
            Role::updateOrCreate(
                ['name' => $name],
                [
                    'role_category' => $data['category'],
                    'kode_role'     => $data['kode'],
                    'division_id'   => $data['divisi'],
                ]
            );
        }

        $this->command->info('Hierarki Jabatan Perbankan berhasil disinkronisasi! (dengan Divisi & Kode Role)');
    }
}