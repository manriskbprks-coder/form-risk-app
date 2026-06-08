<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiskItem;
use App\Models\RiskReport;
use App\Models\Branch;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use App\Http\Requests\StoreRiskReportRequest;
use App\Http\Requests\UpdateRiskApprovalStatusRequest;
use App\Http\Requests\UpdateRiskResolutionRequest;
use App\Http\Requests\AddRiskProgressRequest;
use App\Domain\Enums\RiskReportStatus;
use App\Domain\Enums\RoleCategory;
use App\Services\KodeLaporanService;
use App\Services\RiskReportQueryService;
use App\Services\RiskReportService;

class RiskReportController extends Controller
{
    public function __construct(
        protected KodeLaporanService $kodeLaporanService,
        protected RiskReportQueryService $riskReportQueryService,
        protected RiskReportService $riskReportService,
    ) {}

    public function create($kategori)
    {
        if (!in_array($kategori, ['finansial', 'non-finansial'])) {
            abort(404, 'Kategori Risiko Tidak Ditemukan');
        }

        $userRoleCategory = Auth::user()->roleCategory();
        if (!$userRoleCategory) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak. Role user tidak ditemukan.');
        }

        // Viewer (manrisk, korwil) tidak boleh membuat laporan
        if (Auth::user()->isViewer()) {
            abort(Response::HTTP_FORBIDDEN, 'Akses Ditolak! Role Anda tidak berwenang membuat laporan risiko.');
        }

        $riskItems = RiskItem::with('causes.mitigations')
            ->where('role_target', Auth::user()->primaryRoleName())
            ->where('kategori', $kategori)
            ->get();

