<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiskReport;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportRiskReportController extends Controller
{
    public function export(Request $request)
    {
        $user = Auth::user();
        $role = $user?->primaryRoleName();

        if (!$user || !$role) {
            abort(403, 'Akses ditolak.');
        }

        $query = RiskReport::with(['user', 'item', 'cause.mitigations', 'branch']);

        // Filter by role
        if ($role === 'kacab') {
            $query->where('branch_id', $user->branch_id);
        } elseif ($role === 'korwil') {
            $branchIds = Branch::where('korwil_id', $user->id)->pluck('id');
            $query->whereIn('branch_id', $branchIds);
        } elseif (in_array($role, ['teller', 'ca', 'csr', 'security'])) {
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

        if ($request->filled('branch_id') && in_array($role, ['manrisk', 'korwil'])) {
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

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_kejadian', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('resolution_status')) {
            $query->where('resolution_status', $request->resolution_status);
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

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

                $sumberRisiko = $report->cause->sumber_risiko ?? $report->item->sumber_risiko ?? 'manusia';
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
