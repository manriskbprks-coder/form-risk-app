<?php

namespace App\Services;

use App\Models\RiskItem;
use App\Models\RiskReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChartService
{
    /**
     * Generate data untuk chart tren laporan per bulan.
     *
     * @param array $branchIds
     * @param int $bulanTren
     * @return array ['chartMonths' => [], 'chartCounts' => []]
     */
    public function getTrenLaporan(array $branchIds, int $bulanTren): array
    {
        $chartMonths = [];
        $chartCounts = [];

        // OPTIMASI: 1 bulk query dengan driver detection
        $driver = DB::connection()->getDriverName();
        $dateFormat = match($driver) {
            'mysql' => "DATE_FORMAT(created_at, '%Y-%m')",
            'pgsql'  => "TO_CHAR(created_at, 'YYYY-MM')",
            default  => "strftime('%Y-%m', created_at)", // SQLite
        };

        $monthlyData = RiskReport::selectRaw("{$dateFormat} as bulan, COUNT(*) as total")
            ->whereIn('branch_id', $branchIds)
            ->where('created_at', '>=', now()->subMonths($bulanTren)->startOfMonth())
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        for ($i = $bulanTren - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartMonths[] = $month->format('M Y');
            $chartCounts[] = (int) ($monthlyData[$month->format('Y-m')] ?? 0);
        }

        return compact('chartMonths', 'chartCounts');
    }

    /**
     * Generate data untuk chart distribusi kategori (finansial vs non-finansial).
     *
     * @param array $branchIds
     * @return array ['chartFinansial' => int, 'chartNonFinansial' => int]
     */
    public function getDistribusiKategori(array $branchIds): array
    {
        return [
            'chartFinansial' => RiskReport::query()
                ->whereIn('branch_id', $branchIds)
                ->where('kategori', 'finansial')
                ->count(),
            'chartNonFinansial' => RiskReport::query()
                ->whereIn('branch_id', $branchIds)
                ->where('kategori', 'non-finansial')
                ->count(),
        ];
    }

    /**
     * Generate data untuk chart status tindak lanjut (pending_kacab, in_progress, closed).
     * Note: 'open' resolution status is now mapped to 'pending_kacab' in the unified status.
     * For the chart, we show reports that are pending_kacab (open), in_progress, or closed.
     *
     * @param array $branchIds
     * @return array ['chartOpen' => int, 'chartInProgress' => int, 'chartClosed' => int]
     */
    public function getStatusTindakLanjut(array $branchIds): array
    {
        return [
            'chartOpen' => RiskReport::query()
                ->whereIn('branch_id', $branchIds)
                ->where('status', 'pending_kacab')
                ->count(),
            'chartInProgress' => RiskReport::query()
                ->whereIn('branch_id', $branchIds)
                ->where('status', 'in_progress')
                ->count(),
            'chartClosed' => RiskReport::query()
                ->whereIn('branch_id', $branchIds)
                ->where('status', 'closed')
                ->count(),
        ];
    }

    /**
     * Generate data untuk chart Ranking Risiko (Top 10 horizontal bar).
     *
     * @param array $branchIds
     * @param \Carbon\Carbon $dateFilter
     * @return array ['rankingRisikoLabels' => [], 'rankingRisikoFullLabels' => [], 'rankingRisikoData' => [], 'rankingRisikoColors' => []]
     */
    public function getRankingRisiko(array $branchIds, $dateFilter): array
    {
        // OPTIMASI: JOIN risk_items langsung — ga perlu RiskItem::find() di loop
        $rankingRisiko = RiskReport::selectRaw('risk_reports.risk_item_id, COUNT(*) as total, risk_items.nama_risiko')
            ->join('risk_items', 'risk_reports.risk_item_id', '=', 'risk_items.id')
            ->whereIn('risk_reports.branch_id', $branchIds)
            ->whereNotNull('risk_reports.risk_item_id')
            ->where('risk_reports.created_at', '>=', $dateFilter)
            ->groupBy('risk_reports.risk_item_id', 'risk_items.nama_risiko')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $rankingRisikoLabels = [];
        $rankingRisikoFullLabels = [];
        $rankingRisikoData = [];
        $rankingRisikoColors = [];

        foreach ($rankingRisiko as $item) {
            $namaAsli = $item->nama_risiko ?? 'Risiko #' . $item->risk_item_id;
            $rankingRisikoLabels[] = Str::limit($namaAsli, 35);
            $rankingRisikoFullLabels[] = $namaAsli;
            $rankingRisikoData[] = (int) $item->total;
        }

        // Balik biar yang terbesar di atas (horizontal bar)
        $rankingRisikoLabels = array_reverse($rankingRisikoLabels);
        $rankingRisikoFullLabels = array_reverse($rankingRisikoFullLabels);
        $rankingRisikoData = array_reverse($rankingRisikoData);

        // Generate warna gradasi merah-hijau
        $count = count($rankingRisikoData);
        for ($i = 0; $i < $count; $i++) {
            $ratio = $count > 1 ? $i / ($count - 1) : 0;
            $r = round(239 - (239 - 34) * $ratio);
            $g = round(68 + (197 - 68) * $ratio);
            $b = round(68 + (94 - 68) * $ratio);
            $rankingRisikoColors[] = "rgba({$r}, {$g}, {$b}, 0.8)";
        }

        return compact(
            'rankingRisikoLabels', 'rankingRisikoFullLabels',
            'rankingRisikoData', 'rankingRisikoColors'
        );
    }

    /**
     * Generate data untuk chart Sumber Risiko (Doughnut).
     *
     * @param array $branchIds
     * @param \Carbon\Carbon $dateFilter
     * @return array ['sumberRisikoLabels' => [], 'sumberRisikoData' => [], 'sumberRisikoColors' => []]
     */
    public function getSumberRisiko(array $branchIds, $dateFilter): array
    {
        $sumberMapping = [
            'manusia'       => ['label' => 'Manusia',       'color' => '#ef4444'],
            'proses'        => ['label' => 'Proses Internal','color' => '#f97316'],
            'sistem'        => ['label' => 'Sistem Teknologi','color' => '#eab308'],
            'eksternal'     => ['label' => 'Faktor Eksternal','color' => '#22c55e'],
        ];

        $sumberQuery = RiskReport::selectRaw('COALESCE(risk_reports.sumber_risiko, risk_causes.sumber_risiko, risk_items.sumber_risiko, \'manusia\') as sumber_risiko_alias, COUNT(*) as total')
            ->join('risk_items', 'risk_reports.risk_item_id', '=', 'risk_items.id')
            ->leftJoin('risk_causes', 'risk_reports.risk_cause_id', '=', 'risk_causes.id')
            ->whereIn('risk_reports.branch_id', $branchIds)
            ->where('risk_reports.created_at', '>=', $dateFilter)
            ->groupBy('sumber_risiko_alias')
            ->orderByDesc('total')
            ->get();

        $sumberRisikoLabels = [];
        $sumberRisikoData = [];
        $sumberRisikoColors = [];

        foreach ($sumberQuery as $row) {
            $key = $row->sumber_risiko_alias;
            $label = $sumberMapping[$key]['label'] ?? ucfirst($key);
            $color = $sumberMapping[$key]['color'] ?? '#6b7280';
            $sumberRisikoLabels[] = $label;
            $sumberRisikoData[] = (int) $row->total;
            $sumberRisikoColors[] = $color;
        }

        return compact('sumberRisikoLabels', 'sumberRisikoData', 'sumberRisikoColors');
    }

    /**
     * Generate data untuk chart Tren Top-5 Risiko (Multi-line Chart).
     *
     * @param array $branchIds
     * @param \Carbon\Carbon $dateFilter
     * @param int $bulanTren
     * @return array ['trenTop5Labels' => [], 'trenTop5Datasets' => []]
     */
    public function getTrenTop5(array $branchIds, $dateFilter, int $bulanTren): array
    {
        // OPTIMASI: Query 1 — ambil top 5 risk items + nama_risiko langsung via JOIN
        $top5 = RiskReport::selectRaw('risk_reports.risk_item_id, risk_items.nama_risiko, COUNT(*) as total')
            ->join('risk_items', 'risk_reports.risk_item_id', '=', 'risk_items.id')
            ->whereIn('risk_reports.branch_id', $branchIds)
            ->whereNotNull('risk_reports.risk_item_id')
            ->where('risk_reports.created_at', '>=', $dateFilter)
            ->groupBy('risk_reports.risk_item_id', 'risk_items.nama_risiko')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $top5Ids = $top5->pluck('risk_item_id')->toArray();
        $riskNames = $top5->pluck('nama_risiko', 'risk_item_id');

        // Siapkan label bulan
        $trenTop5Labels = [];
        for ($i = $bulanTren - 1; $i >= 0; $i--) {
            $trenTop5Labels[] = now()->subMonths($i)->format('M Y');
        }

        // OPTIMASI: Query 2 — bulk monthly counts untuk semua top 5 risks
        $driver = DB::connection()->getDriverName();
        $dateFormat = match($driver) {
            'mysql' => "DATE_FORMAT(created_at, '%Y-%m')",
            'pgsql'  => "TO_CHAR(created_at, 'YYYY-MM')",
            default  => "strftime('%Y-%m', created_at)", // SQLite
        };

        $bulkData = RiskReport::selectRaw("risk_item_id, {$dateFormat} as bulan, COUNT(*) as total")
            ->whereIn('risk_item_id', $top5Ids)
            ->whereIn('branch_id', $branchIds)
            ->where('created_at', '>=', now()->subMonths($bulanTren)->startOfMonth())
            ->groupBy('risk_item_id', 'bulan')
            ->orderBy('risk_item_id')
            ->orderBy('bulan')
            ->get()
            ->groupBy('risk_item_id');

        $trenColors = ['#6366f1', '#ef4444', '#f97316', '#22c55e', '#eab308'];
        $trenTop5Datasets = [];
        $idx = 0;

        foreach ($top5Ids as $rid) {
            $nama = $riskNames[$rid] ?? 'Risiko #' . $rid;
            $monthlyMap = collect();
            if ($bulkData->has($rid)) {
                $monthlyMap = $bulkData->get($rid)->keyBy('bulan');
            }

            $data = [];
            for ($i = $bulanTren - 1; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $bulanKey = $month->format('Y-m');
                $data[] = (int) ($monthlyMap[$bulanKey]->total ?? 0);
            }

            $trenTop5Datasets[] = [
                'label' => $nama,
                'data' => $data,
                'borderColor' => $trenColors[$idx % count($trenColors)],
                'backgroundColor' => $trenColors[$idx % count($trenColors)] . '20',
                'tension' => 0.3,
                'fill' => false,
            ];
            $idx++;
        }

        return compact('trenTop5Labels', 'trenTop5Datasets');
    }
}
