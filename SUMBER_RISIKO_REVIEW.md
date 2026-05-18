# REVIEW SUMBER RISIKO

## Valid Values ENUM
- `manusia`
- `sistem_teknologi`
- `proses_internal`
- `faktor_eksternal`

---

## TABEL 1: RISK ITEMS (Sumber Risiko - Usulan Perbaikan)

| # | Role | Nama Risiko | Kategori | Sumber Risiko (Usulan) | Alasan |
|---|------|-------------|----------|------------------------|--------|
| 1 | teller | Selisih kurang pada perhitungan uang kas besar teller | finansial | manusia | Human error perhitungan kas |
| 2 | teller | Salah input data transaksi | finansial | manusia | Human error input |
| 3 | teller | Saldo ATM / CRM melebihi saldo yang dapat diasuransikan | finansial | manusia | Human error manajemen kas |
| 4 | teller | Selisih kurang pada perhitungan uang kas kecil teller | finansial | manusia | Human error perhitungan kas |
| 5 | teller | Potensi Risiko Finansial Lainnya | finansial | manusia | Catch-all finansial |
| 6 | teller | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | non-finansial | manusia | Terkait sikap/sdm (manusia) |
| 7 | teller | Adanya tanda tangan nasabah pada formulir yang masih kosong | non-finansial | manusia | Human error/kelalaian |
| 8 | teller | **Aplikasi Smart Branch System (SBS) gangguan** | non-finansial | **sistem_teknologi** | **Masalah software/aplikasi** |
| 9 | teller | Adanya keluhan nasabah mengenai layanan | non-finansial | proses_internal | Terkait proses layanan |
| 10 | teller | Potensi Risiko Non-Finansial Lainnya | non-finansial | proses_internal | Catch-all non-finansial |
| 11 | ca | Salah perhitungan insentif pencairan kredit | finansial | manusia | Human error perhitungan |
| 12 | ca | Selisih kurang pada perhitungan jumlah materai | finansial | manusia | Human error perhitungan |
| 13 | ca | Salah menentukan biaya provisi | finansial | manusia | Human error perhitungan |
| 14 | ca | Salah menentukan bunga pencairan kredit | finansial | manusia | Human error perhitungan |
| 15 | ca | Potensi risiko kerugian finansial lainnya | finansial | manusia | Catch-all finansial |
| 16 | ca | Adanya keluhan nasabah mengenai pelayanan | non-finansial | proses_internal | Terkait proses pelayanan |
| 17 | ca | **Aplikasi Web Internal KS gangguan** | non-finansial | **sistem_teknologi** | **Masalah software/aplikasi** |
| 18 | ca | Adanya tanda tangan nasabah pada formulir yang masih kosong | non-finansial | manusia | Human error/kelalaian |
| 19 | ca | **Aplikasi T24 gangguan** | non-finansial | **sistem_teknologi** | **Masalah software/aplikasi** |
| 20 | ca | Potensi risiko kerugian non finansial lainnya | non-finansial | proses_internal | Catch-all non-finansial |
| 21 | kacab | Salah perhitungan insentif pencairan kredit | finansial | manusia | Human error perhitungan |
| 22 | kacab | Selisih kurang pada perhitungan jumlah materai | finansial | manusia | Human error perhitungan |
| 23 | kacab | Selisih kurang pada perhitungan uang kas besar teller | finansial | manusia | Human error perhitungan |
| 24 | kacab | Saldo ATM / CRM melebihi saldo yang dapat diasuransikan | finansial | manusia | Human error manajemen kas |
| 25 | kacab | Potensi Risiko Finansial Lainnya | finansial | manusia | Catch-all finansial |
| 26 | kacab | Adanya tanda tangan nasabah pada formulir yang masih kosong | non-finansial | manusia | Human error/kelalaian |
| 27 | kacab | **Aplikasi Web Internal KS gangguan** | non-finansial | **sistem_teknologi** | **Masalah software/aplikasi** |
| 28 | kacab | Adanya keluhan nasabah mengenai pelayanan | non-finansial | proses_internal | Terkait proses pelayanan |
| 29 | kacab | **Aplikasi T24 gangguan** | non-finansial | **sistem_teknologi** | **Masalah software/aplikasi** |
| 30 | kacab | Potensi risiko kerugian non finansial lainnya | non-finansial | proses_internal | Catch-all non-finansial |
| 31 | csr | Salah menentukan biaya provisi | finansial | manusia | Human error perhitungan |
| 32 | csr | Salah menentukan bunga pencairan kredit | finansial | manusia | Human error perhitungan |
| 33 | csr | Salah menentukan bunga deposito | finansial | manusia | Human error perhitungan |
| 34 | csr | Salah menentukan biaya fidusia | finansial | manusia | Human error perhitungan |
| 35 | csr | Potensi risiko kerugian finansial lainnya | finansial | manusia | Catch-all finansial |
| 36 | csr | Adanya keluhan nasabah mengenai pelayanan | non-finansial | proses_internal | Terkait proses pelayanan |
| 37 | csr | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | non-finansial | manusia | Terkait sikap/sdm |
| 38 | csr | **Aplikasi T24 gangguan** | non-finansial | **sistem_teknologi** | **Masalah software/aplikasi** |
| 39 | csr | Meningkatnya keluhan nasabah mengenai ketidakpahaman produk | non-finansial | manusia | Terkait sdm/kurang paham |
| 40 | csr | Potensi risiko kerugian non finansial lainnya | non-finansial | proses_internal | Catch-all non-finansial |
| 41 | security | Tidak melakukan pengawalan ketika melakukan replenish ATM/CRM | finansial | manusia | Human error/sdm |
| 42 | security | Adanya pencurian / perampokan uang tunai | finansial | faktor_eksternal | Kriminal eksternal |
| 43 | security | Adanya skimming di ATM / CRM | finansial | faktor_eksternal | Kriminal eksternal |
| 44 | security | Tidak melakukan pengamanan sesuai dengan ketentuan | finansial | manusia | Human error/sdm |
| 45 | security | Potensi risiko kerugian finansial lainnya | finansial | manusia | Catch-all finansial |
| 46 | security | Adanya keluhan nasabah mengenai pelayanan | non-finansial | proses_internal | Terkait proses pelayanan |
| 47 | security | Sering meninggalkan tempat kerja pada saat jam kantor | non-finansial | manusia | Terkait sdm/disiplin |
| 48 | security | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | non-finansial | manusia | Terkait sikap/sdm |
| 49 | security | Meningkatnya keluhan nasabah mengenai ketidakpahaman produk | non-finansial | manusia | Terkait sdm/kurang paham |
| 50 | security | Potensi risiko kerugian non finansial lainnya | non-finansial | proses_internal | Catch-all non-finansial |

