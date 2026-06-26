<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-bold text-xl text-slate-900 leading-tight tracking-tight">
                {{ __('Manajemen Master Data Cabang') }}
            </h2>
            <p class="text-sm text-slate-500">Atur daftar cabang dan penanggung jawab korwil dengan spacing yang lebih rapi dan mudah dipindai.</p>
        </div>
    </x-slot>

    <div class="pt-4 pb-8 sm:pb-12">
        <div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto page-stack">

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
                    <hr class="my-6 border-slate-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-bold text-gray-700 uppercase">Daftar Cabang</h3>
                        <form action="{{ route('branches.index') }}" method="GET" class="flex gap-2">
                            <select name="korwil_id" class="text-sm rounded border-gray-300 focus:ring-blue-500 py-1.5" onchange="this.form.submit()">
                                <option value="">Semua Korwil</option>
                                <option value="none" {{ request('korwil_id') == 'none' ? 'selected' : '' }}>Tanpa Korwil</option>
                                @foreach($listKorwil as $k)
                                    <option value="{{ $k->id }}" {{ request('korwil_id') == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="sort" value="{{ request('sort', 'kode_cabang') }}">
                            <input type="hidden" name="dir" value="{{ request('dir', 'asc') }}">
                        </form>
                    </div>

                    <x-admin-table :headers="['Kode Cabang', 'Nama Cabang', 'Status', 'Korwil Penanggung Jawab', 'Aksi Update']">
                        @foreach($branches as $branch)
                        <tr class="{{ !$branch->is_active ? 'bg-slate-50 opacity-75' : '' }} hover:bg-slate-50 transition duration-150 group/row">
                            <td class="px-6 py-4 font-bold text-sm text-slate-900 whitespace-nowrap">{{ $branch->kode_cabang }}</td>
                            <td class="px-6 py-4 font-bold text-sm text-slate-900 whitespace-nowrap">{{ $branch->nama_cabang }}</td>
                            
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <x-badge :type="$branch->is_active ? 'success' : 'danger'" class="uppercase tracking-widest">
                                    {{ $branch->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </x-badge>
                            </td>

                             <td class="px-6 py-4 whitespace-nowrap">
                                 <select name="korwil_id" form="branchForm_{{ $branch->id }}" class="min-w-[200px] text-xs font-semibold uppercase rounded border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Tanpa Korwil --</option>
                                    @foreach($listKorwil as $k)
                                    <option value="{{ $k->id }}" {{ $branch->korwil_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->name }}
                                    </option>
                                    @endforeach
                                </select>
                             </td>
                             
                             <td class="px-6 py-4 whitespace-nowrap text-right">
                                 <form id="branchForm_{{ $branch->id }}" action="{{ route('branches.update', $branch->id) }}" method="POST" class="inline-flex items-center gap-3 transition duration-200">
                                     @csrf
                                     @method('PUT')

                                     <input type="hidden" name="kode_cabang" value="{{ $branch->kode_cabang }}">
                                     <input type="hidden" name="nickname_cabang" value="{{ $branch->nickname_cabang }}">
                                     <input type="hidden" name="is_active" id="status_{{ $branch->id }}" value="{{ $branch->is_active }}">

                                    @if($branch->is_active)
                                    <button type="button" onclick="openBranchToggle({{ $branch->id }}, '{{ $branch->nama_cabang }}', '{{ $branch->kode_cabang }}', true)" class="inline-flex items-center gap-1.5 text-rose-500 hover:text-rose-700 text-xs font-bold uppercase tracking-widest transition">
                                        Non-Aktifkan
                                    </button>
                                    @else
                                    <button type="button" onclick="openBranchToggle({{ $branch->id }}, '{{ $branch->nama_cabang }}', '{{ $branch->kode_cabang }}', false)" class="inline-flex items-center gap-1.5 text-emerald-500 hover:text-emerald-700 text-xs font-bold uppercase tracking-widest transition">
                                        Aktifkan
                                    </button>
                                    @endif

                                    <button type="submit" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-xs font-bold uppercase tracking-widest transition">
                                        Simpan
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </x-admin-table>

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
