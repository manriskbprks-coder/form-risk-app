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
     * Hitung nomor urut berikutnya di bulan ini secara atomik (thread-safe).
     */
    private function getNextSequence(): string
    {
        $cacheKey = 'laporan_seq_' . now()->format('Ym');
        
        // Atomic increment, aman dari Race Condition
        // Expire cache 35 hari untuk memastikan awal bulan aman kereset
        $count = \Illuminate\Support\Facades\Cache::remember($cacheKey . '_init', 60 * 60 * 24 * 35, function() {
            // Sinkronisasi dengan database HANYA JIKA cache kosong (misal server restart)
            return RiskReport::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();
        });

        $nextVal = \Illuminate\Support\Facades\Cache::increment($cacheKey);
        
        // Jika cache increment belum ada isinya, kita set dengan nilai DB + 1
        if ($nextVal === 1 && $count > 0) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, $count + 1, 60 * 60 * 24 * 35);
            $nextVal = $count + 1;
        }

        return str_pad($nextVal, 4, '0', STR_PAD_LEFT);
    }
}
