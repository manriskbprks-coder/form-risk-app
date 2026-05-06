<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Selamat Datang kembali, {{ Auth::user()->name }}!
            </h2>
            <p class="text-sm text-slate-500">
                Ringkasan aktivitas hari ini —
                <span class="font-semibold text-indigo-600">{{ now()->format('l, d F Y') }}</span>
            </p>
        </div>
    </x-slot>

    {{-- ============================================================
         GREETING CARD + USER INFO
         ============================================================ --}}
    <div class="surface-card overflow-hidden border-l-4 border-indigo-500 mb-8">
        <div class="section-pad">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ Auth::user()->name }}
                    </h3>
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm text-slate-500">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="font-semibold text-slate-700">{{ Auth::user()->branch->nama_cabang ?? 'Pusat (HQ)' }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-md text-xs font-bold uppercase">{{ Auth::user()->primaryRoleName() ?? 'Tidak Ada Jabatan' }}</span>
                        </span>
                    </div>
                </div>
                <div class="hidden sm:block">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         STAT CARDS — 4 columns
         ============================================================ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-8">
        <div class="stat-card border-l-4 border-l-indigo-500">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">Laporan Saya</p>
                <span class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <p class="stat-card-value">{{ $totalLaporanBulanIni }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Bulan ini</p>
        </div>
        <div class="stat-card border-l-4 border-l-amber-400">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">Menunggu Review</p>
                <span class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="stat-card-value text-amber-600">{{ $totalPending }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Perlu ditindaklanjuti</p>
        </div>
        <div class="stat-card border-l-4 border-l-emerald-500">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">Tervalidasi</p>
                <span class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="stat-card-value text-emerald-600">{{ $totalApproved }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Laporan valid</p>
        </div>
        @hasanyrole('kacab|korwil|manrisk')
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
        @else
        <a href="{{ route('risk.history', ['resolution_status' => 'in_progress']) }}" class="stat-card border-l-4 border-l-sky-500 block hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">Dalam Progres</p>
                <span class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </span>
            </div>
            <p class="stat-card-value text-sky-600">{{ $totalInProgress }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Perlu ditindaklanjuti</p>
        </a>
        @endhasanyrole
    </div>

    {{-- ============================================================
         FILTER DROPDOWN (Khusus ManRisk)
         ============================================================ --}}
    @hasrole('manrisk')
    <div class="surface-card section-pad mb-6">
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">📅 Periode Waktu</label>
                <select name="periode" onchange="this.form.submit()" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 bg-white focus:border-indigo-300 focus:ring-2 focus:ring-indigo-50">
                    <option value="1" {{ $periode == 1 ? 'selected' : '' }}>1 Bulan Terakhir</option>
                    <option value="3" {{ $periode == 3 ? 'selected' : '' }}>3 Bulan Terakhir</option>
                    <option value="6" {{ $periode == 6 ? 'selected' : '' }}>6 Bulan Terakhir</option>
                    <option value="12" {{ $periode == 12 ? 'selected' : '' }}>1 Tahun Terakhir</option>
                    <option value="0" {{ $periode == 0 ? 'selected' : '' }}>Semua Waktu</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">🏢 Cabang</label>
                <select name="cabang_id" onchange="this.form.submit()" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 bg-white focus:border-indigo-300 focus:ring-2 focus:ring-indigo-50">
                    <option value="all" {{ $cabangFilter == 'all' ? 'selected' : '' }}>🏦 Bank Wide (Semua Cabang)</option>
                    @foreach($allBranches as $branch)
                    <option value="{{ $branch->id }}" {{ $cabangFilter == $branch->id ? 'selected' : '' }}>
                        {{ $branch->nama_cabang }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-shrink-0">
                <button type="submit" class="btn-primary btn-sm w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Terapkan Filter
                </button>
                @if(request('periode') || request('cabang_id'))
                <a href="{{ route('dashboard') }}" class="btn-ghost btn-sm text-slate-500 ml-2">Reset</a>
                @endif
            </div>
        </form>
    </div>
    @endhasrole

    {{-- ============================================================
         CHART ANALISA RISIKO SECTION (Khusus Kacab/Korwil/Manrisk)
         HIDE: Diatur lewat env SHOW_CHARTS=true — default false
         ============================================================ --}}
    @if(env('SHOW_CHARTS', false))
    @hasanyrole('kacab|korwil|manrisk')
    {{-- Baris 1: Ranking Risiko + Sumber Risiko --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5 mb-8">
        {{-- Ranking Risiko (Horizontal Bar) --}}
        <div class="surface-card section-pad">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-5 bg-rose-500 rounded-full"></div>
                <h3 class="section-title text-base">🏆 Ranking Risiko</h3>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="rankingRisikoChart" style="height: 100% !important; width: 100% !important;"></canvas>
            </div>
        </div>

        {{-- Sumber Risiko (Doughnut) --}}
        <div class="surface-card section-pad">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-5 bg-emerald-500 rounded-full"></div>
                <h3 class="section-title text-base">🎯 Sumber Risiko</h3>
            </div>
            <div class="flex items-center justify-center" style="height: 300px;">
                <canvas id="sumberRisikoChart" style="height: 100% !important; max-height: 300px; width: auto; max-width: 300px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Baris 2: Tren Top-5 Risiko (Full Width) --}}
    <div class="grid grid-cols-1 gap-4 sm:gap-5 mb-8">
        <div class="surface-card section-pad">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-5 bg-indigo-500 rounded-full"></div>
                <h3 class="section-title text-base">📈 Tren Top-5 Risiko (6 Bulan)</h3>
            </div>
            <div class="relative" style="height: 260px;">
                <canvas id="trenTop5Chart" style="height: 100% !important; width: 100% !important;"></canvas>
            </div>
        </div>
    </div>
    @endhasanyrole
    @endif

    {{-- ============================================================
         MAKER SECTION — Form Entry Cards
         ============================================================ --}}
    @hasanyrole('teller|ca|csr|security|kacab')
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-1 h-6 bg-indigo-500 rounded-full"></div>
            <h3 class="section-title">Form Pelaporan Risiko</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
            <a href="{{ route('form.risiko', 'finansial') }}"
               class="group surface-card-hover border-t-4 border-t-rose-500 p-5 sm:p-6 block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">💸</div>
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-slate-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-1.5">Risiko Finansial</h4>
                <p class="text-sm text-slate-500 leading-relaxed">Laporkan selisih kas, salah input nominal, atau kerugian finansial lainnya.</p>
            </a>
            <a href="{{ route('form.risiko', 'non-finansial') }}"
               class="group surface-card-hover border-t-4 border-t-orange-500 p-5 sm:p-6 block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">⚠️</div>
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-slate-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-1.5">Risiko Non-Finansial</h4>
                <p class="text-sm text-slate-500 leading-relaxed">Laporkan komplain nasabah, sistem down, pelanggaran SOP, dll.</p>
            </a>
        </div>
    </div>
    @endhasanyrole

    {{-- ============================================================
         CHECKER SECTION — Review (Khusus Kacab)
         ============================================================ --}}
    @hasrole('kacab')
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-1 h-6 bg-amber-500 rounded-full"></div>
            <h3 class="section-title">Review & Tindak Lanjut</h3>
        </div>
        <a href="{{ route('review.laporan') }}"
           class="surface-card-hover border-t-4 border-t-amber-400 p-5 sm:p-6 block">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-slate-900">Validasi Laporan Masuk</h4>
                        <p class="text-sm text-slate-500 mt-0.5">Approve atau reject laporan dari bawahan Anda</p>
                    </div>
                </div>
                <span class="badge-pending text-xs">{{ $pendingCount }} Menunggu</span>
            </div>
        </a>
    </div>
    @endhasrole

    {{-- ============================================================
         DEKLARASI NIHIL RISIKO (Khusus Kacab)
         ============================================================ --}}
    @hasrole('kacab')
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
    @endhasrole

    {{-- ============================================================
         RINGKASAN WILAYAH (Khusus ManRisk)
         ============================================================ --}}
    @hasrole('manrisk')
    @if(count($branchSummaries) > 0)
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-1 h-6 bg-emerald-500 rounded-full"></div>
            <h3 class="section-title">🏢 Ringkasan Wilayah</h3>
        </div>

        {{-- Tabel Ringkasan Per Cabang --}}
        <div class="surface-card overflow-hidden mb-5">
            <div class="table-wrap">
                <table class="table-min">
                    <thead>
                        <tr>
                            <th class="table-th">Cabang</th>
                            <th class="table-th text-center">Total Laporan</th>
                            <th class="table-th text-center">Pending</th>
                            <th class="table-th text-center">Approved</th>
                            <th class="table-th text-center">Dalam Progres</th>
                            <th class="table-th text-right">Total Kerugian</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @php
                            $maxTotal = max(array_column($branchSummaries, 'total'));
                            $maxKerugian = max(array_column($branchSummaries, 'kerugian'));
                        @endphp
                        @foreach($branchSummaries as $branch)
                        @php
                            $isTopLaporan = $branch['total'] > 0 && $branch['total'] === $maxTotal;
                            $isTopKerugian = $branch['kerugian'] > 0 && $branch['kerugian'] === $maxKerugian;
                        @endphp
                        <tr class="table-tr {{ $isTopLaporan || $isTopKerugian ? 'bg-amber-50/50' : '' }}">
                            <td class="table-td font-semibold text-slate-800">
                                <div class="flex items-center gap-2">
                                    <span>🏦</span>
                                    <span>{{ $branch['nama'] }}</span>
                                    @if($isTopLaporan)
                                    <span class="badge bg-amber-100 text-amber-700 border-amber-200 text-[9px]">Terbanyak</span>
                                    @endif
                                    @if($isTopKerugian)
                                    <span class="badge bg-rose-100 text-rose-700 border-rose-200 text-[9px]">Kerugian Tertinggi</span>
                                    @endif
                                </div>
                            </td>
                            <td class="table-td text-center font-bold text-slate-700">{{ $branch['total'] }}</td>
                            <td class="table-td text-center">
                                @if($branch['pending'] > 0)
                                <span class="badge-pending text-xs">{{ $branch['pending'] }}</span>
                                @else
                                <span class="text-slate-400 text-sm">0</span>
                                @endif
                            </td>
                            <td class="table-td text-center text-emerald-600 font-semibold">{{ $branch['approved'] }}</td>
                            <td class="table-td text-center">
                                @if($branch['in_progress'] > 0)
                                <span class="badge-in-progress text-xs">{{ $branch['in_progress'] }}</span>
                                @else
                                <span class="text-slate-400 text-sm">0</span>
                                @endif
                            </td>
                            <td class="table-td text-right font-semibold text-rose-600">Rp {{ number_format($branch['kerugian'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @php
                        $totalAll = array_sum(array_column($branchSummaries, 'total'));
                        $totalPendingAll = array_sum(array_column($branchSummaries, 'pending'));
                        $totalApprovedAll = array_sum(array_column($branchSummaries, 'approved'));
                        $totalInProgressAll = array_sum(array_column($branchSummaries, 'in_progress'));
                        $totalKerugianAll = array_sum(array_column($branchSummaries, 'kerugian'));
                    @endphp
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                        <tr>
                            <td class="px-4 py-3 text-sm font-bold text-slate-800">Total Semua Cabang</td>
                            <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $totalAll }}</td>
                            <td class="px-4 py-3 text-center font-bold text-amber-600">{{ $totalPendingAll }}</td>
                            <td class="px-4 py-3 text-center font-bold text-emerald-600">{{ $totalApprovedAll }}</td>
                            <td class="px-4 py-3 text-center font-bold text-sky-600">{{ $totalInProgressAll }}</td>
                            <td class="px-4 py-3 text-right font-bold text-rose-600">Rp {{ number_format($totalKerugianAll, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Bar Chart Perbandingan Laporan Per Cabang --}}
        @if(count($branchChartLabels) > 1)
        <div class="surface-card section-pad">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-5 bg-indigo-500 rounded-full"></div>
                <h3 class="section-title text-base">📊 Laporan per Cabang</h3>
            </div>
            <div class="relative" style="height: 250px;">
                <canvas id="branchChart" style="height: 100% !important; width: 100% !important;"></canvas>
            </div>
        </div>
        @endif

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
                            <td class="table-td text-center font-bold text-slate-700">{{ $d['total'] }}</td>
                            <td class="table-td text-center">
                                @if($d['violate'])
                                    <span class="badge bg-rose-100 text-rose-700 border-rose-200 text-[10px]">⚠️ Violate</span>
                                @else
                                    <span class="badge bg-emerald-100 text-emerald-700 border-emerald-200 text-[10px]">✅ Compliant</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
    @endif
    @endhasrole

    {{-- ============================================================
         DATA TABLE — Recent Reports
         ============================================================ --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-1 h-6 bg-slate-400 rounded-full"></div>
            <h3 class="section-title">Laporan Terbaru</h3>
            <span class="badge bg-slate-100 text-slate-500 border border-slate-200 text-[10px]">Preview</span>
        </div>
        <div class="surface-card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm font-medium text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ $recentReports->count() }}</span> laporan terbaru</p>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" placeholder="Cari laporan..." class="pl-9 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg bg-white focus:border-indigo-300 focus:ring-2 focus:ring-indigo-50 w-full sm:w-56">
                    </div>
                    <button class="btn-secondary btn-sm">Filter</button>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table-min">
                    <thead>
                        <tr>
                            <th class="table-th">Tgl Lapor</th>
                            <th class="table-th">Cabang</th>
                            <th class="table-th">Maker</th>
                            <th class="table-th">Risiko</th>
                            <th class="table-th">Kategori</th>
                            <th class="table-th text-center">Status</th>
                            <th class="table-th text-center">Tindak Lanjut</th>
                            <th class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse($recentReports as $report)
                        <tr class="table-tr">
                            <td class="table-td">
                                <span class="text-xs font-semibold text-slate-700">{{ $report->created_at->format('d/m/Y') }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $report->created_at->format('H:i') }}</span>
                            </td>
                            <td class="table-td font-semibold text-slate-700">{{ $report->branch->nama_cabang ?? 'HQ' }}</td>
                            <td class="table-td">
                                <span class="font-medium text-slate-800">{{ $report->user->name }}</span>
                                <span class="block text-[10px] text-slate-400 uppercase">{{ optional($report->user->roles->first())->name ?? '—' }}</span>
                            </td>
                            <td class="table-td">
                                <span class="text-sm font-medium text-slate-800">{{ $report->item->nama_risiko ?? $report->other_item_description ?? '—' }}</span>
                            </td>
                            <td class="table-td">
                                @if($report->kategori === 'finansial')
                                <span class="badge bg-rose-50 text-rose-700 border-rose-200 text-[10px]">Finansial</span>
                                @else
                                <span class="badge bg-amber-50 text-amber-700 border-amber-200 text-[10px]">Non-Finansial</span>
                                @endif
                            </td>
                            <td class="table-td text-center">
                                @if($report->approval_status === 'approved')
                                <span class="badge-approved">Approved</span>
                                @elseif($report->approval_status === 'rejected')
                                <span class="badge-rejected">Rejected</span>
                                @else
                                <span class="badge-pending">Pending</span>
                                @endif
                            </td>
                            <td class="table-td text-center">
                                @php
                                    $map = ['open' => 'badge-open', 'in_progress' => 'badge-in-progress', 'closed' => 'badge-closed'];
                                    $class = $map[$report->resolution_status] ?? 'badge-open';
                                @endphp
                                <span class="{{ $class }}">{{ str_replace('_', ' ', $report->resolution_status ?? 'open') }}</span>
                            </td>
                            <td class="table-td text-right">
                                <a href="{{ route('risk_reports.show', $report->id) }}"
                                   class="btn-ghost btn-xs text-indigo-600 hover:text-indigo-800">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-600">Belum Ada Laporan</p>
                                        <p class="text-xs text-slate-400 mt-1">Data laporan akan muncul di sini setelah Anda atau tim membuat laporan baru.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <p class="text-xs text-slate-400">Halaman 1 dari 1</p>
                <div class="flex items-center gap-1">
                    <button class="px-3 py-1.5 text-xs font-medium text-slate-400 bg-white border border-slate-200 rounded-lg cursor-not-allowed">&larr; Sebelumnya</button>
                    <button class="px-3 py-1.5 text-xs font-medium text-slate-400 bg-white border border-slate-200 rounded-lg cursor-not-allowed">Selanjutnya &rarr;</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ================================================================
            // CHART 1: RANKING RISIKO (Horizontal Bar)
            // ================================================================
            const rankingCtx = document.getElementById('rankingRisikoChart');
            if (rankingCtx) {
                const rankingFullLabels = {!! json_encode($rankingRisikoFullLabels) !!};
                new Chart(rankingCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($rankingRisikoLabels) !!},
                        datasets: [{
                            label: 'Jumlah Kejadian',
                            data: {!! json_encode($rankingRisikoData) !!},
                            backgroundColor: {!! json_encode($rankingRisikoColors) !!},
                            borderColor: {!! json_encode($rankingRisikoColors) !!},
                            borderWidth: 1,
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
                                    title: function(ctx) {
                                        // Tampilkan nama risiko lengkap di title tooltip
                                        return rankingFullLabels[ctx[0].dataIndex] || ctx[0].label;
                                    },
                                    label: function(ctx) {
                                        return ctx.parsed.x + ' kejadian';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: { size: 10 }
                                },
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            },
                            y: {
                                ticks: {
                                    font: { size: 10 }
                                },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // ================================================================
            // CHART 2: SUMBER RISIKO (Doughnut)
            // ================================================================
            const sumberCtx = document.getElementById('sumberRisikoChart');
            if (sumberCtx) {
                new Chart(sumberCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($sumberRisikoLabels) !!},
                        datasets: [{
                            data: {!! json_encode($sumberRisikoData) !!},
                            backgroundColor: {!! json_encode($sumberRisikoColors) !!},
                            borderColor: '#ffffff',
                            borderWidth: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: { size: 11 },
                                    padding: 14,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        var total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        var pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                        return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '60%',
                    }
                });
            }

            // ================================================================
            // CHART 3: TREN TOP-5 RISIKO (Multi-line)
            // ================================================================
            const trenCtx = document.getElementById('trenTop5Chart');
            if (trenCtx) {
                new Chart(trenCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trenTop5Labels) !!},
                        datasets: {!! json_encode($trenTop5Datasets) !!}
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: { size: 10 },
                                    padding: 12,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return ctx.dataset.label + ': ' + ctx.parsed.y + ' kejadian';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: { size: 10 }
                                },
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            },
                            x: {
                                ticks: { font: { size: 9 } },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // ================================================================
            // CHART 4: LAPORAN PER CABANG (Bar Chart — Khusus ManRisk)
            // ================================================================
            const branchCtx = document.getElementById('branchChart');
            if (branchCtx) {
                new Chart(branchCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($branchChartLabels) !!},
                        datasets: [{
                            label: 'Total Laporan',
                            data: {!! json_encode($branchChartData) !!},
                            backgroundColor: {!! json_encode($branchChartColors) !!},
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return ctx.parsed.y + ' laporan';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: { size: 10 }
                                },
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            },
                            x: {
                                ticks: { font: { size: 10 } },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
