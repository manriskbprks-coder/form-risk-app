<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
                {{ __('Review Laporan Risiko (Checker)') }}
            </h2>
            <p class="text-sm text-slate-500">Antrian approval dan tindak lanjut dipisahkan lebih jelas supaya proses review terasa lebih fokus.</p>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="page-shell page-stack py-4 sm:py-8">

            <div class="surface-card overflow-hidden p-4 sm:p-6 border-l-4 border-yellow-500">
                <h3 class="text-lg font-bold border-b pb-2 mb-4 text-gray-800">1. Menunggu Persetujuan Anda</h3>

                @if($reports->isEmpty())
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <p class="text-yellow-700 italic">Saat ini tidak ada laporan risiko baru yang butuh persetujuan Anda.</p>
                </div>
                @else
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="min-w-[1400px] w-full bg-white border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="kode">ID Laporan</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="tgl">Tgl Lapor & Ketahui</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="pelapor">Pelapor</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="sumber">Sumber Risiko</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="kategori">Kategori</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider">Risiko, Penyebab & Mitigasi</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap sortable" data-sort="dampak">Dampak</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                            @php
                            $sumberRisiko = $report->cause->sumber_risiko ?? $report->item->sumber_risiko ?? 'manusia';
                            $sumberLabels = [
                                'manusia' => ['label' => 'Manusia', 'color' => 'bg-red-100 text-red-800 border-red-200'],
                                'proses_internal' => ['label' => 'Proses Internal', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                                'sistem_teknologi' => ['label' => 'Sistem Teknologi', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
                                'faktor_eksternal' => ['label' => 'Faktor Eksternal', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
                            ];
                            $sumber = $sumberLabels[$sumberRisiko] ?? $sumberLabels['manusia'];
                            @endphp
                            <tr class="hover:bg-gray-50 align-middle">
                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap" data-sort-value="{{ $report->kode_laporan ?? '' }}">
                                    <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded border border-indigo-200">
                                        {{ $report->kode_laporan ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-b whitespace-nowrap text-center align-middle" data-sort-value="{{ $report->created_at->format('YmdHis') }}">
                                    <div class="text-xs font-bold text-blue-700">Lapor: {{ $report->created_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-xs text-gray-600 mt-1">Kejadian: {{ \Carbon\Carbon::parse($report->tanggal_kejadian)->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-600">Diketahui: {{ \Carbon\Carbon::parse($report->tanggal_diketahui)->format('d/m/Y') }}</div>
                                </td>

                                <td class="px-4 py-3 border-b text-sm font-bold text-gray-800 text-center align-middle whitespace-nowrap" data-sort-value="{{ $report->user->name }}">{{ $report->user->name }}</td>

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

                                <td class="px-4 py-3 border-b text-sm text-gray-800 text-center align-middle">
                                    @if($report->kategori === 'finansial')
                                    <span class="font-bold whitespace-nowrap">Rp {{ number_format($report->dampak_finansial, 0, ',', '.') }}</span>
                                    @else
                                    <span class="text-xs italic line-clamp-2 max-w-[160px]" title="{{ $report->dampak_non_finansial }}">{{ $report->dampak_non_finansial }}</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 border-b align-middle">
                                    <div class="flex flex-col gap-2 items-center">
                                        <form action="{{ route('risk_reports.update_status', $report->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="w-20 bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-xs border border-green-600">Approve</button>
                                        </form>
                                        <button type="button" onclick="openRejectModal({{ $report->id }}, '{{ $report->kode_laporan ?? 'N/A' }}')" class="w-20 bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs border border-red-600">
                                            Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <div class="surface-card overflow-hidden p-4 sm:p-6 border-l-4 border-blue-500">
                <h3 class="text-lg font-bold border-b pb-2 mb-4 text-gray-800">2. Menunggu Tindak Lanjut (Penyelesaian)</h3>

                @if($tindakLanjut->isEmpty())
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                    <p class="text-blue-700 italic">Semua laporan yang di-Approve sudah selesai ditindaklanjuti.</p>
                </div>
                @else
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="min-w-[1400px] w-full bg-white border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap">Tgl Lapor & Ketahui</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap">Pelapor</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap">Sumber Risiko</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap">Kategori</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider">Risiko, Penyebab & Mitigasi</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap">Status Tindak Lanjut</th>
                                <th class="px-4 py-3 border-b text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap">Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tindakLanjut as $tl)
                            @php
                            $sumberRisiko = $tl->cause->sumber_risiko ?? $tl->item->sumber_risiko ?? 'manusia';
                            $sumberLabels = [
                                'manusia' => ['label' => 'Manusia', 'color' => 'bg-red-100 text-red-800 border-red-200'],
                                'proses_internal' => ['label' => 'Proses Internal', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                                'sistem_teknologi' => ['label' => 'Sistem Teknologi', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
                                'faktor_eksternal' => ['label' => 'Faktor Eksternal', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
                            ];
                            $sumber = $sumberLabels[$sumberRisiko] ?? $sumberLabels['manusia'];
                            @endphp
                            <tr class="hover:bg-gray-50 align-middle">
                                <td class="px-4 py-3 border-b whitespace-nowrap text-center align-middle">
                                    <div class="text-xs font-bold text-blue-700">Lapor: {{ $tl->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-600 mt-1">Kejadian: {{ \Carbon\Carbon::parse($tl->tanggal_kejadian)->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-600">Diketahui: {{ \Carbon\Carbon::parse($tl->tanggal_diketahui)->format('d/m/Y') }}</div>
                                </td>

                                <td class="px-4 py-3 border-b text-sm font-bold text-gray-800 text-center align-middle whitespace-nowrap">{{ $tl->user->name }}</td>

                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border {{ $sumber['color'] }}">
                                        {{ $sumber['label'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 border-b text-center align-middle whitespace-nowrap">
                                    @if($tl->kategori === 'finansial')
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border bg-green-100 text-green-800 border-green-200">Finansial</span>
                                    @else
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border bg-orange-100 text-orange-800 border-orange-200">Non-Finansial</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 border-b align-middle">
                                    <div class="text-sm font-bold text-gray-900 truncate max-w-[280px]" title="{{ $tl->item->nama_risiko ?? $tl->other_item_description }}">
                                        {{ $tl->item->nama_risiko ?? $tl->other_item_description }}
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-200 truncate max-w-[260px]" title="{{ $tl->cause->penyebab ?? $tl->other_cause_description }}">
                                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                            {{ $tl->cause->penyebab ?? $tl->other_cause_description }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @if($tl->cause && $tl->cause->mitigations->isNotEmpty())
                                        @foreach($tl->cause->mitigations as $mitigasi)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded border border-green-200 truncate max-w-[240px]" title="{{ $mitigasi->mitigasi }}">
                                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            {{ $mitigasi->mitigasi }}
                                        </span>
                                        @endforeach
                                        @endif
                                        @if($tl->mitigasi_tambahan)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-600 bg-gray-50 px-2 py-0.5 rounded border border-gray-200 truncate max-w-[240px]" title="{{ $tl->mitigasi_tambahan }}">
                                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $tl->mitigasi_tambahan }}
                                        </span>
                                        @endif
                                        @if((!$tl->cause || $tl->cause->mitigations->isEmpty()) && empty($tl->mitigasi_tambahan))
                                        <span class="text-[11px] text-gray-400 italic">- Tidak ada mitigasi -</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 border-b text-sm text-center align-middle whitespace-nowrap">
                                    @php
                                        $resolutionColors = [
                                            'open' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'in_progress' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'closed' => 'bg-green-100 text-green-800 border-green-200',
                                        ];
                                        $resolutionClass = $resolutionColors[$tl->resolution_status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                    @endphp
                                    <span class="px-2 py-1 {{ $resolutionClass }} rounded font-bold text-xs uppercase border">
                                        {{ $tl->resolution_status }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 border-b align-middle">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <form action="{{ route('risk_reports.add_progress', $tl->id) }}" method="POST" class="w-full flex flex-col items-center gap-2">
                                            @csrf

                                            <input type="hidden" name="note" value="Update status dari halaman Review">

                                            @php
                                            $userRole = auth()->user()?->primaryRoleName() ?? '';
                                            $canClose = ($userRole === 'kacab');
                                            @endphp

                                            <select name="new_status" class="w-full max-w-[150px] text-xs border-gray-300 rounded shadow-sm focus:ring-blue-500">
                                                <option value="in_progress" {{ $tl->resolution_status == 'in_progress' ? 'selected' : '' }}>In Progress</option>

                                                @if($canClose)
                                                <option value="closed">Selesai (Closed)</option>
                                                @endif
                                            </select>

                                            <button type="submit" class="w-full max-w-[150px] bg-blue-600 hover:bg-blue-800 text-white font-bold py-1.5 px-3 rounded text-xs shadow transition whitespace-nowrap">
                                                Simpan Status
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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

    <!-- Modal Reject -->
    <div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-red-700">✋ Reject Laporan</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="status" value="rejected">

                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">
                        Anda akan menolak laporan: <span id="rejectKodeLaporan" class="font-mono font-bold text-indigo-700"></span>
                    </p>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="alasan_reject" id="alasanReject" rows="4" required minlength="10"
                        class="w-full rounded-md border-gray-300 text-sm focus:ring-red-500 focus:border-red-500"
                        placeholder="Jelaskan alasan kenapa laporan ini ditolak... (min. 10 karakter)"></textarea>
                    <p id="charCount" class="text-xs text-gray-400 mt-1">0 karakter (min. 10)</p>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-bold rounded">
                        Batal
                    </button>
                    <button type="submit" id="submitReject" disabled class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded disabled:opacity-50 disabled:cursor-not-allowed">
                        Kirim Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Reject
        function openRejectModal(reportId, kodeLaporan) {
            document.getElementById('rejectKodeLaporan').textContent = kodeLaporan;
            document.getElementById('rejectForm').action = '/risk-reports/' + reportId + '/status';
            document.getElementById('rejectModal').classList.remove('hidden');
            document.getElementById('alasanReject').value = '';
            document.getElementById('charCount').textContent = '0 karakter (min. 10)';
            document.getElementById('submitReject').disabled = true;
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        // Hitung karakter realtime
        document.addEventListener('DOMContentLoaded', function() {
            const alasanInput = document.getElementById('alasanReject');
            const charCount = document.getElementById('charCount');
            const submitBtn = document.getElementById('submitReject');

            alasanInput.addEventListener('input', function() {
                const len = this.value.length;
                charCount.textContent = len + ' karakter (min. 10)';
                submitBtn.disabled = len < 10;
            });

            // Tutup modal kalo klik backdrop
            document.getElementById('rejectModal').addEventListener('click', function(e) {
                if (e.target === this) closeRejectModal();
            });

            // Client-side table sorting
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('table').forEach(function(table) {
                const headers = table.querySelectorAll('th.sortable');
                const tbody = table.querySelector('tbody');

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
