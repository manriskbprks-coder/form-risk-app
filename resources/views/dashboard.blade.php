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
                <p class="stat-card-label">Total Laporan</p>
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
                <p class="stat-card-label">Disetujui</p>
                <span class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="stat-card-value text-emerald-600">{{ $totalApproved }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Laporan valid</p>
        </div>
        <div class="stat-card border-l-4 border-l-rose-500">
            <div class="flex items-start justify-between mb-2">
                <p class="stat-card-label">Total Kerugian</p>
                <span class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="stat-card-value text-rose-600">Rp {{ number_format($totalLossApproved, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Approved loss</p>
        </div>
    </div>

    {{-- ============================================================
         CHART ANALISA RISIKO SECTION (Khusus Kacab/Korwil/Manrisk)
         ============================================================ --}}
    @hasanyrole('kacab|korwil|manrisk')
    {{-- Baris 1: Ranking Risiko + Sumber Risiko --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5 mb-8">
        {{-- Ranking Risiko (Horizontal Bar) --}}
        <div class="surface-card section-pad">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-5 bg-rose-500 rounded-full"></div>
                <h3 class="section-title text-base">🏆 Ranking Risiko (6 Bulan)</h3>
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

    {{-- ============================================================
         MANRISK SECTION
         ============================================================ --}}
    @hasrole('manrisk')
    <div>
        <div class="flex items-center gap-3 mb-5">
            <div class="w-1 h-6 bg-purple-500 rounded-full"></div>
            <h3 class="section-title">Panel Administrasi</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
            <a href="{{ route('admin.risk_master.index') }}" class="surface-card-hover p-5 border-t-4 border-t-purple-500">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-900 mb-1">Master Data Risiko</h4>
                <p class="text-xs text-slate-500">Kelola bank soal risiko, penyebab, dan mitigasi</p>
            </a>
            <a href="{{ route('admin.users.index') }}" class="surface-card-hover p-5 border-t-4 border-t-rose-500">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-900 mb-1">Manajemen Pengguna</h4>
                <p class="text-xs text-slate-500">Atur akun, role, dan status aktif pegawai</p>
            </a>
            <a href="{{ route('branches.index') }}" class="surface-card-hover p-5 border-t-4 border-t-sky-500">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-900 mb-1">Manajemen Cabang</h4>
                <p class="text-xs text-slate-500">Kelola struktur cabang dan pengawasan</p>
            </a>
        </div>
    </div>
    @endhasrole

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ================================================================
            // CHART 1: RANKING RISIKO (Horizontal Bar)
            // ================================================================
            const rankingCtx = document.getElementById('rankingRisikoChart');
            if (rankingCtx) {
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
        });
    </script>
</x-app-layout>
