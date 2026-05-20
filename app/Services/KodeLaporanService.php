<?php

namespace App\Services;

use App\Models\RiskReport;
use App\Models\User;

class KodeLaporanService
{
    /**
     * Mapping role name ke kode singkat untuk kode laporan.
     */
    protected array $roleMap = [
        'teller' => 'TL',
        'ca' => 'CA',
        'csr' => 'CS',
        'security' => 'SC',
        'kacab' => 'KC',
    ];

    /**
     * Generate kode laporan dengan format:
     * RISK-{kodeCabang}{kodeRole}-{YYYYMM}-{0001}
     */
    public function generate(User $user): string
    {
        $kodeCabang = $user->branch->kode_cabang ?? 'HQ';
        $kodeRole = $this->roleMap[$user->primaryRoleName()] ?? 'XX';
        $tahunBulan = now()->format('Ym');
        $nomorUrut = $this->getNextSequence();

        return "RISK-{$kodeCabang}{$kodeRole}-{$tahunBulan}-{$nomorUrut}";
    }

    /**
     * Hitung nomor urut berikutnya di bulan ini.
     */
    private function getNextSequence(): string
    {
        $count = RiskReport::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}
