<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiskReport;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportRiskReportController extends Controller
{
    public function export(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->roleCategory()) {
            abort(403, 'Akses ditolak.');
        }

        $query = RiskReport::with(['user', 'item', 'cause.mitigations', 'branch']);

        // Filter by role_category
        if ($user->roleCategory() === 'checker') {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->roleCategory() === 'viewer') {
            // Viewer: hanya melihat cabang yang diawasi
            $branchIds = Branch::where('korwil_id', $user->id)->pluck('id');
            $query->whereIn('branch_id', $branchIds);
        } elseif ($user->roleCategory() === 'maker') {
            $query->where('user_id', $user->id);
        }

        // Apply filters from request
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_laporan', 'like', "%{$search}%")
                  ->orWhere('other_item_description', 'like', "%{$search}%")
                  ->orWhere('other_cause_description', 'like', "%{$search}%")
                  ->orWhere('kronologis_kejadian', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('item', function ($iq) use ($search) {
                      $iq->where('nama_risiko', 'like', "%{$search}%");
                  })
                  ->orWhereHas('cause', function ($cq) use ($search) {
                      $cq->where('penyebab', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('branch_id') && $user->roleCategory() === 'viewer') {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('jabatan')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('role_target', $request->jabatan);
            });
        }

        // Terima date_from/date_to (dari form) ATAU start_date/end_date (backward compatible)
        $dateFrom = $request->date_from ?? $request->start_date;
        $dateTo = $request->date_to ?? $request->end_date;

        if ($request->filled('date_from') || $request->filled('start_date')) {
            $query->where('tanggal_kejadian', '>=', $dateFrom);
        }

        if ($request->filled('date_to') || $request->filled('end_date')) {
            $query->where('tanggal_kejadian', '<=', $dateTo);
        }

        if ($request->filled('resolution_status')) {
            $query->where('resolution_status', $request->resolution_status);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

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
