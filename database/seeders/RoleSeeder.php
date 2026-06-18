<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Divisi Operasional (default untuk semua role saat ini)
        $divisiOp = Division::firstOrCreate(
            ['kode_divisi' => 'OP'],
            ['nama_divisi' => 'Operasional']
        );

        // Mapping role name → [role_category, kode_role, division_id]
        $roles = [
            'manrisk'  => ['category' => 'admin',   'kode' => 'MR'],
            'korwil'   => ['category' => 'viewer',  'kode' => 'KW'],
            'kacab'    => ['category' => 'checker', 'kode' => 'KC'],
            'ca'       => ['category' => 'maker',   'kode' => 'CA'],
            'teller'   => ['category' => 'maker',   'kode' => 'TL'],
            'csr'      => ['category' => 'maker',   'kode' => 'CSR'],
            'security' => ['category' => 'maker',   'kode' => 'SC'],
        ];

        foreach ($roles as $name => $data) {
            Role::updateOrCreate(
                ['name' => $name],
                [
                    'role_category' => $data['category'],
                    'kode_role'     => $data['kode'],
                    'division_id'   => $divisiOp->id,
                ]
            );
        }

        $this->command->info('Hierarki Jabatan Perbankan berhasil disinkronisasi! (dengan Divisi & Kode Role)');
    }
}