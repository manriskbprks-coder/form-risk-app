<?php

namespace App\Domain\Rules;

/**
 * Domain rule untuk deklarasi nihil risiko.
 *
 * Rule ini PURE LOGIC — tidak ada dependency ke Laravel, database, atau service.
 * Mengatur logika periode, jabatan wajib, dan validasi deklarasi.
 *
 * Analogi: Ini kayak "SOP deklarasi nihil" — aturan mainnya aja,
 * tanpa tau gimana cara nyimpen datanya.
 */
class DeclarationRule
{
    /**
     * Daftar jabatan yang wajib dideklarasikan setiap periode.
     */
    private const JABATAN_LIST = ['Teller', 'CA', 'CS', 'Security', 'Kacab'];

    /**
     * Tentukan periode saat ini berdasarkan tanggal.
     *
     * Periode 1: tanggal 1-14
     * Periode 2: tanggal 15-akhir bulan
     *
     * @param int $day Tanggal (1-31)
     * @return string '1' atau '2'
     */
    public function getCurrentPeriode(int $day): string
    {
        return $day <= 14 ? '1' : '2';
    }

    /**
     * Dapatkan daftar jabatan yang wajib dideklarasikan.
     *
     * @return array<int, string>
     */
    public function getJabatanList(): array
    {
        return self::JABATAN_LIST;
    }

    /**
     * Hitung tanggal awal dan akhir untuk suatu periode.
     *
     * @param string $periode '1' atau '2'
     * @param int $bulan Bulan (1-12)
     * @param int $tahun Tahun (4 digit)
     * @return array{start: string, end: string} Format 'Y-m-d'
     */
    public function getPeriodeDateRange(string $periode, int $bulan, int $tahun): array
    {
        $startDay = $periode === '1' ? 1 : 15;
        $endDay = $periode === '1' ? 14 : cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        return [
            'start' => "{$tahun}-{$bulan}-{$startDay}",
            'end' => "{$tahun}-{$bulan}-{$endDay}",
        ];
    }

    /**
     * Validasi bahwa deklarasi belum pernah dibuat untuk periode ini.
     *
     * @param bool $alreadyExists Apakah sudah ada deklarasi untuk periode ini
     * @throws \DomainException Jika sudah ada deklarasi
     */
    public function validateNoDuplicateDeclaration(bool $alreadyExists): void
    {
        if ($alreadyExists) {
            throw new \DomainException(
                'Deklarasi nihil risiko untuk periode ini sudah pernah dibuat.'
            );
        }
    }

    /**
     * Validasi bahwa deklarasi belum pernah ditolak sebelumnya.
     *
     * @param bool $alreadyRejected Apakah sudah pernah ditolak
     * @throws \DomainException Jika sudah ditolak
     */
    public function validateNotAlreadyRejected(bool $alreadyRejected): void
    {
        if ($alreadyRejected) {
            throw new \DomainException('Deklarasi ini sudah ditolak sebelumnya.');
        }
    }

    /**
     * Cek apakah ada laporan risiko di periode tertentu.
     * (Pure logic — tinggal return boolean, eksekusi query-nya di Service)
     *
     * @param bool $hasReports Apakah ada laporan di periode tersebut
     * @return bool
     */
    public function canDeclarationBeApproved(bool $hasReports): bool
    {
        // Jika ada laporan risiko di periode ini, deklarasi nihil harus ditolak
        return !$hasReports;
    }
}
