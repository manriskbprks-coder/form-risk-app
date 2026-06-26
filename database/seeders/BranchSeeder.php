<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['kode_cabang' => '000', 'nama_cabang' => 'Kantor Pusat',            'nickname_cabang' => 'PST'],
            ['kode_cabang' => '001', 'nama_cabang' => 'Cabang Abdurahman Saleh', 'nickname_cabang' => 'ARS'],
            ['kode_cabang' => '003', 'nama_cabang' => 'Cabang Cianjur',          'nickname_cabang' => 'CJR'],
            ['kode_cabang' => '004', 'nama_cabang' => 'Cabang Sumedang',         'nickname_cabang' => 'SMD'],
            ['kode_cabang' => '005', 'nama_cabang' => 'Cabang Garut',            'nickname_cabang' => 'GRT'],
            ['kode_cabang' => '006', 'nama_cabang' => 'Cabang Tasikmalaya',      'nickname_cabang' => 'TSK'],
            ['kode_cabang' => '007', 'nama_cabang' => 'Cabang Tuparev',          'nickname_cabang' => 'TPV'],
            ['kode_cabang' => '009', 'nama_cabang' => 'Cabang Jatibarang',       'nickname_cabang' => 'JTB'],
            ['kode_cabang' => '010', 'nama_cabang' => 'Cabang Majalengka',       'nickname_cabang' => 'MJK'],
            ['kode_cabang' => '012', 'nama_cabang' => 'Cabang Kopo',             'nickname_cabang' => 'KPO'],
            ['kode_cabang' => '013', 'nama_cabang' => 'Cabang Wastukencana',     'nickname_cabang' => 'WST'],
            ['kode_cabang' => '014', 'nama_cabang' => 'Cabang Rancaekek',        'nickname_cabang' => 'RCK'],
            ['kode_cabang' => '015', 'nama_cabang' => 'Cabang Leuwipanjang',     'nickname_cabang' => 'LWP'],
            ['kode_cabang' => '016', 'nama_cabang' => 'Cabang Kiaracondong',     'nickname_cabang' => 'KRC'],
            ['kode_cabang' => '017', 'nama_cabang' => 'Cabang Setiabudi',        'nickname_cabang' => 'STB'],
            ['kode_cabang' => '018', 'nama_cabang' => 'Cabang Sukabumi',         'nickname_cabang' => 'SKB'],
            ['kode_cabang' => '019', 'nama_cabang' => 'Cabang Purwakarta',       'nickname_cabang' => 'PWK'],
            ['kode_cabang' => '020', 'nama_cabang' => 'Cabang Katapang',         'nickname_cabang' => 'KTP'],
            ['kode_cabang' => '021', 'nama_cabang' => 'Cabang Subang',           'nickname_cabang' => 'SBG'],
            ['kode_cabang' => '025', 'nama_cabang' => 'Cabang Cimahi',           'nickname_cabang' => 'CMH'],
            ['kode_cabang' => '027', 'nama_cabang' => 'Cabang Sudirman 2',       'nickname_cabang' => 'SDM'],
            ['kode_cabang' => '028', 'nama_cabang' => 'Cabang Rencong',          'nickname_cabang' => 'RCG'],
            ['kode_cabang' => '029', 'nama_cabang' => 'Cabang Bekasi',           'nickname_cabang' => 'BKS'],
            ['kode_cabang' => '030', 'nama_cabang' => 'Cabang Karawang',         'nickname_cabang' => 'KRW'],
            ['kode_cabang' => '031', 'nama_cabang' => 'Cabang Bogor',            'nickname_cabang' => 'BGR'],
            ['kode_cabang' => '032', 'nama_cabang' => 'Cabang Cikarang',         'nickname_cabang' => 'CKR'],
            ['kode_cabang' => '033', 'nama_cabang' => 'Cabang BSD',              'nickname_cabang' => 'BSD'],
            ['kode_cabang' => '035', 'nama_cabang' => 'Cabang Kelapa Gading',    'nickname_cabang' => 'KGD'],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(['kode_cabang' => $branch['kode_cabang']], $branch);
        }

        $this->command->info('Daftar Cabang BPR berhasil disinkronisasi!');
    }
}