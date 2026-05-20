<?php

namespace App\Services;

use App\Models\RiskItem;
use App\Models\RiskReport;
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

        for ($i = $bulanTren - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartMonths[] = $month->format('M Y');
            $chartCounts[] = RiskReport::query()
                ->whereIn('branch_id', $branchIds)
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
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
     * Generate data untuk chart status tindak lanjut (open, in_progress, closed).
     *
     * @param array $branchIds
     * @return array ['chartOpen' => int, 'chartInProgress' => int, 'chartClosed' => int]
     */
    public function getStatusTindakLanjut(array $branchIds): array
    {
        return [
            'chartOpen' => RiskReport::query()
                ->whereIn('branch_id', $branchIds)
                ->where('resolution_status', 'open')
                ->count(),
            'chartInProgress' => RiskReport::query()
                ->whereIn('branch_id', $branchIds)
                ->where('resolution_status', 'in_progress')
                ->count(),
            'chartClosed' => RiskReport::query()
                ->whereIn('branch_id', $branchIds)
                ->where('resolution_status', 'closed')
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
        $rankingRisiko = RiskReport::selectRaw('risk_item_id, COUNT(*) as total')
            ->whereIn('branch_id', $branchIds)
            ->whereNotNull('risk_item_id')
            ->where('created_at', '>=', $dateFilter)
            ->groupBy('risk_item_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $rankingRisikoLabels = [];
        $rankingRisikoFullLabels = [];
        $rankingRisikoData = [];
        $rankingRisikoColors = [];

        foreach ($rankingRisiko as $item) {
            $riskItem = RiskItem::find($item->risk_item_id);
            $namaAsli = $riskItem?->nama_risiko ?? 'Risiko #' . $item->risk_item_id;
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
        $top5Ids = RiskReport::selectRaw('risk_item_id, COUNT(*) as total')
            ->whereIn('branch_id', $branchIds)
            ->whereNotNull('risk_item_id')
            ->where('created_at', '>=', $dateFilter)
            ->groupBy('risk_item_id')
            ->orderByDesc('total')
            ->take(5)
            ->pluck('risk_item_id');

        // Siapkan label bulan
        $trenTop5Labels = [];
        for ($i = $bulanTren - 1; $i >= 0; $i--) {
            $trenTop5Labels[] = now()->subMonths($i)->format('M Y');
        }

        $trenColors = ['#6366f1', '#ef4444', '#f97316', '#22c55e', '#eab308'];
        $trenTop5Datasets = [];
        $idx = 0;

        foreach ($top5Ids as $rid) {
            $riskItem = RiskItem::find($rid);
            $nama = $riskItem?->nama_risiko ?? 'Risiko #' . $rid;
            $data = [];
            for ($i = $bulanTren - 1; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $data[] = RiskReport::where('risk_item_id', $rid)
                    ->whereIn('branch_id', $branchIds)
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count();
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
