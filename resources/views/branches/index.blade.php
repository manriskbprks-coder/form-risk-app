<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-bold text-xl text-slate-900 leading-tight tracking-tight">
                {{ __('Manajemen Master Data Cabang') }}
            </h2>
            <p class="text-sm text-slate-500">Atur daftar cabang dan penanggung jawab korwil dengan spacing yang lebih rapi dan mudah dipindai.</p>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="page-shell page-stack">

            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif

            <div class="surface-card overflow-hidden">
                <div class="p-4 sm:p-6 bg-white border-b border-slate-200">
                    <h3 class="text-sm font-bold text-blue-800 uppercase mb-4">Pendaftaran Cabang Baru</h3>
                    <form action="{{ route('branches.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Kode Cabang</label>
                            <input type="text" name="kode_cabang" required class="w-full text-sm rounded border-gray-300 focus:ring-blue-500" placeholder="Contoh: 001">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Nama Lengkap Cabang</label>
                            <input type="text" name="nama_cabang" required class="w-full text-sm rounded border-gray-300 focus:ring-blue-500" placeholder="Cabang Sudirman">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Nickname</label>
                            <input type="text" name="nickname_cabang" class="w-full text-sm rounded border-gray-300 focus:ring-blue-500" placeholder="KCP Sudirman">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Pilih Korwil</label>
                            <select name="korwil_id" class="w-full text-sm rounded border-gray-300 focus:ring-blue-500">
                                <option value="">-- Tanpa Korwil --</option>
                                @foreach($listKorwil as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded text-xs transition shadow-sm">
                                Daftarkan Cabang
                            </button>
                        </div>
                    </form>
                    <div class="overflow-x-auto -mx-4 sm:mx-0 mt-6">
                    <table class="min-w-[920px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">Kode Cabang</th>
                                <th class="px-6 py-3 text-left">Nama Cabang</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-left">Korwil Penanggung Jawab</th>
                                <th class="px-6 py-3 text-right">Aksi Update</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($branches as $branch)
                            <tr class="{{ !$branch->is_active ? 'bg-gray-50 opacity-60' : '' }} hover:bg-gray-50">
                                <td class="px-6 py-4 font-bold text-sm text-gray-900">{{ $branch->kode_cabang }}</td>
                                <td class="px-6 py-4 font-bold text-sm text-gray-900">{{ $branch->nama_cabang }}</td>

                                <td class="px-6 py-4 text-center">
                                    @if($branch->is_active)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-[10px] font-bold uppercase rounded border border-green-200">Aktif</span>
                                    @else
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-[10px] font-bold uppercase rounded border border-red-200">Non-Aktif</span>
                                    @endif
                                </td>

                                 <td class="px-6 py-4" colspan="2">
                                     <form id="branchForm_{{ $branch->id }}" action="{{ route('branches.update', $branch->id) }}" method="POST" class="flex items-center justify-between gap-4 w-full">
                                         @csrf
                                         @method('PUT')

                                         <input type="hidden" name="kode_cabang" value="{{ $branch->kode_cabang }}">
                                         <input type="hidden" name="nickname_cabang" value="{{ $branch->nickname_cabang }}">

                                         <select name="korwil_id" class="flex-1 text-sm rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">-- Tanpa Korwil --</option>
                                            @foreach($listKorwil as $k)
                                            <option value="{{ $k->id }}" {{ $branch->korwil_id == $k->id ? 'selected' : '' }}>
                                                {{ $k->name }}
                                            </option>
                                            @endforeach
                                        </select>

                                        <div class="flex gap-2">
                                            <input type="hidden" name="is_active" id="status_{{ $branch->id }}" value="{{ $branch->is_active }}">

                                            @if($branch->is_active)
                                            <button type="button" onclick="openBranchToggle({{ $branch->id }}, '{{ $branch->nama_cabang }}', '{{ $branch->kode_cabang }}', true)" class="inline-flex items-center justify-center min-w-[84px] text-rose-600 hover:text-white hover:bg-rose-500 border border-rose-300 px-3.5 py-2 rounded-xl text-[11px] font-bold uppercase tracking-[0.14em] transition bg-white">
                                                Non-Aktifkan
                                            </button>
                                            @else
                                            <button type="button" onclick="openBranchToggle({{ $branch->id }}, '{{ $branch->nama_cabang }}', '{{ $branch->kode_cabang }}', false)" class="inline-flex items-center justify-center min-w-[84px] text-emerald-600 hover:text-white hover:bg-emerald-500 border border-emerald-300 px-3.5 py-2 rounded-xl text-[11px] font-bold uppercase tracking-[0.14em] transition bg-white">
                                                Aktifkan
                                            </button>
                                            @endif

                                            <button type="submit" class="inline-flex items-center justify-center min-w-[84px] text-blue-600 hover:text-white hover:bg-blue-500 border border-blue-300 px-3.5 py-2 rounded-xl text-[11px] font-bold uppercase tracking-[0.14em] transition bg-white">
                                                Simpan
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- MODAL TOGGLE CABANG (KONFIRMASI NON-AKTIFKAN / AKTIFKAN)         -->
    <!-- ================================================================ -->
    <div id="modalBranchToggle" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
        <div id="branchToggleContainer" class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-md shadow-lg rounded-md bg-white border-t-4 border-t-red-500">
            <div class="flex justify-between items-center mb-4">
                <h3 id="branchToggleTitle" class="text-lg font-bold text-gray-900 uppercase">⚠️ Non-Aktifkan Cabang</h3>
                <button onclick="closeBranchToggle()" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
            </div>
            <div class="space-y-4">
                <div class="bg-gray-50 border-l-4 border-gray-400 p-4 rounded">
                    <p class="text-sm text-gray-800 font-semibold">Konfirmasi Aksi</p>
                </div>
                <p class="text-sm text-gray-700">
                    Apakah Anda yakin ingin <strong id="branchToggleActionText">menonaktifkan</strong> cabang berikut:
                </p>
                <div class="bg-gray-50 p-3 rounded border text-center">
                    <p id="branchToggleName" class="font-bold text-gray-900 text-lg"></p>
                    <p id="branchToggleCode" class="text-xs text-gray-500 mt-1"></p>
                </div>

                <!-- Warning khusus untuk Non-Aktifkan -->
                <div id="branchToggleWarningBlock" class="bg-red-50 border border-red-200 p-3 rounded text-xs text-red-800 space-y-1">
                    <p>🔴 <strong>Dampak menonaktifkan cabang:</strong></p>
                    <p>• Cabang <strong>TIDAK</strong> akan muncul di pilihan saat pembuatan laporan</p>
                    <p>• User yang ditempatkan di cabang ini tetap bisa login</p>
                    <p>• Data laporan yang sudah dibuat tetap tersimpan</p>
                </div>

                <!-- Info untuk Aktifkan -->
                <div id="branchToggleInfoBlock" class="bg-green-50 border border-green-200 p-3 rounded text-xs text-green-800 space-y-1 hidden">
                    <p>🟢 <strong>Dampak mengaktifkan cabang:</strong></p>
                    <p>• Cabang akan muncul kembali di pilihan saat pembuatan laporan</p>
                    <p>• Semua akses dan fungsi cabang akan kembali normal</p>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button onclick="closeBranchToggle()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-bold">Batal</button>
                    <button id="branchToggleConfirmBtn"
                            onclick="executeBranchToggle()"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-bold shadow">🔴 Non-Aktifkan</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ================================================================
        // TOGGLE CABANG — KONFIRMASI NON-AKTIFKAN / AKTIFKAN
        // ================================================================
        let branchToggleId = null;
        let branchToggleIsActive = false;

        function openBranchToggle(id, name, kodeCabang, isActive) {
            branchToggleId = id;
            branchToggleIsActive = isActive;

            // Isi data cabang di modal
            document.getElementById('branchToggleName').textContent = name;
            document.getElementById('branchToggleCode').textContent = 'Kode: ' + kodeCabang;

            // Set border color
            const container = document.getElementById('branchToggleContainer');
            container.className = container.className.replace(/border-t-(red|green)-500/g, '');
            container.classList.add(isActive ? 'border-t-red-500' : 'border-t-green-500');

            // Set title
            document.getElementById('branchToggleTitle').textContent = isActive ? '⚠️ Non-Aktifkan Cabang' : '✅ Aktifkan Cabang';

            // Set action text (menonaktifkan / mengaktifkan)
            document.getElementById('branchToggleActionText').textContent = isActive ? 'menonaktifkan' : 'mengaktifkan';

            // Toggle warning/info blocks
            document.getElementById('branchToggleWarningBlock').classList.toggle('hidden', !isActive);
            document.getElementById('branchToggleInfoBlock').classList.toggle('hidden', isActive);

            // Set confirm button
            const confirmBtn = document.getElementById('branchToggleConfirmBtn');
            confirmBtn.className = 'px-4 py-2 text-white rounded text-sm font-bold shadow ' +
                (isActive ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700');
            confirmBtn.textContent = isActive ? '🔴 Non-Aktifkan' : '🟢 Aktifkan';

            // Tampilkan modal
            document.getElementById('modalBranchToggle').classList.remove('hidden');
        }

        function closeBranchToggle() {
            document.getElementById('modalBranchToggle').classList.add('hidden');
            branchToggleId = null;
        }

        function executeBranchToggle() {
            if (!branchToggleId) return;

            // Set hidden input value
            document.getElementById('status_' + branchToggleId).value = branchToggleIsActive ? '0' : '1';

            // Disable button biar ga double-click
            const btn = document.getElementById('branchToggleConfirmBtn');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');

            // Submit form
            document.getElementById('branchForm_' + branchToggleId).submit();
        }

        // Tutup modal toggle jika klik di luar
        document.getElementById('modalBranchToggle').addEventListener('click', function(e) {
            if (e.target === this) {
                closeBranchToggle();
            }
        });
    </script>
</x-app-layout>
