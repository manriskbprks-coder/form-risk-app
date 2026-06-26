<?php

namespace App\Services;

use App\Domain\Enums\RiskReportStatus;
use App\Domain\Enums\RoleCategory;
use App\Models\Branch;
use App\Models\RiskFreeDeclaration;
use App\Models\RiskReport;
use App\Models\User;

class SummaryService
{
    /**
     * Hitung stat cards untuk dashboard berdasarkan role.
     *
     * @param User $user
     * @param string $roleCategory
     * @param array $branchIds
     * @param \Carbon\Carbon|null $dateFilter
     * @param \Carbon\Carbon|null $dateFilterEnd
     * @return array ['totalClosed', 'totalPending', 'totalLossApproved', 'totalInProgress', 'labelTotalLaporan']
     */
    public function getStatCards(User $user, string $roleCategory, array $branchIds, $dateFilter = null, $dateFilterEnd = null): array
    {
        $reportQuery = RiskReport::query();

        if (in_array($roleCategory, [RoleCategory::Viewer->value, RoleCategory::Admin->value])) {
            // Viewer & Admin: apply branch filter
            $reportQuery->whereIn('branch_id', $branchIds);
        } else {
            // Checker & Maker: apply Role Scope (yang sudah mengandung filter Cabang & Divisi)
            $reportQuery = app(\App\Services\RiskReportQueryService::class)->applyRoleScope($reportQuery, $user);
        }

        // Apply date filter jika ada
        if ($dateFilter && $dateFilterEnd) {
            $reportQuery->whereBetween('created_at', [$dateFilter, $dateFilterEnd]);
        }

        $totalClosed = (clone $reportQuery)
            ->where('status', RiskReportStatus::Closed->value)
            ->count();

        $totalPending = (clone $reportQuery)
            ->where(function ($q) {
                $q->whereIn('status', [
                    RiskReportStatus::PendingAtasan->value,
                    RiskReportStatus::PendingRevision->value,
                    RiskReportStatus::ApprovedInProgress->value
                ])
                ->orWhere('status', 'pending_korwil');
            })
            ->count();

        $totalLossApproved = (clone $reportQuery)
            ->whereIn('status', [
                RiskReportStatus::ApprovedInProgress->value,
                RiskReportStatus::Closed->value,
            ])
            ->where('kategori', 'finansial')
            ->sum('dampak_finansial');

        $totalInProgress = (clone $reportQuery)
            ->where('status', RiskReportStatus::ApprovedInProgress->value)
            ->count();

        $labelTotalLaporan = match($roleCategory) {
            RoleCategory::Maker->value => 'Laporan Saya (Closed)',
            RoleCategory::Checker->value => 'Laporan Cabang (Closed)',
            RoleCategory::Viewer->value => 'Laporan Wilayah (Closed)',
            default => 'Total Laporan (Closed)',
        };

        return compact(
            'totalClosed', 'totalPending', 'totalLossApproved',
            'totalInProgress', 'labelTotalLaporan'
        );
    }

    /**
     * Hitung pending count badge untuk checker/viewer.
     *
     * @param User $user
     * @param string $roleCategory
     * @param array $branchIds
     * @return int
     */
    public function getPendingCount(User $user, string $roleCategory, array $branchIds): int
    {
        if ($roleCategory === RoleCategory::Checker->value) {
            $query = RiskReport::whereIn('status', [
                RiskReportStatus::PendingAtasan->value,
                RiskReportStatus::PendingRevision->value,
                RiskReportStatus::ApprovedInProgress->value
            ]);
            return app(\App\Services\RiskReportQueryService::class)->applyRoleScope($query, $user)->count();
        } elseif ($roleCategory === RoleCategory::Viewer->value) {
            return RiskReport::whereIn('branch_id', $branchIds)
                ->where('status', 'pending_korwil')
                ->count();
        }

        return 0;
    }

