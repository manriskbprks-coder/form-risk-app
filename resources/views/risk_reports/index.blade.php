<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
                {{ __('Riwayat & Monitoring Risiko Operasional') }}
            </h2>
            <p class="text-sm text-slate-500">Filter, ringkasan, dan tabel data disusun lebih lega agar lebih cepat dibaca di semua ukuran layar.</p>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="page-shell page-stack">
            <div class="surface-card section-pad">
                <form method="GET" action="{{ route('risk.history') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end mb-6">

                    @if(in_array($role, ['teller', 'ca', 'csr', 'security']))
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kategori Risiko</label>
                        <select name="kategori" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm h-[38px]">
                            <option value="">Semua Kategori</option>
                            <option value="finansial" {{ request('kategori') == 'finansial' ? 'selected' : '' }}>Finansial</option>
                            <option value="non-finansial" {{ request('kategori') == 'non-finansial' ? 'selected' : '' }}>Non-Finansial</option>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status Penyelesaian</label>
                        <select name="resolution_status" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm h-[38px]">
                            <option value="">Semua Status</option>
                            <option value="open" {{ request('resolution_status') == 'open' ? 'selected' : '' }}>Open (Baru)</option>
                            <option value="in_progress" {{ request('resolution_status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="closed" {{ request('resolution_status') == 'closed' ? 'selected' : '' }}>Closed (Selesai)</option>
                        </select>
                    </div>

                    @else
                    @if(in_array($role, ['manrisk', 'korwil']))
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Filter Cabang</label>
                        <select name="branch_id" id="select-cabang" class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">Semua Cabang (View All)</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->nama_cabang }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="{{ in_array($role, ['manrisk', 'korwil']) ? 'md:col-span-1' : 'md:col-span-2' }}">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kategori</label>
                        <select name="kategori" class="block w-full border-gray-300 rounded-md shadow-sm text-sm h-[38px]">
                            <option value="">Semua</option>
                            <option value="finansial" {{ request('kategori') == 'finansial' ? 'selected' : '' }}>Finansial</option>
                            <option value="non-finansial" {{ request('kategori') == 'non-finansial' ? 'selected' : '' }}>Non-Finansial</option>
                        </select>
                    </div>

                    <div class="{{ in_array($role, ['manrisk', 'korwil']) ? 'md:col-span-1' : 'md:col-span-2' }}">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jabatan</label>
                        <select name="jabatan" class="block w-full border-gray-300 rounded-md shadow-sm text-sm h-[38px]">
                            <option value="">Semua</option>
                            <option value="teller" {{ request('jabatan') == 'teller' ? 'selected' : '' }}>Teller</option>
                            <option value="ca" {{ request('jabatan') == 'ca' ? 'selected' : '' }}>CA</option>
                            <option value="csr" {{ request('jabatan') == 'csr' ? 'selected' : '' }}>CSR</option>
                            <option value="security" {{ request('jabatan') == 'security' ? 'selected' : '' }}>Security</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status Penyelesaian</label>
                        <select name="resolution_status" class="block w-full border-gray-300 rounded-md shadow-sm text-sm h-[38px]">
                            <option value="">Semua Status</option>
                            <option value="open" {{ request('resolution_status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ request('resolution_status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="closed" {{ request('resolution_status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <div class="md:col-span-4 flex flex-col sm:flex-row gap-2">
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dari Tgl</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="block w-full border-gray-300 rounded-md shadow-sm text-sm h-[38px]">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sampai</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="block w-full border-gray-300 rounded-md shadow-sm text-sm h-[38px]">
                        </div>
                    </div>
                    @endif

                    <div class="{{ in_array($role, ['teller', 'ca', 'csr', 'security']) ? 'md:col-span-6 mt-4' : 'md:col-span-2 mt-0' }} flex flex-col sm:flex-row gap-2 justify-end items-stretch sm:items-end sm:h-[38px]">
                        <a href="{{ route('risk.history') }}" class="w-full sm:w-auto text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded text-sm shadow transition">
                            Reset
                        </a>
                        <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-6 rounded text-sm shadow transition">
                            Filter
                        </button>
                    </div>
                </form>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="surface-card-muted p-6 border-l-4 border-green-500">
                    <p class="text-sm text-slate-500 font-bold uppercase tracking-[0.14em]">Total Kejadian Terdata</p>
                    <p class="text-2xl font-black text-green-600">{{ $reports->count() }} <span class="text-sm font-normal text-gray-400">Kasus</span></p>
                </div>
                <div class="surface-card-muted p-6 border-l-4 border-red-500">
                    <p class="text-sm text-slate-500 font-bold uppercase tracking-[0.14em]">Total Kerugian (Approved)</p>
                    <p class="text-2xl font-black text-red-600">Rp {{ number_format($totalLoss, 0, ',', '.') }}</p>
                </div>
                <div class="surface-card-muted p-6 border-l-4 border-orange-500">
                    <p class="text-sm text-slate-500 font-bold uppercase tracking-[0.14em]">Laporan di-Reject</p>
                    <p class="text-2xl font-black text-orange-600">{{ $reports->where('status', 'rejected')->count() }}</p>
                </div>
            </div>

            <div class="surface-card overflow-hidden">
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="min-w-[1400px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="kode">ID Laporan</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="tgl">Tgl Lapor & Ketahui</th>
                                @if(in_array($role, ['manrisk', 'korwil']))
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="cabang">Cabang</th>
                                @endif
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="maker">Maker</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="sumber">Sumber Risiko</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="kategori">Kategori</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider">Risiko, Penyebab & Mitigasi</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="dampak">Dampak</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="status">Status Approval</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="tindak">Tindak Lanjut</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($reports as $report)
                            @php
                            $tglDiketahui = \Carbon\Carbon::parse($report->tanggal_diketahui)->startOfDay();
                            $tglLapor = $report->created_at->startOfDay();

                            // Ngitung selisih hari. Kalau hasilnya positif, berarti lapornya setelah diketahui.
                            $selisihHari = $tglDiketahui->diffInDays($tglLapor, false);

                            // Flag merah kalau lewat 5 hari SLA
                            $isLate = $selisihHari > 5;
                            @endphp

                            <tr class="hover:bg-gray-50 transition duration-150 {{ $isLate ? 'bg-red-50' : '' }}">

                                <td class="px-4 py-3 text-center align-middle whitespace-nowrap" data-sort-value="{{ $report->kode_laporan ?? '' }}">
                                    <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded border border-indigo-200">
                                        {{ $report->kode_laporan ?? '—' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-center align-middle" data-sort-value="{{ $report->created_at->format('YmdHis') }}">
                                    <div class="text-xs font-bold text-blue-700" title="Waktu Input ke Sistem">Lapor: {{ $report->created_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-xs text-gray-600 mt-1" title="Tanggal Kejadian Diketahui">Diketahui: {{ $tglDiketahui->format('d/m/Y') }}</div>

                                    @if($isLate)
                                    <div class="mt-2 flex items-center gap-1 text-red-700 font-extrabold text-[10px] uppercase bg-red-200 px-2 py-1 rounded-sm w-max border border-red-300">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        Telat {{ $selisihHari }} Hari
                                    </div>
                                    @endif
                                </td>

                                @if(in_array($role, ['manrisk', 'korwil']))
                                <td class="px-4 py-3 text-sm text-gray-800 font-semibold text-center align-middle whitespace-nowrap" data-sort-value="{{ $report->branch->nama_cabang ?? 'HQ' }}">
                                    {{ $report->branch->nama_cabang ?? 'HQ' }}
                                </td>
                                @endif

                                <td class="px-4 py-3 text-sm text-gray-800 font-bold text-center align-middle whitespace-nowrap" data-sort-value="{{ $report->user->name }}">
                                    {{ $report->user->name }}
                                </td>

                                @php
                                // Ambil sumber risiko dari cause dulu, fallback ke item
                                $sumberRisiko = $report->cause->sumber_risiko ?? $report->item->sumber_risiko ?? 'manusia';
                                $sumberLabels = [
                                    'manusia' => ['label' => 'Manusia', 'color' => 'bg-red-100 text-red-800 border-red-200'],
                                    'proses_internal' => ['label' => 'Proses Internal', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                                    'sistem_teknologi' => ['label' => 'Sistem Teknologi', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
                                    'faktor_eksternal' => ['label' => 'Faktor Eksternal', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
                                ];
                                $sumber = $sumberLabels[$sumberRisiko] ?? $sumberLabels['manusia'];

                                $skalaLabels = [
                                    'ringan' => ['label' => 'Ringan', 'color' => 'bg-green-100 text-green-800 border-green-200'],
                                    'sedang' => ['label' => 'Sedang', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                                    'berat' => ['label' => 'Berat', 'color' => 'bg-red-100 text-red-800 border-red-200'],
                                ];
                                $skala = $skalaLabels[$report->skala_dampak] ?? ['label' => '-', 'color' => 'bg-gray-100 text-gray-600 border-gray-200'];
                                @endphp

                                <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border {{ $sumber['color'] }}">
                                        {{ $sumber['label'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                    @if($report->kategori === 'finansial')
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border bg-green-100 text-green-800 border-green-200">Finansial</span>
                                    @else
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border bg-orange-100 text-orange-800 border-orange-200">Non-Finansial</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 align-middle">
                                    <div class="text-sm font-bold text-gray-900 truncate max-w-[280px]" title="{{ $report->item->nama_risiko ?? $report->other_item_description }}">
                                        {{ $report->item->nama_risiko ?? $report->other_item_description }}
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-200 truncate max-w-[260px]" title="{{ $report->cause->penyebab ?? $report->other_cause_description }}">
                                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                            {{ $report->cause->penyebab ?? $report->other_cause_description }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @if($report->cause && $report->cause->mitigations->isNotEmpty())
                                        @foreach($report->cause->mitigations as $mitigasi)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded border border-green-200 truncate max-w-[240px]" title="{{ $mitigasi->mitigasi }}">
                                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            {{ $mitigasi->mitigasi }}
                                        </span>
                                        @endforeach
                                        @endif
                                        @if($report->mitigasi_tambahan)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-600 bg-gray-50 px-2 py-0.5 rounded border border-gray-200 truncate max-w-[240px]" title="{{ $report->mitigasi_tambahan }}">
                                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $report->mitigasi_tambahan }}
                                        </span>
                                        @endif
                                        @if((!$report->cause || $report->cause->mitigations->isEmpty()) && empty($report->mitigasi_tambahan))
                                        <span class="text-[11px] text-gray-400 italic">- Tidak ada mitigasi -</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center align-middle text-sm text-gray-800">
                                    @if($report->kategori === 'finansial')
                                    <span class="font-bold whitespace-nowrap">Rp {{ number_format($report->dampak_finansial, 0, ',', '.') }}</span>
                                    @else
                                    <span class="text-xs italic line-clamp-2 max-w-[160px]" title="{{ $report->dampak_non_finansial }}">{{ $report->dampak_non_finansial }}</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                    @if($report->approval_status === 'approved')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-[10px] font-bold uppercase rounded border border-green-200">Approved</span>
                                    @elseif($report->approval_status === 'rejected')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-[10px] font-bold uppercase rounded border border-red-200">Rejected</span>
                                    @else
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-[10px] font-bold uppercase rounded border border-yellow-200">Pending {{ str_replace('pending_', '', $report->approval_status) }}</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                    @php
                                    $resColors = [
                                    'open' => 'bg-gray-100 text-gray-600 border-gray-200',
                                    'in_progress' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'closed' => 'bg-gray-800 text-white border-gray-900',
                                    ];
                                    $resClass = $resColors[$report->resolution_status] ?? $resColors['open'];
                                    @endphp
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border {{ $resClass }}">
                                        {{ str_replace('_', ' ', $report->resolution_status) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                    <a href="{{ route('risk_reports.show', $report->id) }}" class="inline-block bg-blue-600 hover:bg-blue-800 text-white font-bold py-1.5 px-3 rounded text-[10px] uppercase tracking-wider shadow transition">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="px-6 py-10 text-center text-sm text-gray-500 italic">
                                    Tidak ada data riwayat laporan yang sesuai dengan filter Anda.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#select-cabang').select2({
                placeholder: "Ketik nama cabang...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <style>
        /* Styling biar Select2 nyatu sama form Tailwind lu */
        .select2-container .select2-selection--single {
            height: 38px !important;
            border-color: #d1d5db !important;
            border-radius: 0.375rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            font-size: 0.875rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        /* Line clamp untuk dampak non-finansial */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

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
        // Client-side table sorting
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('table').forEach(function(table) {
                const headers = table.querySelectorAll('th.sortable');
                const tbody = table.querySelector('tbody');

                headers.forEach(function(header) {
                    header.addEventListener('click', function() {
                        const sortKey = this.dataset.sort;
                        const isAsc = this.classList.contains('asc');

                        // Reset all headers
                        headers.forEach(h => h.classList.remove('asc', 'desc'));

                        // Toggle
                        this.classList.add(isAsc ? 'desc' : 'asc');

                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        // Skip empty state row
                        const dataRows = rows.filter(row => row.querySelector('td[data-sort-value]'));

                        dataRows.sort(function(a, b) {
                            let aVal = a.querySelector(`td[data-sort-value]`)?.dataset.sortValue || '';
                            let bVal = b.querySelector(`td[data-sort-value]`)?.dataset.sortValue || '';

                            // Cari td dengan data-sort-value yang sesuai dengan kolom
                            const tdIndex = Array.from(header.parentElement.children).indexOf(header);
                            const aTd = a.children[tdIndex];
                            const bTd = b.children[tdIndex];
                            if (aTd && aTd.dataset.sortValue) aVal = aTd.dataset.sortValue;
                            if (bTd && bTd.dataset.sortValue) bVal = bTd.dataset.sortValue;

                            // Numeric comparison if both are numbers
                            if (!isNaN(aVal) && !isNaN(bVal)) {
                                return isAsc ? bVal - aVal : aVal - bVal;
                            }
                            // String comparison
                            return isAsc
                                ? bVal.localeCompare(aVal)
                                : aVal.localeCompare(bVal);
                        });

                        // Re-append sorted rows
                        dataRows.forEach(row => tbody.appendChild(row));
                    });
                });
            });
        });
    </script>
</x-app-layout>
