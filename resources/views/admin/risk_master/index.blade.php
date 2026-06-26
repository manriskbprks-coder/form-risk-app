<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-bold text-xl text-slate-900 leading-tight tracking-tight uppercase">
                Control Center: Risk Dictionary
            </h2>
            <p class="text-sm text-slate-500">Kelola master data indikator risiko operasional untuk setiap jabatan secara dinamis.</p>
        </div>
    </x-slot>

    <div class="pt-4 pb-8 sm:pb-12" x-data="{ 
        filterRole: 'semua',
        filterKategori: 'semua',
        filterSumber: 'semua',
        createModal: false,
        activeModal: null,
        editingItemId: null,
        editCauseModal: false,
        editData: { id: '', penyebab: '', sumber_risiko: '', mitigasi: '' },
        causes: [{ id: Date.now(), penyebab: '', sumber_risiko: 'proses_internal', mitigasi: '' }],
        
        addCause() {
            this.causes.push({ id: Date.now(), penyebab: '', sumber_risiko: 'proses_internal', mitigasi: '' });
        },
        removeCause(index) {
            if(this.causes.length > 1) {
                this.causes.splice(index, 1);
            }
        },
        openEdit(id, penyebab, sumber_risiko, mitigasi) {
            this.editData = { id, penyebab, sumber_risiko, mitigasi };
            this.editCauseModal = true;
        }
    }">
        <div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto page-stack">

            <!-- FILTER & TOMBOL TAMBAH -->
            <div class="surface-card p-4 sm:p-5 mb-6 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between border-t-4 border-blue-600">
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Jabatan:</label>
                        <select x-model="filterRole" class="flex-1 min-w-[160px] text-xs rounded border-slate-300 focus:ring-blue-500 font-semibold bg-slate-50">
                            <option value="semua">-- Semua Jabatan --</option>
                            @foreach($riskItems->pluck('role_target')->unique() as $role)
                                <option value="{{ strtolower($role) }}">{{ strtoupper($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kategori:</label>
                        <select x-model="filterKategori" class="flex-1 min-w-[160px] text-xs rounded border-slate-300 focus:ring-blue-500 font-semibold bg-slate-50">
                            <option value="semua">-- Semua Kategori --</option>
                            <option value="finansial">Finansial</option>
                            <option value="non-finansial">Non-Finansial</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sumber:</label>
                        <select x-model="filterSumber" class="flex-1 min-w-[160px] text-xs rounded border-slate-300 focus:ring-blue-500 font-semibold bg-slate-50">
                            <option value="semua">-- Semua Sumber --</option>
                            <option value="manusia">Manusia</option>
                            <option value="proses_internal">Proses Internal</option>
                            <option value="sistem_teknologi">Sistem Teknologi</option>
                            <option value="faktor_eksternal">Faktor Eksternal</option>
                        </select>
                    </div>
                </div>
                
                <button @click="createModal = true" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Risiko Baru
                </button>
            </div>

            <!-- TABEL DAFTAR RISIKO -->
            <x-admin-table :headers="['Target Jabatan', 'Grup Kategori', 'Kategori & Sumber', 'Pertanyaan Risiko', 'Akar Masalah', 'Aksi']">
                @forelse($riskItems as $item)
                @php 
                    $roleCategory = \Spatie\Permission\Models\Role::where('name', $item->role_target)->value('role_category') ?? 'default'; 
                @endphp
                <tr x-show="(filterRole === 'semua' || filterRole === '{{ strtolower($item->role_target) }}') && (filterKategori === 'semua' || filterKategori === '{{ strtolower($item->kategori) }}') && (filterSumber === 'semua' || filterSumber === '{{ strtolower($item->sumber_risiko) }}')" 
                    x-transition
                    class="hover:bg-slate-50 transition duration-150 group/row">
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-badge :type="strtolower($roleCategory)" class="uppercase tracking-widest">
                            {{ str_replace('_', ' ', $item->role_target) }}
                        </x-badge>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                            {{ $item->category->nama_kategori ?? 'Umum' }}
                        </span>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col gap-1.5">
                            <x-badge :type="strtolower($item->kategori)" class="uppercase tracking-widest w-fit text-[10px]">
                                {{ $item->kategori }}
                            </x-badge>
                            <x-badge :type="strtolower($item->sumber_risiko)" class="uppercase tracking-widest w-fit text-[10px]">
                                {{ str_replace('_', ' ', $item->sumber_risiko) }}
                            </x-badge>
                        </div>
                    </td>
                    
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-slate-900 leading-snug">{{ $item->nama_risiko }}</p>
                    </td>
                    
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold text-sm border border-slate-200">
                            {{ $item->causes->count() }}
                        </span>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2 transition duration-200">
                        <button @click="activeModal = '{{ $item->id }}'" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-xs font-bold uppercase tracking-widest transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Detail & Edit
                        </button>
                        <form action="{{ route('admin.risk_master.destroy_item', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus seluruh pertanyaan ini beserta semua penyebab dan mitigasinya?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 text-rose-500 hover:text-rose-700 text-xs font-bold uppercase tracking-widest transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>

                            <!-- MODAL DETAIL & EDIT (Untuk masing-masing item) -->
                            <div x-show="activeModal === '{{ $item->id }}'" style="display: none;" class="fixed inset-0 z-40 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="activeModal === '{{ $item->id }}'" x-transition.opacity class="fixed inset-0 bg-slate-900 bg-opacity-75 backdrop-blur-sm transition-opacity" @click="activeModal = null" aria-hidden="true"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    <div x-show="activeModal === '{{ $item->id }}'" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full max-w-full border-t-4 border-blue-500">
                                        
                                        <div class="bg-slate-50 px-6 py-5 border-b border-slate-200 flex flex-col md:flex-row md:items-start justify-between gap-4 sticky top-0 z-10">
                                            <div class="flex-1 w-full">
                                                <!-- View Mode -->
                                                <template x-if="editingItemId !== '{{ $item->id }}'">
                                                    <div>
                                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                                            <span class="px-2.5 py-1 text-[10px] font-bold bg-slate-800 text-white uppercase tracking-widest rounded">{{ $item->role_target }}</span>
                                                            <span class="px-2.5 py-1 text-[10px] font-bold {{ strtolower($item->kategori) === 'finansial' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }} uppercase tracking-widest rounded">{{ $item->kategori }}</span>
                                                            <span class="px-2.5 py-1 text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-widest rounded">{{ $item->category->nama_kategori ?? 'Umum' }}</span>
                                                            <span class="px-2.5 py-1 text-[10px] font-bold bg-slate-200 text-slate-700 uppercase tracking-widest rounded">{{ str_replace('_', ' ', $item->sumber_risiko) }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-start">
                                                            <h3 class="text-xl font-bold text-slate-900" id="modal-title">{{ $item->nama_risiko }}</h3>
                                                            <button type="button" @click="editingItemId = '{{ $item->id }}'" class="text-amber-500 hover:text-amber-600 bg-amber-50 hover:bg-amber-100 px-3 py-1 rounded text-xs font-bold uppercase tracking-widest ml-4 transition shrink-0 border border-amber-200">
                                                                Ubah Detail Risiko
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>

                                                <!-- Edit Mode -->
                                                <template x-if="editingItemId === '{{ $item->id }}'">
                                                    <form action="{{ route('admin.risk_master.update_item', $item->id) }}" method="POST" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                            <div class="md:col-span-2">
                                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Redaksi Pertanyaan</label>
                                                                <input type="text" name="nama_risiko" value="{{ $item->nama_risiko }}" required class="w-full text-sm font-semibold border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Target Jabatan</label>
                                                                <select name="role_target" required class="w-full text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                                                    @foreach(\Spatie\Permission\Models\Role::whereIn('role_category', ['maker', 'checker'])->orderBy('name')->get() as $roleOption)
                                                                        <option value="{{ $roleOption->name }}" {{ $item->role_target == $roleOption->name ? 'selected' : '' }}>{{ strtoupper($roleOption->name) }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Grup Kategori</label>
                                                                <select name="risk_category_id" required class="w-full text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                                                    @foreach($categories as $cat)
                                                                        <option value="{{ $cat->id }}" {{ $item->risk_category_id == $cat->id ? 'selected' : '' }}>{{ $cat->nama_kategori }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Kategori</label>
                                                                <select name="kategori" required class="w-full text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                                                    <option value="finansial" {{ $item->kategori == 'finansial' ? 'selected' : '' }}>FINANSIAL</option>
                                                                    <option value="non-finansial" {{ $item->kategori == 'non-finansial' ? 'selected' : '' }}>NON-FINANSIAL</option>
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Sumber Risiko</label>
                                                                <select name="sumber_risiko" required class="w-full text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                                                    <option value="manusia" {{ $item->sumber_risiko == 'manusia' ? 'selected' : '' }}>MANUSIA</option>
                                                                    <option value="proses_internal" {{ $item->sumber_risiko == 'proses_internal' ? 'selected' : '' }}>PROSES INTERNAL</option>
                                                                    <option value="sistem_teknologi" {{ $item->sumber_risiko == 'sistem_teknologi' ? 'selected' : '' }}>SISTEM TEKNOLOGI</option>
                                                                    <option value="faktor_eksternal" {{ $item->sumber_risiko == 'faktor_eksternal' ? 'selected' : '' }}>FAKTOR EKSTERNAL</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
                                                            <button type="button" @click="editingItemId = null" class="px-4 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 rounded uppercase tracking-widest">Batal</button>
                                                            <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded uppercase tracking-widest">Simpan Data</button>
                                                        </div>
                                                    </form>
                                                </template>
                                            </div>
                                            <button @click="activeModal = null" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-200 text-slate-500 hover:bg-rose-100 hover:text-rose-600 transition focus:outline-none shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>

                                        <div class="p-6">
                                            <h4 class="text-sm font-bold text-slate-700 uppercase tracking-widest mb-4 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                                Pohon Akar Masalah & Mitigasi
                                            </h4>
                                            
                                            <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-2 custom-scrollbar">
                                                @forelse($item->causes as $cause)
                                                    @php $mitigasiTeks = $cause->mitigations->first()->mitigasi ?? ''; @endphp
                                                    
                                                    <div class="flex flex-col md:flex-row border border-slate-200 rounded-xl overflow-hidden group hover:border-blue-400 hover:shadow-md transition duration-200 relative bg-white">
                                                        
                                                        <button type="button" @click="openEdit('{{ $cause->id }}', '{{ addslashes($cause->penyebab) }}', '{{ $cause->sumber_risiko }}', '{{ addslashes($mitigasiTeks) }}')" class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 bg-amber-100 text-amber-700 hover:bg-amber-500 hover:text-white px-3 py-1.5 rounded flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest transition z-10 shadow-sm">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                            Edit Data
                                                        </button>

                                                        <div class="w-full md:w-5/12 bg-slate-50 p-4 border-b md:border-b-0 md:border-r border-slate-200 relative">
                                                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-rose-400"></div>
                                                            <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest mb-1 pl-2">Akar Penyebab</p>
                                                            <p class="text-sm font-semibold text-slate-800 pr-8 pl-2 leading-relaxed">"{{ $cause->penyebab }}"</p>
                                                            <div class="mt-3 pl-2">
                                                                <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded bg-white border border-slate-300 text-slate-600 uppercase tracking-widest shadow-sm">
                                                                    {{ str_replace('_', ' ', $cause->sumber_risiko) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="w-full md:w-7/12 p-4 relative">
                                                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-400"></div>
                                                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-1 pl-2">Rekomendasi Mitigasi</p>
                                                            @if($mitigasiTeks)
                                                                <p class="text-sm text-slate-600 pr-8 pl-2 leading-relaxed">{{ $mitigasiTeks }}</p>
                                                            @else
                                                                <p class="text-sm text-slate-400 italic pr-8 pl-2">- Belum ada mitigasi yang terdaftar -</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-center py-10 bg-slate-50 rounded-xl border-2 border-dashed border-slate-300">
                                                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                                        <p class="text-sm text-slate-500 font-medium">Belum ada penyebab yang ditambahkan untuk risiko ini.</p>
                                                    </div>
                                                @endforelse
                                            </div>

                                            <div class="mt-6 bg-blue-50/50 border border-blue-200 rounded-xl p-5">
                                                <h4 class="text-xs font-bold text-blue-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                    Tambah Susulan Penyebab Baru
                                                </h4>
                                                <form action="{{ route('admin.risk_master.store_cause', $item->id) }}" method="POST" class="flex flex-col md:flex-row gap-3">
                                                    @csrf
                                                    <input type="text" name="penyebab" required placeholder="Tuliskan akar penyebab..." class="text-sm flex-1 border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                                    <select name="sumber_risiko" required class="text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 font-semibold text-slate-700 shadow-sm uppercase">
                                                        <option value="manusia">Manusia</option>
                                                        <option value="proses_internal">Proses Internal</option>
                                                        <option value="sistem_teknologi">Sistem Teknologi</option>
                                                        <option value="faktor_eksternal">Faktor Eksternal</option>
                                                    </select>
                                                    <input type="text" name="mitigasi" placeholder="Tuliskan mitigasi (Opsional)..." class="text-sm flex-1 border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                                    <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2.5 rounded-lg text-xs transition shadow-md uppercase tracking-widest whitespace-nowrap">Simpan</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-sm font-medium">Belum ada master data indikator risiko.</p>
                                        <button @click="createModal = true" class="mt-4 text-blue-600 font-bold hover:underline text-sm">Buat Kuesioner Pertama</button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
            </x-admin-table>
        </div>

        <!-- MODAL TAMBAH RISIKO BARU (DYNAMIC FORM) -->
        <div x-show="createModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="createModal" x-transition.opacity class="fixed inset-0 bg-slate-900 bg-opacity-80 backdrop-blur-sm transition-opacity" @click="createModal = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="createModal" x-transition class="inline-block align-bottom bg-slate-50 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl w-full">
                    
                    <div class="bg-white px-6 py-5 border-b border-slate-200 flex items-center justify-between sticky top-0 z-20 shadow-sm">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-wide">✨ Form Dinamis Kuesioner Risiko</h3>
                            <p class="text-xs text-slate-500 font-medium mt-1">Buat pertanyaan utama beserta seluruh akar masalah dan mitigasinya sekaligus.</p>
                        </div>
                        <button @click="createModal = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-100 hover:text-rose-600 transition focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form action="{{ route('admin.risk_master.store_item') }}" method="POST">
                        @csrf
                        <div class="p-6 max-h-[70vh] overflow-y-auto custom-scrollbar space-y-6">
                            
                            <!-- DATA INTI PERTANYAAN -->
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                                <h4 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                                    <span class="w-6 h-6 rounded bg-blue-100 text-blue-600 flex items-center justify-center text-xs">1</span>
                                    Data Inti Pertanyaan
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1">Target Jabatan <span class="text-rose-500">*</span></label>
                                        <select name="role_target" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold text-slate-700 bg-slate-50">
                                            @foreach(\Spatie\Permission\Models\Role::whereIn('role_category', ['maker', 'checker'])->orderBy('name')->get() as $roleOption)
                                                <option value="{{ $roleOption->name }}">{{ strtoupper($roleOption->name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1">Grup Kategori <span class="text-rose-500">*</span></label>
                                        <select name="risk_category_id" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold text-slate-700 bg-slate-50">
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->nama_kategori }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1">Redaksi Pertanyaan <span class="text-rose-500">*</span></label>
                                        <input type="text" name="nama_risiko" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Contoh: Terjadi selisih kurang pada perhitungan kas fisik harian...">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1">Kategori <span class="text-rose-500">*</span></label>
                                        <select name="kategori" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold text-slate-700 bg-slate-50">
                                            <option value="finansial">FINANSIAL (Loss Event)</option>
                                            <option value="non-finansial">NON-FINANSIAL (Risk Event)</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1">Sumber Utama Risiko <span class="text-rose-500">*</span></label>
                                        <select name="sumber_risiko" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold text-slate-700 bg-slate-50 uppercase">
                                            <option value="manusia">Manusia</option>
                                            <option value="proses_internal">Proses Internal</option>
                                            <option value="sistem_teknologi">Sistem Teknologi</option>
                                            <option value="faktor_eksternal">Faktor Eksternal</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- DYNAMIC CAUSES & MITIGATIONS -->
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm relative">
                                <h4 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                                    <span class="w-6 h-6 rounded bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">2</span>
                                    Daftar Akar Masalah & Mitigasi
                                </h4>
                                
                                <div class="space-y-4">
                                    <template x-for="(cause, index) in causes" :key="cause.id">
                                        <div class="p-4 border-2 border-slate-100 rounded-xl bg-slate-50 relative group">
                                            
                                            <!-- Tombol Hapus Cause -->
                                            <button type="button" @click="removeCause(index)" x-show="causes.length > 1" class="absolute -right-3 -top-3 w-7 h-7 bg-rose-500 hover:bg-rose-600 text-white rounded-full flex items-center justify-center shadow-lg transition transform hover:scale-110 z-10" title="Hapus baris ini">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                            </button>

                                            <div class="flex items-center gap-2 mb-3">
                                                <span class="px-2 py-0.5 bg-slate-200 text-slate-600 font-bold text-[10px] rounded uppercase tracking-widest" x-text="`Penyebab #${index + 1}`"></span>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                <div class="md:col-span-2">
                                                    <label class="block text-[10px] font-bold text-rose-500 uppercase tracking-widest mb-1">Akar Masalah <span class="text-rose-500">*</span></label>
                                                    <input type="text" :name="`causes[${index}][penyebab]`" x-model="cause.penyebab" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-rose-500 focus:border-rose-500 text-sm bg-white" placeholder="Sebutkan penyebab...">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Klasifikasi Sumber <span class="text-rose-500">*</span></label>
                                                    <select :name="`causes[${index}][sumber_risiko]`" x-model="cause.sumber_risiko" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm bg-white uppercase">
                                                        <option value="manusia">Manusia</option>
                                                        <option value="proses_internal">Proses Internal</option>
                                                        <option value="sistem_teknologi">Sistem Teknologi</option>
                                                        <option value="faktor_eksternal">Faktor Eksternal</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Rekomendasi Mitigasi</label>
                                                    <input type="text" :name="`causes[${index}][mitigasi]`" x-model="cause.mitigasi" class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white" placeholder="Solusi / Mitigasi...">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-4 pt-4 border-t border-slate-200 flex justify-center">
                                    <button type="button" @click="addCause()" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold uppercase tracking-widest transition shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Tambah Akar Masalah Lain
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-100 px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3 sticky bottom-0 z-20">
                            <button type="button" @click="createModal = false" class="px-6 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-xl font-bold text-xs uppercase tracking-widest transition shadow-sm">
                                Batal
                            </button>
                            <button type="submit" class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition shadow-md flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Simpan Kuesioner
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL EDIT CAUSE & MITIGASI -->
        <div x-show="editCauseModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editCauseModal" x-transition.opacity class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" @click="editCauseModal = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editCauseModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border-t-4 border-amber-500">
                    <div class="bg-white px-6 pt-6 pb-5">
                        <div class="flex justify-between items-center mb-5 border-b border-slate-100 pb-3">
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-wide">Edit Data Akar Masalah</h3>
                            <button @click="editCauseModal = false" class="text-slate-400 hover:text-rose-500 font-bold text-2xl transition">&times;</button>
                        </div>
                        
                        <form :action="`/admin/risk-master/cause/${editData.id}`" method="POST" class="space-y-5">
                            @csrf
                            @method('PATCH')
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-widest">Teks Penyebab <span class="text-rose-500">*</span></label>
                                <input type="text" name="penyebab" x-model="editData.penyebab" required class="block w-full border-slate-300 rounded-lg shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500 bg-slate-50">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-widest">Sumber Risiko <span class="text-rose-500">*</span></label>
                                <select name="sumber_risiko" x-model="editData.sumber_risiko" required class="block w-full border-slate-300 rounded-lg shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500 bg-slate-50 uppercase font-semibold text-slate-700">
                                    <option value="manusia">Manusia</option>
                                    <option value="proses_internal">Proses Internal</option>
                                    <option value="sistem_teknologi">Sistem Teknologi</option>
                                    <option value="faktor_eksternal">Faktor Eksternal</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-widest">Teks Mitigasi <span class="text-slate-400 font-medium normal-case tracking-normal">(Kosongkan jika ingin dihapus)</span></label>
                                <textarea name="mitigasi" x-model="editData.mitigasi" rows="3" class="block w-full border-slate-300 rounded-lg shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500 bg-slate-50"></textarea>
                            </div>
                            
                            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-slate-100">
                                <button type="button" @click="editCauseModal = false" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-6 rounded-xl text-xs uppercase tracking-widest transition shadow-sm">Batal</button>
                                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 px-6 rounded-xl text-xs uppercase tracking-widest shadow-md transition">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</x-app-layout>
