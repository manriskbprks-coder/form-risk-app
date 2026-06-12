<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RiskReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ExportRiskReportController;
use App\Http\Controllers\RiskFreeDeclarationController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Admin\RiskMasterController;
use App\Http\Controllers\Admin\RoleController;
use App\Models\Branch;
use App\Models\RiskReport;
use App\Services\ChartService;
use App\Services\SummaryService;

Route::get('/', function () {
    return redirect()->route('login');
});

// =========================================================================
// DASHBOARD — throttle 35 per menit (refresh halaman wajar, cegah DoS)
// =========================================================================
Route::get('/dashboard', function (ChartService $chartService, SummaryService $summaryService) {
    $user = auth()->user();
    $userBranchId = $user->branch_id;
    $roleCategory = $user->roleCategory();

    // ================================================================
    // FILTER: Bulan & Cabang (khusus ManRisk, opsional untuk role lain)
    // ================================================================
    // Month multi-select — array format "2026-05", default [] = semua bulan (12 bulan terakhir)
    $bulanFilters = request('bulan', []);
    // Cabang multi-select — array of IDs, default [] = semua cabang
    $cabangFilter = request('cabang_ids', []);

    // Tentukan branch IDs yang bisa dilihat user
    if (in_array($roleCategory, ['viewer', 'admin'])) {
        if ($roleCategory === 'viewer') {
            $branchIds = Branch::where('korwil_id', $user->id)
                ->whereRaw('is_active = true')
                ->pluck('id');
        } else { // admin
            if (empty($cabangFilter)) {
                $branchIds = Branch::whereRaw('is_active = true')->pluck('id');
            } else {
                $branchIds = collect($cabangFilter)->map(fn($id) => (string) $id);
            }
        }
    } elseif ($roleCategory === 'checker') {
        $branchIds = collect([$userBranchId]);
    } else {
        // Maker — lihat laporan sendiri
        $branchIds = collect();
    }

    // Semua cabang aktif (buat dropdown filter ManRisk)
    $allBranches = Branch::whereRaw('is_active = true')->get();

    // Siapkan data month picker (12 bulan terakhir)
    $availableMonths = [];
    for ($i = 11; $i >= 0; $i--) {
        $m = now()->subMonths($i);
        $availableMonths[] = [
            'value' => $m->format('Y-m'),
            'label' => $m->format('F Y'),
        ];
    }

    // Parse bulan filter jadi date range
    // Jika user milih beberapa bulan, ambil dari bulan paling awal sampai paling akhir
    if (!empty($bulanFilters)) {
        $sortedMonths = collect($bulanFilters)->sort();
        $firstMonth = \Carbon\Carbon::parse($sortedMonths->first() . '-01')->startOfMonth();
        $lastMonth = \Carbon\Carbon::parse($sortedMonths->last() . '-01')->endOfMonth();
        $dateFilter = $firstMonth;
        $dateFilterEnd = $lastMonth;
        $bulanTren = count($bulanFilters); // jumlah bulan yang dipilih buat tren
    } else {
        // Default: 12 bulan terakhir
        $dateFilter = now()->subMonths(11)->startOfMonth();
        $dateFilterEnd = now()->endOfMonth();
        $bulanTren = 12;
    }

    // === DATA VIA SERVICES ===
    $recentReports = $summaryService->getRecentReports($user, $roleCategory, $branchIds->toArray());
    $statCards = $summaryService->getStatCards($user, $roleCategory, $branchIds->toArray(), $dateFilter, $dateFilterEnd);
    $pendingCount = $summaryService->getPendingCount($user, $roleCategory, $branchIds->toArray());

    // Extract stat card variables for the view (which expects individual variables)
    $totalClosed = $statCards['totalClosed'];
    $totalPending = $statCards['totalPending'];
    $totalLossApproved = $statCards['totalLossApproved'];
    $totalInProgress = $statCards['totalInProgress'];
    $labelTotalLaporan = $statCards['labelTotalLaporan'];

    // === DATA CHART (hanya untuk kacab/korwil/manrisk) ===
    $chartMonths = [];
    $chartCounts = [];
    $chartFinansial = 0;
    $chartNonFinansial = 0;
    $chartOpen = 0;
    $chartInProgress = 0;
    $chartClosed = 0;
    $rankingRisikoLabels = [];
    $rankingRisikoFullLabels = [];
    $rankingRisikoData = [];
    $rankingRisikoColors = [];
    $sumberRisikoLabels = [];
    $sumberRisikoData = [];
    $sumberRisikoColors = [];
    $trenTop5Labels = [];
    $trenTop5Datasets = [];
    $topCabangLabels = [];
    $topCabangData = [];
    $topCabangColors = [];
    $makerDistribusiLabels = [];
    $makerDistribusiData = [];
    $makerDistribusiColors = [];
    $inProgressReports = collect();

    if ($roleCategory !== 'maker') {
        $branchIdsArray = $branchIds->toArray();

        // 1. Tren laporan per bulan (pake bulan filter — 1 bulan doang)
        $trenData = $chartService->getTrenLaporan($branchIdsArray, $bulanTren);
        $chartMonths = $trenData['chartMonths'];
        $chartCounts = $trenData['chartCounts'];

        // 2. Distribusi kategori (all time — ga kena filter bulan)
        $distribusi = $chartService->getDistribusiKategori($branchIdsArray);
        $chartFinansial = $distribusi['chartFinansial'];
        $chartNonFinansial = $distribusi['chartNonFinansial'];

        // 3. Status tindak lanjut (all time — ga kena filter bulan)
        $statusTindakLanjut = $chartService->getStatusTindakLanjut($branchIdsArray);
        $chartOpen = $statusTindakLanjut['chartOpen'];
        $chartInProgress = $statusTindakLanjut['chartInProgress'];
        $chartClosed = $statusTindakLanjut['chartClosed'];

        // 4. Ranking Risiko (filter per bulan)
        $ranking = $chartService->getRankingRisiko($branchIdsArray, $dateFilter);
        $rankingRisikoLabels = $ranking['rankingRisikoLabels'];
        $rankingRisikoFullLabels = $ranking['rankingRisikoFullLabels'];
        $rankingRisikoData = $ranking['rankingRisikoData'];
        $rankingRisikoColors = $ranking['rankingRisikoColors'];

        // 5. Sumber Risiko (filter per bulan)
        $sumber = $chartService->getSumberRisiko($branchIdsArray, $dateFilter);
        $sumberRisikoLabels = $sumber['sumberRisikoLabels'];
        $sumberRisikoData = $sumber['sumberRisikoData'];
        $sumberRisikoColors = $sumber['sumberRisikoColors'];

        // 6. Tren Top-5 Risiko (filter per bulan)
        $trenTop5 = $chartService->getTrenTop5($branchIdsArray, $dateFilter, $bulanTren);
        $trenTop5Labels = $trenTop5['trenTop5Labels'];
        $trenTop5Datasets = $trenTop5['trenTop5Datasets'];

        // 7. Top Cabang Paling Berisiko (Hanya untuk Viewer/Korwil)
        if ($roleCategory === 'viewer') {
            $topCabang = $chartService->getTopBerisikoBranches($branchIdsArray, $dateFilter);
            $topCabangLabels = $topCabang['topCabangLabels'];
            $topCabangData = $topCabang['topCabangData'];
            $topCabangColors = $topCabang['topCabangColors'];
        }
    } else {
        // MAKER ONLY CHARTS
        $makerDistribusi = $chartService->getDistribusiKategoriUser($user->id);
        $makerDistribusiLabels = $makerDistribusi['makerDistribusiLabels'];
        $makerDistribusiData = $makerDistribusi['makerDistribusiData'];
        $makerDistribusiColors = $makerDistribusi['makerDistribusiColors'];
    }

    // === DATA RINGKASAN WILAYAH (Khusus ManRisk) ===
    $branchSummaries = [];
    $branchChartLabels = [];
    $branchChartData = [];
    $branchChartColors = [];

    if ($roleCategory === 'admin') {
        $branchData = $summaryService->getBranchSummaries($allBranches, $bulanFilters, $branchIds->toArray());
        $branchSummaries = $branchData['branchSummaries'];
        $branchChartLabels = $branchData['branchChartLabels'];
        $branchChartData = $branchData['branchChartData'];
        $branchChartColors = $branchData['branchChartColors'];
    }

    // === DATA INSIDEN KRITIS (Khusus Korwil/ManRisk) ===
    $kritisReports = collect();
    if (in_array($roleCategory, ['viewer', 'admin'])) {
        $kritisReports = RiskReport::with(['user', 'branch'])
            ->whereIn('branch_id', $branchIds->toArray())
            ->where(function ($query) {
                $query->where('dampak_finansial', '>=', 100000000) // >= 100 Juta
                      ->orWhereIn('skala_dampak', ['Sangat Tinggi', 'Tinggi']);
            })
            ->whereIn('status', ['pending_atasan', 'pending_korwil', 'pending_revision', 'approved_in_progress'])
            ->orderBy('dampak_finansial', 'desc')
            ->take(5)
            ->get();
    }

    // === TUGAS SAYA (Khusus Kacab) & REVISI (Khusus Maker) ===
    $myTasks = collect();
    $makerRevisions = collect();
    if ($roleCategory === 'checker') {
        $myTasks = RiskReport::with(['user', 'item'])
            ->where('branch_id', $userBranchId)
            ->where('status', 'pending_atasan')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
            
        $inProgressReports = RiskReport::with(['user', 'item'])
            ->where('branch_id', $userBranchId)
            ->where('status', 'approved_in_progress')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
    } elseif ($roleCategory === 'maker') {
        $makerRevisions = RiskReport::where('user_id', $user->id)
            ->where('status', 'need_revision')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    // === DATA DEKLARASI NIHIL RISIKO (Khusus ManRisk) ===
    $deklarasiSummaries = [];
    $cabangBelumDeklarasi = [];

    if ($roleCategory === 'admin') {
        $deklarasiData = $summaryService->getDeklarasiSummaries($allBranches, $bulanFilters, $branchIds->toArray());
        $deklarasiSummaries = $deklarasiData['deklarasiSummaries'];
        $cabangBelumDeklarasi = $deklarasiData['cabangBelumDeklarasi'];
    }

    return view('dashboard', compact(
        'recentReports',
        'statCards',
        'totalClosed',
        'totalPending',
        'totalLossApproved',
        'totalInProgress',
        'pendingCount',
        'roleCategory',
        'labelTotalLaporan',
        'chartMonths',
        'chartCounts',
        'chartFinansial',
        'chartNonFinansial',
        'chartOpen',
        'chartInProgress',
        'chartClosed',
        'rankingRisikoLabels',
        'rankingRisikoFullLabels',
        'rankingRisikoData',
        'rankingRisikoColors',
        'sumberRisikoLabels',
        'sumberRisikoData',
        'sumberRisikoColors',
        'trenTop5Labels',
        'trenTop5Datasets',
        'branchSummaries',
        'branchChartLabels',
        'branchChartData',
        'branchChartColors',
        'bulanFilters',
        'cabangFilter',
        'allBranches',
        'availableMonths',
        'deklarasiSummaries',
        'cabangBelumDeklarasi',
        'kritisReports',
        'myTasks',
        'makerRevisions',
        'topCabangLabels',
        'topCabangData',
        'topCabangColors',
        'makerDistribusiLabels',
        'makerDistribusiData',
        'makerDistribusiColors',
        'inProgressReports'
    ));
})->middleware(['auth', 'verified', 'throttle:dashboard'])->name('dashboard');


// =========================================================================
// AREA PENGGUNA LOGIN (MAKER & CHECKER)
// Semua rute di sini udah dibungkus 1 gembok auth biar nggak numpuk
// =========================================================================
Route::middleware('auth')->group(function () {

    // --- Profile (Ganti Password) — throttle 5 per menit ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    // --- Glosarium (Kamus Istilah Manajemen Risiko) ---
    Route::get('/glosarium', function () {
        return view('glosarium');
    })->name('glosarium');

    // --- Onboarding Tour: Tandai user sudah selesai tour ---
    Route::post('/user/finish-tour', function () {
        auth()->user()->update(['has_seen_tour' => true]);
        return response()->json(['status' => 'ok']);
    })->name('user.finish_tour');

    Route::match(['post', 'patch'], '/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:profile')
        ->name('profile.update');

    // --- MENU 1: INPUT LAPORAN (MAKER) — throttle 10 per menit ---
    Route::get('/form-risiko/{kategori}', [RiskReportController::class, 'create'])->name('form.risiko');
    Route::post('/form-risiko', [RiskReportController::class, 'store'])
        ->middleware('throttle:store_report')
        ->name('form.risiko.store');

    // --- MENU 2: REVIEW & TINDAK LANJUT (CHECKER: KACAB) ---
    Route::get('/review-laporan', [RiskReportController::class, 'review'])->name('review.laporan');

    // Approve/Reject — throttle 10 per menit
    Route::post('/risk-reports/{id}/status', [RiskReportController::class, 'updateStatus'])
        ->middleware('throttle:approval')
        ->name('risk_reports.update_status');

    // Tindak Lanjut (Monitoring/Closed) — throttle 10 per menit
    Route::post('/risk-reports/{id}/resolution', [RiskReportController::class, 'updateResolution'])
        ->middleware('throttle:resolution')
        ->name('risk_reports.update_resolution');

    // Fitur Analisa SKMR (ManRisk / Admin)
    Route::post('/risk-reports/{id}/skmr-analysis', [RiskReportController::class, 'saveSkmrAnalysis'])
        ->name('risk_reports.save_skmr_analysis');

    // --- MENU 3: RIWAYAT KESELURUHAN ---
    Route::get('/riwayat-risiko', [RiskReportController::class, 'index'])->name('risk.history');

    // Rute Detail & Progress Laporan
    Route::get('/risk-report/{id}', [RiskReportController::class, 'show'])->name('risk_reports.show');

    // Rute untuk nambahin Progress Catatan — throttle 10 per menit
    Route::post('/risk-report/{id}/progress', [RiskReportController::class, 'addProgress'])
        ->middleware('throttle:progress')
        ->name('risk_reports.add_progress');

    // --- RUTE REVISI LAPORAN — throttle 10 per menit ---
    Route::post('/risk-report/{id}/request-revision', [RiskReportController::class, 'requestRevision'])
        ->middleware('throttle:revision')
        ->name('risk_reports.request_revision');
    Route::post('/risk-report/{id}/submit-revision', [RiskReportController::class, 'submitRevision'])
        ->middleware('throttle:revision')
        ->name('risk_reports.submit_revision');
    Route::post('/risk-report/{id}/approve-revision', [RiskReportController::class, 'approveRevision'])
        ->middleware('throttle:revision')
        ->name('risk_reports.approve_revision');

    // --- NOTIFIKASI IN-APP ---
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark_all_read');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread_count');

    // --- EXPORT CSV — throttle 5 per menit (berat, cegah abuse) ---
    Route::get('/export-risiko', [ExportRiskReportController::class, 'export'])
        ->middleware('throttle:export')
        ->name('risk.export');

    // --- DEKLARASI NIHIL RISIKO (Kacab) — throttle 10 per menit ---
    Route::get('/deklarasi-nihil', [RiskFreeDeclarationController::class, 'create'])->name('risk_free_declarations.create');
    Route::post('/deklarasi-nihil', [RiskFreeDeclarationController::class, 'store'])
        ->middleware('throttle:deklarasi_nihil')
        ->name('risk_free_declarations.store');
    Route::get('/deklarasi-nihil/riwayat', [RiskFreeDeclarationController::class, 'history'])->name('risk_free_declarations.history');
});


