<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RiskCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Transaksi & Operasional',
            'Hardware & Infrastruktur IT',
            'Software & Aplikasi',
            'Jaringan & Komunikasi',
            'Sumber Daya Manusia (SDM)',
            'Regulasi & Kepatuhan',
            'Keamanan Fisik',
            'Lainnya'
        ];

        foreach ($categories as $cat) {
            \App\Models\RiskCategory::firstOrCreate(['nama_kategori' => $cat]);
        }
    }
}
