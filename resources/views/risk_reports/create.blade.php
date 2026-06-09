<x-app-layout>
    @section('page_title', 'Form Laporan')
    <x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
            {{ __('Form Input Risiko Operasional (' . ($kategori === 'finansial' ? 'Finansial' : 'Non-Finansial') . ')') }}
        </h2>
    </div>
</x-slot>

    <div class="pt-4 pb-8 sm:pb-12">
        <div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">

                {{-- =============================================================
                     ERROR SUMMARY — Muncul kalau ada error validasi
                     ============================================================= --}}
                @if($errors->any())
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg"
                    role="alert"
                >
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-rose-800">Terdapat {{ $errors->count() }} kesalahan dalam pengisian form</h4>
                            <p class="text-xs text-rose-600 mt-0.5">Silakan perbaiki data yang ditandai dengan warna merah di bawah ini.</p>
                            <ul class="mt-2 text-xs text-rose-700 list-disc list-inside space-y-0.5">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button @click="show = false" class="text-rose-400 hover:text-rose-600 transition shrink-0">
                            <svg class="w-5 h-
                @endif

                <form action="{{ route('form.risiko.store') }}" method="POST" id="riskForm">
                    @csrf

                    <input type="hidden" name="kategori" value="{{ $kategori }}">

                    <h3 class="text-lg font-bold border-b pb-2 mb-4 text-blue-700">1. Tanggal Kejadian</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Kejadian <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_kejadian" id="tanggalKejadian" value="{{ old('tanggal_kejadian') }}" max="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('tanggal_kejadian') border-red-500 bg-red-50 @enderror" required>
                            @error('tanggal_kejadian')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Diketahui <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_diketahui" id="tanggalDiketahui" value="{{ old('tanggal_diketahui') }}" max="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('tanggal_diketahui') border-red-500 bg-red-50 @enderror" required>
                            @error('tanggal_diketahui')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <h3 class="text-lg font-bold border-b pb-2 mb-4 text-blue-700">2. Detail Risiko</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Pilih Potensi Risiko <span class="text-red-500">*</span></label>
                        <select id="riskItemSelect" name="risk_item_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('risk_item_id') border-red-500 bg-red-50 @enderror" required>
                            <option value="">-- Pilih Potensi Risiko --</option>
                            @foreach($riskItems as $item)
                            <option value="{{ $item->id }}" data-sumber-risiko="{{ $item->sumber_risiko }}" {{ old('risk_item_id') == $item->id ? 'selected' : '' }}>{{ $item->nama_risiko }}</option>
                            @endforeach
                        </select>
                        @error('risk_item_id')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="otherItemContainer" class="mt-3 hidden p-3 bg-red-50 border border-red-200 rounded">
                        <label class="block text-sm font-medium text-red-700">Sebutkan Potensi Risiko Tersebut <span class="text-red-500">*</span></label>
                        <input type="text" name="other_item_description" id="otherItemInput" value="{{ old('other_item_description') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('other_item_description') border-red-500 bg-red-50 @enderror" placeholder="Ketik jenis risiko di sini...">
                        @error('other_item_description')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- SUMBER RISIKO (khusus Lainnya) -->
                    <div id="sumberRisikoContainer" class="mt-3 hidden p-3 bg-purple-50 border border-purple-200 rounded-lg">
                        <label class="block text-sm font-medium text-purple-700">Sumber Risiko <span class="text-red-500">*</span></label>
                        <select name="sumber_risiko" id="sumberRisikoSelect" class="mt-1 block w-full rounded-md border-purple-300 shadow-sm @error('sumber_risiko') border-red-500 bg-red-50 @enderror">
                            <option value="">-- Pilih Sumber Risiko --</option>
                            <option value="manusia" {{ old('sumber_risiko') == 'manusia' ? 'selected' : '' }}>Manusia (Human Error)</option>
                            <option value="sistem_teknologi" {{ old('sumber_risiko') == 'sistem_teknologi' ? 'selected' : '' }}>Sistem & Teknologi</option>
                            <option value="proses_internal" {{ old('sumber_risiko') == 'proses_internal' ? 'selected' : '' }}>Proses Internal</option>
                            <option value="faktor_eksternal" {{ old('sumber_risiko') == 'faktor_eksternal' ? 'selected' : '' }}>Faktor Eksternal</option>
                        </select>
                        @error('sumber_risiko')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="causeContainer" class="mb-4 hidden">
                        <label class="block text-sm font-medium text-gray-700">Apa penyebabnya? <span class="text-red-500">*</span></label>
                        <select id="riskCauseSelect" name="risk_cause_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('risk_cause_id') border-red-500 bg-red-50 @enderror">
                            <option value="">-- Pilih Penyebab --</option>
                        </select>
                        @error('risk_cause_id')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="otherCauseContainer" class="mt-3 mb-4 hidden p-3 bg-yellow-50 border border-yellow-200 rounded">
                        <label class="block text-sm font-medium text-yellow-700">Sebutkan Penyebab Risiko Tersebut <span class="text-red-500">*</span></label>
                        <input type="text" name="other_cause_description" id="otherCauseInput" value="{{ old('other_cause_description') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('other_cause_description') border-red-500 bg-red-50 @enderror" placeholder="Ketik penyebab detail di sini...">
                        @error('other_cause_description')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- KRONOLOGIS KEJADIAN -->
                    <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <label class="block text-sm font-bold text-gray-800 mb-1">
                            📝 Kronologis Kejadian <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Jelaskan secara detail bagaimana kejadian tersebut bisa terjadi (minimal 20 kata).</p>
                        <textarea name="kronologis_kejadian" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kronologis_kejadian') border-red-500 bg-red-50 @enderror" placeholder="Contoh: Nasabah datang ke teller untuk melakukan setoran tunai sebesar Rp 5.000.000, namun teller salah input nominal sehingga terjadi selisih kas...">{{ old('kronologis_kejadian') }}</textarea>
                        @error('kronologis_kejadian')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="mitigationContainer" class="mt-4 mb-2 hidden p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-md">
                        <p class="text-xs font-bold text-blue-800 uppercase tracking-wide">Mitigasi Standar (SOP)</p>
                        <p id="mitigationText" class="text-sm text-blue-700 mt-1 font-medium"></p>
                    </div>

                    <!-- DURASI PENYELESAIAN (khusus sumber risiko = sistem_teknologi atau proses_internal) -->
                    <div id="durasiContainer" class="mb-4 hidden p-4 bg-orange-50 border border-orange-200 rounded-lg">
                        <label class="block text-sm font-bold text-orange-800 mb-1">
                            ⏱ Durasi Penyelesaian (SLA)
                        </label>
                        <p class="text-xs text-orange-600 mb-2">Berapa lama waktu yang dibutuhkan untuk menyelesaikan masalah ini?</p>
                        <div class="flex gap-2 items-center">
                            <input type="number" name="durasi_penyelesaian" id="durasiInput" value="{{ old('durasi_penyelesaian') }}" min="1" class="mt-1 block w-full rounded-md border-orange-300 shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('durasi_penyelesaian') border-red-500 bg-red-50 @enderror" placeholder="Contoh: 2">
                            <select name="durasi_satuan" id="durasiSatuan" class="mt-1 rounded-md border-orange-300 shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                                <option value="menit" {{ old('durasi_satuan') == 'menit' ? 'selected' : '' }}>Menit</option>
                                <option value="jam" {{ old('durasi_satuan') == 'jam' ? 'selected' : '' }}>Jam</option>
                                <option value="hari" {{ old('durasi_satuan') == 'hari' ? 'selected' : '' }}>Hari</option>
                                <option value="minggu" {{ old('durasi_satuan') == 'minggu' ? 'selected' : '' }}>Minggu</option>
                                <option value="bulan" {{ old('durasi_satuan') == 'bulan' ? 'selected' : '' }}>Bulan</option>
                            </select>
                        </div>
                        @error('durasi_penyelesaian')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Apakah ada mitigasi tambahan yang lain?</label>
                        <textarea name="mitigasi_tambahan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('mitigasi_tambahan') border-red-500 bg-red-50 @enderror" rows="2" placeholder="Ketik mitigasi tambahan di sini...">{{ old('mitigasi_tambahan') }}</textarea>
                        @error('mitigasi_tambahan')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($kategori === 'finansial')
                    <div class="mt-8 border-b pb-2 mb-4">
                        <h3 class="text-lg font-bold text-blue-700">3. Dampak Finansial</h3>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Seberapa besar kerugian (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="dampak_finansial" value="{{ old('dampak_finansial') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('dampak_finansial') border-red-500 bg-red-50 @enderror" placeholder="Contoh: 150000">
                        @error('dampak_finansial')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @else
                    <div class="mt-8 border-b pb-2 mb-4">
                        <h3 class="text-lg font-bold text-blue-700">3. Analisa Dampak Non-Finansial</h3>
                    </div>

                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <label class="block text-sm font-bold text-gray-800 mb-2">Seberapa besar dampak kerugian yang timbul? <span class="text-red-500">*</span></label>
                        <div class="space-y-3">
                            <label class="flex items-center p-3 border border-gray-300 rounded hover:bg-white cursor-pointer transition @error('skala_dampak') border-red-500 bg-red-50 @enderror">
                                <input type="radio" name="skala_dampak" value="Sangat Tinggi" required class="h-4 w-4 text-orange-600 focus:ring-orange-500" {{ old('skala_dampak') == 'Sangat Tinggi' ? 'checked' : '' }}>
                                <x-skala-badge skala="Sangat Tinggi" class="ml-3 text-xs" />
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded hover:bg-white cursor-pointer transition">
                                <input type="radio" name="skala_dampak" value="Tinggi" class="h-4 w-4 text-orange-600 focus:ring-orange-500" {{ old('skala_dampak') == 'Tinggi' ? 'checked' : '' }}>
                                <x-skala-badge skala="Tinggi" class="ml-3 text-xs" />
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded hover:bg-white cursor-pointer transition">
                                <input type="radio" name="skala_dampak" value="Sedang" class="h-4 w-4 text-orange-600 focus:ring-orange-500" {{ old('skala_dampak') == 'Sedang' ? 'checked' : '' }}>
                                <x-skala-badge skala="Sedang" class="ml-3 text-xs" />
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded hover:bg-white cursor-pointer transition">
                                <input type="radio" name="skala_dampak" value="Rendah" class="h-4 w-4 text-orange-600 focus:ring-orange-500" {{ old('skala_dampak') == 'Rendah' ? 'checked' : '' }}>
                                <x-skala-badge skala="Rendah" class="ml-3 text-xs" />
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded hover:bg-white cursor-pointer transition">
                                <input type="radio" name="skala_dampak" value="Sangat Rendah" class="h-4 w-4 text-orange-600 focus:ring-orange-500" {{ old('skala_dampak') == 'Sangat Rendah' ? 'checked' : '' }}>
                                <x-skala-badge skala="Sangat Rendah" class="ml-3 text-xs" />
                            </label>
                        </div>
                        @error('skala_dampak')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-800">Penjelasan Dampaknya: <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-2">Berikan penjelasan mengenai dampak riil dari potensi risiko tersebut.</p>
                        <textarea name="dampak_non_finansial" rows="4" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('dampak_non_finansial') border-red-500 bg-red-50 @enderror" placeholder="Ketik kronologi atau rincian dampak di sini...">{{ old('dampak_non_finansial') }}</textarea>
                        @error('dampak_non_finansial')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    <div class="mt-8 border-b pb-2 mb-4">
                        <h3 class="text-lg font-bold text-blue-700">4. Penanganan yang telah dilakukan</h3>
                    </div>

                    <div class="mb-6">
                        <p class="text-xs text-gray-500 mb-1">Jika masalah sudah langsung ditangani saat kejadian, ceritakan di sini.</p>
                        <textarea name="tindakan_awal" rows="2" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tindakan_awal') border-red-500 bg-red-50 @enderror" placeholder="Contoh: Selisih kas sudah langsung diganti sore itu juga / Nasabah sudah ditelepon untuk TT ulang...">{{ old('tindakan_awal') }}</textarea>
                        @error('tindakan_awal')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                            Submit Laporan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <style>
        /* Cursor not-allowed untuk input date yang disabled / readonly */
        input[type="date"]:disabled,
        input[type="date"][readonly],
        input[type="date"]:disabled::-webkit-calendar-picker-indicator,
        input[type="date"][readonly]::-webkit-calendar-picker-indicator {
            cursor: not-allowed !important;
        }
        /* Sembunyikan icon kalender bawaan browser agar user ga bisa klik tanggal masa depan */
        input[type="date"]:disabled::-webkit-calendar-picker-indicator {
            opacity: 0.3;
            pointer-events: none;
        }
    </style>

    <script>
        // 1. Ambil data master dari backend, ubah jadi format JSON biar bisa dibaca Javascript
        const riskData = @json($riskItems); 

        const itemSelect = document.getElementById('riskItemSelect');
        const causeSelect = document.getElementById('riskCauseSelect');
        const causeContainer = document.getElementById('causeContainer');
        const otherContainer = document.getElementById('otherCauseContainer');
        const otherInput = document.getElementById('otherCauseInput');
        const mitigationContainer = document.getElementById('mitigationContainer');
        const mitigationText = document.getElementById('mitigationText');
        const durasiContainer = document.getElementById('durasiContainer');
        const durasiInput = document.getElementById('durasiInput');
        const sumberRisikoContainer = document.getElementById('sumberRisikoContainer');
        const sumberRisikoSelect = document.getElementById('sumberRisikoSelect');

        // Fungsi untuk cek apakah sumber risiko = sistem_teknologi atau proses_internal
        function cekSumberTeknologi(itemId) {
            if (!itemId) return false;
            const selectedItem = riskData.find(item => item.id == itemId);
            return selectedItem && (selectedItem.sumber_risiko === 'sistem_teknologi' || selectedItem.sumber_risiko === 'proses_internal');
        }

        // Fungsi untuk toggle durasi container
        function toggleDurasi(show) {
            if (show) {
                durasiContainer.classList.remove('hidden');
                durasiInput.setAttribute('required', 'required');
            } else {
                durasiContainer.classList.add('hidden');
                durasiInput.removeAttribute('required');
                durasiInput.value = '';
            }
        }

        // Logika saat Potensi Risiko dipilih
        itemSelect.addEventListener('change', function() {
            const selectedItemId = this.value;
            const selectedText = this.options[this.selectedIndex].text.toLowerCase();

            // Ambil elemen
            const otherItemContainer = document.getElementById('otherItemContainer');
            const otherItemInput = document.getElementById('otherItemInput');
            const otherCauseContainer = document.getElementById('otherCauseContainer');
            const otherCauseInput = document.getElementById('otherCauseInput');

            // Reset dulu
            causeSelect.innerHTML = '<option value="">-- Pilih Penyebab --</option>';
            mitigationContainer.classList.add('hidden');
            otherItemContainer.classList.add('hidden');
            otherCauseContainer.classList.add('hidden');
            toggleDurasi(false);

            // Logika "Lainnya"
            if (selectedText.includes('lainnya') || selectedText.includes('other')) {
                // 1. Munculin Teks Nama Risiko
                otherItemContainer.classList.remove('hidden');
                otherItemInput.setAttribute('required', 'required');

                // 2. OTOMATIS Munculin Teks Penyebab (karena dropdown penyebab pasti kosong)
                otherCauseContainer.classList.remove('hidden');
                otherCauseInput.setAttribute('required', 'required');

                causeContainer.classList.add('hidden'); // Sembunyiin dropdown penyebab

                // 3. Munculin dropdown sumber risiko
                sumberRisikoContainer.classList.remove('hidden');
                sumberRisikoSelect.setAttribute('required', 'required');
            } else {
                otherItemInput.removeAttribute('required');
                otherItemInput.value = '';
                otherCauseInput.removeAttribute('required');
                otherCauseInput.value = '';

                // Sembunyiin dropdown sumber risiko
                sumberRisikoContainer.classList.add('hidden');
                sumberRisikoSelect.removeAttribute('required');
                sumberRisikoSelect.value = '';

                // ... (Lanjutin kode narik data cause dari riskData kayak biasa di sini) ...
                if (selectedItemId) {
                    const selectedItem = riskData.find(item => item.id == selectedItemId);
                    if (selectedItem && selectedItem.causes && selectedItem.causes.length > 0) {
                        causeContainer.classList.remove('hidden');
                        selectedItem.causes.forEach(cause => {
                            let option = new Option(cause.penyebab, cause.id);
                            // Simpan sumber_risiko dari cause
                            option.setAttribute('data-sumber-risiko', cause.sumber_risiko || '');
                            // Kalau ada mitigasi, selipin di dataset
                            if (cause.mitigations && cause.mitigations.length > 0) {
                                option.setAttribute('data-mitigasi', cause.mitigations[0].mitigasi);
                            }
                            causeSelect.add(option);
                        });
                        // Tambahin opsi Other di paling bawah dropdown
                        causeSelect.add(new Option('Lainnya / Other', 'other'));
                    } else {
                        causeContainer.classList.add('hidden');
                    }

                    // Cek sumber risiko dari item
                    if (cekSumberTeknologi(selectedItemId)) {
                        toggleDurasi(true);
                    }
                }
            }

            // Restore old value for cause select if exists
            const oldCause = '{{ old('risk_cause_id') }}';
            if (oldCause) {
                causeSelect.value = oldCause;
                // Trigger change event to show mitigation
                causeSelect.dispatchEvent(new Event('change'));
            }
        });

        // Trigger validasi Sumber Risiko manual saat item Lainnya
        sumberRisikoSelect.addEventListener('change', function() {
            if (this.value === 'sistem_teknologi' || this.value === 'proses_internal') {
                toggleDurasi(true);
            } else {
                toggleDurasi(false);
            }
        });

        // Trigger saat milih dropdown penyebab
        causeSelect.addEventListener('change', function() {
            // Guard: kalo dropdown kosong atau ga ada option yang dipilih, skip
            if (!this.options.length || !this.options[this.selectedIndex]) return;

            const otherCauseContainer = document.getElementById('otherCauseContainer');
            const otherCauseInput = document.getElementById('otherCauseInput');

            const mitigationContainer = document.getElementById('mitigationContainer');
            const mitigationText = document.getElementById('mitigationText');

            // Logika Penyebab "Lainnya" (Skenario kemarin)
            if (this.value === 'other') {
                otherCauseContainer.classList.remove('hidden');
                otherCauseInput.setAttribute('required', 'required');
            } else {
                otherCauseContainer.classList.add('hidden');
                otherCauseInput.removeAttribute('required');
                otherCauseInput.value = '';
            }

            // Logika Munculin Mitigasi Otomatis
            const selectedOption = this.options[this.selectedIndex];
            const mitigasi = selectedOption.getAttribute('data-mitigasi');

            if (mitigasi && this.value !== 'other' && this.value !== '') {
                mitigationContainer.classList.remove('hidden');
                mitigationText.textContent = mitigasi;
            } else {
                // Sembunyiin kalau kosong atau pilih "Lainnya"
                mitigationContainer.classList.add('hidden');
                mitigationText.textContent = '';
            }

            // Logika Durasi: cek sumber risiko dari cause yang dipilih
            const sumberRisiko = selectedOption.getAttribute('data-sumber-risiko');
            if ((sumberRisiko === 'sistem_teknologi' || sumberRisiko === 'proses_internal') && this.value !== 'other' && this.value !== '') {
                toggleDurasi(true);
            } else if (this.value === 'other') {
                // Kalo pilih "Lainnya", jangan sembarangan munculin durasi!
                // Cek dulu apakah Item-nya adalah sistem teknologi
                const selectedItemId = itemSelect.value;
                if (cekSumberTeknologi(selectedItemId)) {
                    toggleDurasi(true);
                } else {
                    toggleDurasi(false);
                }
            } else {
                // Fallback: cek dari item yang dipilih
                const selectedItemId = itemSelect.value;
                if (!cekSumberTeknologi(selectedItemId)) {
                    toggleDurasi(false);
                }
            }
        });

        // =============================================================
        // VALIDASI TANGGAL: tanggal_diketahui >= tanggal_kejadian
        // =============================================================
        const tanggalKejadian = document.getElementById('tanggalKejadian');
        const tanggalDiketahui = document.getElementById('tanggalDiketahui');

        function updateMinTanggalDiketahui() {
            if (tanggalKejadian.value) {
                tanggalDiketahui.setAttribute('min', tanggalKejadian.value);
                // Kalau tanggal_diketahui lebih kecil dari tanggal_kejadian, reset
                if (tanggalDiketahui.value && tanggalDiketahui.value < tanggalKejadian.value) {
                    tanggalDiketahui.value = tanggalKejadian.value;
                }
            } else {
                tanggalDiketahui.removeAttribute('min');
            }
        }

        tanggalKejadian.addEventListener('change', updateMinTanggalDiketahui);
        tanggalKejadian.addEventListener('input', updateMinTanggalDiketahui);

        // =============================================================
        // AUTO-SCROLL KE ERROR PERTAMA SAAT PAGE LOAD
        // =============================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Cari elemen error pertama
            const firstError = document.querySelector('.border-red-500');
            if (firstError) {
                // Scroll smooth ke elemen error
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Kasih highlight effect
                firstError.classList.add('ring-2', 'ring-red-300');
                setTimeout(() => {
                    firstError.classList.remove('ring-2', 'ring-red-300');
                }, 3000);
            }

            // Restore old values for dynamic fields
            const oldItem = '{{ old('risk_item_id') }}';
            if (oldItem) {
                itemSelect.value = oldItem;
                itemSelect.dispatchEvent(new Event('change'));
            }

            // Trigger validasi tanggal saat load (untuk old values)
            updateMinTanggalDiketahui();
        });

    </script>
</x-app-layout>