    /**
     * Dapatkan laporan terbaru untuk dashboard.
     *
     * @param User $user
     * @param string $roleCategory
     * @param array $branchIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentReports(User $user, string $roleCategory, array $branchIds)
    {
        $query = RiskReport::with(['user', 'branch', 'item'])->latest()->take(5);

        if (in_array($roleCategory, [RoleCategory::Viewer->value, RoleCategory::Admin->value])) {
            return $query->whereIn('branch_id', $branchIds)->get();
        }

        // Checker & Maker: apply Role Scope (yang sudah mengandung filter Cabang & Divisi)
        return app(\App\Services\RiskReportQueryService::class)->applyRoleScope($query, $user)->get();
    }

    /**
     * Dapatkan ringkasan per cabang (khusus ManRisk).
     *
     * @param \Illuminate\Support\Collection $allBranches
     * @param array $bulanFilters Array format "Y-m", contoh ["2026-05", "2026-04"]. Kosong = semua waktu (10 tahun).
     * @param array $branchIds Filter cabang tertentu. Kosong = semua cabang dari $allBranches.
     * @return array ['branchSummaries' => [], 'branchChartLabels' => [], 'branchChartData' => [], 'branchChartColors' => []]
     */
    public function getBranchSummaries($allBranches, array $bulanFilters = [], array $branchIds = []): array
    {
        $branchSummaries = [];
        $branchChartLabels = [];
        $branchChartData = [];
        $branchChartColors = [];

        // Filter cabang: jika $branchIds tidak kosong, filter $allBranches
        if (!empty($branchIds)) {
            $allBranches = $allBranches->whereIn('id', $branchIds);
        }

        // Parse bulan filter — ambil range dari bulan paling awal ke paling akhir
        if (!empty($bulanFilters)) {
            $sortedMonths = collect($bulanFilters)->sort();
            $dateStart = \Carbon\Carbon::parse($sortedMonths->first() . '-01')->startOfMonth();
            $dateEnd = \Carbon\Carbon::parse($sortedMonths->last() . '-01')->endOfMonth();
        } else {
            $dateStart = now()->subYears(10);
            $dateEnd = now();
        }

        // OPTIMASI: 1 bulk query dengan SUM(CASE WHEN...) untuk semua metric per cabang
        $branchIdsList = $allBranches->pluck('id')->toArray();

        $branchStats = RiskReport::selectRaw("
            branch_id,
            COUNT(*) as total,
            SUM(CASE WHEN status IN ('pending_atasan','pending_korwil') THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved_in_progress' THEN 1 ELSE 0 END) as approved_in_progress,
            SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
            SUM(CASE WHEN status IN ('approved_in_progress','closed') AND kategori = 'finansial' THEN dampak_finansial ELSE 0 END) as kerugian
        ")
            ->whereIn('branch_id', $branchIdsList)
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $maxTotal = 0;

        foreach ($allBranches as $branch) {
            $stats = $branchStats->get($branch->id);
            $total = $stats ? (int) $stats->total : 0;
            $pending = $stats ? (int) $stats->pending : 0;
            $approved = $stats ? (int) $stats->approved_in_progress : 0;
            $closed = $stats ? (int) $stats->closed : 0;
            $kerugian = $stats ? (int) $stats->kerugian : 0;

            if ($total > $maxTotal) $maxTotal = $total;

            $branchSummaries[] = [
                'nama' => $branch->nama_cabang,
                'total' => $total,
                'pending' => $pending,
                'approved' => $approved,
                'closed' => $closed,
                'kerugian' => $kerugian,
            ];

            $branchChartLabels[] = $branch->nickname_cabang ?? $branch->nama_cabang;
            $branchChartData[] = $total;
        }

        // Generate warna gradasi biru
        $count = count($branchChartData);
        for ($i = 0; $i < $count; $i++) {
            $ratio = $count > 1 ? $i / ($count - 1) : 0.5;
            $alpha = 0.4 + (0.6 * (1 - $ratio));
            $branchChartColors[] = "rgba(99, 102, 241, {$alpha})";
        }

        return compact('branchSummaries', 'branchChartLabels', 'branchChartData', 'branchChartColors');
    }

    /**
     * Dapatkan ringkasan deklarasi nihil risiko per cabang (khusus ManRisk).
     *
     * @param \Illuminate\Support\Collection $allBranches
     * @param array $bulanFilters Array format "Y-m", contoh ["2026-05", "2026-04"]. Kosong = bulan saat ini.
     * @param array $branchIds Filter cabang tertentu. Kosong = semua cabang dari $allBranches.
     * @return array ['deklarasiSummaries' => []]
     */
    public function getDeklarasiSummaries($allBranches, array $bulanFilters = [], array $branchIds = []): array
    {
        $deklarasiSummaries = [];

        // Filter cabang: jika $branchIds tidak kosong, filter $allBranches
        if (!empty($branchIds)) {
            $allBranches = $allBranches->whereIn('id', $branchIds);
        }

        // Parse bulan filter — ambil bulan terakhir dari array (default ke bulan saat ini)
        if (!empty($bulanFilters)) {
            $sortedMonths = collect($bulanFilters)->sort();
            $lastMonth = $sortedMonths->last();
            $bulanCarbon = \Carbon\Carbon::parse($lastMonth . '-01');
            $bulanIni = (int) $bulanCarbon->month;
            $tahunIni = (int) $bulanCarbon->year;
        } else {
            $bulanIni = now()->month;
            $tahunIni = now()->year;
        }

        // OPTIMASI: Bulk fetch semua deklarasi bulan ini (1 query)
        $branchIdsList = $allBranches->pluck('id')->toArray();

        $allDeklarasi = RiskFreeDeclaration::whereIn('branch_id', $branchIdsList)
            ->where('bulan', $bulanIni)
            ->where('tahun', $tahunIni)
            ->get()
            ->groupBy('branch_id');

        // OPTIMASI: Bulk cek cabang mana yang ada laporan bulan ini (1 query)
        $branchesWithReports = RiskReport::whereIn('branch_id', $branchIdsList)
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->select('branch_id')
            ->distinct()
            ->pluck('branch_id')
            ->toArray();
        
        $cabangBelumDeklarasi = [];

        $allBranches = $allBranches->sortBy('kode_cabang');

        foreach ($allBranches as $branch) {
            $branchDeklarasi = $allDeklarasi->get($branch->id, collect());

            $periode1 = $branchDeklarasi->contains('periode', 1);
            $periode2 = $branchDeklarasi->contains('periode', 2);
            $total = $branchDeklarasi->count();

            // Rejected = ada laporan risiko di cabang ini bulan ini, tapi kacab deklarasi nihil
            $adaLaporan = in_array($branch->id, $branchesWithReports);
            $rejected = $adaLaporan && $total > 0;

            if (!$adaLaporan && $total == 0) {
                $cabangBelumDeklarasi[] = $branch->kode_cabang . ' ' . $branch->nama_cabang;
            }

            $deklarasiSummaries[] = [
                'nama' => $branch->nama_cabang,
                'periode1' => $periode1,
                'periode2' => $periode2,
                'total' => $total,
                'rejected' => $rejected,
            ];
        }

        return compact('deklarasiSummaries', 'cabangBelumDeklarasi');
    }
}
