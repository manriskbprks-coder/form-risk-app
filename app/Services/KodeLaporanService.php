<?php

namespace App\Services;

use App\Models\RiskReport;
use App\Models\User;

class KodeLaporanService
{
    /**
     * Generate kode laporan dengan format:
     * RISK-{kodeCabang}{kodeRole}-{YYYYMM}-{0001}
     *
     * kode_role sekarang diambil langsung dari kolom kode_role di tabel roles (database),
     * bukan dari hardcode mapping lagi.
     */
    public function generate(User $user): string
    {
        $kodeCabang = $user->branch->kode_cabang ?? 'HQ';

        // Ambil kode_role dari database (kolom baru di tabel roles)
        $role = $user->roles->first();
        $kodeRole = $role?->kode_role ?? 'XX';

        $tahunBulan = now()->format('Ym');
        $nomorUrut = $this->getNextSequence();

        return "RISK-{$kodeCabang}{$kodeRole}-{$tahunBulan}-{$nomorUrut}";
    }

    /**
     * Hitung nomor urut berikutnya di bulan ini.
     * Menggunakan query database secara langsung untuk menghindari isu Cache increment yang mengembalikan 0 atau false.
     */
    private function getNextSequence(): string
    {
        // Ambil laporan terakhir di bulan ini
        $lastReport = RiskReport::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastReport) {
            return '0001';
        }

        // Parse nomor urut dari kode_laporan terakhir (format: RISK-XXX-YYYYMM-0001)
        $parts = explode('-', $lastReport->kode_laporan);
        $lastSequenceStr = end($parts);
        
        // Coba konversi ke integer
        $lastSequence = (int) $lastSequenceStr;
        
        // Jika karena suatu alasan hasilnya 0 (misal format lama), kita fallback ke count
        if ($lastSequence === 0) {
            $count = RiskReport::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();
            $lastSequence = $count;
        }

        $nextVal = $lastSequence + 1;

        return str_pad($nextVal, 4, '0', STR_PAD_LEFT);
    }
}