        return view('risk_reports.create', compact('riskItems', 'kategori'));
    }

    public function store(StoreRiskReportRequest $request)
    {
        try {
            $user = Auth::user();
            $report = $this->riskReportService->create($request->validated(), $user);

            return redirect()->route('dashboard')->with('success', 'Laporan berhasil dikirim!');
        } catch (\Exception $e) {
            Log::channel('daily')->error('[ERROR] Gagal menyimpan laporan', [
                'user_id' => Auth::user()?->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan laporan. Silakan coba lagi.');
        }
    }

    // VIEW 1 & 2: MONITORING & PERSETUJUAN — Khusus Checker (Kacab)
    public function review()
    {
        $user = Auth::user();
        if (!$user) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
        }

        $reports = collect();
        $tindakLanjut = collect();
        $closedReports = collect();

        if ($user->roleCategoryEnum()?->isChecker()) {
            $reports = RiskReport::with(['user.roles', 'item', 'cause.mitigations', 'branch'])
                ->where('branch_id', $user->branch_id)
                ->whereIn('status', [RiskReportStatus::PendingAtasan->value, RiskReportStatus::NeedRevision->value])
                ->orderBy('created_at', 'desc')
                ->get();

            $tindakLanjut = RiskReport::with(['user.roles', 'item', 'cause.mitigations', 'branch'])
                ->where('branch_id', $user->branch_id)
                ->where('status', RiskReportStatus::ApprovedInProgress->value)
                ->orderBy('updated_at', 'desc')
                ->get();

            $closedReports = RiskReport::with(['user.roles', 'item', 'branch'])
                ->where('branch_id', $user->branch_id)
                ->where('status', RiskReportStatus::Closed->value)
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return view('risk_reports.review', compact('reports', 'tindakLanjut', 'closedReports'));
    }

    // PROSES APPROVAL / REJECT (Kacab)
    public function updateStatus(UpdateRiskApprovalStatusRequest $request, $id)
    {
        $report = RiskReport::findOrFail($id);
        Gate::authorize('approve', $report);

        $user = Auth::user();

        if ($request->status === 'need_revision') {
            $this->riskReportService->requestRevisionFromKacab($report, $user, $request->alasan_reject);
            return redirect()->back()->with('success', 'Laporan dikembalikan untuk direvisi. Alasan sudah dicatat.');
        }

        $this->riskReportService->approve($report, $user);
        return redirect()->back()->with('success', 'Status persetujuan diperbarui!');
    }

    // VIEW 3: RIWAYAT & MONITORING KESELURUHAN (DENGAN FILTER + SEARCH + PAGINATION)
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
        }

        $role = $user->primaryRoleName();

        $query = $this->riskReportQueryService->baseQuery();
        $query = $this->riskReportQueryService->applyRoleScope($query, $user);
        $query = $this->riskReportQueryService->applyFilters($query, $request, $user);

        $branches = $this->riskReportQueryService->getBranchesForUser($user);

        $totalLoss = (clone $query)->whereIn('status', [RiskReportStatus::ApprovedInProgress->value, RiskReportStatus::Closed->value])->sum('dampak_finansial');
        $totalKejadian = (clone $query)->count();
        $totalRejected = (clone $query)->where('status', 'need_revision')->count();

        // === SORTING ===
        $sortField = 'created_at';
        $sortDir = 'desc';
        if ($request->filled('sort')) {
            $sortMap = [
                'created_at_desc' => ['field' => 'created_at', 'dir' => 'desc'],
                'created_at_asc'  => ['field' => 'created_at', 'dir' => 'asc'],
                'dampak_desc'     => ['field' => 'dampak_finansial', 'dir' => 'desc'],
                'dampak_asc'      => ['field' => 'dampak_finansial', 'dir' => 'asc'],
                'kode_asc'        => ['field' => 'kode_laporan', 'dir' => 'asc'],
                'kode_desc'       => ['field' => 'kode_laporan', 'dir' => 'desc'],
            ];
            $sort = $sortMap[$request->sort] ?? $sortMap['created_at_desc'];
            $sortField = $sort['field'];
            $sortDir = $sort['dir'];
        }

        // Split into 2 queries: Active (not closed) and Closed
        $activeReports = (clone $query)->where('status', '!=', RiskReportStatus::Closed->value)
            ->orderBy($sortField, $sortDir)
            ->paginate(15, ['*'], 'active_page')
            ->appends($request->query());

        $closedReports = (clone $query)->where('status', RiskReportStatus::Closed->value)
            ->orderBy($sortField, $sortDir)
            ->paginate(15, ['*'], 'closed_page')
            ->appends($request->query());

        return view('risk_reports.index', compact('activeReports', 'closedReports', 'totalLoss', 'totalKejadian', 'totalRejected', 'branches', 'role'));
    }

    // UPDATE TINDAK LANJUT (RESOLUTION)
    public function updateResolution(UpdateRiskResolutionRequest $request, $id)
    {
        $user = Auth::user();
        $report = RiskReport::findOrFail($id);
        Gate::authorize('updateProgress', $report);

        $this->riskReportService->updateResolution($report, $user, $request->status);

        return redirect()->back()->with('success', 'Status tindak lanjut diperbarui!');
    }

    // FUNGSI TAMBAHAN: CATAT PROGRESS TINDAK LANJUT (NOTE + STATUS)
    public function addProgress(AddRiskProgressRequest $request, $id)
    {
        $user = Auth::user();
        $report = RiskReport::findOrFail($id);
        Gate::authorize('updateProgress', $report);

        $newStatus = $request->new_status ?? $report->status;

        if ($newStatus === \App\Domain\Enums\RiskReportStatus::Closed->value) {
            if (!$user->roleCategoryEnum()?->isChecker()) {
                return back()->with('error', 'Hanya Checker (Kacab) yang berwenang menutup laporan.');
            }
            if ((string) $report->branch_id !== (string) $user->branch_id) {
                return back()->with('error', 'Anda tidak berwenang menutup laporan dari cabang lain.');
            }
        }

        $this->riskReportService->addProgress($report, $user, $request->note, $newStatus);

        return back()->with('success', 'Progress berhasil dicatat!');
    }

    // Nampilin Detail Laporan & Timeline
    public function show($id)
    {
        $report = RiskReport::with(['user', 'item', 'branch', 'cause.mitigations', 'logs.user'])->findOrFail($id);
        Gate::authorize('view', $report);

        return view('risk_reports.show', compact('report'));
    }

    // ========================================================================
    // FUNGSI REVISI LAPORAN
    // ========================================================================

    /**
     * ManRisk meminta revisi laporan yang sudah approved.
     */
    public function requestRevision(Request $request, $id)
    {
        $user = Auth::user();
        $report = RiskReport::findOrFail($id);

        Gate::authorize('requestRevision', $report);

        $request->validate([
            'revision_note' => 'required|string|min:10|max:2000',
        ]);

        $revisionNote = strip_tags($request->input('revision_note'));

        $this->riskReportService->requestRevisionFromManRisk($report, $user, $revisionNote);

        return back()->with('success', 'Permintaan revisi telah dikirim.');
    }

    /**
     * Maker/Kacab mengirimkan revisi laporan.
     */
    public function submitRevision(Request $request, $id)
    {
        $user = Auth::user();
        $report = RiskReport::findOrFail($id);

        Gate::authorize('submitRevision', $report);

        if ($report->status !== RiskReportStatus::NeedRevision->value) {
            return back()->with('error', 'Laporan ini tidak dalam status perlu revisi.');
        }

        $request->validate([
            'kronologis_kejadian' => 'required|string|min:20',
            'dampak_finansial' => 'nullable|numeric|min:0',
            'skala_dampak' => 'nullable|string|max:50',
            'dampak_non_finansial' => 'nullable|string',
            'mitigasi_tambahan' => 'nullable|string',
            'durasi_penyelesaian' => 'nullable|integer|min:1',
            'durasi_satuan' => 'nullable|in:jam,hari,minggu',
            'sumber_risiko' => 'nullable|string|in:manusia,sistem_teknologi,proses_internal,faktor_eksternal',
        ]);

        $oldData = $report->only([
            'kronologis_kejadian', 'dampak_finansial', 'skala_dampak',
            'dampak_non_finansial', 'mitigasi_tambahan',
            'durasi_penyelesaian', 'durasi_satuan',
            'sumber_risiko',
        ]);

        $this->riskReportService->submitRevision($report, $user, $request->all(), $oldData);

        return redirect()->route('dashboard')->with('success', 'Revisi laporan berhasil dikirim!');
    }

    /**
     * ManRisk menyetujui hasil revisi.
     */
    public function approveRevision($id)
    {
        $user = Auth::user();
        $report = RiskReport::findOrFail($id);

        Gate::authorize('approveRevision', $report);

        $this->riskReportService->approveRevision($report, $user);

        return back()->with('success', 'Revisi laporan disetujui!');
    }
}
