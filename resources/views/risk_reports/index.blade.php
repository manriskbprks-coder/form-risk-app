<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
                {{ __('Riwayat & Monitoring Risiko Operasional') }}
            </h2>
            <p class="text-sm text-slate-500">Pantau seluruh laporan risiko — aktif dan yang sudah selesai.</p>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="page-shell page-stack py-4 sm:py-8">

            {{-- =============================================================
                 SUMMARY CARDS
                 ============================================================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5 mb-6">
                <div class="surface-card p-4 sm:p-5 border-l-4 border-blue-500">
                    <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">Total Kejadian</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $totalKejadian }}</p>
                </div>
                <div class="surface-card p-4 sm:p-5 border-l-4 border-green-500">
                    <p class="text-xs font-bold text-green-600 uppercase tracking-wider">Total Kerugian (Approved)</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($totalLoss, 0, ',', '.') }}</p>
                </div>
                <div class="surface-card p-4 sm:p-5 border-l-4 border-red-500">
                    <p class="text-xs font-bold text-red-600 uppercase tracking-wider">Ditolak</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $totalRejected }}</p>
                </div>
            </div>

            {{-- =============================================================
                 SEARCH & FILTER FORM
                 ============================================================= --}}
            <div class="surface-card overflow-hidden p-4 sm:p-6 mb-6">
                <form method="GET" action="{{ route('risk.history') }}" class="space-y-4">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <input type="text" name="search" placeholder="Cari kode laporan, pelapor, risiko..." value="{{ request('search') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg text-sm transition shadow-sm">
                            <svg class="w-4 h-4 inline-block -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Cari
                        </button>
                        @if(request()->anyFilled(['search', 'kategori', 'resolution_status', 'approval_status', 'branch_id', 'jabatan', 'start_date', 'end_date']))
                        <a href="{{ route('risk.history') }}" class="w-full sm:w-auto inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg text-sm transition">
                            Reset
                        </a>
                        @endif

                        @if($role === 'manrisk')
                        <a href="{{ route('risk.export', request()->query()) }}" class="w-full sm:w-auto inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition shadow-sm">
                            <svg class="w-4 h-4 inline-block -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Export CSV
                        </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @if($branches->isNotEmpty())
                        <div>
                            <select name="branch_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Cabang</option>
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div>
                            <select name="kategori" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Kategori</option>
                                <option value="finansial" {{ request('kategori') == 'finansial' ? 'selected' : '' }}>Finansial</option>
                                <option value="non-finansial" {{ request('kategori') == 'non-finansial' ? 'selected' : '' }}>Non-Finansial</option>
                            </select>
                        </div>

                        @if(!in_array($role, ['teller', 'ca', 'csr', 'security']))
                        <div>
                            <select name="jabatan" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Jabatan</option>
                                @foreach(['teller', 'ca', 'csr', 'security', 'kacab'] as $jabatan)
                                <option value="{{ $jabatan }}" {{ request('jabatan') == $jabatan ? 'selected' : '' }}>{{ ucfirst($jabatan) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div>
                            <select name="approval_status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Status Approval</option>
                                <option value="pending_kacab" {{ request('approval_status') == 'pending_kacab' ? 'selected' : '' }}>Pending Kacab</option>
                                <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="need_revision" {{ request('approval_status') == 'need_revision' ? 'selected' : '' }}>Need Revision</option>
                                <option value="pending_revision" {{ request('approval_status') == 'pending_revision' ? 'selected' : '' }}>Pending Revision</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Tanggal Kejadian Dari</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Sampai</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </form>
            </div>

            {{-- =============================================================
                 TABEL 1: LAPORAN AKTIF
                 ============================================================= --}}
            <div class="surface-card overflow-hidden p-4 sm:p-6 border-l-4 border-amber-500 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">📋 LAPORAN AKTIF</h3>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200">
                        {{ $activeReports->total() }} laporan
                    </span>
                </div>
                <p class="text-sm text-gray-500 mb-4">Laporan yang masih dalam proses — belum selesai (closed).</p>

                @if($activeReports->isEmpty())
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-4">
                    <p class="text-amber-700 italic">Tidak ada laporan aktif saat ini.</p>
                </div>
                @else
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="min-w-[1400px] w-full bg-white border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="kode">ID Laporan</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="tgl">Tgl Lapor & Kejadian</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="pelapor">Pelapor</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="cabang">Cabang</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="sumber">Sumber Risiko</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="kategori">Kategori</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider">Risiko & Penyebab</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="dampak">Dampak</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="approval">Approval</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="resolusi">Resolusi</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeReports as $report)
                            @php
                            $sumberRisiko = $report->cause->sumber_risiko ?? $report->item->sumber_risiko ?? 'manusia';
                            $sumberLabels = [
                                'manusia' => ['label' => 'Manusia', 'color' => 'bg-red-100 text-red-800 border-red-200'],
                                'proses_internal' => ['label' => 'Proses Internal', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                                'sistem_teknologi' => ['label' => 'Sistem Teknologi', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
                                'faktor_eksternal' => ['label' => 'Faktor Eksternal', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
                            ];
                            $sumber = $sumberLabels[$sumberRisiko] ?? $sumberLabels['manusia'];

                            $approvalColors = [
                                'pending_kacab' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'approved' => 'bg-green-100 text-green-800 border-green-200',
                                'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                'need_revision' => 'bg-orange-100 text-orange-800 border-orange-200',
                                'pending_revision' => 'bg-blue-100 text-blue-800 border-blue-200',
                            ];
                            $approvalClass = $approvalColors[$report->approval_status] ?? 'bg-gray-100 text-gray-800 border-gray-200';

                            $resolutionColors = [
                                'open' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'in_progress' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'closed' => 'bg-green-100 text-green-800 border-green-200',
                            ];
                            $resolutionClass = $resolutionColors[$report->resolution_status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                            @endphp
                            <tr class="hover:bg-gray-50 align-middle">
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap" data-sort-value="{{ $report->kode_laporan ?? '' }}">
                                    <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded border border-indigo-200">
                                        {{ $report->kode_laporan ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-b whitespace-nowrap text-center align-middle" data-sort-value="{{ $report->created_at->format('YmdHis') }}">
                                    <div class="text-xs font-bold text-blue-700">Lapor: {{ $report->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-600 mt-1">Kejadian: {{ \Carbon\Carbon::parse($report->tanggal_kejadian)->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-4 py-3 border-b text-sm font-bold text-gray-800 text-center align-middle whitespace-nowrap" data-sort-value="{{ $report->user->name }}">{{ $report->user->name }}</td>
                                <td class="px-4 py-3 border-b text-sm text-gray-600 text-center align-middle whitespace-nowrap" data-sort-value="{{ $report->branch->nama_cabang ?? '' }}">{{ $report->branch->nama_cabang ?? '—' }}</td>
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border {{ $sumber['color'] }}">
                                        {{ $sumber['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap">
                                    @if($report->kategori === 'finansial')
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border bg-green-100 text-green-800 border-green-200">Finansial</span>
                                    @else
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border bg-orange-100 text-orange-800 border-orange-200">Non-Finansial</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-b align-middle">
                                    <div class="text-sm font-bold text-gray-900 truncate max-w-[240px]" title="{{ $report->item->nama_risiko ?? $report->other_item_description }}">
                                        {{ $report->item->nama_risiko ?? $report->other_item_description }}
                                    </div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-200 truncate max-w-[240px]" title="{{ $report->cause->penyebab ?? $report->other_cause_description }}">
                                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                            {{ $report->cause->penyebab ?? $report->other_cause_description }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-b text-sm text-gray-800 text-center align-middle">
                                    @if($report->kategori === 'finansial')
                                    <span class="font-bold whitespace-nowrap">Rp {{ number_format($report->dampak_finansial, 0, ',', '.') }}</span>
                                    @else
                                    <div class="flex flex-col items-center gap-1">
                                        @php
                                            $skalaDampak = $report->skala_dampak ?? '';
                                            $skalaColors = [
                                                'Sangat Tinggi' => 'bg-red-700 text-white',
                                                'Tinggi' => 'bg-orange-500 text-white',
                                                'Sedang' => 'bg-yellow-500 text-white',
                                                'Rendah' => 'bg-blue-500 text-white',
                                                'Sangat Rendah' => 'bg-green-600 text-white',
                                            ];
                                            $skalaColor = $skalaColors[$skalaDampak] ?? 'bg-gray-500 text-white';
                                        @endphp
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded {{ $skalaColor }}">
                                            {{ $skalaDampak ?: '—' }}
                                        </span>
                                    </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border {{ $approvalClass }}">
                                        {{ str_replace('_', ' ', $report->approval_status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border {{ $resolutionClass }}">
                                        {{ str_replace('_', ' ', $report->resolution_status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap">
                                    <a href="{{ route('risk_reports.show', $report->id) }}"
                                       class="inline-flex items-center gap-1 bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-1.5 px-3 rounded text-xs transition shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination for Active Reports --}}
                <div class="mt-4">
                    {{ $activeReports->appends(request()->query())->links() }}
                </div>
                @endif
            </div>

            {{-- =============================================================
                 TABEL 2: LAPORAN SELESAI / CLOSED
                 ============================================================= --}}
            <div class="surface-card overflow-hidden p-4 sm:p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">✅ LAPORAN SELESAI / CLOSED</h3>
                    <span class="text-xs font-bold text-green-700 bg-green-50 px-3 py-1 rounded-full border border-green-200">
                        {{ $closedReports->total() }} laporan
                    </span>
                </div>
                <p class="text-sm text-gray-500 mb-4">Laporan yang sudah selesai ditindaklanjuti dan ditutup (closed).</p>

                @if($closedReports->isEmpty())
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <p class="text-green-700 italic">Belum ada laporan yang selesai / closed.</p>
                </div>
                @else
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="min-w-[1400px] w-full bg-white border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="kode">ID Laporan</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="tgl">Tgl Lapor & Kejadian</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="pelapor">Pelapor</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="cabang">Cabang</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="sumber">Sumber Risiko</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="kategori">Kategori</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider">Risiko & Penyebab</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="dampak">Dampak</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="approval">Approval</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($closedReports as $report)
                            @php
                            $sumberRisiko = $report->cause->sumber_risiko ?? $report->item->sumber_risiko ?? 'manusia';
                            $sumberLabels = [
                                'manusia' => ['label' => 'Manusia', 'color' => 'bg-red-100 text-red-800 border-red-200'],
                                'proses_internal' => ['label' => 'Proses Internal', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                                'sistem_teknologi' => ['label' => 'Sistem Teknologi', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
                                'faktor_eksternal' => ['label' => 'Faktor Eksternal', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
                            ];
                            $sumber = $sumberLabels[$sumberRisiko] ?? $sumberLabels['manusia'];

                            $approvalColors = [
                                'pending_kacab' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'approved' => 'bg-green-100 text-green-800 border-green-200',
                                'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                'need_revision' => 'bg-orange-100 text-orange-800 border-orange-200',
                                'pending_revision' => 'bg-blue-100 text-blue-800 border-blue-200',
                            ];
                            $approvalClass = $approvalColors[$report->approval_status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                            @endphp
                            <tr class="hover:bg-gray-50 align-middle">
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap" data-sort-value="{{ $report->kode_laporan ?? '' }}">
                                    <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded border border-indigo-200">
                                        {{ $report->kode_laporan ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-b whitespace-nowrap text-center align-middle" data-sort-value="{{ $report->created_at->format('YmdHis') }}">
                                    <div class="text-xs font-bold text-blue-700">Lapor: {{ $report->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-600 mt-1">Kejadian: {{ \Carbon\Carbon::parse($report->tanggal_kejadian)->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-4 py-3 border-b text-sm font-bold text-gray-800 text-center align-middle whitespace-nowrap" data-sort-value="{{ $report->user->name }}">{{ $report->user->name }}</td>
                                <td class="px-4 py-3 border-b text-sm text-gray-600 text-center align-middle whitespace-nowrap" data-sort-value="{{ $report->branch->nama_cabang ?? '' }}">{{ $report->branch->nama_cabang ?? '—' }}</td>
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border {{ $sumber['color'] }}">
                                        {{ $sumber['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap">
                                    @if($report->kategori === 'finansial')
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border bg-green-100 text-green-800 border-green-200">Finansial</span>
                                    @else
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border bg-orange-100 text-orange-800 border-orange-200">Non-Finansial</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-b align-middle">
                                    <div class="text-sm font-bold text-gray-900 truncate max-w-[240px]" title="{{ $report->item->nama_risiko ?? $report->other_item_description }}">
                                        {{ $report->item->nama_risiko ?? $report->other_item_description }}
                                    </div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-200 truncate max-w-[240px]" title="{{ $report->cause->penyebab ?? $report->other_cause_description }}">
                                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                            {{ $report->cause->penyebab ?? $report->other_cause_description }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-b text-sm text-gray-800 text-center align-middle">
                                    @if($report->kategori === 'finansial')
                                    <span class="font-bold whitespace-nowrap">Rp {{ number_format($report->dampak_finansial, 0, ',', '.') }}</span>
                                    @else
                                    <div class="flex flex-col items-center gap-1">
                                        @php
                                            $skalaDampak = $report->skala_dampak ?? '';
                                            $skalaColors = [
                                                'Sangat Tinggi' => 'bg-red-700 text-white',
                                                'Tinggi' => 'bg-orange-500 text-white',
                                                'Sedang' => 'bg-yellow-500 text-white',
                                                'Rendah' => 'bg-blue-500 text-white',
                                                'Sangat Rendah' => 'bg-green-600 text-white',
                                            ];
                                            $skalaColor = $skalaColors[$skalaDampak] ?? 'bg-gray-500 text-white';
                                        @endphp
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded {{ $skalaColor }}">
                                            {{ $skalaDampak ?: '—' }}
                                        </span>
                                    </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border {{ $approvalClass }}">
                                        {{ str_replace('_', ' ', $report->approval_status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap">
                                    <a href="{{ route('risk_reports.show', $report->id) }}"
                                       class="inline-flex items-center gap-1 bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-1.5 px-3 rounded text-xs transition shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination for Closed Reports --}}
                <div class="mt-4">
                    {{ $closedReports->appends(request()->query())->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>

    <style>
        /* Sorting cursor */
        th.sortable {
            cursor: pointer;
            user-select: none;
        }
        th.sortable:hover {
            background-color: #e5e7eb;
        }
        th.sortable::after {
            content: ' ↕';
            font-size: 10px;
            opacity: 0.4;
        }
        th.sortable.asc::after {
            content: ' ↑';
            opacity: 1;
        }
        th.sortable.desc::after {
            content: ' ↓';
            opacity: 1;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Client-side table sorting — default descending (terbaru di atas)
            document.querySelectorAll('table').forEach(function(table) {
                const headers = table.querySelectorAll('th.sortable');
                const tbody = table.querySelector('tbody');

                // Auto-sort by first column (Tgl Lapor) descending on load
                const defaultSortHeader = table.querySelector('th.sortable[data-sort="tgl"]');
                if (defaultSortHeader) {
                    setTimeout(function() {
                        defaultSortHeader.classList.add('desc');
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        const dataRows = rows.filter(row => row.querySelector('td[data-sort-value]'));

                        dataRows.sort(function(a, b) {
                            const tdIndex = Array.from(defaultSortHeader.parentElement.children).indexOf(defaultSortHeader);
                            const aTd = a.children[tdIndex];
                            const bTd = b.children[tdIndex];
                            let aVal = aTd?.dataset.sortValue || '';
                            let bVal = bTd?.dataset.sortValue || '';

                            // Descending: terbaru di atas
                            return bVal.localeCompare(aVal);
                        });

                        dataRows.forEach(row => tbody.appendChild(row));
                    }, 50);
                }

                headers.forEach(function(header) {
                    header.addEventListener('click', function() {
                        const isAsc = this.classList.contains('asc');

                        // Reset all headers
                        headers.forEach(h => h.classList.remove('asc', 'desc'));

                        // Toggle
                        this.classList.add(isAsc ? 'desc' : 'asc');

                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        const dataRows = rows.filter(row => row.querySelector('td[data-sort-value]'));

                        dataRows.sort(function(a, b) {
                            const tdIndex = Array.from(header.parentElement.children).indexOf(header);
                            const aTd = a.children[tdIndex];
                            const bTd = b.children[tdIndex];
                            let aVal = aTd?.dataset.sortValue || '';
                            let bVal = bTd?.dataset.sortValue || '';

                            if (!isNaN(aVal) && !isNaN(bVal)) {
                                return isAsc ? bVal - aVal : aVal - bVal;
                            }
                            return isAsc
                                ? bVal.localeCompare(aVal)
                                : aVal.localeCompare(bVal);
                        });

                        dataRows.forEach(row => tbody.appendChild(row));
                    });
                });
            });
        });
    </script>
</x-app-layout>
