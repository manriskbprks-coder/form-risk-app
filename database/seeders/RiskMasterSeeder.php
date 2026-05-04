<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiskItem;
use App\Models\RiskCause;
use App\Models\RiskMitigation;

class RiskMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. KAMUS MITIGASI (Versi Clean & Terkalibrasi)
        $mitigasiKamus = [
            'Tidak ada / lemahnya double check' => 'Meningkatkan double check',
            'Lalai: Karena tidak paham' => 'Coaching',
            'Lalai: Karena volume banyak / beban kerja banyak' => 'Penilaian kembali volume / beban kerja',
            'Lalai: Karena lupa / tidak sengaja' => 'Atasan melakukan arahan / pemberitahuan',
            'Software belum ada / masih manual' => 'Pengadaan Software baru',
            'Itikad buruk karyawan' => 'Melakukan teguran / punishment',
            'Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja' => 'Meningkatkan komunikasi, koordinasi dan kerja sama di unit kerja',
            'Banyaknya volume / beban kerja' => 'Penilaian kembali volume / beban kerja',
            'Tidak terampil dan cekatan' => 'Coaching',
            'Peralatan pendukung operasional rusak / tidak memadai' => 'Pengadaan / perbaikan peralatan pendukung operasional agar memadai',
            'Software sudah usang' => 'Pengadaan Software baru',
            'Bugs (Salah Pemrograman)' => 'Perbaikan program',
            'Kabel jaringan rusak / tidak memadai' => 'Memperbaiki kabel yang rusak / tidak memadai',
            'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank' => 'Coaching',
            'Tidak ada motivasi, tidak bersemangat kerja' => 'Meningkatkan semangat kerja menjadi lebih baik',
            'Lemahnya pengawasan dan kontrol atasan' => 'Meningkatkan pengawasan dan kontrol atasan',
            'Aktivitas kriminal eksternal' => 'Meningkatkan keamanan terhadap aktivitas kriminal eksternal',
            'Lemahnya pengawasan dan kontrol karyawan' => 'Meningkatkan pengawasan dan kontrol karyawan',
            'Tidak patuh terhadap prosedur / kebijakan' => 'Teguran / Punishment',
        ];

        // 2. KAMUS SUMBER RISIKO (Mapping penyebab -> sumber risiko)
        $sumberRisikoKamus = [
            'Tidak ada / lemahnya double check' => 'proses_internal',
            'Lalai: Karena tidak paham' => 'manusia',
            'Lalai: Karena volume banyak / beban kerja banyak' => 'manusia',
            'Lalai: Karena lupa / tidak sengaja' => 'manusia',
            'Software belum ada / masih manual' => 'sistem_teknologi',
            'Itikad buruk karyawan' => 'manusia',
            'Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja' => 'proses_internal',
            'Banyaknya volume / beban kerja' => 'proses_internal',
            'Tidak terampil dan cekatan' => 'manusia',
            'Peralatan pendukung operasional rusak / tidak memadai' => 'sistem_teknologi',
            'Software sudah usang' => 'sistem_teknologi',
            'Bugs (Salah Pemrograman)' => 'sistem_teknologi',
            'Kabel jaringan rusak / tidak memadai' => 'sistem_teknologi',
            'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank' => 'manusia',
            'Tidak ada motivasi, tidak bersemangat kerja' => 'manusia',
            'Lemahnya pengawasan dan kontrol atasan' => 'proses_internal',
            'Aktivitas kriminal eksternal' => 'faktor_eksternal',
            'Lemahnya pengawasan dan kontrol karyawan' => 'proses_internal',
            'Tidak patuh terhadap prosedur / kebijakan' => 'manusia',
        ];

        // 3. KAMUS SUMBER RISIKO UNTUK ITEM (Berdasarkan nama risiko)
        $itemSumberRisikoKamus = [
            // TELLER - Finansial
            'Selisih kurang pada perhitungan uang kas besar teller' => 'manusia',
            'Salah input data transaksi' => 'manusia',
            'Saldo ATM / CRM melebihi saldo yang dapat diasuransikan' => 'proses_internal',
            'Selisih kurang pada perhitungan uang kas kecil teller' => 'manusia',
            'Potensi Risiko Finansial Lainnya' => 'manusia',
            // TELLER - Non-Finansial
            'Meningkatnya keluhan nasabah mengenai sikap kurang ramah' => 'manusia',
            'Adanya tanda tangan nasabah pada formulir yang masih kosong' => 'proses_internal',
            'Aplikasi Smart Branch System (SBS) gangguan' => 'sistem_teknologi',
            'Adanya keluhan nasabah mengenai layanan' => 'manusia',
            'Potensi Risiko Non-Finansial Lainnya' => 'manusia',
            // CA - Finansial
            'Salah perhitungan insentif pencairan kredit' => 'manusia',
            'Selisih kurang pada perhitungan jumlah materai' => 'manusia',
            'Salah menentukan biaya provisi' => 'manusia',
            'Salah menentukan bunga pencairan kredit' => 'manusia',
            'Potensi risiko kerugian finansial lainnya' => 'manusia',
            // CA - Non-Finansial
            'Adanya keluhan nasabah mengenai pelayanan' => 'manusia',
            'Aplikasi Web Internal KS gangguan' => 'sistem_teknologi',
            'Adanya tanda tangan nasabah pada formulir yang masih kosong' => 'proses_internal',
            'Aplikasi T24 gangguan' => 'sistem_teknologi',
            'Potensi risiko kerugian non finansial lainnya' => 'manusia',
            // KACAB - Finansial
            'Salah perhitungan insentif pencairan kredit' => 'manusia',
            'Selisih kurang pada perhitungan jumlah materai' => 'manusia',
            'Selisih kurang pada perhitungan uang kas besar teller' => 'manusia',
            'Saldo ATM / CRM melebihi saldo yang dapat diasuransikan' => 'proses_internal',
            'Potensi Risiko Finansial Lainnya' => 'manusia',
            // KACAB - Non-Finansial
            'Adanya tanda tangan nasabah pada formulir yang masih kosong' => 'proses_internal',
            'Aplikasi Web Internal KS gangguan' => 'sistem_teknologi',
            'Adanya keluhan nasabah mengenai pelayanan' => 'manusia',
            'Aplikasi T24 gangguan' => 'sistem_teknologi',
            'Potensi risiko kerugian non finansial lainnya' => 'manusia',
            // CSR - Finansial
            'Salah menentukan biaya provisi' => 'manusia',
            'Salah menentukan bunga pencairan kredit' => 'manusia',
            'Salah menentukan bunga deposito' => 'manusia',
            'Salah menentukan biaya fidusia' => 'manusia',
            'Potensi risiko kerugian finansial lainnya' => 'manusia',
            // CSR - Non-Finansial
            'Adanya keluhan nasabah mengenai pelayanan' => 'manusia',
            'Meningkatnya keluhan nasabah mengenai sikap kurang ramah' => 'manusia',
            'Aplikasi T24 gangguan' => 'sistem_teknologi',
            'Meningkatnya keluhan nasabah mengenai ketidakpahaman produk' => 'manusia',
            'Potensi risiko kerugian non finansial lainnya' => 'manusia',
            // SECURITY - Finansial
            'Tidak melakukan pengawalan ketika melakukan replenish ATM/CRM' => 'manusia',
            'Adanya pencurian / perampokan uang tunai' => 'faktor_eksternal',
            'Adanya skimming di ATM / CRM' => 'faktor_eksternal',
            'Tidak melakukan pengamanan sesuai dengan ketentuan' => 'manusia',
            'Potensi risiko kerugian finansial lainnya' => 'manusia',
            // SECURITY - Non-Finansial
            'Adanya keluhan nasabah mengenai pelayanan' => 'manusia',
            'Sering meninggalkan tempat kerja pada saat jam kantor' => 'manusia',
            'Meningkatnya keluhan nasabah mengenai sikap kurang ramah' => 'manusia',
            'Meningkatnya keluhan nasabah mengenai ketidakpahaman produk' => 'manusia',
            'Potensi risiko kerugian non finansial lainnya' => 'manusia',
        ];
            
        // 4. DATA ITEM & PENYEBAB (Digabung: TELLER & KACAB)
        $masterData = [
            // ==========================================
            // SOAL UNTUK TELLER
            // ==========================================
            [
                'nama_risiko' => 'Selisih kurang pada perhitungan uang kas besar teller',
                'role_target' => 'teller',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Itikad buruk karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Salah input data transaksi',
                'role_target' => 'teller',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Itikad buruk karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Saldo ATM / CRM melebihi saldo yang dapat diasuransikan',
                'role_target' => 'teller',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja'
                ]
            ],
            [
                'nama_risiko' => 'Selisih kurang pada perhitungan uang kas kecil teller',
                'role_target' => 'teller',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Itikad buruk karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Potensi Risiko Finansial Lainnya',
                'role_target' => 'teller',
                'causes' => [] 
            ],
            // ==========================================
            // SOAL NON-FINANSIAL UNTUK TELLER
            // ==========================================
            [
                'nama_risiko' => 'Meningkatnya keluhan nasabah mengenai sikap kurang ramah',
                'kategori' => 'non-finansial',
                'role_target' => 'teller',
                'causes' => [
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Itikad buruk karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Adanya tanda tangan nasabah pada formulir yang masih kosong',
                'kategori' => 'non-finansial',
                'role_target' => 'teller',
                'causes' => [
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Itikad buruk karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Aplikasi Smart Branch System (SBS) gangguan',
                'kategori' => 'non-finansial',
                'role_target' => 'teller',
                'causes' => [
                    'Software sudah usang',
                    'Kabel jaringan rusak / tidak memadai',
                    'Bugs (Salah Pemrograman)'
                ]
            ],
            [
                'nama_risiko' => 'Adanya keluhan nasabah mengenai layanan',
                'kategori' => 'non-finansial',
                'role_target' => 'teller',
                'causes' => [
                    'Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja',
                    'Banyaknya volume / beban kerja',
                    'Tidak terampil dan cekatan',
                    'Peralatan pendukung operasional rusak / tidak memadai'
                ]
            ],
            [
                'nama_risiko' => 'Potensi Risiko Non-Finansial Lainnya',
                'kategori' => 'non-finansial',
                'role_target' => 'teller',
                'causes' => [] 
            ],

            // ==========================================
            // SOAL FINANSIAL UNTUK CUSTOMER ASSISTANT (CA)
            // ==========================================
            [
                'nama_risiko' => 'Salah perhitungan insentif pencairan kredit',
                'kategori' => 'finansial',
                'role_target' => 'ca',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena tidak paham',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Software belum ada / masih manual'
                ]
            ],
            [
                'nama_risiko' => 'Selisih kurang pada perhitungan jumlah materai',
                'kategori' => 'finansial',
                'role_target' => 'ca',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Itikad buruk karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Salah menentukan biaya provisi',
                'kategori' => 'finansial',
                'role_target' => 'ca',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja'
                ]
            ],
            [
                'nama_risiko' => 'Salah menentukan bunga pencairan kredit',
                'kategori' => 'finansial',
                'role_target' => 'ca',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja'
                ]
            ],
            [
                'nama_risiko' => 'Potensi risiko kerugian finansial lainnya',
                'kategori' => 'finansial',
                'role_target' => 'ca',
                'causes' => [] 
            ],

            // ==========================================
            // SOAL NON-FINANSIAL UNTUK CUSTOMER ASSISTANT (CA)
            // ==========================================
            [
                'nama_risiko' => 'Adanya keluhan nasabah mengenai pelayanan',
                'kategori' => 'non-finansial',
                'role_target' => 'ca',
                'causes' => [
                    'Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja',
                    'Banyaknya volume / beban kerja',
                    'Tidak terampil dan cekatan',
                    'Peralatan pendukung operasional rusak / tidak memadai'
                ]
            ],
            [
                'nama_risiko' => 'Aplikasi Web Internal KS gangguan',
                'kategori' => 'non-finansial',
                'role_target' => 'ca',
                'causes' => [
                    'Software sudah usang',
                    'Kabel jaringan rusak / tidak memadai',
                    'Bugs (Salah Pemrograman)'
                ]
            ],
            [
                'nama_risiko' => 'Adanya tanda tangan nasabah pada formulir yang masih kosong',
                'kategori' => 'non-finansial',
                'role_target' => 'ca',
                'causes' => [
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Itikad buruk karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Aplikasi T24 gangguan',
                'kategori' => 'non-finansial',
                'role_target' => 'ca',
                'causes' => [
                    'Software sudah usang',
                    'Kabel jaringan rusak / tidak memadai',
                    'Bugs (Salah Pemrograman)'
                ]
            ],
            [
                'nama_risiko' => 'Potensi risiko kerugian non finansial lainnya',
                'kategori' => 'non-finansial',
                'role_target' => 'ca',
                'causes' => [] 
            ],

            // ==========================================
            // SOAL UNTUK KACAB (Sesuai Gambar Lu)
            // ==========================================
            [
                'nama_risiko' => 'Salah perhitungan insentif pencairan kredit',
                'role_target' => 'kacab',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena tidak paham',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Software belum ada / masih manual'
                ]
            ],
            [
                'nama_risiko' => 'Selisih kurang pada perhitungan jumlah materai',
                'role_target' => 'kacab',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Itikad buruk karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Selisih kurang pada perhitungan uang kas besar teller',
                'role_target' => 'kacab',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Itikad buruk karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Saldo ATM / CRM melebihi saldo yang dapat diasuransikan',
                'role_target' => 'kacab',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja'
                ]
            ],
            [
                'nama_risiko' => 'Potensi Risiko Finansial Lainnya',
                'role_target' => 'kacab',
                'causes' => [] 
            ],
            // ==========================================
            // SOAL NON-FINANSIAL UNTUK KACAB (BRANCH MANAGER)
            // ==========================================
            [
                'nama_risiko' => 'Adanya tanda tangan nasabah pada formulir yang masih kosong',
                'kategori' => 'non-finansial',
                'role_target' => 'kacab',
                'causes' => [
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Itikad buruk karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Aplikasi Web Internal KS gangguan',
                'kategori' => 'non-finansial',
                'role_target' => 'kacab',
                'causes' => [
                    'Software sudah usang',
                    'Kabel jaringan rusak / tidak memadai',
                    'Bugs (Salah Pemrograman)'
                ]
            ],
            [
                'nama_risiko' => 'Adanya keluhan nasabah mengenai pelayanan',
                'kategori' => 'non-finansial',
                'role_target' => 'kacab',
                'causes' => [
                    'Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja',
                    'Banyaknya volume / beban kerja',
                    'Tidak terampil dan cekatan',
                    'Peralatan pendukung operasional rusak / tidak memadai'
                ]
            ],
            [
                'nama_risiko' => 'Aplikasi T24 gangguan',
                'kategori' => 'non-finansial',
                'role_target' => 'kacab',
                'causes' => [
                    'Software sudah usang',
                    'Kabel jaringan rusak / tidak memadai',
                    'Bugs (Salah Pemrograman)'
                ]
            ],
            [
                'nama_risiko' => 'Potensi risiko kerugian non finansial lainnya',
                'kategori' => 'non-finansial',
                'role_target' => 'kacab',
                'causes' => [] 
            ],
            // ==========================================
            // SOAL FINANSIAL UNTUK CSR
            // ==========================================
            [
                'nama_risiko' => 'Salah menentukan biaya provisi',
                'kategori' => 'finansial',
                'role_target' => 'csr',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank'
                ]
            ],
            [
                'nama_risiko' => 'Salah menentukan bunga pencairan kredit',
                'kategori' => 'finansial',
                'role_target' => 'csr',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank'
                ]
            ],
            [
                'nama_risiko' => 'Salah menentukan bunga deposito',
                'kategori' => 'finansial',
                'role_target' => 'csr',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank'
                ]
            ],
            [
                'nama_risiko' => 'Salah menentukan biaya fidusia',
                'kategori' => 'finansial',
                'role_target' => 'csr',
                'causes' => [
                    'Tidak ada / lemahnya double check',
                    'Lalai: Karena volume banyak / beban kerja banyak',
                    'Lalai: Karena lupa / tidak sengaja',
                    'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank'
                ]
            ],
            [
                'nama_risiko' => 'Potensi risiko kerugian finansial lainnya',
                'kategori' => 'finansial',
                'role_target' => 'csr',
                'causes' => [] 
            ],

            // ==========================================
            // SOAL NON-FINANSIAL UNTUK CSR
            // ==========================================
            [
                'nama_risiko' => 'Adanya keluhan nasabah mengenai pelayanan',
                'kategori' => 'non-finansial',
                'role_target' => 'csr',
                'causes' => [
                    'Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja',
                    'Banyaknya volume / beban kerja',
                    'Tidak terampil dan cekatan',
                    'Peralatan pendukung operasional rusak / tidak memadai',
                    'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank'
                ]
            ],
            [
                'nama_risiko' => 'Meningkatnya keluhan nasabah mengenai sikap kurang ramah',
                'kategori' => 'non-finansial',
                'role_target' => 'csr',
                'causes' => [
                    'Banyaknya volume / beban kerja',
                    'Tidak ada motivasi, tidak bersemangat kerja'
                ]
            ],
            [
                'nama_risiko' => 'Aplikasi T24 gangguan',
                'kategori' => 'non-finansial',
                'role_target' => 'csr',
                'causes' => [
                    'Software sudah usang',
                    'Kabel jaringan rusak / tidak memadai',
                    'Bugs (Salah Pemrograman)'
                ]
            ],
            [
                'nama_risiko' => 'Meningkatnya keluhan nasabah mengenai ketidakpahaman produk',
                'kategori' => 'non-finansial',
                'role_target' => 'csr',
                'causes' => [
                    'Lemahnya pengawasan dan kontrol atasan',
                    'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank'
                ]
            ],
            [
                'nama_risiko' => 'Potensi risiko kerugian non finansial lainnya',
                'kategori' => 'non-finansial',
                'role_target' => 'csr',
                'causes' => [] 
            ],
            // ==========================================
            // SOAL FINANSIAL UNTUK SECURITY
            // ==========================================
            [
                'nama_risiko' => 'Tidak melakukan pengawalan ketika melakukan replenish ATM/CRM',
                'kategori' => 'finansial',
                'role_target' => 'security',
                'causes' => [
                    'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank',
                    'Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja',
                    'Banyaknya volume / beban kerja'
                ]
            ],
            [
                'nama_risiko' => 'Adanya pencurian / perampokan uang tunai',
                'kategori' => 'finansial',
                'role_target' => 'security',
                'causes' => [
                    'Aktivitas kriminal eksternal',
                    'Itikad buruk karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Adanya skimming di ATM / CRM',
                'kategori' => 'finansial',
                'role_target' => 'security',
                'causes' => [
                    'Aktivitas kriminal eksternal',
                    'Itikad buruk karyawan',
                    'Lemahnya pengawasan dan kontrol karyawan'
                ]
            ],
            [
                'nama_risiko' => 'Tidak melakukan pengamanan sesuai dengan ketentuan',
                'kategori' => 'finansial',
                'role_target' => 'security',
                'causes' => [
                    'Tidak patuh terhadap prosedur / kebijakan',
                    'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank',
                    'Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja'
                ]
            ],
            [
                'nama_risiko' => 'Potensi risiko kerugian finansial lainnya',
                'kategori' => 'finansial',
                'role_target' => 'security',
                'causes' => [] 
            ],

            // ==========================================
            // SOAL NON-FINANSIAL UNTUK SECURITY
            // ==========================================
            [
                'nama_risiko' => 'Adanya keluhan nasabah mengenai pelayanan',
                'kategori' => 'non-finansial',
                'role_target' => 'security',
                'causes' => [
                    'Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja',
                    'Banyaknya volume / beban kerja',
                    'Tidak terampil dan cekatan',
                    'Peralatan pendukung operasional rusak / tidak memadai',
                    'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank'
                ]
            ],
            [
                'nama_risiko' => 'Sering meninggalkan tempat kerja pada saat jam kantor',
                'kategori' => 'non-finansial',
                'role_target' => 'security',
                'causes' => [
                    'Lemahnya pengawasan dan kontrol atasan',
                    'Tidak ada motivasi, tidak bersemangat kerja'
                ]
            ],
            [
                'nama_risiko' => 'Meningkatnya keluhan nasabah mengenai sikap kurang ramah',
                'kategori' => 'non-finansial',
                'role_target' => 'security',
                'causes' => [
                    'Banyaknya volume / beban kerja',
                    'Tidak ada motivasi, tidak bersemangat kerja'
                ]
            ],
            [
                'nama_risiko' => 'Meningkatnya keluhan nasabah mengenai ketidakpahaman produk',
                'kategori' => 'non-finansial',
                'role_target' => 'security',
                'causes' => [
                    'Lemahnya pengawasan dan kontrol atasan',
                    'Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank'
                ]
            ],
            [
                'nama_risiko' => 'Potensi risiko kerugian non finansial lainnya',
                'kategori' => 'non-finansial',
                'role_target' => 'security',
                'causes' => [] 
            ]
        ];

        // 5. EKSEKUSI PENYUNTIKAN
        foreach ($masterData as $data) {
            // 1. Insert Item dan pastikan kategori ikut dimasukkan
            $item = RiskItem::create([
                'nama_risiko' => $data['nama_risiko'],
                'role_target' => $data['role_target'],
                'kategori'    => $data['kategori'] ?? 'finansial',
                'sumber_risiko' => $itemSumberRisikoKamus[$data['nama_risiko']] ?? 'manusia',
            ]);

            // 2. Insert Causes & Mitigations
            if (isset($data['causes'])) {
                foreach ($data['causes'] as $penyebab) {
                    $cause = RiskCause::create([
                        'risk_item_id' => $item->id,
                        'penyebab' => $penyebab,
                        'sumber_risiko' => $sumberRisikoKamus[$penyebab] ?? 'manusia',
                    ]);

                    // Cek di kamus, ada nggak mitigasi otomatisnya?
                    if (array_key_exists($penyebab, $mitigasiKamus)) {
                        RiskMitigation::create([
                            'risk_cause_id' => $cause->id,
                            'mitigasi' => $mitigasiKamus[$penyebab],
                        ]);
                    }
                }
            }
        }
        
        $this->command->info('Kamus Risiko & Mitigasi Cerdas berhasil diisi dengan sumber risiko!');
    }
}
