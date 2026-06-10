<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-1">
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                    Selamat Datang kembali, {{ Auth::user()->name }}!
                </h2>
                <p class="text-sm text-slate-500">
                    Ringkasan aktivitas hari ini —
                    <span class="font-semibold text-indigo-600">{{ now()->format('l, d F Y') }}</span>
                </p>
            </div>
            
            {{-- QUICK ACTION BUTTONS (MAKER & CHECKER) --}}
            @if(Auth::user()->isMaker() || Auth::user()->isChecker())
            <div class="flex items-center gap-3">
                <a href="{{ route('form.risiko', 'finansial') }}" class="btn-primary py-2 px-4 shadow-sm hover:shadow-md transition whitespace-nowrap">
                    <span class="mr-2">💸</span>+ Lapor Finansial
                </a>
                <a href="{{ route('form.risiko', 'non-finansial') }}" class="btn-secondary bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100 py-2 px-4 shadow-sm hover:shadow-md transition whitespace-nowrap">
                    <span class="mr-2">⚠️</span>+ Lapor Non-Finansial
                </a>
            </div>
            @endif
        </div>
    </x-slot>

    {{-- ============================================================
         STAT CARDS
         ============================================================ --}}
    @if(Auth::user()->roleCategory() === 'maker')
    {{-- Staff: 3 card --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5 mb-8">
        <div class="stat-card border-l-4 border-l-amber-400">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">Menunggu Review</p>
                <span class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="stat-card-value text-amber-600">{{ $totalPending }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Perlu approval</p>
        </div>
        <div class="stat-card border-l-4 border-l-sky-500">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">Perlu Ditindak Lanjut</p>
                <span class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </span>
            </div>
            <p class="stat-card-value text-sky-600">{{ $totalInProgress }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Belum closed</p>
        </div>
        <div class="stat-card border-l-4 border-l-indigo-500">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">{{ $labelTotalLaporan }}</p>
                <span class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <p class="stat-card-value">{{ $totalClosed }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Selesai</p>
        </div>
    </div>
    @else
    {{-- Kacab/Korwil/ManRisk: 4 card --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-8">
        <div class="stat-card border-l-4 border-l-amber-400">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">Menunggu Review</p>
                <span class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="stat-card-value text-amber-600">{{ $totalPending }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Perlu approval</p>
        </div>
        <div class="stat-card border-l-4 border-l-sky-500">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">Perlu Ditindak Lanjut</p>
                <span class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </span>
            </div>
            <p class="stat-card-value text-sky-600">{{ $totalInProgress }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Belum closed</p>
        </div>
        <div class="stat-card border-l-4 border-l-indigo-500">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">{{ $labelTotalLaporan }}</p>
                <span class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <p class="stat-card-value">{{ $totalClosed }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Selesai</p>
        </div>
        <div class="stat-card border-l-4 border-l-rose-500">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">Nilai Dampak</p>
                <span class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="stat-card-value text-rose-600">Rp {{ number_format($totalLossApproved, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Total Dampak</p>
        </div>
    </div>
    @endif

    {{-- ============================================================
         FILTER ADMIN/VIEWER
         ============================================================ --}}
    @if(Auth::user()->isAdmin() || Auth::user()->isViewer())
    <div class="surface-card section-pad mb-6" x-data="filterManager()">
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">📅 Pilih Bulan</label>
                <div class="relative" @click.away="monthOpen = false">
                    <button type="button" @click="monthOpen = !monthOpen" class="w-full flex items-center justify-between border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 bg-white hover:border-indigo-300 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-50">
                        <span x-text="monthCount > 0 ? monthCount + ' bulan dipilih' : '📅 Semua Bulan (12 bln)'" class="truncate"></span>
                        <svg class="w-4 h-4 text-slate-400 ml-2 flex-shrink-0" :class="monthOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="monthOpen" style="display: none;" class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-72 overflow-y-auto">
                        <label class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 cursor-pointer border-b border-slate-100">
                            <input type="checkbox" @click="toggleAllMonths($event)" :checked="allMonthsSelected" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-semibold text-slate-700">Pilih Semua</span>
                        </label>
                        <template x-for="yearGroup in groupedMonths" :key="yearGroup.year">
                            <div>
                                <button type="button" @click="toggleYear(yearGroup.year)" class="w-full flex items-center justify-between px-3 py-2 text-sm font-bold text-slate-600 bg-slate-50 hover:bg-slate-100 border-b border-slate-100">
                                    <span x-text="yearGroup.year"></span>
                                    <svg class="w-4 h-4 text-slate-400 transition-transform" :class="expandedYears.includes(yearGroup.year) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <template x-for="month in yearGroup.months" :key="month.value">
                                    <label x-show="expandedYears.includes(yearGroup.year)" class="flex items-center gap-2 px-3 py-1.5 pl-8 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" name="bulan[]" :value="month.value" x-model="selectedMonths" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-slate-700" x-text="month.label"></span>
                                    </label>
                                </template>
                            </div>
                        </template>
                    </div>
                    <input type="hidden" name="bulan" value="" x-bind:disabled="selectedMonths.length > 0">
                </div>
            </div>

            @if(Auth::user()->isAdmin())
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">🏢 Cabang</label>
                <div class="relative" @click.away="branchOpen = false">
                    <button type="button" @click="branchOpen = !branchOpen" class="w-full flex items-center justify-between border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 bg-white hover:border-indigo-300 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-50">
                        <span x-text="branchCount > 0 ? branchCount + ' cabang dipilih' : '🏦 Semua Cabang'" class="truncate"></span>
                        <svg class="w-4 h-4 text-slate-400 ml-2 flex-shrink-0" :class="branchOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="branchOpen" style="display: none;" class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <div class="p-2 border-b border-slate-100">
                            <input type="text" x-model="branchSearch" placeholder="🔍 Cari cabang..." class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-md focus:border-indigo-300 focus:ring-2 focus:ring-indigo-50">
                        </div>
                        <label class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 cursor-pointer border-b border-slate-100">
                            <input type="checkbox" @click="toggleAllBranches($event)" :checked="allBranchesSelected" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-semibold text-slate-700">Pilih Semua</span>
                        </label>
                        <template x-for="branch in filteredBranches" :key="branch.id">
                            <label class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="cabang_ids[]" :value="branch.id" x-model="selectedBranches" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-slate-700" x-text="branch.nama"></span>
                            </label>
                        </template>
                        <p x-show="filteredBranches.length === 0" class="px-3 py-4 text-sm text-slate-400 text-center">Tidak ada cabang ditemukan</p>
                    </div>
                    <input type="hidden" name="cabang_ids" value="" x-bind:disabled="selectedBranches.length > 0">
                </div>
            </div>
            @endif

            <div class="flex-shrink-0 flex items-center gap-2">
                <button type="submit" class="btn-primary btn-sm w-full sm:w-auto">
                    Terapkan Filter
                </button>
                @if(request('bulan') || request('cabang_ids'))
                <a href="{{ route('dashboard') }}" class="btn-ghost btn-sm text-slate-500">Reset</a>
                @endif
            </div>
        </form>
    </div>
    @endif

    {{-- ============================================================
         GRID UTAMA (70% Kiri, 30% Kanan)
         ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
        
        {{-- ================= KONTEN UTAMA (KIRI 70%) ================= --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- MAKER: TIMELINE AKTIVITAS --}}
            @if(Auth::user()->isMaker())
            <div class="surface-card p-5 sm:p-6">
                <h3 class="section-title mb-4">🕒 Aktivitas Terakhir Saya</h3>
                <div class="relative pl-4 border-l-2 border-slate-200 space-y-6">
                    @forelse($recentReports->take(5) as $report)
                    <div class="relative">
                        <div class="absolute -left-[25px] top-1 w-3 h-3 rounded-full {{ $report->status === 'closed' ? 'bg-indigo-500' : ($report->status === 'need_revision' ? 'bg-rose-500' : 'bg-amber-400') }} border-2 border-white"></div>
                        <p class="text-xs text-slate-400 mb-1">{{ $report->updated_at->diffForHumans() }}</p>
                        <p class="text-sm text-slate-800">
                            Laporan <a href="{{ route('risk_reports.show', $report->id) }}" class="font-bold text-indigo-600 hover:underline">{{ $report->nomor_laporan }}</a> 
                            - {{ $report->item->nama_item ?? 'Risiko' }}
                        </p>
                        <p class="text-xs font-semibold mt-1">Status: <span class="uppercase text-slate-500">{{ str_replace('_', ' ', $report->status) }}</span></p>
                    </div>
                    @empty
                    <p class="text-sm text-slate-500">Belum ada aktivitas laporan akhir-akhir ini.</p>
                    @endforelse
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <a href="{{ route('risk.history') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Lihat Semua Riwayat Laporan &rarr;</a>
                </div>
            </div>
            @endif

            {{-- CHECKER: TUGAS SAYA (To-Do List) --}}
            @if(Auth::user()->isChecker())
            <div class="surface-card p-5 sm:p-6">
                <h3 class="section-title mb-4">📋 Tugas Saya: Menunggu Review</h3>
                <div class="space-y-4">
                    @forelse($myTasks as $task)
                    <div class="p-4 border border-slate-200 rounded-xl hover:border-indigo-300 transition-colors">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $task->nomor_laporan }} <span class="font-normal text-slate-500">— {{ $task->item->nama_item ?? 'Laporan' }}</span></p>
                                <p class="text-xs text-slate-500 mt-1">Dari: <span class="font-semibold">{{ $task->user->name ?? 'Staf' }}</span> | Menunggu sejak {{ $task->updated_at->diffForHumans() }}</p>
                            </div>
                            <a href="{{ route('risk_reports.show', $task->id) }}" class="btn-primary py-1 px-3 text-xs whitespace-nowrap">Review Cepat &rarr;</a>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <p class="text-slate-500 text-sm">Tidak ada tugas review saat ini. Kerja bagus! 🎉</p>
                    </div>
                    @endforelse
                </div>
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <a href="{{ route('risk.history') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Buka Tabel Riwayat Lengkap &rarr;</a>
                </div>
            </div>

            {{-- CHECKER: LAPORAN IN PROGRESS --}}
            <div class="surface-card p-5 sm:p-6 mt-6">
                <h3 class="section-title mb-4">⚠️ Laporan Open/In Progress</h3>
                <div class="space-y-4">
                    @forelse($inProgressReports as $inProg)
                    <div class="p-4 border border-slate-200 rounded-xl hover:border-orange-300 transition-colors">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $inProg->nomor_laporan }} <span class="font-normal text-slate-500">— {{ $inProg->item->nama_item ?? 'Laporan' }}</span></p>
                                <p class="text-xs text-slate-500 mt-1">Status: <span class="uppercase font-semibold text-orange-600">{{ str_replace('_', ' ', $inProg->status) }}</span></p>
                            </div>
                            <a href="{{ route('risk_reports.show', $inProg->id) }}" class="btn-secondary py-1 px-3 text-xs whitespace-nowrap border-orange-200 text-orange-700 bg-orange-50 hover:bg-orange-100">Update Status &rarr;</a>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <p class="text-slate-500 text-sm">Tidak ada laporan yang menggantung/in progress saat ini. Mantap! 👍</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @endif

            {{-- VIEWER / ADMIN: CHARTS --}}
            @if(Auth::user()->isViewer() || Auth::user()->isAdmin())
                @if(Auth::user()->isViewer())
                {{-- Frekuensi Cabang Lapor Risiko (Viewer Only) --}}
                <div class="surface-card section-pad mb-6">
                    <h3 class="section-title text-base mb-4">🏢 Frekuensi Cabang Lapor Risiko</h3>
                    @if(count($topCabangData ?? []) > 0)
                    <div class="relative" style="height: 300px;">
                        <canvas id="topCabangChart" style="height: 100% !important; width: 100% !important;"></canvas>
                    </div>
                    @else
                    <div class="flex items-center justify-center p-6 border-2 border-dashed border-slate-200 rounded-lg bg-slate-50">
                        <p class="text-sm font-semibold text-slate-400">Belum ada cabang yang melaporkan risiko bulan ini.</p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Ranking Risiko (Bar) --}}
                <div class="surface-card section-pad">
                    <h3 class="section-title text-base mb-4">🏆 Ranking Risiko (Terbanyak)</h3>
                    @if(count($rankingRisikoData ?? []) > 0)
                    <div class="relative" style="height: 300px;">
                        <canvas id="rankingRisikoChart" style="height: 100% !important; width: 100% !important;"></canvas>
                    </div>
                    @else
                    <div class="flex items-center justify-center p-6 border-2 border-dashed border-slate-200 rounded-lg bg-slate-50">
                        <p class="text-sm font-semibold text-slate-400">Belum ada laporan risiko bulan ini.</p>
                    </div>
                    @endif
                </div>

                @if(Auth::user()->isAdmin())
                {{-- Tren Risiko (Line) - Admin Only --}}
                <div class="surface-card section-pad mt-6">
                    <h3 class="section-title text-base mb-4">📈 Tren Top-5 Risiko (6 Bulan)</h3>
                    @if(count($trenTop5Datasets ?? []) > 0)
                    <div class="relative" style="height: 260px;">
                        <canvas id="trenTop5Chart" style="height: 100% !important; width: 100% !important;"></canvas>
                    </div>
                    @else
                    <div class="flex items-center justify-center p-6 border-2 border-dashed border-slate-200 rounded-lg bg-slate-50">
                        <p class="text-sm font-semibold text-slate-400">Belum ada tren data risiko dalam 6 bulan terakhir.</p>
                    </div>
                    @endif
                </div>
                @endif
            @endif

        </div>

        {{-- ============================================================
         DEKLARASI NIHIL RISIKO (Khusus Kacab)
         ============================================================ --}}
    @if(Auth::user()->isKacab())
    @php
        $hariIni = now()->day;
        $periodeSekarang = $hariIni <= 14 ? '1' : '2';
        $periodeLabel = $periodeSekarang === '1' ? '1 - 14' : '15 - ' . now()->daysInMonth;
        $sudahDeklarasi = \App\Models\RiskFreeDeclaration::where('branch_id', Auth::user()->branch_id)
            ->where('periode', $periodeSekarang)
            ->where('bulan', now()->month)
            ->where('tahun', now()->year)
            ->exists();
        $deklarasiAktif = \App\Models\RiskFreeDeclaration::where('branch_id', Auth::user()->branch_id)
            ->where('status', 'active')
            ->count();
    @endphp
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-1 h-6 bg-green-500 rounded-full"></div>
            <h3 class="section-title">Deklarasi Nihil Risiko</h3>
        </div>
        <div class="surface-card section-pad">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">
                        Periode <strong>{{ $periodeSekarang }}</strong> (Tanggal {{ $periodeLabel }} {{ now()->translatedFormat('F Y') }})
                    </p>
                    @if ($sudahDeklarasi)
                        <p class="text-sm text-green-600 font-medium mt-1">
                            ✅ Deklarasi untuk periode ini sudah dilakukan.
                        </p>
                    @else
                        <p class="text-sm text-amber-600 font-medium mt-1">
                            ⏳ Belum melakukan deklarasi untuk periode ini.
                        </p>
                    @endif
                    @if ($deklarasiAktif > 0)
                        <p class="text-xs text-slate-400 mt-1">
                            Total {{ $deklarasiAktif }} deklarasi aktif tersimpan.
                        </p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('risk_free_declarations.create') }}" 
                       class="btn-primary btn-sm {{ $sudahDeklarasi ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $sudahDeklarasi ? 'Sudah Dideklarasikan' : 'Deklarasi Nihil' }}
                    </a>
                    <a href="{{ route('risk_free_declarations.history') }}" class="btn-ghost btn-sm text-slate-500">
                        Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

        {{-- ================= SIDEBAR (KANAN 30%) ================= --}}
        <div class="space-y-6">
            
            {{-- MAKER: ALERT REVISI --}}
            @if(Auth::user()->isMaker())
            @if($makerRevisions->count() > 0)
            <div class="surface-card p-5 border-t-4 border-rose-500 shadow-sm bg-rose-50/30">
                <h3 class="text-sm font-bold text-rose-700 flex items-center mb-3"><span class="mr-2">🚨</span> ALERT: BUTUH REVISI!</h3>
                <div class="space-y-3">
                    @foreach($makerRevisions as $rev)
                    <div class="bg-white p-3 rounded-lg border border-rose-100 shadow-sm">
                        <p class="text-sm font-bold text-slate-800">{{ $rev->nomor_laporan }}</p>
                        <p class="text-xs text-slate-600 mt-1 line-clamp-2">Laporan ini ditolak oleh atasan dan membutuhkan perbaikan segera.</p>
                        <a href="{{ route('risk_reports.show', $rev->id) }}" class="mt-2 inline-block text-xs font-semibold text-rose-600 hover:text-rose-800">Revisi Sekarang &rarr;</a>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="surface-card p-5 border-t-4 border-emerald-500 shadow-sm bg-emerald-50/30">
                <h3 class="text-sm font-bold text-emerald-700 flex items-center mb-3"><span class="mr-2">✅</span> STATUS REVISI</h3>
                <p class="text-xs text-emerald-600 font-semibold">Tidak ada laporan yang butuh revisi saat ini. Kerja bagus!</p>
            </div>
            @endif

            {{-- MAKER: DISTRIBUSI RISIKO SAYA (DONUT CHART) --}}
            <div class="surface-card p-5 mt-6">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">📈 Distribusi Risiko Saya</p>
                @if(array_sum($makerDistribusiData ?? []) > 0)
                <div style="height: 180px;">
                    <canvas id="makerDistribusiChart" style="height: 100% !important; max-height: 180px; width: auto; max-width: 180px; margin: auto;"></canvas>
                </div>
                @else
                <div class="flex items-center justify-center h-32 border-2 border-dashed border-slate-200 rounded-lg bg-slate-50">
                    <p class="text-xs font-semibold text-slate-400 text-center">Belum ada laporan yang Anda buat.</p>
                </div>
                @endif
            </div>
            @endif

            {{-- CHECKER: INSIGHT CABANG BULAN INI --}}
            @if(Auth::user()->isChecker())
            <div class="surface-card p-5">
                <h3 class="section-title text-sm mb-4">📊 Insight Cabang Bulan Ini</h3>
                <div class="mb-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Top Risiko Tertinggi</p>
                    <ul class="space-y-2">
                        @forelse(collect($rankingRisikoLabels ?? [])->take(3) as $index => $label)
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-slate-700">{{ $index + 1 }}. {{ $label }}</span>
                            <span class="font-bold text-indigo-600">{{ $rankingRisikoData[$index] ?? 0 }}x</span>
                        </li>
                        @empty
                        <li class="text-xs text-slate-400">Belum ada data risiko bulan ini.</li>
                        @endforelse
                    </ul>
                </div>
                
                {{-- Mini Chart Sumber Risiko --}}
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sumber Risiko</p>
                    @if(count($sumberRisikoData ?? []) > 0)
                    <div style="height: 150px;">
                        <canvas id="sumberRisikoChart" style="height: 100% !important; max-height: 150px; width: auto; max-width: 150px; margin: auto;"></canvas>
                    </div>
                    @else
                    <div class="flex items-center justify-center h-24 border-2 border-dashed border-slate-200 rounded-lg bg-slate-50">
                        <p class="text-xs font-semibold text-slate-400">Data kosong</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- VIEWER / ADMIN: ALERT & INSIDEN KRITIS --}}
            @if(Auth::user()->isViewer() || Auth::user()->isAdmin())
                @if(isset($kritisReports) && $kritisReports->count() > 0)
                <div class="surface-card p-5 border-t-4 border-rose-500 shadow-sm bg-rose-50/30">
                    <h3 class="text-sm font-bold text-rose-700 flex items-center mb-3"><span class="mr-2">🚨</span> INSIDEN KRITIS (RED ALERT)</h3>
                    <div class="space-y-3">
                        @foreach($kritisReports as $kr)
                        <div class="bg-white p-3 rounded-lg border border-rose-100 shadow-sm">
                            <p class="text-xs font-semibold text-slate-500">{{ $kr->branch->nama_cabang ?? 'Unknown' }}</p>
                            <p class="text-sm font-bold text-slate-800">{{ $kr->nomor_laporan }}</p>
                            @if($kr->dampak_finansial > 0)
                                <p class="text-xs text-rose-600 font-bold mt-1">Dampak: Rp {{ number_format($kr->dampak_finansial, 0, ',', '.') }}</p>
                            @else
                                <div class="mt-1 flex items-center gap-1 whitespace-nowrap">
                                    <span class="text-xs text-rose-600 font-bold">Skala Dampak:</span>
                                    <x-skala-badge :skala="$kr->skala_dampak" />
                                </div>
                            @endif
                            <a href="{{ route('risk_reports.show', $kr->id) }}" class="mt-2 inline-block text-xs font-semibold text-rose-600 hover:text-rose-800">Investigasi &rarr;</a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="surface-card p-5 border-t-4 border-emerald-500 shadow-sm bg-emerald-50/30">
                    <h3 class="text-sm font-bold text-emerald-700 flex items-center mb-3"><span class="mr-2">✅</span> INSIDEN KRITIS</h3>
                    <p class="text-xs text-emerald-600 font-semibold">Belum ada insiden kritis atau laporan berisiko tinggi saat ini. Kondisi aman terkendali.</p>
                </div>
                @endif
                
                @if(Auth::user()->isAdmin())
                {{-- Tabel Rekap Deklarasi Nihil Risiko --}}
        @if(count($deklarasiSummaries) > 0)
        <div class="surface-card overflow-hidden">
            <div class="flex items-center gap-3 mb-4 px-5 pt-5">
                <div class="w-1 h-5 bg-green-500 rounded-full"></div>
                <h3 class="section-title text-base">✅ Rekap Deklarasi Nihil Risiko</h3>
            </div>
            <div class="table-wrap">
                <table class="table-min">
                    <thead>
                        <tr>
                            <th class="table-th">Cabang</th>
                            <th class="table-th text-center">Periode 1</th>
                            <th class="table-th text-center">Periode 2</th>
                            <th class="table-th text-center">Total</th>
                            <th class="table-th text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @foreach($deklarasiSummaries as $d)
                        <tr class="table-tr">
                            <td class="table-td font-semibold text-slate-800">{{ $d['nama'] }}</td>
                            <td class="table-td text-center">
                                @if($d['periode1'])
                                    <span class="text-emerald-600 font-semibold">✅</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="table-td text-center">
                                @if($d['periode2'])
                                    <span class="text-emerald-600 font-semibold">✅</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="table-td text-center font-bold text-slate-700">
                                {{ ($d['periode1'] ? 1 : 0) + ($d['periode2'] ? 1 : 0) }} / 2
                            </td>
                            <td class="table-td text-center">
                                @if($d['periode1'] && $d['periode2'])
                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded">LENGKAP</span>
                                @else
                                    <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded">BELUM LENGKAP</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
                {{-- ADMIN: CABANG BELUM DEKLARASI --}}
                <div class="surface-card p-5 mt-6 border-t-4 border-orange-400 bg-orange-50/30">
                    <h3 class="text-sm font-bold text-orange-700 flex items-center mb-3"><span class="mr-2">🏆</span> BELUM DEKLARASI NIHIL</h3>
                    <p class="text-xs text-slate-500 mb-3">Daftar cabang yang tidak memiliki laporan risiko namun belum mengirim deklarasi bulan ini:</p>
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        @forelse($cabangBelumDeklarasi ?? [] as $namaCabang)
                        <div class="flex items-center text-sm text-slate-700 bg-white p-2 rounded-md border border-orange-100">
                            <span class="mr-2 text-orange-500">❌</span> {{ $namaCabang }}
                        </div>
                        @empty
                        <p class="text-xs text-emerald-600 font-semibold bg-emerald-50 p-2 rounded border border-emerald-100">Semua cabang aman / sudah deklarasi!</p>
                        @endforelse
                    </div>
                    <div class="mt-4 pt-3 border-t border-orange-200">
                        <a href="{{ route('risk_free_declarations.history') }}" class="text-sm font-semibold text-orange-700 hover:text-orange-900">Lihat Semua Riwayat Deklarasi &rarr;</a>
                    </div>
                </div>

                {{-- Chart Sumber Risiko for Admin (since Kacab has it above) --}}
                <div class="surface-card p-5 mt-6">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">🎯 Distribusi Sumber Risiko</p>
                    @if(count($sumberRisikoData ?? []) > 0)
                    <div style="height: 180px;">
                        <canvas id="sumberRisikoChart" style="height: 100% !important; max-height: 180px; width: auto; max-width: 180px; margin: auto;"></canvas>
                    </div>
                    @else
                    <div class="flex items-center justify-center h-32 border-2 border-dashed border-slate-200 rounded-lg bg-slate-50">
                        <p class="text-xs font-semibold text-slate-400">Data kosong</p>
                    </div>
                    @endif
                </div>
                @endif

                @if(Auth::user()->isViewer())
                {{-- Chart Sumber Risiko for Viewer --}}
                <div class="surface-card p-5 mt-6">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">🎯 Distribusi Sumber Risiko</p>
                    @if(count($sumberRisikoData ?? []) > 0)
                    <div style="height: 180px;">
                        <canvas id="sumberRisikoChart" style="height: 100% !important; max-height: 180px; width: auto; max-width: 180px; margin: auto;"></canvas>
                    </div>
                    @else
                    <div class="flex items-center justify-center h-32 border-2 border-dashed border-slate-200 rounded-lg bg-slate-50">
                        <p class="text-xs font-semibold text-slate-400">Data kosong</p>
                    </div>
                    @endif
                </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ============================================================
         SCRIPTS (Alpine JS Filters & Chart JS)
         ============================================================ --}}
    @if(Auth::user()->isAdmin() || Auth::user()->isViewer())
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('filterManager', () => ({
                monthOpen: false,
                selectedMonths: @js($bulanFilters ?? []),
                expandedYears: @js(collect($availableMonths ?? [])->pluck('value')->map(fn($v) => substr($v, 0, 4))->unique()->sort()->values()->toArray()),
                get groupedMonths() {
                    const groups = {};
                    @js($availableMonths ?? []).forEach(m => {
                        const year = m.value.substring(0, 4);
                        if (!groups[year]) groups[year] = [];
                        groups[year].push(m);
                    });
                    return Object.keys(groups).sort().reverse().map(year => ({ year: year, months: groups[year] }));
                },
                get monthCount() { return this.selectedMonths.length; },
                get allMonthsSelected() { return this.selectedMonths.length === {{ count($availableMonths ?? []) }}; },
                toggleAllMonths(event) { this.selectedMonths = event.target.checked ? @js(collect($availableMonths ?? [])->pluck('value')->toArray()) : []; },
                toggleYear(year) {
                    const idx = this.expandedYears.indexOf(year);
                    if (idx > -1) this.expandedYears.splice(idx, 1);
                    else this.expandedYears.push(year);
                },

                branchOpen: false,
                branchSearch: '',
                selectedBranches: @js($cabangFilter ?? []),
                allBranchesData: @js(collect($allBranches ?? [])->map(fn($b) => ['id' => $b->id, 'nama' => $b->nama_cabang])->values()->toArray()),
                get filteredBranches() {
                    if (!this.branchSearch) return this.allBranchesData;
                    const q = this.branchSearch.toLowerCase();
                    return this.allBranchesData.filter(b => b.nama.toLowerCase().includes(q));
                },
                get branchCount() { return this.selectedBranches.length; },
                get allBranchesSelected() { return this.selectedBranches.length === {{ count($allBranches ?? []) }}; },
                toggleAllBranches(event) { this.selectedBranches = event.target.checked ? @js(collect($allBranches ?? [])->pluck('id')->toArray()) : []; }
            }));
        });
    </script>
    @endpush
    @endif

    {{-- Chart.js Scripts --}}
    @if(Auth::user()->isMaker() || Auth::user()->isChecker() || Auth::user()->isViewer() || Auth::user()->isAdmin())
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rankingCtx = document.getElementById('rankingRisikoChart');
            if (rankingCtx) {
                const rankingFullLabels = {!! json_encode($rankingRisikoFullLabels ?? []) !!};
                new Chart(rankingCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($rankingRisikoLabels ?? []) !!},
                        datasets: [{
                            label: 'Jumlah Kejadian',
                            data: {!! json_encode($rankingRisikoData ?? []) !!},
                            backgroundColor: {!! json_encode($rankingRisikoColors ?? []) !!},
                            borderRadius: 4,
                        }]
                    },
                    options: { 
                        indexAxis: 'y', 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: function(context) {
                                        return rankingFullLabels[context[0].dataIndex];
                                    }
                                }
                            }
                        } 
                    }
                });
            }

            const sumberCtx = document.getElementById('sumberRisikoChart');
            if (sumberCtx) {
                new Chart(sumberCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($sumberRisikoLabels ?? []) !!},
                        datasets: [{
                            data: {!! json_encode($sumberRisikoData ?? []) !!},
                            backgroundColor: {!! json_encode($sumberRisikoColors ?? []) !!},
                            borderWidth: 3,
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: {font: {size: 11}, usePointStyle: true} } }, cutout: '60%' }
                });
            }

            const trenCtx = document.getElementById('trenTop5Chart');
            if (trenCtx) {
                new Chart(trenCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trenTop5Labels ?? []) !!},
                        datasets: {!! json_encode($trenTop5Datasets ?? []) !!}
                    },
                    options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { position: 'bottom' } } }
                });
            }

            const topCabangCtx = document.getElementById('topCabangChart');
            if (topCabangCtx) {
                new Chart(topCabangCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($topCabangLabels ?? []) !!},
                        datasets: [{
                            label: 'Jumlah Laporan',
                            data: {!! json_encode($topCabangData ?? []) !!},
                            backgroundColor: {!! json_encode($topCabangColors ?? []) !!},
                            borderRadius: 4,
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }

            const makerCtx = document.getElementById('makerDistribusiChart');
            if (makerCtx) {
                new Chart(makerCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($makerDistribusiLabels ?? []) !!},
                        datasets: [{
                            data: {!! json_encode($makerDistribusiData ?? []) !!},
                            backgroundColor: {!! json_encode($makerDistribusiColors ?? []) !!},
                            borderWidth: 3,
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: {font: {size: 11}, usePointStyle: true} } }, cutout: '60%' }
                });
            }
        });
    </script>
    @endif
</x-app-layout>