// =========================================================================
// AREA KHUSUS DEWA APLIKASI (MANAJEMEN RISIKO) — throttle 20 per menit
// =========================================================================
Route::middleware(['auth', 'admin', 'throttle:admin'])->group(function () {
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle');

    // CRUD Bank Soal
    Route::get('/admin/risk-master', [RiskMasterController::class, 'index'])->name('admin.risk_master.index');
    Route::post('/admin/risk-master/item', [RiskMasterController::class, 'storeItem'])->name('admin.risk_master.store_item');
    Route::post('/admin/risk-master/item/{id}/cause', [RiskMasterController::class, 'storeCause'])->name('admin.risk_master.store_cause');
    Route::delete('/admin/risk-master/item/{id}', [RiskMasterController::class, 'destroyItem'])->name('admin.risk_master.destroy_item');

    // ... rute manrisk yang lain ...

    // CRUD User Management
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');

    // CRUD Role Management
    Route::get('/admin/roles', [RoleController::class, 'index'])->name('admin.roles.index');
    Route::post('/admin/roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::patch('/admin/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
    Route::delete('/admin/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');

    // Rute buat update penyebab & mitigasi
    Route::patch('/admin/risk-master/cause/{id}', [\App\Http\Controllers\Admin\RiskMasterController::class, 'updateCause'])->name('admin.risk_master.update_cause');

    // Rute tambah mitigasi ke cause yang sudah ada (terpisah dari storeCause)
    Route::post('/admin/risk-master/cause/{causeId}/mitigation', [\App\Http\Controllers\Admin\RiskMasterController::class, 'storeMitigation'])->name('admin.risk_master.store_mitigation');

    // Rute Manajemen Master Data Cabang (Khusus ManRisk)
    Route::get('/branches-management', [App\Http\Controllers\BranchManagementController::class, 'index'])->name('branches.index');
    Route::put('/branches-management/{id}', [App\Http\Controllers\BranchManagementController::class, 'update'])->name('branches.update');
    Route::post('/branches-management', [App\Http\Controllers\BranchManagementController::class, 'store'])->name('branches.store');

    // --- RESET PASSWORD (ManRisk) ---
    Route::post('/admin/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset_password');

    // --- DEKLARASI NIHIL RISIKO (ManRisk) ---
    Route::post('/deklarasi-nihil/{id}/reject', [RiskFreeDeclarationController::class, 'reject'])->name('risk_free_declarations.reject');
});

require __DIR__ . '/auth.php';
