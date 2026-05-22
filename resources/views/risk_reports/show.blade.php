<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-bold text-lg sm:text-xl text-gray-800 leading-tight">
                {{ __('Detail Laporan Risiko') }}
                @if($report->kode_laporan)
                <span class="text-sm font-mono font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded border border-indigo-200 ml-2 align-middle">
                    {{ $report->kode_laporan }}
                </span>
                @endif
            </h2>
            <a href="{{ url()->previous() }}" class="inline-flex w-full sm:w-auto justify-center bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm shadow">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="space-y-6">

                {{-- BANNER REVISI — kalo status need_revision --}}
                @if($report->status === 'need_revision')
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div class="flex-1">
                            <h3 class="font-bold text-red-800 text-sm uppercase">Laporan Perlu Direvisi</h3>
                            <p class="text-sm text-red-700 mt-1 bg-white p-3 rounded border border-red-200">
                                {{ $report->revision_note ?? 'Tidak ada catatan revisi.' }}
                            </p>
                            <p class="text-xs text-red-500 mt-2">
                                Silakan edit data laporan di bawah, lalu kirim ulang untuk direview kembali.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- BANNER PENDING REVISION — kalo status pending_revision --}}
                @if($report->status === 'pending_revision')
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">⏳</span>
                        <div class="flex-1">
                            <h3 class="font-bold text-yellow-800 text-sm uppercase">Menunggu Review Revisi</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                Revisi laporan telah dikirim dan sedang menunggu persetujuan dari ManRisk.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- CARD 1: Informasi Laporan --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 {{ $report->kategori === 'finansial' ? 'border-t-red-500' : 'border-t-orange-500' }}">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start mb-6 border-b pb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 break-words">{{ $report->item->nama_risiko ?? $report->other_item_description }}</h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Dilaporkan oleh: <span class="font-bold text-gray-700">{{ $report->user->name }}</span>
                                    ({{ $report->branch->nama_cabang }})
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2 sm:max-w-[280px] sm:justify-end sm:text-right">
                                @php
                                $sumberRisiko = $report->sumber_risiko ?? $report->cause->sumber_risiko ?? $report->item->sumber_risiko ?? 'manusia';
                                $sumberLabels = [
                                    'manusia' => ['label' => 'Manusia', 'color' => 'bg-red-100 text-red-800 border-red-200'],
                                    'proses_internal' => ['label' => 'Proses Internal', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                                    'sistem_teknologi' => ['label' => 'Sistem Teknologi', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
                                    'faktor_eksternal' => ['label' => 'Faktor Eksternal', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
                                ];
                                $sumber = $sumberLabels[$sumberRisiko] ?? $sumberLabels['manusia'];
                                @endphp
                                <span class="px-3 py-1 text-xs font-bold uppercase rounded-full border {{ $sumber['color'] }}">
                                    {{ $sumber['label'] }}
                                </span>
                                <span class="px-3 py-1 text-xs font-bold uppercase rounded-full {{ $report->kategori === 'finansial' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ $report->kategori }}
                                </span>
                                @php
                                $statusColors = [
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'pending_kacab' => 'bg-yellow-100 text-yellow-800',
                                    'need_revision' => 'bg-orange-100 text-orange-800',
                                    'pending_revision' => 'bg-blue-100 text-blue-800',
                                ];
                                $statusLabels = [
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    'pending_kacab' => 'Menunggu Kacab',
                                    'need_revision' => 'Perlu Revisi',
                                    'pending_revision' => 'Menunggu Review Revisi',
                                ];
                                $statusColor = $statusColors[$report->status] ?? 'bg-gray-100 text-gray-800';
                                $statusLabel = $statusLabels[$report->status] ?? $report->status;
                                @endphp
                                <span class="px-3 py-1 text-xs font-bold uppercase rounded-full {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm mb-4">
                            <div>
                                <p class="text-gray-500 font-bold text-xs uppercase">Tanggal Kejadian</p>
                                <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($report->tanggal_kejadian)->format('d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 font-bold text-xs uppercase">Tanggal Diketahui</p>
                                <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($report->tanggal_diketahui)->format('d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 font-bold text-xs uppercase">Jabatan Pelapor</p>
                                <p class="font-semibold text-gray-800">{{ $report->user->primaryRoleName() ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: Analisa & Mitigasi + Status Resolusi --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-md font-bold text-gray-900 border-b pb-2 mb-4 uppercase tracking-wider">Analisa & Mitigasi</h3>

                        <div class="mb-4">
                            <p class="text-gray-500 font-bold text-xs uppercase mb-1">Akar Penyebab Kejadian</p>
                            <p class="font-semibold text-red-600 bg-red-50 p-3 rounded border border-red-100">{{ $report->cause->penyebab ?? $report->other_cause_description }}</p>
                        </div>

                        @if($report->kronologis_kejadian)
                        <div class="mb-4">
                            <p class="text-gray-500 font-bold text-xs uppercase mb-1">📝 Kronologis Kejadian</p>
                            <p class="text-sm text-gray-800 bg-gray-50 p-3 rounded border border-gray-200 leading-relaxed whitespace-pre-wrap">{{ $report->kronologis_kejadian }}</p>
                        </div>
                        @endif

                        <div class="mb-4">
                            <p class="text-gray-500 font-bold text-xs uppercase mb-1">Rekomendasi Mitigasi Sistem</p>
                            <div class="bg-green-50 p-3 rounded border border-green-100">
                                @if($report->cause && $report->cause->mitigations->isNotEmpty())
                                <ul class="list-disc list-inside text-green-800 text-sm font-semibold">
                                    @foreach($report->cause->mitigations as $mitigasi)
                                    <li>{{ $mitigasi->mitigasi }}</li>
                                    @endforeach
                                </ul>
                                @else
                                <p class="text-gray-500 italic text-sm">- Tidak ada saran mitigasi dari sistem -</p>
                                @endif
                            </div>
                        </div>

                        @if($report->durasi_penyelesaian)
                        <div class="mb-4">
                            <p class="text-gray-500 font-bold text-xs uppercase mb-1">⏱ Durasi Penyelesaian</p>
                            <p class="text-sm font-semibold text-orange-700 bg-orange-50 p-3 rounded border border-orange-200">{{ $report->durasi_penyelesaian }} {{ $report->durasi_satuan }}</p>
                        </div>
                        @endif

                        @if($report->mitigasi_tambahan)
                        <div class="mb-4">
                            <p class="text-gray-500 font-bold text-xs uppercase mb-1">Mitigasi Tambahan (Manual)</p>
                            <p class="text-sm text-gray-800 bg-gray-50 p-3 rounded border border-gray-200 italic">{{ $report->mitigasi_tambahan }}</p>
                        </div>
                        @endif

                        {{-- FORM REVISI — kalo status need_revision --}}
                        @if($report->status === 'need_revision')
                        @php
                            $isOwner = (int) $report->user_id === (int) auth()->id();
                            $isCheckerOwner = auth()->user()->roleCategory() === 'checker' && (int) $report->branch_id === (int) auth()->user()->branch_id;
                            $canRevise = $isOwner || $isCheckerOwner;
                            $sumberRisiko = $report->sumber_risiko ?? $report->cause->sumber_risiko ?? $report->item->sumber_risiko ?? '';
                            $isSistemTeknologi = $sumberRisiko === 'sistem_teknologi';
                        @endphp

                        @if($canRevise)
                        <div class="mb-6 bg-orange-50 p-4 rounded-lg border border-orange-200">
                            <h4 class="text-sm font-bold text-orange-800 uppercase mb-3">✏️ Form Revisi Laporan</h4>
                            <form action="{{ route('risk_reports.submit_revision', $report->id) }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kronologis Kejadian <span class="text-red-500">*</span></label>
                                    <textarea name="kronologis_kejadian" rows="4" required minlength="20"
                                        class="w-full rounded-md border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500">{{ old('kronologis_kejadian', $report->kronologis_kejadian) }}</textarea>
                                </div>

                                @if($report->kategori === 'finansial')
                                <div class="mb-3">
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dampak Finansial (Rp)</label>
                                    <input type="number" name="dampak_finansial" min="0" step="1"
                                        class="w-full rounded-md border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500"
                                        value="{{ old('dampak_finansial', $report->dampak_finansial) }}">
                                </div>
                                @else
                                <div class="mb-3">
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Skala Dampak <span class="text-red-500">*</span></label>
                                    <div class="space-y-2">
                                        @php $oldSkala = old('skala_dampak', $report->skala_dampak); @endphp
                                        <label class="flex items-center p-2 border border-gray-300 rounded hover:bg-white cursor-pointer transition">
                                            <input type="radio" name="skala_dampak" value="Sangat Tinggi" class="h-4 w-4 text-orange-600 focus:ring-orange-500" {{ $oldSkala == 'Sangat Tinggi' ? 'checked' : '' }}>
                                            <span class="ml-3 text-sm font-bold text-gray-700">Sangat Tinggi</span>
                                        </label>
                                        <label class="flex items-center p-2 border border-gray-300 rounded hover:bg-white cursor-pointer transition">
                                            <input type="radio" name="skala_dampak" value="Tinggi" class="h-4 w-4 text-orange-600 focus:ring-orange-500" {{ $oldSkala == 'Tinggi' ? 'checked' : '' }}>
                                            <span class="ml-3 text-sm font-bold text-gray-700">Tinggi</span>
                                        </label>
                                        <label class="flex items-center p-2 border border-gray-300 rounded hover:bg-white cursor-pointer transition">
                                            <input type="radio" name="skala_dampak" value="Sedang" class="h-4 w-4 text-orange-600 focus:ring-orange-500" {{ $oldSkala == 'Sedang' ? 'checked' : '' }}>
                                            <span class="ml-3 text-sm font-bold text-gray-700">Sedang</span>
                                        </label>
                                        <label class="flex items-center p-2 border border-gray-300 rounded hover:bg-white cursor-pointer transition">
                                            <input type="radio" name="skala_dampak" value="Rendah" class="h-4 w-4 text-orange-600 focus:ring-orange-500" {{ $oldSkala == 'Rendah' ? 'checked' : '' }}>
                                            <span class="ml-3 text-sm font-bold text-gray-700">Rendah</span>
                                        </label>
                                        <label class="flex items-center p-2 border border-gray-300 rounded hover:bg-white cursor-pointer transition">
                                            <input type="radio" name="skala_dampak" value="Sangat Rendah" class="h-4 w-4 text-orange-600 focus:ring-orange-500" {{ $oldSkala == 'Sangat Rendah' ? 'checked' : '' }}>
                                            <span class="ml-3 text-sm font-bold text-gray-700">Sangat Rendah</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penjelasan Dampak</label>
                                    <textarea name="dampak_non_finansial" rows="3"
                                        class="w-full rounded-md border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500">{{ old('dampak_non_finansial', $report->dampak_non_finansial) }}</textarea>
                                </div>
                                @endif

                                <div class="mb-3">
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Mitigasi Tambahan</label>
                                    <textarea name="mitigasi_tambahan" rows="2"
                                        class="w-full rounded-md border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500">{{ old('mitigasi_tambahan', $report->mitigasi_tambahan) }}</textarea>
                                </div>

                                {{-- Durasi Penyelesaian — hanya tampil kalo sumber risiko = sistem_teknologi --}}
                                <div id="durasiRevisiContainer" class="grid grid-cols-2 gap-3 mb-3 {{ $isSistemTeknologi ? '' : 'hidden' }}">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Durasi Penyelesaian</label>
                                        <input type="number" name="durasi_penyelesaian" min="1"
                                            class="w-full rounded-md border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500"
                                            value="{{ old('durasi_penyelesaian', $report->durasi_penyelesaian) }}">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Satuan</label>
                                        <select name="durasi_satuan" class="w-full rounded-md border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500">
                                            <option value="jam" {{ ($report->durasi_satuan ?? '') == 'jam' ? 'selected' : '' }}>Jam</option>
                                            <option value="hari" {{ ($report->durasi_satuan ?? '') == 'hari' ? 'selected' : '' }}>Hari</option>
                                            <option value="minggu" {{ ($report->durasi_satuan ?? '') == 'minggu' ? 'selected' : '' }}>Minggu</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded text-sm transition">
                                    Kirim Revisi
                                </button>
                            </form>
                        </div>
                        @endif
                        @endif

                        {{-- Status Resolusi & Timeline --}}
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-4">
                                <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Status Resolusi</h4>
                                @php
                                $resColors = [
                                'pending_kacab' => 'bg-yellow-100 text-yellow-800',
                                'need_revision' => 'bg-orange-100 text-orange-800',
                                'pending_revision' => 'bg-blue-100 text-blue-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'in_progress' => 'bg-blue-100 text-blue-800',
                                'closed' => 'bg-green-100 text-green-800',
                                ];
                                $resClass = $resColors[$report->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-3 py-1 text-xs font-bold uppercase rounded-full {{ $resClass }}">
                                    {{ str_replace('_', ' ', $report->status) }}
                                </span>
                            </div>

                            @php
                                $isOwner = (int) $report->user_id === (int) auth()->id();
                                $canUpdate = $report->status === 'approved' && $report->status !== 'closed';

                                // Maker bisa update kalo laporan milik mereka sendiri
                                // Checker bisa update kalo laporan dari cabangnya
                                $showForm = $canUpdate && (
                                    (auth()->user()->isMaker() && $isOwner) ||
                                    (auth()->user()->roleCategory() === 'checker' && (int) $report->branch_id === (int) auth()->user()->branch_id)
                                );
                            @endphp

                            @if($showForm)
                            <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-100">
                                <form action="{{ route('risk_reports.add_progress', $report->id) }}" method="POST">
                                    @csrf

                                    <label class="block text-xs font-bold text-blue-800 uppercase mb-2">Update Progress Baru</label>
                                    <textarea name="note" rows="3" required class="w-full rounded-md border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 mb-2" placeholder="Ketik tindakan penyelesaian di sini..."></textarea>

                                    <label class="block text-xs font-bold text-blue-800 uppercase mb-1">Set Status Menjadi:</label>
                                    <select name="new_status" class="w-full rounded-md border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 mb-3">
                                        <option value="in_progress" {{ $report->status == 'in_progress' ? 'selected' : '' }}>In Progress (Sedang dikerjakan)</option>
                                        @if(auth()->user()->roleCategory() === 'checker')
                                        <option value="closed" class="font-bold text-green-600">Closed (Selesai Tuntas)</option>
                                        @endif
                                    </select>

                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm transition">
                                        Simpan Progress
                                    </button>
                                </form>
                            </div>
                            @endif

                            {{-- TOMBOL MANRISK: Request Revision --}}
                            @if(auth()->user()?->hasRole('manrisk') && $report->status === 'approved')
                            <div class="mb-6 bg-purple-50 p-4 rounded-lg border border-purple-200">
                                <h4 class="text-sm font-bold text-purple-800 uppercase mb-3">🔁 Minta Revisi (ManRisk)</h4>
                                <form action="{{ route('risk_reports.request_revision', $report->id) }}" method="POST">
                                    @csrf
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan Revisi <span class="text-red-500">*</span></label>
                                    <textarea name="revision_note" rows="3" required minlength="10"
                                        class="w-full rounded-md border-gray-300 text-sm focus:ring-purple-500 focus:border-purple-500 mb-2"
                                        placeholder="Jelaskan apa yang perlu direvisi... (min. 10 karakter)"></textarea>
                                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm transition">
                                        Kirim Permintaan Revisi
                                    </button>
                                </form>
                            </div>
                            @endif

                            {{-- TOMBOL MANRISK: Approve Revision --}}
                            @if(auth()->user()?->hasRole('manrisk') && $report->status === 'pending_revision')
                            <div class="mb-6 bg-green-50 p-4 rounded-lg border border-green-200">
                                <form action="{{ route('risk_reports.approve_revision', $report->id) }}" method="POST">
                                    @csrf
                                    <p class="text-sm text-green-800 mb-2">Revisi laporan ini sedang menunggu persetujuan Anda.</p>
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm transition">
                                        ✅ Setujui Revisi
                                    </button>
                                </form>
                            </div>
                            @endif

                            <div>
                                <h4 class="text-sm font-bold text-gray-700 uppercase mb-4">Riwayat Penyelesaian</h4>

                                @if($report->logs->isEmpty())
                                <p class="text-xs text-gray-500 italic bg-gray-50 p-3 rounded text-center">Belum ada catatan progress untuk laporan ini.</p>
                                @else
                                <div class="space-y-4 relative before:absolute before:inset-0 before:ml-2 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-300 before:to-transparent">

                                    @foreach($report->logs as $log)
                                    <div class="relative flex items-start justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div class="flex items-center justify-center w-5 h-5 rounded-full border-2 border-white {{ $log->old_data ? 'bg-orange-500' : 'bg-blue-500' }} shadow shrink-0 absolute left-0 md:left-1/2 md:-translate-x-1/2 z-10"></div>

                                        <div class="w-[calc(100%-2rem)] md:w-[calc(50%-1.5rem)] bg-white p-3 rounded border border-gray-200 shadow-sm ml-6 md:ml-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-[10px] font-bold {{ $log->old_data ? 'text-orange-600' : 'text-blue-600' }}">
                                                    {{ $log->old_data ? '✏️ ' : '' }}{{ $log->user->name }}
                                                </span>
                                                <span class="text-[10px] text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                            </div>
                                            <p class="text-xs text-gray-700 leading-relaxed mb-2">{{ $log->note }}</p>
                                            <span class="text-[9px] uppercase font-extrabold px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded border border-gray-200">
                                                &rarr; {{ str_replace('_', ' ', $log->status_after_note) }}
                                            </span>

                                            {{-- Tampilkan diff kalo ada old_data --}}
                                            @if($log->old_data)
                                            @php
                                                $oldData = is_string($log->old_data) ? json_decode($log->old_data, true) : $log->old_data;
                                            @endphp
                                            <details class="mt-2">
                                                <summary class="text-[10px] text-blue-600 cursor-pointer font-bold hover:text-blue-800">
                                                    📋 Lihat perubahan data
                                                </summary>
                                                <div class="mt-2 text-[11px] bg-gray-50 p-2 rounded border border-gray-200 space-y-1">
                                                    @foreach($oldData as $field => $oldValue)
                                                        @php
                                                            $currentValue = $report->$field;
                                                            $fieldLabels = [
                                                                'kronologis_kejadian' => 'Kronologis Kejadian',
                                                                'dampak_finansial' => 'Dampak Finansial',
                                                                'skala_dampak' => 'Skala Dampak',
                                                                'dampak_non_finansial' => 'Penjelasan Dampak',
                                                                'mitigasi_tambahan' => 'Mitigasi Tambahan',
                                                                'durasi_penyelesaian' => 'Durasi Penyelesaian',
                                                                'durasi_satuan' => 'Satuan Durasi',
                                                            ];
                                                            $label = $fieldLabels[$field] ?? $field;
                                                        @endphp
                                                        @if((string) $oldValue !== (string) $currentValue)
                                                        <div class="mb-1 pb-1 border-b border-gray-100 last:border-b-0">
                                                            <span class="font-bold text-gray-700">{{ $label }}:</span><br>
                                                            <span class="text-red-600 line-through">{{ $oldValue ?? '(kosong)' }}</span><br>
                                                            <span class="text-green-600">{{ $currentValue ?? '(kosong)' }}</span>
                                                        </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </details>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach

                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: Dampak Kerugian --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-md font-bold text-gray-900 border-b pb-2 mb-4 uppercase tracking-wider">Dampak Kerugian</h3>

                        @if($report->kategori === 'finansial')
                        <div class="bg-red-50 p-6 rounded-lg border border-red-200 text-center">
                            <p class="text-red-500 font-bold text-sm uppercase mb-1">Total Kerugian Finansial</p>
                            <p class="text-3xl font-extrabold text-red-700">Rp {{ number_format($report->dampak_finansial, 0, ',', '.') }}</p>
                        </div>
                        @else
                        <div>
                            <p class="text-gray-500 font-bold text-xs uppercase mb-1">Skala Dampak</p>
                            <span class="px-3 py-1 bg-gray-800 text-white text-xs font-bold rounded shadow">{{ $report->skala_dampak ?? 'Tidak ada skala' }}</span>
                        </div>
                        <div class="mt-4">
                            <p class="text-gray-500 font-bold text-xs uppercase mb-1">Penjelasan Dampak (Kronologi)</p>
                            <p class="text-sm text-gray-800 bg-orange-50 p-4 rounded border border-orange-100 leading-relaxed whitespace-pre-wrap">{{ $report->dampak_non_finansial }}</p>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
