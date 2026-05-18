<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Mapping role name → role_category
        $roles = [
            'manrisk'  => 'admin',
            'korwil'   => 'viewer',
            'kacab'    => 'checker',
            'ca'       => 'maker',
            'teller'   => 'maker',
            'csr'      => 'maker',
            'security' => 'maker',
        ];

        foreach ($roles as $name => $category) {
            // Create or update role with correct role_category
            Role::updateOrCreate(
                ['name' => $name],
                ['role_category' => $category]
            );
        }


        $this->command->info('Hierarki Jabatan Perbankan berhasil disinkronisasi!');
    }
}