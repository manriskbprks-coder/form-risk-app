<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['id' => 1, 'nama_cabang' => 'Cabang Pusat', 'kode_cabang' => 'PST'],
            ['id' => 2, 'nama_cabang' => 'Cabang Sudirman', 'kode_cabang' => 'SDM'],
            ['id' => 3, 'nama_cabang' => 'Cabang Cianjur', 'kode_cabang' => 'CJR'],
            ['id' => 4, 'nama_cabang' => 'Cabang Tasikmalaya', 'kode_cabang' => 'TSK'],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(['id' => $branch['id']], $branch);
        }

        $this->command->info('Daftar Cabang BPR berhasil disinkronisasi!');
    }
}