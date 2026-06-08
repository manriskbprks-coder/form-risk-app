<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
            {{ __('Control Center: Manajemen Master Data Risiko') }}
            </h2>
            <p class="text-sm text-slate-500">Kelola bank soal risiko dengan tata letak yang lebih rapi, lega, dan cepat dipindai.</p>
        </div>
    </x-slot>

    <div class="pt-4 pb-8 sm:pb-12" x-data="{ 
        filterRole: 'semua',
        filterKategori: 'semua',
        filterSumber: 'semua',
        activeModal: null,
        editCauseModal: false,
        editData: { id: '', penyebab: '', sumber_risiko: '', mitigasi: '' },
        openEdit(id, penyebab, sumber_risiko, mitigasi) {
            this.editData = { id, penyebab, sumber_risiko, mitigasi };
            this.editCauseModal = true;
        }
    }">
        <div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto page-stack">

            <div class="surface-card section-pad border-t-4 border-blue-600">
                <div class="mb-6 space-y-1">
                    <h3 class="section-title">Tambah Pertanyaan Risiko Baru</h3>
                    <p class="section-subtitle">Isi data inti secara singkat. Form sengaja dibuat lapang agar tetap nyaman dipakai di desktop maupun smartphone.</p>
                </div>
                <form action="{{ route('admin.risk_master.store_item') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Jabatan (Role)</label>
                            <select name="role_target" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm uppercase">
                                <option value="teller">Teller</option>
                                <option value="ca">Customer Assistant (CA)</option>
                                <option value="csr">CSR</option>
                                <option value="security">Security</option>
                                <option value="kacab">Kepala Cabang (Kacab)</option>
                                <option value="korwil">Korwil</option>
                            </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nama Risiko (Pertanyaan)</label>
                        <input type="text" name="nama_risiko" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Contoh: Selisih kurang pada perhitungan kas">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kategori Risiko</label>
                        <select name="kategori" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm uppercase">
                            <option value="finansial">Finansial (Loss Event)</option>
                            <option value="non-finansial">Non-Finansial (Risk Event)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sumber Risiko Operasional</label>
                        <select name="sumber_risiko" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm uppercase">
                            <option value="manusia">Manusia</option>
                            <option value="proses_internal">Proses Internal</option>
                            <option value="sistem_teknologi">Sistem Teknologi</option>
                            <option value="faktor_eksternal">Faktor Eksternal</option>
                        </select>
                    </div>
                    <div class="md:col-span-3 flex justify-end pt-2">
                        <button type="submit" class="inline-flex w-full sm:w-auto justify-center items-center px-5 py-2.5 bg-blue-600 rounded-xl font-semibold text-xs text-white uppercase tracking-[0.16em] hover:bg-blue-700 transition shadow-sm">
                            Simpan Pertanyaan
                        </button>
                    </div>
                </form>
            </div>

            <div class="surface-card overflow-hidden">
                
                <div class="bg-slate-50/90 border-b border-slate-200 p-4 sm:p-5 flex flex-col gap-4 md:flex-row md:justify-between md:items-center">
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-[0.16em]">Daftar Pertanyaan Risiko</h3>
                        <p class="text-sm text-slate-500">Gunakan filter untuk menyaring kategori dan jabatan, lalu buka detail untuk edit penyebab dan mitigasi.</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row sm:flex-wrap items-start sm:items-center gap-3 sm:gap-4 w-full md:w-auto">
                        
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.16em]">Kategori:</label>
                            <select x-model="filterKategori" class="flex-1 min-w-[160px] text-xs rounded-xl border-slate-300 focus:ring-blue-500 py-2 px-3 font-semibold bg-white">
                                <option value="semua">Semua</option>
                                <option value="finansial">Finansial</option>
                                <option value="non-finansial">Non-Finansial</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.16em]">Sumber:</label>
                            <select x-model="filterSumber" class="flex-1 min-w-[160px] text-xs rounded-xl border-slate-300 focus:ring-blue-500 py-2 px-3 font-semibold bg-white">
                                <option value="semua">Semua</option>
                                <option value="manusia">Manusia</option>
                                <option value="proses_internal">Proses Internal</option>
                                <option value="sistem_teknologi">Sistem Teknologi</option>
                                <option value="faktor_eksternal">Faktor Eksternal</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.16em]">Jabatan:</label>
                            <select x-model="filterRole" class="flex-1 min-w-[160px] text-xs rounded-xl border-slate-300 focus:ring-blue-500 py-2 px-3 font-semibold bg-white">
                                <option value="semua">Semua</option>
                                @foreach($riskItems->pluck('role_target')->unique() as $role)
                                    <option value="{{ strtolower($role) }}">{{ strtoupper($role) }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($riskItems as $item)
                        <div x-show="(filterRole === 'semua' || filterRole === '{{ strtolower($item->role_target) }}') && (filterKategori === 'semua' || filterKategori === '{{ strtolower($item->kategori) }}') && (filterSumber === 'semua' || filterSumber === '{{ strtolower($item->sumber_risiko) }}')" 
                             x-transition
                             class="p-4 sm:p-5 hover:bg-slate-50 cursor-pointer transition flex items-start justify-between gap-4 group"
                             @click="activeModal = '{{ $item->id }}'">
                            
                            <div class="min-w-0 flex-1 pr-2">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-1 inline-flex text-[10px] leading-none font-bold rounded-full bg-blue-100 text-blue-700 uppercase tracking-[0.16em]">
                                        {{ $item->role_target }}
                                    </span>
                                    <span class="px-2.5 py-1 inline-flex text-[10px] leading-none font-bold rounded-full {{ strtolower($item->kategori) === 'finansial' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }} uppercase tracking-[0.16em]">
                                        {{ $item->kategori }}
                                    </span>
                                    <span class="px-2.5 py-1 inline-flex text-[10px] leading-none font-bold rounded-full 
                                        {{ $item->sumber_risiko === 'manusia' ? 'bg-purple-100 text-purple-700' : '' }}
                                        {{ $item->sumber_risiko === 'proses_internal' ? 'bg-cyan-100 text-cyan-700' : '' }}
                                        {{ $item->sumber_risiko === 'sistem_teknologi' ? 'bg-orange-100 text-orange-700' : '' }}
                                        {{ $item->sumber_risiko === 'faktor_eksternal' ? 'bg-teal-100 text-teal-700' : '' }}
                                    uppercase tracking-[0.16em]">
                                        {{ str_replace('_', ' ', $item->sumber_risiko) }}
                                    </span>
                                </div>
                                <h4 class="text-sm sm:text-base font-semibold text-slate-900 group-hover:text-blue-700 transition leading-snug break-words">{{ $item->nama_risiko }}</h4>
                                <p class="text-xs text-slate-500 mt-2">{{ $item->causes->count() }} Penyebab terdaftar</p>
                            </div>

                            <div class="flex shrink-0 items-start gap-3 ml-auto">
                                <button type="button" class="hidden md:inline-flex items-center justify-center min-w-[84px] text-blue-600 hover:text-white hover:bg-blue-500 border border-blue-300 px-3.5 py-2 rounded-xl text-[11px] font-bold uppercase tracking-[0.14em] transition bg-white">
                                    Lihat Detail
                                </button>
                                <form id="deleteItemForm_{{ $item->id }}" action="{{ route('admin.risk_master.destroy_item', $item->id) }}" method="POST" class="relative z-10" @click.stop>
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="openDeleteItem('{{ $item->id }}', '{{ addslashes($item->nama_risiko) }}')" class="inline-flex items-center justify-center min-w-[84px] text-rose-600 hover:text-white hover:bg-rose-500 border border-rose-300 px-3.5 py-2 rounded-xl text-[11px] font-bold uppercase tracking-[0.14em] transition bg-white">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div x-show="activeModal === '{{ $item->id }}'" style="display: none;" class="fixed inset-0 z-40 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                
                                <div x-show="activeModal === '{{ $item->id }}'" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="activeModal = null" aria-hidden="true"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                <div x-show="activeModal === '{{ $item->id }}'" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full max-w-full">
                                    
                                    <div class="bg-slate-50 px-4 py-4 border-b border-slate-200 flex items-start justify-between gap-3 sticky top-0 z-10">
                                        <div>
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.16em]">{{ $item->role_target }}</span>
                                            <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">{{ $item->nama_risiko }}</h3>
                                        </div>
                                        <button @click="activeModal = null" class="text-gray-400 hover:text-red-500 font-bold text-2xl focus:outline-none">&times;</button>
                                    </div>

                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-4 border-b pb-2">Daftar Penyebab & Mitigasi (Sebab-Akibat)</h4>
                                        
                                        <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-1 sm:pr-2">
                                            @forelse($item->causes as $cause)
                                                @php $mitigasiTeks = $cause->mitigations->first()->mitigasi ?? ''; @endphp
                                                
                                                <div class="flex flex-col md:flex-row gap-0 md:gap-4 border border-gray-200 rounded-lg overflow-hidden group hover:border-blue-300 transition relative">
                                                    
                                                    <button type="button" @click="openEdit('{{ $cause->id }}', '{{ addslashes($cause->penyebab) }}', '{{ $cause->sumber_risiko }}', '{{ addslashes($mitigasiTeks) }}')" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 bg-yellow-100 text-yellow-700 hover:bg-yellow-200 px-2 py-1 rounded text-[10px] font-bold uppercase transition z-10 border border-yellow-300">
                                                        Edit
                                                    </button>

                                                    <div class="w-full md:w-1/2 bg-gray-50 p-3 border-b md:border-b-0 md:border-r border-gray-200">
                                                        <p class="text-[10px] font-bold text-red-500 uppercase mb-1">Akar Penyebab</p>
                                                        <p class="text-sm font-semibold text-gray-800 italic pr-8">"{{ $cause->penyebab }}"</p>
                                                        <span class="mt-2 inline-block px-2 py-0.5 text-[10px] leading-none font-bold rounded-full 
                                                            {{ $cause->sumber_risiko === 'manusia' ? 'bg-purple-100 text-purple-700' : '' }}
                                                            {{ $cause->sumber_risiko === 'proses_internal' ? 'bg-cyan-100 text-cyan-700' : '' }}
                                                            {{ $cause->sumber_risiko === 'sistem_teknologi' ? 'bg-orange-100 text-orange-700' : '' }}
                                                            {{ $cause->sumber_risiko === 'faktor_eksternal' ? 'bg-teal-100 text-teal-700' : '' }}
                                                        uppercase tracking-[0.16em]">
                                                            {{ str_replace('_', ' ', $cause->sumber_risiko) }}
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="w-full md:w-1/2 p-3 bg-white">
                                                        <p class="text-[10px] font-bold text-green-600 uppercase mb-1">Rekomendasi Mitigasi</p>
                                                        @if($mitigasiTeks)
                                                            <p class="text-sm text-gray-600 pr-8">{{ $mitigasiTeks }}</p>
                                                        @else
                                                            <p class="text-sm text-gray-400 italic pr-8">- Belum ada mitigasi -</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-6 bg-gray-50 rounded border border-dashed border-gray-300">
                                                    <p class="text-sm text-gray-500 italic">Belum ada penyebab yang ditambahkan untuk risiko ini.</p>
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="mt-6 bg-blue-50 border border-blue-100 rounded-lg p-4">
                                            <h4 class="text-xs font-bold text-blue-800 uppercase mb-2">Tambah Akar Penyebab Baru</h4>
                                            <form action="{{ route('admin.risk_master.store_cause', $item->id) }}" method="POST" class="flex flex-col md:flex-row gap-2">
                                                @csrf
                                                <input type="text" name="penyebab" required placeholder="Teks Penyebab..." class="text-sm flex-1 border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                                <select name="sumber_risiko" required class="text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 uppercase">
                                                    <option value="manusia">Manusia</option>
                                                    <option value="proses_internal">Proses Internal</option>
                                                    <option value="sistem_teknologi">Sistem Teknologi</option>
                                                    <option value="faktor_eksternal">Faktor Eksternal</option>
                                                </select>
                                                <input type="text" name="mitigasi" placeholder="Teks Mitigasi (Opsional)..." class="text-sm flex-1 border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                                <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-800 text-white font-bold px-4 py-2 rounded text-xs transition shadow-sm uppercase">Simpan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-gray-500 italic bg-gray-50">
                            Belum ada data pertanyaan. Silakan tambah lewat form di atas.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <div x-show="editCauseModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editCauseModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="editCauseModal = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editCauseModal" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border-t-4 border-yellow-500">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-lg font-bold text-gray-900 uppercase">Edit Penyebab & Mitigasi</h3>
                            <button @click="editCauseModal = false" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                        </div>
                        
                        <form :action="`/admin/risk-master/cause/${editData.id}`" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Teks Penyebab <span class="text-red-500">*</span></label>
                                <input type="text" name="penyebab" x-model="editData.penyebab" required class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-yellow-500 focus:border-yellow-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Sumber Risiko <span class="text-red-500">*</span></label>
                                <select name="sumber_risiko" x-model="editData.sumber_risiko" required class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-yellow-500 focus:border-yellow-500 uppercase">
                                    <option value="manusia">Manusia</option>
                                    <option value="proses_internal">Proses Internal</option>
                                    <option value="sistem_teknologi">Sistem Teknologi</option>
                                    <option value="faktor_eksternal">Faktor Eksternal</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Teks Mitigasi <span class="text-gray-400 font-normal">(Kosongkan jika ingin dihapus)</span></label>
                                <input type="text" name="mitigasi" x-model="editData.mitigasi" class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-yellow-500 focus:border-yellow-500">
                            </div>
                            
                            <div class="mt-6 flex justify-end gap-2 pt-4 border-t border-gray-100">
                                <button type="button" @click="editCauseModal = false" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded text-sm transition">Batal</button>
                                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded text-sm shadow transition">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ================================================================ -->
    <!-- MODAL HAPUS PERTANYAAN RISIKO (KONFIRMASI 1-STEP)                -->
    <!-- ================================================================ -->
    <div id="modalDeleteItem" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
        <div id="deleteItemContainer" class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-md shadow-lg rounded-md bg-white border-t-4 border-t-red-500">
            <div class="flex justify-between items-center mb-4">
                <h3 id="deleteItemTitle" class="text-lg font-bold text-gray-900 uppercase">🗑️ Hapus Pertanyaan Risiko</h3>
                <button onclick="closeDeleteItem()" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
            </div>
            <div class="space-y-4">
                <div class="bg-gray-50 border-l-4 border-gray-400 p-4 rounded">
                    <p class="text-sm text-gray-800 font-semibold">Konfirmasi Aksi</p>
                </div>
                <p class="text-sm text-gray-700">
                    Apakah Anda yakin ingin menghapus pertanyaan risiko berikut:
                </p>
                <div class="bg-gray-50 p-3 rounded border text-center">
                    <p id="deleteItemName" class="font-bold text-gray-900 text-lg"></p>
                </div>

                <!-- Warning dampak -->
                <div id="deleteItemWarningBlock" class="bg-red-50 border border-red-200 p-3 rounded text-xs text-red-800 space-y-1">
                    <p>🔴 <strong>Dampak menghapus pertanyaan:</strong></p>
                    <p>• Semua penyebab & mitigasi terkait akan ikut terhapus</p>
                    <p>• Laporan yang sudah menggunakan pertanyaan ini <strong>tidak terpengaruh</strong></p>
                    <p>• Data tetap tersimpan di histori laporan</p>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button onclick="closeDeleteItem()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-bold">Batal</button>
                    <button id="deleteItemConfirmBtn"
                            onclick="executeDeleteItem()"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-bold shadow">🗑️ Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ================================================================
        // HAPUS PERTANYAAN RISIKO — KONFIRMASI 1-STEP
        // ================================================================
        let deleteItemId = null;

        function openDeleteItem(id, namaRisiko) {
            deleteItemId = id;

            // Isi data pertanyaan di modal
            document.getElementById('deleteItemName').textContent = namaRisiko;

            // Tampilkan modal
            document.getElementById('modalDeleteItem').classList.remove('hidden');
        }

        function closeDeleteItem() {
            document.getElementById('modalDeleteItem').classList.add('hidden');
            deleteItemId = null;
        }

        function executeDeleteItem() {
            if (!deleteItemId) return;

            // Disable button biar ga double-click
            const btn = document.getElementById('deleteItemConfirmBtn');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');

            // Submit form
            document.getElementById('deleteItemForm_' + deleteItemId).submit();
        }

        // Tutup modal jika klik di luar
        document.getElementById('modalDeleteItem').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteItem();
            }
        });
    </script>
</x-app-layout>