### Perubahan yang gua usul di Tabel 1:
| # | Item | Dari | Ke | Alasan |
|---|------|------|----|--------|
| 8 | Aplikasi Smart Branch System (SBS) gangguan | proses_internal | **sistem_teknologi** | Masalah software/aplikasi |
| 17 | Aplikasi Web Internal KS gangguan | proses_internal | **sistem_teknologi** | Masalah software/aplikasi |
| 19 | Aplikasi T24 gangguan | proses_internal | **sistem_teknologi** | Masalah software/aplikasi |
| 27 | Aplikasi Web Internal KS gangguan | proses_internal | **sistem_teknologi** | Masalah software/aplikasi |
| 29 | Aplikasi T24 gangguan | proses_internal | **sistem_teknologi** | Masalah software/aplikasi |
| 38 | Aplikasi T24 gangguan | proses_internal | **sistem_teknologi** | Masalah software/aplikasi |
| 42 | Adanya pencurian / perampokan uang tunai | manusia | **faktor_eksternal** | Kriminal dari luar |
| 43 | Adanya skimming di ATM / CRM | manusia | **faktor_eksternal** | Kriminal dari luar |
| 6 | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | proses_internal | **manusia** | Terkait sikap SDM |
| 7 | Adanya tanda tangan nasabah pada formulir yang masih kosong | proses_internal | **manusia** | Human error/kelalaian |
| 18 | Adanya tanda tangan nasabah pada formulir yang masih kosong | proses_internal | **manusia** | Human error/kelalaian |
| 26 | Adanya tanda tangan nasabah pada formulir yang masih kosong | proses_internal | **manusia** | Human error/kelalaian |
| 37 | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | proses_internal | **manusia** | Terkait sikap SDM |
| 39 | Meningkatnya keluhan nasabah mengenai ketidakpahaman produk | proses_internal | **manusia** | Terkait SDM |
| 47 | Sering meninggalkan tempat kerja pada saat jam kantor | proses_internal | **manusia** | Terkait disiplin SDM |
| 48 | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | proses_internal | **manusia** | Terkait sikap SDM |
| 49 | Meningkatnya keluhan nasabah mengenai ketidakpahaman produk | proses_internal | **manusia** | Terkait SDM |

