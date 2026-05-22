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
        } elseif ($roleCategory === RoleCategory::Checker->value) {
            $reportQuery->where('branch_id', $user->branch_id);
        } elseif ($roleCategory === RoleCategory::Maker->value) {
            $reportQuery->where('user_id', $user->id);
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
                $q->where('status', RiskReportStatus::PendingKacab->value)
                  ->orWhere('status', 'pending_korwil');
            })
            ->count();

        $totalLossApproved = (clone $reportQuery)
            ->whereIn('status', [
                RiskReportStatus::ApprovedStatus->value,
                RiskReportStatus::InProgress->value,
                RiskReportStatus::Closed->value,
            ])
            ->where('kategori', 'finansial')
            ->sum('dampak_finansial');

        $totalInProgress = (clone $reportQuery)
            ->where('status', RiskReportStatus::InProgress->value)
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
            return RiskReport::where('branch_id', $user->branch_id)
                ->where('status', RiskReportStatus::PendingKacab->value)
                ->count();
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
        if (in_array($roleCategory, [RoleCategory::Viewer->value, RoleCategory::Checker->value, RoleCategory::Admin->value])) {
            return RiskReport::with(['user', 'branch', 'item'])
                ->whereIn('branch_id', $branchIds)
                ->latest()
                ->take(10)
                ->get();
        }

        // Maker — lihat laporan sendiri
        return RiskReport::with(['user', 'branch', 'item'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();
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

        $maxTotal = 0;

        foreach ($allBranches as $branch) {
            $query = RiskReport::where('branch_id', $branch->id)
                ->whereBetween('created_at', [$dateStart, $dateEnd]);
            $total = (clone $query)->count();
            $pending = (clone $query)->whereIn('status', [RiskReportStatus::PendingKacab->value, 'pending_korwil'])->count();
            $approved = (clone $query)->where('status', RiskReportStatus::ApprovedStatus->value)->count();
            $closed = (clone $query)->where('status', RiskReportStatus::Closed->value)->count();
            $kerugian = (clone $query)->whereIn('status', [
                RiskReportStatus::ApprovedStatus->value,
                RiskReportStatus::InProgress->value,
                RiskReportStatus::Closed->value,
            ])->where('kategori', 'finansial')->sum('dampak_finansial');
            $inProgress = (clone $query)->where('status', RiskReportStatus::InProgress->value)->count();

            if ($total > $maxTotal) $maxTotal = $total;

            $branchSummaries[] = [
                'nama' => $branch->nama_cabang,
                'total' => $total,
                'pending' => $pending,
                'approved' => $approved,
                'in_progress' => $inProgress,
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

        foreach ($allBranches as $branch) {
            $periode1 = RiskFreeDeclaration::where('branch_id', $branch->id)
                ->where('periode', 1)
                ->where('bulan', $bulanIni)
                ->where('tahun', $tahunIni)
                ->exists();

            $periode2 = RiskFreeDeclaration::where('branch_id', $branch->id)
                ->where('periode', 2)
                ->where('bulan', $bulanIni)
                ->where('tahun', $tahunIni)
                ->exists();

            $total = RiskFreeDeclaration::where('branch_id', $branch->id)
                ->where('bulan', $bulanIni)
                ->where('tahun', $tahunIni)
                ->count();

            // Rejected = ada laporan risiko approved di cabang ini bulan ini, tapi kacab deklarasi nihil
            $adaLaporanApproved = RiskReport::where('branch_id', $branch->id)
                ->where('status', RiskReportStatus::ApprovedStatus->value)
                ->whereMonth('created_at', $bulanIni)
                ->whereYear('created_at', $tahunIni)
                ->exists();

            $rejected = $adaLaporanApproved && $total > 0;

            $deklarasiSummaries[] = [
                'nama' => $branch->nama_cabang,
                'periode1' => $periode1,
                'periode2' => $periode2,
                'total' => $total,
                'rejected' => $rejected,
            ];
        }

        return compact('deklarasiSummaries');
    }
}
