<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiskReport;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\RiskReportQueryService;

class ExportRiskReportController extends Controller
{
    public function __construct(
        protected RiskReportQueryService $riskReportQueryService,
    ) {}

    public function export(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->roleCategory()) {
            abort(403, 'Akses ditolak.');
        }

        $query = $this->riskReportQueryService->baseQuery();
        $query = $this->riskReportQueryService->applyRoleScope($query, $user);
        $query = $this->riskReportQueryService->applyFilters($query, $request, $user);

        $reports = $query->orderBy('created_at', 'desc')->get();

        // Catat aktivitas export ke log harian
        Log::channel('daily')->info('[AUDIT] User export CSV', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role_category' => $user->roleCategory(),
            'filename' => 'export-risiko-' . now()->format('Ymd-His') . '.csv',
            'total_reports' => $reports->count(),
            'filters' => $request->only(['search', 'branch_id', 'kategori', 'jabatan', 'start_date', 'end_date', 'resolution_status', 'approval_status']),
            'ip' => $request->ip(),
        ]);

        // Generate CSV
        $filename = 'export-risiko-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($reports) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, [
                'No',
                'Kode Laporan',
                'Tanggal Lapor',
                'Tanggal Kejadian',
                'Tanggal Diketahui',
                'Cabang',
                'Pelapor',
                'Jabatan',
                'Kategori',
                'Sumber Risiko',
                'Risiko',
                'Penyebab',
                'Mitigasi',
                'Kronologis Kejadian',
                'Dampak Finansial (Rp)',
                'Dampak Non-Finansial',
                'Skala Dampak',
                'Durasi Penyelesaian',
                'Durasi Satuan',
                'Status Approval',
                'Status Tindak Lanjut',
            ]);

            $no = 1;
            foreach ($reports as $report) {
                $mitigasiList = collect();
                if ($report->cause && $report->cause->mitigations->isNotEmpty()) {
                    $mitigasiList = $report->cause->mitigations->pluck('mitigasi');
                }
                if ($report->mitigasi_tambahan) {
                    $mitigasiList->push($report->mitigasi_tambahan);
                }

                $sumberRisiko = $report->sumber_risiko ?? $report->cause->sumber_risiko ?? $report->item->sumber_risiko ?? 'manusia';
                $sumberLabels = [
                    'manusia' => 'Manusia',
                    'proses_internal' => 'Proses Internal',
                    'sistem_teknologi' => 'Sistem Teknologi',
                    'faktor_eksternal' => 'Faktor Eksternal',
                ];

                fputcsv($handle, [
                    $no++,
                    $report->kode_laporan ?? '-',
                    $report->created_at->format('d/m/Y H:i'),
                    $report->tanggal_kejadian ? \Carbon\Carbon::parse($report->tanggal_kejadian)->format('d/m/Y') : '-',
                    $report->tanggal_diketahui ? \Carbon\Carbon::parse($report->tanggal_diketahui)->format('d/m/Y') : '-',
                    $report->branch->nama_cabang ?? 'HQ',
                    $report->user->name ?? '-',
                    $report->user?->primaryRoleName() ?? '-',
                    ucfirst($report->kategori),
                    $sumberLabels[$sumberRisiko] ?? 'Manusia',
                    $report->item->nama_risiko ?? $report->other_item_description,
                    $report->cause->penyebab ?? $report->other_cause_description,
                    $mitigasiList->implode('; '),
                    $report->kronologis_kejadian,
                    $report->kategori === 'finansial' ? number_format($report->dampak_finansial, 0, ',', '.') : '0',
                    $report->dampak_non_finansial ?? '-',
                    ucfirst($report->skala_dampak ?? '-'),
                    $report->durasi_penyelesaian ?? '-',
                    $report->durasi_satuan ?? '-',
                    $report->approval_status,
                    str_replace('_', ' ', $report->resolution_status ?? 'open'),
                ]);
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
