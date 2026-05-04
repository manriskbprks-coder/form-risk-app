<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiskItem;
use App\Models\RiskReport;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use App\Http\Requests\StoreRiskReportRequest;
use App\Http\Requests\UpdateRiskApprovalStatusRequest;
use App\Http\Requests\UpdateRiskResolutionRequest;
use App\Http\Requests\AddRiskProgressRequest;

class RiskReportController extends Controller
{
    private function primaryRoleName(): ?string
    {
        return Auth::user()?->primaryRoleName();
    }

    private function ensureCanViewReport(RiskReport $report): void
    {
        $user = Auth::user();
        $role = $user?->primaryRoleName();

        if (!$user || !$role) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
        }

        if ($role === 'manrisk') {
            return;
        }

        if ($role === 'kacab' && (int) $report->branch_id === (int) $user->branch_id) {
            return;
        }

        if ($role === 'korwil') {
            $branchIds = Branch::where('korwil_id', $user->id)->pluck('id');
            if ($branchIds->contains((int) $report->branch_id)) {
                return;
            }
        }

        if (in_array($role, ['teller', 'ca', 'csr', 'security'], true) && (int) $report->user_id === (int) $user->id) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
    }

    private function ensureCanApproveReport(RiskReport $report): void
    {
        $user = Auth::user();
        $role = $user?->primaryRoleName();

        if (!$user || !$role) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
        }

        if ($role === 'kacab') {
            if ((int) $report->branch_id !== (int) $user->branch_id || $report->approval_status !== 'pending_kacab') {
                abort(Response::HTTP_FORBIDDEN, 'Anda tidak berwenang menyetujui laporan ini.');
            }
            return;
        }

        abort(Response::HTTP_FORBIDDEN, 'Anda tidak berwenang menyetujui laporan ini.');
    }

    private function ensureCanUpdateProgress(RiskReport $report): void
    {
        $user = Auth::user();
        $role = $user?->primaryRoleName();

        if (!$user || !$role) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
        }

        // ManRisk hanya pantau.
        if ($role === 'manrisk') {
            abort(Response::HTTP_FORBIDDEN, 'Akses Ditolak! Divisi ManRisk hanya berwenang memantau, bukan mengubah progress penanganan.');
        }

        // Korwil hanya pantau (read-only).
        if ($role === 'korwil') {
            abort(Response::HTTP_FORBIDDEN, 'Akses Ditolak! Korwil hanya berwenang memantau, bukan mengubah progress penanganan.');
        }

        // Minimal bisa melihat laporan dulu.
        $this->ensureCanViewReport($report);
    }

    public function create($kategori)
    {
        if (!in_array($kategori, ['finansial', 'non-finansial'])) {
            abort(404, 'Kategori Risiko Tidak Ditemukan');
        }

        $userRole = $this->primaryRoleName();
        if (!$userRole) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak. Role user tidak ditemukan.');
        }

        $riskItems = RiskItem::with('causes.mitigations')
            ->where('role_target', $userRole)
            ->where('kategori', $kategori)
            ->get();

        return view('risk_reports.create', compact('riskItems', 'kategori'));
    }

    private function generateKodeLaporan($user): string
    {
        // Ambil kode cabang, fallback ke 'HQ'
        $kodeCabang = $user->branch->kode_cabang ?? 'HQ';
        
        // Mapping role ke kode singkat
        $roleMap = [
            'teller' => 'TL',
            'ca' => 'CA',
            'csr' => 'CS',
            'security' => 'SC',
            'kacab' => 'KC',
        ];
        $role = $user->primaryRoleName();
        $kodeRole = $roleMap[$role] ?? 'XX';
        
        // TahunBulan
        $tahunBulan = now()->format('Ym');
        
        // Hitung nomor urut di bulan ini
        $count = RiskReport::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        
        $nomorUrut = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        return "RISK-{$kodeCabang}{$kodeRole}-{$tahunBulan}-{$nomorUrut}";
    }

    public function store(StoreRiskReportRequest $request)
    {
        $user = Auth::user();
        $targetApproval = $user->hasRole('kacab') ? 'approved' : 'pending_kacab';

        $report = RiskReport::create([
            'kode_laporan' => $this->generateKodeLaporan($user),
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'kategori' => $request->kategori,
            'tanggal_kejadian' => $request->tanggal_kejadian,
            'tanggal_diketahui' => $request->tanggal_diketahui,
            'risk_item_id' => $request->risk_item_id,
            'other_item_description' => $request->other_item_description,
            'risk_cause_id' => $request->risk_cause_id,
            'other_cause_description' => $request->other_cause_description,
            'kronologis_kejadian' => $request->kronologis_kejadian,
            'mitigasi_tambahan' => $request->mitigasi_tambahan,
            'durasi_penyelesaian' => $request->durasi_penyelesaian,
            'durasi_satuan' => $request->durasi_satuan,
            'dampak_finansial' => $request->dampak_finansial ?? 0,
            'dampak_non_finansial' => $request->dampak_non_finansial,
            'skala_dampak' => $request->skala_dampak,
            'approval_status' => $targetApproval,
            'resolution_status' => $request->status_awal,
        ]);

        if ($request->filled('tindakan_awal')) {
            $report->logs()->create([
                'user_id' => $user->id,
                'note' => 'Penanganan Awal: ' . $request->tindakan_awal,
                'status_after_note' => $request->status_awal
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Laporan berhasil dikirim!');
    }

    // VIEW 1 & 2: MONITORING & PERSETUJUAN — Khusus Kacab
    public function review()
    {
        $user = Auth::user();
        $role = $user?->primaryRoleName();
        if (!$user || !$role) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
        }

        $reports = collect();
        $tindakLanjut = collect();

        if ($role === 'kacab') {
            $reports = RiskReport::with(['user', 'item', 'cause.mitigations'])
                ->where('branch_id', $user->branch_id)
                ->where('approval_status', 'pending_kacab')
                ->get();

            $tindakLanjut = RiskReport::with(['user', 'item', 'cause.mitigations'])
                ->where('branch_id', $user->branch_id)
                ->where('approval_status', 'approved')
                ->whereIn('resolution_status', ['open', 'in_progress'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return view('risk_reports.review', compact('reports', 'tindakLanjut'));
    }

    // PROSES APPROVAL
    public function updateStatus(UpdateRiskApprovalStatusRequest $request, $id)
    {
        $report = RiskReport::findOrFail($id);
        $this->ensureCanApproveReport($report);

        $report->update(['approval_status' => $request->status]);

        return redirect()->back()->with('success', 'Status persetujuan diperbarui!');
    }

    // VIEW 3: RIWAYAT & MONITORING KESELURUHAN (DENGAN FILTER)
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user?->primaryRoleName();
        if (!$user || !$role) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
        }

        $query = RiskReport::with(['user', 'item', 'cause.mitigations', 'branch']);

        if ($role === 'kacab') {
            $query->where('branch_id', $user->branch_id);
            $branches = collect();
        } elseif ($role === 'korwil') {
            $branchIds = Branch::where('korwil_id', $user->id)->pluck('id');
            $query->whereIn('branch_id', $branchIds);
            $branches = Branch::whereIn('id', $branchIds)->get();
        } elseif (in_array($role, ['teller', 'ca', 'csr', 'security'])) {
            $query->where('user_id', $user->id);
            $branches = collect();
        } else {
            $branches = Branch::all();
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

        $totalLoss = (clone $query)->where('approval_status', 'approved')->sum('dampak_finansial');
        $totalKejadian = (clone $query)->count();
        $totalRejected = (clone $query)->where('approval_status', 'rejected')->count();

        $reports = $query->orderBy('created_at', 'desc')->get();

        return view('risk_reports.index', compact('reports', 'totalLoss', 'totalKejadian', 'totalRejected', 'branches', 'role'));
    }

    // UPDATE TINDAK LANJUT (RESOLUTION)
    public function updateResolution(UpdateRiskResolutionRequest $request, $id)
    {
        $report = RiskReport::findOrFail($id);
        $this->ensureCanUpdateProgress($report);

        $report->update(['resolution_status' => $request->resolution_status]);

        return redirect()->back()->with('success', 'Status tindak lanjut diperbarui!');
    }

    // FUNGSI TAMBAHAN: CATAT PROGRESS TINDAK LANJUT (NOTE + STATUS)
    public function addProgress(AddRiskProgressRequest $request, $id)
    {
        $user = Auth::user();
        $report = RiskReport::findOrFail($id);
        $this->ensureCanUpdateProgress($report);

        if ($request->new_status === 'closed') {
            if (!$user->hasRole('kacab')) {
                return back()->with('error', 'Hanya Kacab yang berwenang menutup laporan.');
            }

            if ((int) $report->branch_id !== (int) $user->branch_id) {
                return back()->with('error', 'Anda tidak berwenang menutup laporan dari cabang lain.');
            }
        }

        $report->logs()->create([
            'user_id' => $user->id,
            'note' => $request->note,
            'status_after_note' => $request->new_status
        ]);

        $report->update(['resolution_status' => $request->new_status]);

        return back()->with('success', 'Progress berhasil dicatat!');
    }

    // Nampilin Detail Laporan & Timeline
    public function show($id)
    {
        $report = RiskReport::with(['user', 'item', 'branch', 'cause.mitigations', 'logs.user'])->findOrFail($id);
        $this->ensureCanViewReport($report);

        return view('risk_reports.show', compact('report'));
    }
}
