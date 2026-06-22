<x-app-layout>
    @section('page_title', 'Detail Laporan')
    <x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
            {{ __('Detail Laporan Risiko') }}
        </h2>
    </div>
</x-slot>

    <div class="pt-4 pb-8 sm:pb-12">
        <div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto">

            {{-- BANNER AREA --}}
            @if($report->status === 'need_revision')
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm mb-6">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">⚠️</span>
                    <div class="flex-1">
                        <h3 class="font-bold text-red-800 text-sm uppercase">Laporan Perlu Direvisi</h3>
                        <p class="text-sm text-red-700 mt-1 bg-white p-3 rounded border border-red-200">
                            {{ $report->revision_note ?? 'Tidak ada catatan revisi.' }}
                        </p>
                        <p class="text-xs text-red-500 mt-2">
                            Silakan edit data laporan di kolom "Area Aksi" di bawah, lalu kirim ulang untuk direview kembali.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if($report->status === 'pending_revision')
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg shadow-sm mb-6">
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

            {{-- MAIN 2-COLUMN LAYOUT --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- KOLOM KIRI (KONTEN UTAMA 70%) --}}
                <div class="col-span-1 lg:col-span-2 space-y-6">
                    
                    {{-- SECTION 1: INFORMASI KEJADIAN --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 {{ $report->kategori === 'finansial' ? 'border-t-red-500' : 'border-t-orange-500' }}">
                        <div class="p-5 sm:p-6">
                            <h3 class="text-lg font-bold text-gray-900 border-b pb-3 mb-4 uppercase tracking-wider flex items-center gap-2">
                                <span>📄</span> Informasi Kejadian
                            </h3>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm mb-6">
                                <div>
                                    <p class="text-gray-500 font-bold text-xs uppercase">Risiko</p>
                                    <p class="font-semibold text-gray-900 break-words">{{ $report->item->nama_risiko ?? $report->other_item_description }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-bold text-xs uppercase">Kategori</p>
                                    <span class="inline-block mt-1 px-3 py-1 text-xs font-bold uppercase rounded-full {{ $report->kategori === 'finansial' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                                        {{ $report->kategori }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-bold text-xs uppercase">Sumber Risiko</p>
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
                                    <span class="inline-block mt-1 px-3 py-1 text-xs font-bold uppercase rounded-full border {{ $sumber['color'] }}">
                                        {{ $sumber['label'] }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-bold text-xs uppercase">Tanggal Lapor</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <p class="font-semibold text-gray-800">
                                            {{ $report->created_at->format('d M Y, H:i') }}
                                        </p>
                                        @php
                                            $tglDiketahui = \Carbon\Carbon::parse($report->tanggal_diketahui)->startOfDay();
                                            $tglLapor = $report->created_at->startOfDay();
                                            $isLate = $tglDiketahui->diffInDays($tglLapor, false) > 7;
                                        @endphp
                                        @if(auth()->user()?->isAdmin() && $isLate)
                                            <span class="px-2 py-0.5 text-[10px] font-bold text-red-700 bg-red-100 border border-red-200 rounded uppercase" title="Lebih dari 7 hari sejak tanggal diketahui">⚠️ Late Report</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-bold text-xs uppercase">Tanggal Kejadian / Diketahui</p>
                                    <p class="font-semibold text-gray-800 mt-1">
                                        {{ \Carbon\Carbon::parse($report->tanggal_kejadian)->format('d M Y') }} / 
                                        {{ \Carbon\Carbon::parse($report->tanggal_diketahui)->format('d M Y') }}
                                    </p>
                                </div>
                            </div>

                            @if($report->kronologis_kejadian)
                            <div class="mt-4 border-t pt-4">
                                <p class="text-gray-500 font-bold text-xs uppercase mb-2">📝 Kronologis Kejadian</p>
                                <p class="text-sm text-gray-800 bg-gray-50 p-4 rounded-lg border border-gray-200 leading-relaxed whitespace-pre-wrap">{{ $report->kronologis_kejadian }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- SECTION 2: ANALISA & DAMPAK --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5 sm:p-6">
                            <h3 class="text-lg font-bold text-gray-900 border-b pb-3 mb-4 uppercase tracking-wider flex items-center gap-2">
                                <span>🔍</span> Analisa & Dampak
                            </h3>

                            <div class="mb-5">
                                <p class="text-gray-500 font-bold text-xs uppercase mb-1">Akar Penyebab Kejadian</p>
                                <p class="font-semibold text-red-700 bg-red-50 p-3 rounded-lg border border-red-100">{{ $report->cause->penyebab ?? $report->other_cause_description }}</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @if($report->kategori === 'finansial')
                                <div class="bg-rose-50 p-4 rounded-lg border border-rose-200">
                                    <p class="text-rose-600 font-bold text-xs uppercase mb-1">Kerugian Finansial</p>
                                    <p class="text-xl font-extrabold text-rose-800">Rp {{ number_format($report->dampak_finansial, 0, ',', '.') }}</p>
                                </div>
                                @else
                                <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                                    <p class="text-orange-600 font-bold text-xs uppercase mb-1">Skala Dampak</p>
                                    <div class="mt-1">
                                        <x-skala-badge :skala="$report->skala_dampak" />
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if($report->kategori !== 'finansial' && $report->dampak_non_finansial)
                            <div class="mt-4">
                                <p class="text-gray-500 font-bold text-xs uppercase mb-2">Penjelasan Dampak</p>
                                <p class="text-sm text-gray-800 bg-gray-50 p-4 rounded-lg border border-gray-200 leading-relaxed whitespace-pre-wrap">{{ $report->dampak_non_finansial }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- SECTION 3: MITIGASI & PENYELESAIAN --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5 sm:p-6">
                            <h3 class="text-lg font-bold text-gray-900 border-b pb-3 mb-4 uppercase tracking-wider flex items-center gap-2">
                                <span>🛡️</span> Mitigasi & Penyelesaian
                            </h3>

                            <div class="space-y-5">
                                <div>
                                    <p class="text-gray-500 font-bold text-xs uppercase mb-2">Rekomendasi Sistem</p>
                                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                        @if($report->cause && $report->cause->mitigations->isNotEmpty())
                                        <ul class="list-disc list-inside text-green-800 text-sm font-semibold space-y-1">
                                            @foreach($report->cause->mitigations as $mitigasi)
                                            <li>{{ $mitigasi->mitigasi }}</li>
                                            @endforeach
                                        </ul>
                                        @else
                                        <p class="text-gray-500 italic text-sm">- Tidak ada saran mitigasi dari sistem -</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @if($report->durasi_penyelesaian)
                                    <div>
                                        <p class="text-gray-500 font-bold text-xs uppercase mb-1">Durasi Penyelesaian (SLA)</p>
                                        <p class="text-sm font-semibold text-blue-700 bg-blue-50 p-3 rounded-lg border border-blue-200">{{ $report->durasi_penyelesaian }} {{ $report->durasi_satuan }}</p>
                                    </div>
                                    @endif

                                    @if($report->mitigasi_tambahan)
                                    <div>
                                        <p class="text-gray-500 font-bold text-xs uppercase mb-1">Mitigasi Tambahan (Manual)</p>
                                        <p class="text-sm text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-200 italic">{{ $report->mitigasi_tambahan }}</p>
                                    </div>
                                    @endif
                                </div>

                                @if($report->tindakan_penyelesaian)
                                <div>
                                    <p class="text-gray-500 font-bold text-xs uppercase mb-2">Tindakan Penyelesaian Akhir</p>
                                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                        <p class="text-sm font-semibold text-green-800 whitespace-pre-wrap">{{ $report->tindakan_penyelesaian }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ANALISA SKMR (MANRISK ONLY) --}}
                    @if(auth()->user()->isAdmin())
                    <div class="bg-indigo-50 overflow-hidden shadow-inner sm:rounded-lg border-2 border-indigo-200 mt-6">
                        <div class="p-5 sm:p-6">
                            <h3 class="text-lg font-extrabold text-indigo-900 mb-4 uppercase tracking-wider flex items-center gap-2">
                                <span>🕵️‍♂️</span> Analisa SKMR (Private Admin)
                            </h3>
                            <p class="text-sm text-indigo-700 mb-4 bg-indigo-100 p-3 rounded-lg border border-indigo-200">
                                Bagian ini hanya dapat dilihat dan diisi oleh ManRisk / Admin. Catatan dan analisa di sini bersifat rahasia dan tidak akan tampil untuk cabang atau pembuat laporan.
                            </p>

                            <form action="{{ route('risk_reports.save_skmr_analysis', $report->id) }}" method="POST" class="space-y-4">
                                @csrf
                                @php
                                    $skmr = $report->skmrAnalysis;
                                @endphp

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan SKMR</label>
                                    <textarea name="catatan_skmr" rows="3" class="w-full rounded-md border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('catatan_skmr', $skmr?->catatan_skmr) }}</textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Ketersediaan Kebijakan</label>
                                        <input type="text" name="ketersediaan_kebijakan" class="w-full rounded-md border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('ketersediaan_kebijakan', $skmr?->ketersediaan_kebijakan) }}">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kesesuaian Proses Terhadap SOP</label>
                                        <input type="text" name="kesesuaian_sop" class="w-full rounded-md border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('kesesuaian_sop', $skmr?->kesesuaian_sop) }}">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Rekomendasi SKMR 1</label>
                                        <textarea name="rekomendasi_1" rows="2" class="w-full rounded-md border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('rekomendasi_1', $skmr?->rekomendasi_1) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dampak Rekomendasi 1</label>
                                        <textarea name="dampak_rekomendasi_1" rows="2" class="w-full rounded-md border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('dampak_rekomendasi_1', $skmr?->dampak_rekomendasi_1) }}</textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Rekomendasi SKMR 2 <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                        <textarea name="rekomendasi_2" rows="2" class="w-full rounded-md border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('rekomendasi_2', $skmr?->rekomendasi_2) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dampak Rekomendasi 2 <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                        <textarea name="dampak_rekomendasi_2" rows="2" class="w-full rounded-md border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('dampak_rekomendasi_2', $skmr?->dampak_rekomendasi_2) }}</textarea>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-indigo-200">
                                    <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition">
                                        💾 Simpan Analisa SKMR
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- AREA AKSI FORM (REVISI / UPDATE PROGRESS) --}}
                    @php
                        $isOwner = (string) $report->user_id === (string) auth()->id();
                        $canRevise = $isOwner && $report->status === 'need_revision';
                        $isSistemTeknologi = $sumberRisiko === 'sistem_teknologi';
                        
                        $canUpdate = $report->status === 'approved_in_progress';
                        $showUpdateForm = $canUpdate && (
                            (auth()->user()->isMaker() && $isOwner) ||
                            (auth()->user()->roleCategory() === 'checker' && (string) $report->branch_id === (string) auth()->user()->branch_id)
                        );

                        $isManRisk = auth()->user()?->isAdmin() ?? false;
                        
                        $canReview = auth()->user()->roleCategory() === 'checker' && $report->status === 'pending_atasan' && (string) $report->branch_id === (string) auth()->user()->branch_id;
                    @endphp

                    @if($canRevise || $showUpdateForm || $canReview || ($isManRisk && in_array($report->status, ['approved_in_progress', 'closed', 'pending_revision'])))
                    <div class="bg-slate-50 overflow-hidden shadow-inner sm:rounded-lg border-2 border-dashed border-slate-300">
                        <div class="p-5 sm:p-6">
                            <h3 class="text-lg font-extrabold text-slate-800 mb-4 uppercase tracking-wider flex items-center gap-2">
                                <span>🛠️</span> Area Aksi
                            </h3>

                            {{-- FORM REVISI --}}
                            @if($canRevise)
                            <div class="bg-white p-5 rounded-lg border border-orange-200 shadow-sm">
                                <h4 class="text-sm font-bold text-orange-800 uppercase mb-4">Form Revisi Laporan</h4>
                                <form action="{{ route('risk_reports.submit_revision', $report->id) }}" method="POST">
                                    @csrf

                                    <div class="mb-4">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kronologis Kejadian <span class="text-red-500">*</span></label>
                                        <textarea name="kronologis_kejadian" rows="4" required minlength="20"
                                            class="w-full rounded-md border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500">{{ old('kronologis_kejadian', $report->kronologis_kejadian) }}</textarea>
                                    </div>

                                    @if($report->kategori === 'finansial')
                                    <div class="mb-4">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dampak Finansial (Rp)</label>
                                        <input type="number" name="dampak_finansial" min="0" step="1"
                                            class="w-full rounded-md border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500"
                                            value="{{ old('dampak_finansial', $report->dampak_finansial) }}">
                                    </div>
                                    @else
                                    <div class="mb-4">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Skala Dampak <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-2 gap-2">
                                            @php $oldSkala = old('skala_dampak', $report->skala_dampak); @endphp
                                            @foreach(['Sangat Tinggi', 'Tinggi', 'Sedang', 'Rendah', 'Sangat Rendah'] as $skala)
                                            <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                                <input type="radio" name="skala_dampak" value="{{ $skala }}" class="h-4 w-4 text-orange-600 focus:ring-orange-500" {{ $oldSkala == $skala ? 'checked' : '' }}>
                                                <span class="ml-2 text-xs font-bold text-gray-700">{{ $skala }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penjelasan Dampak</label>
                                        <textarea name="dampak_non_finansial" rows="3"
                                            class="w-full rounded-md border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500">{{ old('dampak_non_finansial', $report->dampak_non_finansial) }}</textarea>
                                    </div>
                                    @endif

                                    <div class="mb-4">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Mitigasi Tambahan</label>
                                        <textarea name="mitigasi_tambahan" rows="2"
                                            class="w-full rounded-md border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500">{{ old('mitigasi_tambahan', $report->mitigasi_tambahan) }}</textarea>
                                    </div>

                                    <div id="durasiRevisiContainer" class="grid grid-cols-2 gap-3 mb-4 {{ $isSistemTeknologi ? '' : 'hidden' }}">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Durasi Penyelesaian (SLA)</label>
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

                                    <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition">
                                        🚀 Kirim Revisi
                                    </button>
                                </form>
                            </div>
                            @endif

                            {{-- FORM UPDATE STATUS & PROGRESS --}}
                            @if($showUpdateForm)
                            <div x-data="{ 
                                actionStatus: 'approved_in_progress', 
                                actionNote: '',
                                get wordCount() {
                                    return this.actionNote.trim() === '' ? 0 : this.actionNote.trim().split(/\s+/).length;
                                }
                            }" class="bg-white p-5 rounded-lg border border-blue-200 shadow-sm mt-4">
                                <h4 class="text-sm font-bold text-blue-800 uppercase mb-3">Tindak Lanjut & Penyelesaian</h4>
                                <form action="{{ route('risk_reports.add_progress', $report->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status Laporan</label>
                                        <select name="new_status" x-model="actionStatus" class="w-full text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                            <option value="approved_in_progress">In Progress (Sedang Dikerjakan)</option>
                                            <option value="closed">Closed (Selesai Tuntas)</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1" x-text="actionStatus === 'closed' ? 'Tindakan Penyelesaian' : 'Catatan Progress'"></label>
                                        <textarea name="note" x-model="actionNote" rows="3" required class="w-full rounded-md border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500" :placeholder="actionStatus === 'closed' ? 'Ketik tindakan penyelesaian akhir di sini...' : 'Ketik catatan progress di sini...'"></textarea>
                                        
                                        @error('note')
                                            <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                                        @enderror

                                        <p class="text-xs text-gray-400 mt-1" x-text="wordCount + ' kata (min. 10)'"></p>
                                    </div>
                                    <button type="submit" :disabled="wordCount < 10" :class="wordCount < 10 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-800'" class="w-full bg-blue-600 text-white font-bold py-2.5 px-4 rounded-lg shadow transition mt-2">
                                        💾 Simpan Status & Progress
                                    </button>
                                </form>
                            </div>
                            @endif

                            {{-- FORM APPROVE / REJECT (KHUSUS CHECKER) --}}
                            @if(auth()->user()->roleCategory() === 'checker' && $report->status === 'pending_atasan')
                            <div x-data="{ showRejectForm: false, alasanReject: '' }" class="bg-white p-5 rounded-lg border border-indigo-200 shadow-sm mt-4">
                                <h4 class="text-sm font-bold text-indigo-800 uppercase mb-3">Tinjauan Laporan (Menunggu Review)</h4>
                                
                                <div x-show="!showRejectForm" class="grid grid-cols-2 gap-3">
                                    <!-- Approve Button -->
                                    <form action="{{ route('risk_reports.update_status', $report->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition">
                                            ✅ Setujui (Approve)
                                        </button>
                                    </form>

                                    <!-- Toggle Reject Form Button -->
                                    <button type="button" @click="showRejectForm = true" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition">
                                        ❌ Tolak (Reject)
                                    </button>
                                </div>

                                <!-- Reject Form (Collapsible) -->
                                <div x-show="showRejectForm" style="display: none;" class="mt-4 border-t pt-4 border-gray-200">
                                    <form action="{{ route('risk_reports.update_status', $report->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="need_revision">
                                        <label class="block text-xs font-bold text-red-700 uppercase mb-1">Alasan Penolakan / Catatan Revisi <span class="text-red-500">*</span></label>
                                        <textarea name="alasan_reject" rows="3" required x-model="alasanReject" class="w-full rounded-md border-red-300 text-sm focus:ring-red-500 focus:border-red-500 mb-1" placeholder="Sebutkan bagian mana yang perlu diperbaiki oleh staff..."></textarea>
                                        <p class="text-xs text-gray-400 mt-1 mb-3" x-text="alasanReject.length + ' karakter (min. 10)'"></p>
                                        
                                        <div class="flex gap-2 justify-end">
                                            <button type="button" @click="showRejectForm = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                                Batal
                                            </button>
                                            <button type="submit" :disabled="alasanReject.length < 10" :class="alasanReject.length < 10 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-700'" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                                                Kirim Penolakan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endif



                            {{-- TOMBOL MANRISK --}}
                            @if($isManRisk && in_array($report->status, ['approved_in_progress', 'closed']))
                            <div class="bg-white p-5 rounded-lg border border-purple-200 shadow-sm">
                                <h4 class="text-sm font-bold text-purple-800 uppercase mb-3">🔁 Minta Revisi (ManRisk)</h4>
                                <form action="{{ route('risk_reports.request_revision', $report->id) }}" method="POST">
                                    @csrf
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan Revisi <span class="text-red-500">*</span></label>
                                    <textarea name="revision_note" rows="3" required minlength="10"
                                        class="w-full rounded-md border-gray-300 text-sm focus:ring-purple-500 focus:border-purple-500 mb-3"
                                        placeholder="Jelaskan apa yang perlu direvisi... (min. 10 karakter)"></textarea>
                                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition">
                                        Kirim Permintaan Revisi
                                    </button>
                                </form>
                            </div>
                            @endif

                            @if($isManRisk && $report->status === 'pending_revision')
                            <div class="bg-white p-5 rounded-lg border border-green-200 shadow-sm">
                                <form action="{{ route('risk_reports.approve_revision', $report->id) }}" method="POST">
                                    @csrf
                                    <p class="text-sm text-green-800 mb-3">Revisi laporan ini sedang menunggu persetujuan Anda.</p>
                                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition">
                                        ✅ Setujui Revisi
                                    </button>
                                </form>
                            </div>
                            @endif

                        </div>
                    </div>
                    @endif
                </div>


                {{-- KOLOM KANAN (SIDEBAR 30%) --}}
                <div class="col-span-1 space-y-6">
                    
                    {{-- SIDEBAR 1: INFORMASI PELAPOR --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5">
                            <h3 class="text-sm font-bold text-gray-900 border-b pb-2 mb-4 uppercase tracking-wider flex items-center gap-2">
                                <span>👤</span> Profil Pelapor
                            </h3>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg">
                                    {{ substr($report->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ $report->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $report->user->primaryRoleName() ?? 'No Role' }}</p>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <p class="text-xs text-gray-500 font-bold uppercase mb-1">Cabang Asal</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $report->branch->nama_cabang }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- SIDEBAR 2: STATUS & TINDAK LANJUT --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5">
                            <h3 class="text-sm font-bold text-gray-900 border-b pb-2 mb-4 uppercase tracking-wider flex items-center gap-2">
                                <span>📊</span> Status Laporan
                            </h3>

                            @php
                                $statusColors = [
                                    'approved' => 'bg-green-100 text-green-800 border-green-200',
                                    'approved_in_progress' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                    'pending_atasan' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'need_revision' => 'bg-orange-100 text-orange-800 border-orange-200',
                                    'pending_revision' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'closed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                ];
                                $statusLabels = [
                                    'approved' => 'Disetujui',
                                    'approved_in_progress' => 'Dalam Proses',
                                    'rejected' => 'Ditolak',
                                    'pending_atasan' => 'Menunggu Atasan',
                                    'need_revision' => 'Perlu Revisi',
                                    'pending_revision' => 'Review Revisi',
                                    'closed' => 'Selesai / Closed',
                                ];
                                $statusColor = $statusColors[$report->status] ?? 'bg-gray-100 text-gray-800';
                                $statusLabel = $statusLabels[$report->status] ?? str_replace('_', ' ', $report->status);
                            @endphp

                            <div class="mb-4 text-center">
                                <span class="inline-block w-full text-center px-4 py-2 text-sm font-extrabold uppercase rounded-lg border shadow-sm {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            @php
                                $status = $report->status;
                                $isNeedRevision = $status === 'need_revision';
                                $isApproved = $status === 'approved_in_progress';
                                $isClosed = $status === 'closed';

                                // Logika Progress (3 steps)
                                $step1State = 'active'; $step1Color = 'blue';
                                if ($isNeedRevision) $step1Color = 'red';
                                elseif ($isApproved || $isClosed) $step1State = 'completed';

                                $step2State = 'pending';
                                if ($isApproved) $step2State = 'active';
                                if ($isClosed) $step2State = 'completed';

                                $step3State = $isClosed ? 'active' : 'pending';
                            @endphp

                            <div class="relative flex flex-col gap-6 mt-6 ml-2">
                                <!-- Step 1 -->
                                <div class="flex items-center gap-4 relative z-10">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition-colors duration-300 {{ $step1State === 'completed' ? 'bg-blue-600 text-white' : ($step1State === 'active' && $step1Color === 'red' ? 'bg-red-500 text-white ring-4 ring-red-100' : 'bg-blue-600 text-white ring-4 ring-blue-100') }}">
                                        @if($step1State === 'completed') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> @else 1 @endif
                                    </div>
                                    <span class="text-sm font-bold {{ $step1Color === 'red' ? 'text-red-600' : ($step1State === 'pending' ? 'text-gray-400' : 'text-blue-700') }}">
                                        {{ $isNeedRevision ? 'Perlu Revisi' : 'Menunggu Review' }}
                                    </span>
                                </div>
                                <!-- Step 2 -->
                                <div class="flex items-center gap-4 relative z-10">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition-colors duration-300 {{ $step2State === 'completed' ? 'bg-blue-600 text-white' : ($step2State === 'active' ? 'bg-blue-600 text-white ring-4 ring-blue-100' : 'bg-white border-2 border-gray-300 text-gray-400') }}">
                                        @if($step2State === 'completed') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> @else 2 @endif
                                    </div>
                                    <span class="text-sm font-bold {{ $step2State === 'pending' ? 'text-gray-400' : 'text-blue-700' }}">Diproses (In Progress)</span>
                                </div>
                                <!-- Step 3 -->
                                <div class="flex items-center gap-4 relative z-10">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition-colors duration-300 {{ $step3State === 'active' ? 'bg-green-500 text-white ring-4 ring-green-100' : 'bg-white border-2 border-gray-300 text-gray-400' }}">
                                        @if($step3State === 'active') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> @else 3 @endif
                                    </div>
                                    <span class="text-sm font-bold {{ $step3State === 'pending' ? 'text-gray-400' : 'text-green-600' }}">Selesai (Closed)</span>
                                </div>
                                <!-- Vertical Line -->
                                <div class="absolute left-4 top-4 bottom-4 w-0.5 bg-gray-200 z-0"></div>
                            </div>

                        </div>
                    </div>

                    {{-- SIDEBAR 3: TIMELINE (RIWAYAT) --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5">
                            <h3 class="text-sm font-bold text-gray-900 border-b pb-2 mb-4 uppercase tracking-wider flex items-center gap-2">
                                <span>⏱️</span> Timeline Riwayat
                            </h3>

                            @if($report->logs->isEmpty())
                            <p class="text-xs text-gray-500 italic bg-gray-50 p-3 rounded text-center">Belum ada catatan.</p>
                            @else
                            @php
                                $groupedLogs = [];
                                foreach($report->logs as $log) {
                                    $clonedLog = clone $log;
                                    $lastIdx = count($groupedLogs) - 1;
                                    if ($lastIdx >= 0 && 
                                        str_starts_with($clonedLog->note, 'Penanganan Awal:') && 
                                        $groupedLogs[$lastIdx]->note === 'Laporan dibuat' &&
                                        $groupedLogs[$lastIdx]->created_at->diffInSeconds($clonedLog->created_at) < 5
                                    ) {
                                        $isiPenanganan = trim(substr($clonedLog->note, 17));
                                        $groupedLogs[$lastIdx]->note = "notif system : laporan dibuat\npenanganan awal : " . $isiPenanganan;
                                    } else {
                                        if ($clonedLog->note === 'Laporan dibuat') {
                                            $clonedLog->note = "notif system : laporan dibuat";
                                        }
                                        $groupedLogs[] = $clonedLog;
                                    }
                                }
                            @endphp
                            
                            <div class="space-y-4 relative before:absolute before:inset-0 before:ml-2 before:h-full before:w-0.5 before:bg-gray-200 pl-6">
                                @foreach($groupedLogs as $log)
                                <div class="relative">
                                    <div class="absolute -left-6 mt-1.5 w-4 h-4 rounded-full border-2 border-white {{ $log->old_data ? 'bg-orange-500' : 'bg-blue-500' }} shadow z-10"></div>
                                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 shadow-sm">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[10px] font-bold {{ $log->old_data ? 'text-orange-600' : 'text-blue-600' }}">
                                                {{ $log->user->name }}
                                            </span>
                                            <span class="text-[9px] text-gray-400">{{ $log->created_at->format('d M, H:i') }}</span>
                                        </div>
                                        <p class="text-xs text-gray-700 leading-relaxed mb-2">{!! nl2br(e($log->note)) !!}</p>
                                        <span class="text-[9px] uppercase font-extrabold px-1.5 py-0.5 bg-gray-200 text-gray-600 rounded">
                                            &rarr; {{ str_replace('_', ' ', $log->status_after_note) }}
                                        </span>

                                        {{-- Diff Data --}}
                                        @if($log->old_data)
                                        @php
                                            $oldData = is_string($log->old_data) ? json_decode($log->old_data, true) : $log->old_data;
                                        @endphp
                                        <details class="mt-2">
                                            <summary class="text-[10px] text-blue-600 cursor-pointer font-bold hover:text-blue-800">
                                                📋 Lihat perubahan data
                                            </summary>
                                            <div class="mt-2 text-[10px] bg-white p-2 rounded border border-gray-200 space-y-1">
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
                                                        <span class="text-red-500 line-through">{{ $oldValue ?? '(kosong)' }}</span><br>
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

                </div> {{-- END KOLOM KANAN --}}

            </div>
        </div>
    </div>
</x-app-layout>
