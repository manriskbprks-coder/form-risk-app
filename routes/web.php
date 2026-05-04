<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RiskReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ExportRiskReportController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Admin\RiskMasterController;
use App\Models\RiskReport;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    $userBranchId = $user->branch_id;
    $role = $user?->primaryRoleName();

    // Tentukan branch IDs yang bisa dilihat user
    if ($role === 'korwil') {
        $branchIds = \App\Models\Branch::where('korwil_id', $user->id)
            ->where('is_active', true)
            ->pluck('id');
    } elseif ($role === 'kacab') {
        $branchIds = collect([$userBranchId]);
    } elseif ($role === 'manrisk') {
        $branchIds = \App\Models\Branch::where('is_active', true)->pluck('id');
    } else {
        // Maker (teller/ca/csr/security) — lihat laporan sendiri
        $branchIds = collect();
    }

    // Laporan terbaru (untuk tabel)
    if (in_array($role, ['korwil', 'kacab', 'manrisk'])) {
        $recentReports = RiskReport::with(['user', 'branch', 'item'])
            ->whereIn('branch_id', $branchIds)
            ->latest()
            ->take(10)
            ->get();
    } else {
        // Teller/CA/CSR/Security — lihat laporan sendiri
        $recentReports = RiskReport::with(['user', 'branch', 'item'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();
    }

    // Stat cards — difilter sesuai role
    $reportQuery = RiskReport::query();
    if ($role === 'korwil') {
        $reportQuery->whereIn('branch_id', $branchIds);
    } elseif ($role === 'kacab') {
        $reportQuery->where('branch_id', $userBranchId);
    } elseif (in_array($role, ['teller', 'ca', 'csr', 'security'])) {
        $reportQuery->where('user_id', $user->id);
    }
    // ManRisk ga perlu filter

    $totalLaporanBulanIni = (clone $reportQuery)
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();

    $totalPending = (clone $reportQuery)
        ->where(function ($q) {
            $q->where('approval_status', 'pending')
              ->orWhere('approval_status', 'pending_kacab')
              ->orWhere('approval_status', 'pending_korwil');
        })
        ->count();

    $totalApproved = (clone $reportQuery)
        ->where('approval_status', 'approved')
        ->count();

    $totalLossApproved = (clone $reportQuery)
        ->where('approval_status', 'approved')
        ->where('kategori', 'finansial')
        ->sum('dampak_finansial');

    // === DATA UNTUK CHART ANALISA RISIKO (hanya untuk kacab/korwil/manrisk) ===
    $chartMonths = [];
    $chartCounts = [];
    $chartFinansial = 0;
    $chartNonFinansial = 0;
    $chartOpen = 0;
    $chartInProgress = 0;
    $chartClosed = 0;

    // Data untuk 3 chart baru
    $rankingRisikoLabels = [];
    $rankingRisikoData = [];
    $rankingRisikoColors = [];
    $sumberRisikoLabels = [];
    $sumberRisikoData = [];
    $sumberRisikoColors = [];
    $trenTop5Labels = [];
    $trenTop5Datasets = [];

    if (in_array($role, ['kacab', 'korwil', 'manrisk'])) {
        // 1. Tren laporan per bulan (6 bulan terakhir) — existing
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartMonths[] = $month->format('M Y');
            $chartCounts[] = RiskReport::query()
                ->whereIn('branch_id', $branchIds)
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        }

        // 2. Distribusi kategori — existing
        $chartFinansial = RiskReport::query()
            ->whereIn('branch_id', $branchIds)
            ->where('kategori', 'finansial')
            ->count();
        $chartNonFinansial = RiskReport::query()
            ->whereIn('branch_id', $branchIds)
            ->where('kategori', 'non-finansial')
            ->count();

        // 3. Status tindak lanjut — existing
        $chartOpen = RiskReport::query()
            ->whereIn('branch_id', $branchIds)
            ->where('resolution_status', 'open')
            ->count();
        $chartInProgress = RiskReport::query()
            ->whereIn('branch_id', $branchIds)
            ->where('resolution_status', 'in_progress')
            ->count();
        $chartClosed = RiskReport::query()
            ->whereIn('branch_id', $branchIds)
            ->where('resolution_status', 'closed')
            ->count();

        // ================================================================
        // CHART 1: RANKING RISIKO (Horizontal Bar — Top 10)
        // ================================================================
        $rankingRisiko = RiskReport::selectRaw('risk_item_id, COUNT(*) as total')
            ->whereIn('branch_id', $branchIds)
            ->whereNotNull('risk_item_id')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('risk_item_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $rankingRisikoLabels = [];
        $rankingRisikoData = [];
        $maxRank = $rankingRisiko->max('total') ?: 1;
        foreach ($rankingRisiko as $item) {
            $riskItem = \App\Models\RiskItem::find($item->risk_item_id);
            $rankingRisikoLabels[] = $riskItem?->nama_risiko ?? 'Risiko #' . $item->risk_item_id;
            $rankingRisikoData[] = (int) $item->total;
        }
        // Balik biar yang terbesar di atas (horizontal bar)
        $rankingRisikoLabels = array_reverse($rankingRisikoLabels);
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

        // ================================================================
        // CHART 2: SUMBER RISIKO (Doughnut)
        // ================================================================
        $sumberMapping = [
            'manusia'       => ['label' => 'Manusia',       'color' => '#ef4444'],
            'proses'        => ['label' => 'Proses Internal','color' => '#f97316'],
            'sistem'        => ['label' => 'Sistem Teknologi','color' => '#eab308'],
            'eksternal'     => ['label' => 'Faktor Eksternal','color' => '#22c55e'],
        ];

        $sumberRisikoLabels = [];
        $sumberRisikoData = [];
        $sumberRisikoColors = [];

        // Query sumber_risiko dari risk_items yang muncul di laporan 6 bulan terakhir
        $sumberQuery = RiskReport::selectRaw('risk_items.sumber_risiko, COUNT(*) as total')
            ->join('risk_items', 'risk_reports.risk_item_id', '=', 'risk_items.id')
            ->whereIn('risk_reports.branch_id', $branchIds)
            ->where('risk_reports.created_at', '>=', now()->subMonths(6))
            ->groupBy('risk_items.sumber_risiko')
            ->orderByDesc('total')
            ->get();

        foreach ($sumberQuery as $row) {
            $key = $row->sumber_risiko;
            $label = $sumberMapping[$key]['label'] ?? ucfirst($key);
            $color = $sumberMapping[$key]['color'] ?? '#6b7280';
            $sumberRisikoLabels[] = $label;
            $sumberRisikoData[] = (int) $row->total;
            $sumberRisikoColors[] = $color;
        }

        // ================================================================
        // CHART 3: TREN TOP-5 RISIKO (Multi-line Chart — 6 Bulan)
        // ================================================================
        // Ambil top 5 risk_item_id berdasarkan total kejadian 6 bulan
        $top5Ids = RiskReport::selectRaw('risk_item_id, COUNT(*) as total')
            ->whereIn('branch_id', $branchIds)
            ->whereNotNull('risk_item_id')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('risk_item_id')
            ->orderByDesc('total')
            ->take(5)
            ->pluck('risk_item_id');

        // Siapkan label bulan
        $trenTop5Labels = [];
        for ($i = 5; $i >= 0; $i--) {
            $trenTop5Labels[] = now()->subMonths($i)->format('M Y');
        }

        $trenColors = ['#6366f1', '#ef4444', '#f97316', '#22c55e', '#eab308'];
        $trenTop5Datasets = [];
        $idx = 0;
        foreach ($top5Ids as $rid) {
            $riskItem = \App\Models\RiskItem::find($rid);
            $nama = $riskItem?->nama_risiko ?? 'Risiko #' . $rid;
            $data = [];
            for ($i = 5; $i >= 0; $i--) {
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
    }

    // Hitung badge pending untuk checker
    $pendingCount = 0;
    if ($role === 'kacab') {
        $pendingCount = RiskReport::where('branch_id', $userBranchId)
            ->where('approval_status', 'pending_kacab')
            ->count();
    } elseif ($role === 'korwil') {
        $pendingCount = RiskReport::whereIn('branch_id', $branchIds)
            ->where('approval_status', 'pending_korwil')
            ->count();
    }

    return view('dashboard', compact(
        'recentReports',
        'totalLaporanBulanIni',
        'totalPending',
        'totalApproved',
        'totalLossApproved',
        'pendingCount',
        'role',
        'chartMonths',
        'chartCounts',
        'chartFinansial',
        'chartNonFinansial',
        'chartOpen',
        'chartInProgress',
        'chartClosed',
        'rankingRisikoLabels',
        'rankingRisikoData',
        'rankingRisikoColors',
        'sumberRisikoLabels',
        'sumberRisikoData',
        'sumberRisikoColors',
        'trenTop5Labels',
        'trenTop5Datasets'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');


// =========================================================================
// AREA PENGGUNA LOGIN (MAKER & CHECKER)
// Semua rute di sini udah dibungkus 1 gembok auth biar nggak numpuk
// =========================================================================
Route::middleware('auth')->group(function () {

    // --- Profile Bawaan Laravel Breeze ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- MENU 1: INPUT LAPORAN (MAKER) ---
    Route::get('/form-risiko/{kategori}', [RiskReportController::class, 'create'])->name('form.risiko');
    Route::post('/form-risiko', [RiskReportController::class, 'store'])->name('form.risiko.store');

    // --- MENU 2: REVIEW & TINDAK LANJUT (CHECKER: KACAB & KORWIL) ---
    Route::get('/review-laporan', [RiskReportController::class, 'review'])->name('review.laporan');

    // Ini perbaikan rute persetujuan (Approve/Reject)
    Route::post('/risk-reports/{id}/status', [RiskReportController::class, 'updateStatus'])->name('risk_reports.update_status');

    // Ini rute baru buat Tindak Lanjut (Monitoring/Closed)
    Route::post('/risk-reports/{id}/resolution', [RiskReportController::class, 'updateResolution'])->name('risk_reports.update_resolution');

    // --- MENU 3: RIWAYAT KESELURUHAN ---
    Route::get('/riwayat-risiko', [RiskReportController::class, 'index'])->name('risk.history');

    // Rute Detail & Progress Laporan
    Route::get('/risk-report/{id}', [RiskReportController::class, 'show'])->name('risk_reports.show');

    // Rute untuk nambahin Progress Catatan (Action POST dari halaman show)
    Route::post('/risk-report/{id}/progress', [RiskReportController::class, 'addProgress'])->name('risk_reports.add_progress');

    // --- RUTE REVISI LAPORAN ---
    Route::post('/risk-report/{id}/request-revision', [RiskReportController::class, 'requestRevision'])->name('risk_reports.request_revision');
    Route::post('/risk-report/{id}/submit-revision', [RiskReportController::class, 'submitRevision'])->name('risk_reports.submit_revision');
    Route::post('/risk-report/{id}/approve-revision', [RiskReportController::class, 'approveRevision'])->name('risk_reports.approve_revision');

    // --- NOTIFIKASI IN-APP ---
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark_all_read');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread_count');

    // --- EXPORT CSV ---
    Route::get('/export-risiko', [ExportRiskReportController::class, 'export'])->name('risk.export');
});


// =========================================================================
// AREA KHUSUS DEWA APLIKASI (MANAJEMEN RISIKO)
// =========================================================================
Route::middleware(['auth', 'role:manrisk'])->group(function () {
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

    // Rute buat update penyebab & mitigasi
    Route::patch('/admin/risk-master/cause/{id}', [\App\Http\Controllers\Admin\RiskMasterController::class, 'updateCause'])->name('admin.risk_master.update_cause');

    // Rute tambah mitigasi ke cause yang sudah ada (terpisah dari storeCause)
    Route::post('/admin/risk-master/cause/{causeId}/mitigation', [\App\Http\Controllers\Admin\RiskMasterController::class, 'storeMitigation'])->name('admin.risk_master.store_mitigation');

    // Rute Manajemen Master Data Cabang (Khusus ManRisk)
    Route::get('/branches-management', [App\Http\Controllers\BranchManagementController::class, 'index'])->name('branches.index');
    Route::put('/branches-management/{id}', [App\Http\Controllers\BranchManagementController::class, 'update'])->name('branches.update');
    Route::post('/branches-management', [App\Http\Controllers\BranchManagementController::class, 'store'])->name('branches.store');

});

require __DIR__ . '/auth.php';