---

## TABEL 2: RISK CAUSES (Sumber Risiko - Usulan Perbaikan)

| # | Risk Item | Penyebab | Sumber Risiko (Usulan) | Alasan |
|---|-----------|----------|------------------------|--------|
| 1 | Selisih kurang pada perhitungan uang kas besar teller | Tidak ada / lemahnya double check | manusia | Human error |
| 2 | Selisih kurang pada perhitungan uang kas besar teller | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 3 | Selisih kurang pada perhitungan uang kas besar teller | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 4 | Selisih kurang pada perhitungan uang kas besar teller | Itikad buruk karyawan | manusia | Human error |
| 5 | Salah input data transaksi | Tidak ada / lemahnya double check | manusia | Human error |
| 6 | Salah input data transaksi | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 7 | Salah input data transaksi | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 8 | Salah input data transaksi | Itikad buruk karyawan | manusia | Human error |
| 9 | Saldo ATM / CRM melebihi saldo yang dapat diasuransikan | Tidak ada / lemahnya double check | manusia | Human error |
| 10 | Saldo ATM / CRM melebihi saldo yang dapat diasuransikan | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 11 | Saldo ATM / CRM melebihi saldo yang dapat diasuransikan | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 12 | Selisih kurang pada perhitungan uang kas kecil teller | Tidak ada / lemahnya double check | manusia | Human error |
| 13 | Selisih kurang pada perhitungan uang kas kecil teller | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 14 | Selisih kurang pada perhitungan uang kas kecil teller | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 15 | Selisih kurang pada perhitungan uang kas kecil teller | Itikad buruk karyawan | manusia | Human error |
| 16 | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 17 | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 18 | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | Itikad buruk karyawan | manusia | Human error |
| 19 | Adanya tanda tangan nasabah pada formulir yang masih kosong | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 20 | Adanya tanda tangan nasabah pada formulir yang masih kosong | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 21 | Adanya tanda tangan nasabah pada formulir yang masih kosong | Itikad buruk karyawan | manusia | Human error |
| 22 | Aplikasi Smart Branch System (SBS) gangguan | Software sudah usang | sistem_teknologi | Masalah software |
| 23 | Aplikasi Smart Branch System (SBS) gangguan | Kabel jaringan rusak / tidak memadai | sistem_teknologi | Masalah infrastruktur |
| 24 | Aplikasi Smart Branch System (SBS) gangguan | Bugs (Salah Pemrograman) | sistem_teknologi | Masalah software |
| 25 | Adanya keluhan nasabah mengenai layanan | Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja | proses_internal | Proses internal |
| 26 | Adanya keluhan nasabah mengenai layanan | Banyaknya volume / beban kerja | proses_internal | Proses internal |
| 27 | Adanya keluhan nasabah mengenai layanan | Tidak terampil dan cekatan | manusia | Human error |
| 28 | Adanya keluhan nasabah mengenai layanan | Peralatan pendukung operasional rusak / tidak memadai | sistem_teknologi | Masalah hardware/fasilitas |
| 29 | Salah perhitungan insentif pencairan kredit | Tidak ada / lemahnya double check | manusia | Human error |
| 30 | Salah perhitungan insentif pencairan kredit | Lalai: Karena tidak paham | manusia | Human error |
| 31 | Salah perhitungan insentif pencairan kredit | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 32 | Salah perhitungan insentif pencairan kredit | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 33 | Salah perhitungan insentif pencairan kredit | Software belum ada / masih manual | sistem_teknologi | Masalah software |
| 34 | Selisih kurang pada perhitungan jumlah materai | Tidak ada / lemahnya double check | manusia | Human error |
| 35 | Selisih kurang pada perhitungan jumlah materai | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 36 | Selisih kurang pada perhitungan jumlah materai | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 37 | Selisih kurang pada perhitungan jumlah materai | Itikad buruk karyawan | manusia | Human error |
| 38 | Salah menentukan biaya provisi | Tidak ada / lemahnya double check | manusia | Human error |
| 39 | Salah menentukan biaya provisi | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 40 | Salah menentukan biaya provisi | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 41 | Salah menentukan bunga pencairan kredit | Tidak ada / lemahnya double check | manusia | Human error |
| 42 | Salah menentukan bunga pencairan kredit | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 43 | Salah menentukan bunga pencairan kredit | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 44 | Adanya keluhan nasabah mengenai pelayanan | Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja | proses_internal | Proses internal |
| 45 | Adanya keluhan nasabah mengenai pelayanan | Banyaknya volume / beban kerja | proses_internal | Proses internal |
| 46 | Adanya keluhan nasabah mengenai pelayanan | Tidak terampil dan cekatan | manusia | Human error |
| 47 | Adanya keluhan nasabah mengenai pelayanan | Peralatan pendukung operasional rusak / tidak memadai | sistem_teknologi | Masalah hardware/fasilitas |
| 48 | Aplikasi Web Internal KS gangguan | Software sudah usang | sistem_teknologi | Masalah software |
| 49 | Aplikasi Web Internal KS gangguan | Kabel jaringan rusak / tidak memadai | sistem_teknologi | Masalah infrastruktur |
| 50 | Aplikasi Web Internal KS gangguan | Bugs (Salah Pemrograman) | sistem_teknologi | Masalah software |
| 51 | Adanya tanda tangan nasabah pada formulir yang masih kosong | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 52 | Adanya tanda tangan nasabah pada formulir yang masih kosong | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 53 | Adanya tanda tangan nasabah pada formulir yang masih kosong | Itikad buruk karyawan | manusia | Human error |
| 54 | Aplikasi T24 gangguan | Software sudah usang | sistem_teknologi | Masalah software |
| 55 | Aplikasi T24 gangguan | Kabel jaringan rusak / tidak memadai | sistem_teknologi | Masalah infrastruktur |
| 56 | Aplikasi T24 gangguan | Bugs (Salah Pemrograman) | sistem_teknologi | Masalah software |
| 57 | Salah perhitungan insentif pencairan kredit | Tidak ada / lemahnya double check | manusia | Human error |
| 58 | Salah perhitungan insentif pencairan kredit | Lalai: Karena tidak paham | manusia | Human error |
| 59 | Salah perhitungan insentif pencairan kredit | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 60 | Salah perhitungan insentif pencairan kredit | Software belum ada / masih manual | sistem_teknologi | Masalah software |
| 61 | Selisih kurang pada perhitungan jumlah materai | Tidak ada / lemahnya double check | manusia | Human error |
| 62 | Selisih kurang pada perhitungan jumlah materai | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 63 | Selisih kurang pada perhitungan jumlah materai | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 64 | Selisih kurang pada perhitungan jumlah materai | Itikad buruk karyawan | manusia | Human error |
| 65 | Selisih kurang pada perhitungan uang kas besar teller | Tidak ada / lemahnya double check | manusia | Human error |
| 66 | Selisih kurang pada perhitungan uang kas besar teller | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 67 | Selisih kurang pada perhitungan uang kas besar teller | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 68 | Selisih kurang pada perhitungan uang kas besar teller | Itikad buruk karyawan | manusia | Human error |
| 69 | Saldo ATM / CRM melebihi saldo yang dapat diasuransikan | Tidak ada / lemahnya double check | manusia | Human error |
| 70 | Saldo ATM / CRM melebihi saldo yang dapat diasuransikan | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 71 | Saldo ATM / CRM melebihi saldo yang dapat diasuransikan | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 72 | Adanya tanda tangan nasabah pada formulir yang masih kosong | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 73 | Adanya tanda tangan nasabah pada formulir yang masih kosong | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 74 | Adanya tanda tangan nasabah pada formulir yang masih kosong | Itikad buruk karyawan | manusia | Human error |
| 75 | Aplikasi Web Internal KS gangguan | Software sudah usang | sistem_teknologi | Masalah software |
| 76 | Aplikasi Web Internal KS gangguan | Kabel jaringan rusak / tidak memadai | sistem_teknologi | Masalah infrastruktur |
| 77 | Aplikasi Web Internal KS gangguan | Bugs (Salah Pemrograman) | sistem_teknologi | Masalah software |
| 78 | Adanya keluhan nasabah mengenai pelayanan | Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja | proses_internal | Proses internal |
| 79 | Adanya keluhan nasabah mengenai pelayanan | Banyaknya volume / beban kerja | proses_internal | Proses internal |
| 80 | Adanya keluhan nasabah mengenai pelayanan | Tidak terampil dan cekatan | manusia | Human error |
| 81 | Adanya keluhan nasabah mengenai pelayanan | Peralatan pendukung operasional rusak / tidak memadai | sistem_teknologi | Masalah hardware/fasilitas |
| 82 | Aplikasi T24 gangguan | Software sudah usang | sistem_teknologi | Masalah software |
| 83 | Aplikasi T24 gangguan | Kabel jaringan rusak / tidak memadai | sistem_teknologi | Masalah infrastruktur |
| 84 | Aplikasi T24 gangguan | Bugs (Salah Pemrograman) | sistem_teknologi | Masalah software |
| 85 | Salah menentukan biaya provisi | Tidak ada / lemahnya double check | manusia | Human error |
| 86 | Salah menentukan biaya provisi | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 87 | Salah menentukan biaya provisi | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 88 | Salah menentukan biaya provisi | Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank | manusia | Human error |
| 89 | Salah menentukan bunga pencairan kredit | Tidak ada / lemahnya double check | manusia | Human error |
| 90 | Salah menentukan bunga pencairan kredit | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 91 | Salah menentukan bunga pencairan kredit | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 92 | Salah menentukan bunga pencairan kredit | Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank | manusia | Human error |
| 93 | Salah menentukan bunga deposito | Tidak ada / lemahnya double check | manusia | Human error |
| 94 | Salah menentukan bunga deposito | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 95 | Salah menentukan bunga deposito | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 96 | Salah menentukan bunga deposito | Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank | manusia | Human error |
| 97 | Salah menentukan biaya fidusia | Tidak ada / lemahnya double check | manusia | Human error |
| 98 | Salah menentukan biaya fidusia | Lalai: Karena volume banyak / beban kerja banyak | manusia | Human error |
| 99 | Salah menentukan biaya fidusia | Lalai: Karena lupa / tidak sengaja | manusia | Human error |
| 100 | Salah menentukan biaya fidusia | Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank | manusia | Human error |
| 101 | Adanya keluhan nasabah mengenai pelayanan | Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja | proses_internal | Proses internal |
| 102 | Adanya keluhan nasabah mengenai pelayanan | Banyaknya volume / beban kerja | proses_internal | Proses internal |
| 103 | Adanya keluhan nasabah mengenai pelayanan | Tidak terampil dan cekatan | manusia | Human error |
| 104 | Adanya keluhan nasabah mengenai pelayanan | Peralatan pendukung operasional rusak / tidak memadai | sistem_teknologi | Masalah hardware/fasilitas |
| 105 | Adanya keluhan nasabah mengenai pelayanan | Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank | manusia | Human error |
| 106 | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | Banyaknya volume / beban kerja | proses_internal | Proses internal |
| 107 | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | Tidak ada motivasi, tidak bersemangat kerja | manusia | Human error |
| 108 | Aplikasi T24 gangguan | Software sudah usang | sistem_teknologi | Masalah software |
| 109 | Aplikasi T24 gangguan | Kabel jaringan rusak / tidak memadai | sistem_teknologi | Masalah infrastruktur |
| 110 | Aplikasi T24 gangguan | Bugs (Salah Pemrograman) | sistem_teknologi | Masalah software |
| 111 | Meningkatnya keluhan nasabah mengenai ketidakpahaman produk | Lemahnya pengawasan dan kontrol atasan | proses_internal | Proses internal |
| 112 | Meningkatnya keluhan nasabah mengenai ketidakpahaman produk | Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank | manusia | Human error |
| 113 | Tidak melakukan pengawalan ketika melakukan replenish ATM/CRM | Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank | manusia | Human error |
| 114 | Tidak melakukan pengawalan ketika melakukan replenish ATM/CRM | Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja | proses_internal | Proses internal |
| 115 | Tidak melakukan pengawalan ketika melakukan replenish ATM/CRM | Banyaknya volume / beban kerja | proses_internal | Proses internal |
| 116 | Adanya pencurian / perampokan uang tunai | Aktivitas kriminal eksternal | faktor_eksternal | Kriminal dari luar |
| 117 | Adanya pencurian / perampokan uang tunai | Itikad buruk karyawan | manusia | Human error |
| 118 | Adanya skimming di ATM / CRM | Aktivitas kriminal eksternal | faktor_eksternal | Kriminal dari luar |
| 119 | Adanya skimming di ATM / CRM | Itikad buruk karyawan | manusia | Human error |
| 120 | Adanya skimming di ATM / CRM | Lemahnya pengawasan dan kontrol karyawan | proses_internal | Proses internal |
| 121 | Tidak melakukan pengamanan sesuai dengan ketentuan | Tidak patuh terhadap prosedur / kebijakan | manusia | Human error |
| 122 | Tidak melakukan pengamanan sesuai dengan ketentuan | Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank | manusia | Human error |
| 123 | Tidak melakukan pengamanan sesuai dengan ketentuan | Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja | proses_internal | Proses internal |
| 124 | Adanya keluhan nasabah mengenai pelayanan | Kurangnya komunikasi, koordinasi dan kerja sama di unit kerja | proses_internal | Proses internal |
| 125 | Adanya keluhan nasabah mengenai pelayanan | Banyaknya volume / beban kerja | proses_internal | Proses internal |
| 126 | Adanya keluhan nasabah mengenai pelayanan | Tidak terampil dan cekatan | manusia | Human error |
| 127 | Adanya keluhan nasabah mengenai pelayanan | Peralatan pendukung operasional rusak / tidak memadai | sistem_teknologi | Masalah hardware/fasilitas |
| 128 | Adanya keluhan nasabah mengenai pelayanan | Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank | manusia | Human error |
| 129 | Sering meninggalkan tempat kerja pada saat jam kantor | Lemahnya pengawasan dan kontrol atasan | proses_internal | Proses internal |
| 130 | Sering meninggalkan tempat kerja pada saat jam kantor | Tidak ada motivasi, tidak bersemangat kerja | manusia | Human error |
| 131 | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | Banyaknya volume / beban kerja | proses_internal | Proses internal |
| 132 | Meningkatnya keluhan nasabah mengenai sikap kurang ramah | Tidak ada motivasi, tidak bersemangat kerja | manusia | Human error |
| 133 | Meningkatnya keluhan nasabah mengenai ketidakpahaman produk | Lemahnya pengawasan dan kontrol atasan | proses_internal | Proses internal |
| 134 | Meningkatnya keluhan nasabah mengenai ketidakpahaman produk | Kurang pemahaman / penguasaan akan produk / prosedur / kebijakan bank | manusia | Human error |

---

## RINGKASAN PERUBAHAN YANG DIAJUKAN

### Risk Items (17 perubahan):
1. **3 item aplikasi** → dari `proses_internal` ke **`sistem_teknologi`**: SBS, Web KS, T24
2. **2 item kriminal** → dari `manusia` ke **`faktor_eksternal`**: pencurian, skimming
3. **12 item SDM/keluhan** → dari `proses_internal` ke **`manusia`**: keluhan sikap, tanda tangan kosong, ketidakpahaman, tinggalkan tempat

### Risk Causes (Tidak ada perubahan - mapping udah bener):
- Causes udah pakai mapping per penyebab yang sesuai

Silakan dicek bro, kalo ada yang kurang pas bilang aja nanti gua betulin.
