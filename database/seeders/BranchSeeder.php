<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['id' => 1,     'kode_cabang' => '001', 'nama_cabang' => 'Cabang Abdurahman Saleh', 'nickname_cabang' => 'ARS'],
            ['id' => 2,     'kode_cabang' => '003', 'nama_cabang' => 'Cabang Cianjur',          'nickname_cabang' => 'CJR'],
            ['id' => 3,     'kode_cabang' => '004', 'nama_cabang' => 'Cabang Sumedang',         'nickname_cabang' => 'SMD'],
            ['id' => 4,     'kode_cabang' => '005', 'nama_cabang' => 'Cabang Garut',            'nickname_cabang' => 'GRT'],
            ['id' => 5,     'kode_cabang' => '006', 'nama_cabang' => 'Cabang Tasikmalaya',      'nickname_cabang' => 'TSK'],
            ['id' => 6,     'kode_cabang' => '007', 'nama_cabang' => 'Cabang Tuparev',          'nickname_cabang' => 'TPV'],
            ['id' => 7,     'kode_cabang' => '009', 'nama_cabang' => 'Cabang Jatibarang',       'nickname_cabang' => 'JTB'],
            ['id' => 8,     'kode_cabang' => '010', 'nama_cabang' => 'Cabang Majalengka',       'nickname_cabang' => 'MJK'],
            ['id' => 9,     'kode_cabang' => '012', 'nama_cabang' => 'Cabang Kopo',             'nickname_cabang' => 'KPO'],
            ['id' => 10,    'kode_cabang' => '013', 'nama_cabang' => 'Cabang Wastukencana',     'nickname_cabang' => 'WST'],
            ['id' => 11,    'kode_cabang' => '014', 'nama_cabang' => 'Cabang Rancaekek',        'nickname_cabang' => 'RCK'],
            ['id' => 12,    'kode_cabang' => '015', 'nama_cabang' => 'Cabang Leuwipanjang',     'nickname_cabang' => 'LWP'],
            ['id' => 13,    'kode_cabang' => '016', 'nama_cabang' => 'Cabang Kiaracondong',     'nickname_cabang' => 'KRC'],
            ['id' => 14,    'kode_cabang' => '017', 'nama_cabang' => 'Cabang Setiabudi',        'nickname_cabang' => 'STB'],
            ['id' => 15,    'kode_cabang' => '018', 'nama_cabang' => 'Cabang Sukabumi',         'nickname_cabang' => 'SKB'],
            ['id' => 16,    'kode_cabang' => '019', 'nama_cabang' => 'Cabang Purwakarta',       'nickname_cabang' => 'PWK'],
            ['id' => 17,    'kode_cabang' => '020', 'nama_cabang' => 'Cabang Katapang',         'nickname_cabang' => 'KTP'],
            ['id' => 18,    'kode_cabang' => '021', 'nama_cabang' => 'Cabang Subang',           'nickname_cabang' => 'SBG'],
            ['id' => 19,    'kode_cabang' => '025', 'nama_cabang' => 'Cabang Cimahi',           'nickname_cabang' => 'CMH'],
            ['id' => 20,    'kode_cabang' => '027', 'nama_cabang' => 'Cabang Sudirman 2',       'nickname_cabang' => 'SDM'],
            ['id' => 21,    'kode_cabang' => '028', 'nama_cabang' => 'Cabang Rencong',          'nickname_cabang' => 'RCG'],
            ['id' => 22,    'kode_cabang' => '029', 'nama_cabang' => 'Cabang Bekasi',           'nickname_cabang' => 'BKS'],
            ['id' => 23,    'kode_cabang' => '030', 'nama_cabang' => 'Cabang Karawang',         'nickname_cabang' => 'KRW'],
            ['id' => 24,    'kode_cabang' => '031', 'nama_cabang' => 'Cabang Bogor',            'nickname_cabang' => 'BGR'],
            ['id' => 25,    'kode_cabang' => '032', 'nama_cabang' => 'Cabang Cikarang',         'nickname_cabang' => 'CKR'],
            ['id' => 26,    'kode_cabang' => '033', 'nama_cabang' => 'Cabang BSD',              'nickname_cabang' => 'BSD'],
            ['id' => 27,    'kode_cabang' => '035', 'nama_cabang' => 'Cabang Kelapa Gading',    'nickname_cabang' => 'KGD'],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(['id' => $branch['id']], $branch);
        }

        $this->command->info('Daftar Cabang BPR berhasil disinkronisasi!');
    }
}