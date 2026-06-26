<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
                {{ __('Manajemen Divisi') }}
            </h2>
            <p class="text-sm text-slate-500">Kelola master data divisi yang menaungi berbagai role/jabatan di sistem.</p>
        </div>
    </x-slot>

    <div class="pt-4 pb-8 sm:pb-12">
        <div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto page-stack">

            <!-- HEADER & ACTIONS -->@if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
            @endif

            <div class="surface-card overflow-hidden">
                <div class="p-4 sm:p-6 text-gray-900 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                    <h3 class="text-lg font-bold">Daftar Divisi</h3>
                    <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm shadow">
                        + Tambah Divisi Baru
                    </button>
                </div>

                <x-admin-table :headers="['Nama Divisi', 'Kode Divisi', 'Jumlah Jabatan (Role)', 'Aksi']">
                    @foreach($divisions as $divisi)
                    <tr class="hover:bg-slate-50 transition duration-150 group/row">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-900 uppercase">{{ $divisi->nama_divisi }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-[10px] font-bold uppercase">
                                {{ $divisi->kode_divisi }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600">
                            <span class="font-bold">{{ $divisi->roles_count }}</span> jabatan
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-3 transition duration-200">
                            <button onclick="openEditModal('{{ $divisi->id }}', '{{ addslashes($divisi->nama_divisi) }}', '{{ $divisi->kode_divisi }}')" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-xs font-bold uppercase tracking-widest transition">
                                Edit
                            </button>
                            <button onclick="openDeleteModal('{{ $divisi->id }}', '{{ addslashes($divisi->nama_divisi) }}', {{ $divisi->roles_count }})" class="inline-flex items-center gap-1.5 text-rose-500 hover:text-rose-700 text-xs font-bold uppercase tracking-widest transition">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </x-admin-table>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH DIVISI --}}
    <div id="modalTambah" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-lg shadow-lg rounded-md bg-white border-t-4 border-t-indigo-600">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900 uppercase">Tambah Divisi Baru</h3>
                <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
            </div>
            <form action="{{ route('admin.divisions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Divisi</label>
                    <input type="text" name="nama_divisi" required placeholder="contoh: Operasional" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode Divisi</label>
                    <input type="text" name="kode_divisi" required placeholder="contoh: OP" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-400">Digunakan sebagai prefix internal, disarankan 2-3 huruf.</p>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded text-sm transition">Batal</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm shadow">Simpan Divisi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT DIVISI --}}
    <div id="modalEdit" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-lg shadow-lg rounded-md bg-white border-t-4 border-t-yellow-500">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900 uppercase">Edit Divisi</h3>
                <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
            </div>
            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Divisi</label>
                    <input type="text" name="nama_divisi" id="edit_nama_divisi" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode Divisi</label>
                    <input type="text" name="kode_divisi" id="edit_kode_divisi" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded text-sm transition">Batal</button>
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded text-sm shadow">Update Divisi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DELETE DIVISI (2-STEP VERIFICATION) --}}
    <div id="modalDelete" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-md shadow-lg rounded-md bg-white border-t-4 border-t-red-500">

            <!-- STEP 1: Konfirmasi Awal -->
            <div id="deleteStep1">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 uppercase">⚠️ Hapus Divisi</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                </div>
                <div class="space-y-4">
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                        <p class="text-sm text-red-800 font-semibold">STEP 1/2 — Konfirmasi Awal</p>
                    </div>
                    
                    <div id="deleteWarningActive" class="hidden bg-orange-50 border border-orange-200 p-3 rounded text-sm text-orange-800 font-bold text-center">
                        <p>Divisi ini masih dipakai oleh <span id="deleteRolesCount">0</span> Role!</p>
                        <p class="text-xs mt-1 font-normal">Anda tidak dapat menghapus divisi ini sampai semua role dipindahkan.</p>
                    </div>

                    <div id="deleteSafeBlock">
                        <p class="text-sm text-gray-700">Apakah Anda yakin ingin menghapus divisi berikut:</p>
                        <div class="bg-gray-50 p-3 rounded border text-center my-3">
                            <p id="deleteDivisiName" class="font-bold text-gray-900 text-lg uppercase"></p>
                        </div>
                        <div class="bg-red-50 border border-red-200 p-3 rounded text-xs text-red-800 space-y-1">
                            <p>🔴 <strong>Dampak menghapus divisi:</strong></p>
                            <p>• Divisi akan terhapus dari sistem</p>
                            <p>• Aksi ini <strong>TIDAK</strong> bisa dibatalkan</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-bold">Batal</button>
                        <button id="btnNextDelete" onclick="goToDeleteStep2()" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded text-sm font-bold">Lanjut →</button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Konfirmasi Akhir (Ketik "HAPUS") -->
            <div id="deleteStep2" class="hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 uppercase">🔴 Hapus Divisi (Final)</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                </div>
                <div class="space-y-4">
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                        <p class="text-sm text-red-800 font-semibold">STEP 2/2 — Konfirmasi Akhir</p>
                    </div>
                    <div class="bg-red-50 border border-red-200 p-3 rounded text-xs text-red-800 space-y-1">
                        <p>🔴 Divisi <strong id="deleteDivisiName2" class="text-red-900"></strong> akan dihapus secara permanen!</p>
                        <p>• Aksi ini tidak bisa dibatalkan</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Ketik <strong>"HAPUS"</strong> untuk konfirmasi:
                        </label>
                        <input type="text" id="deleteConfirmInput"
                               oninput="checkDeleteConfirm()"
                               placeholder="Ketik HAPUS di sini..."
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-red-500 focus:border-red-500 uppercase">
                        <p id="deleteConfirmError" class="text-xs text-red-600 mt-1 hidden">Anda harus mengetik "HAPUS" untuk melanjutkan.</p>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button onclick="goToDeleteStep1()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-bold">Kembali</button>
                        <button id="deleteConfirmBtn" disabled
                                onclick="executeDelete()"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-bold opacity-50 cursor-not-allowed">
                            🔴 Hapus Divisi
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Loading -->
            <div id="deleteLoading" class="hidden">
                <div class="text-center py-8">
                    <svg class="animate-spin h-10 w-10 text-red-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-700 font-semibold">Menghapus divisi...</p>
                </div>
            </div>

        </div>
    </div>

    <script>
        // ================================================================
        // EDIT DIVISI — OPEN MODAL
        // ================================================================
        function openEditModal(id, nama, kode) {
            document.getElementById('modalEdit').classList.remove('hidden');
            document.getElementById('edit_nama_divisi').value = nama;
            document.getElementById('edit_kode_divisi').value = kode;
            document.getElementById('editForm').action = `/admin/divisions/${id}`;
        }

        // ================================================================
        // DELETE DIVISI — 2-STEP VERIFICATION
        // ================================================================
        let deleteId = null;

        function openDeleteModal(id, name, rolesCount) {
            deleteId = id;

            // Reset state
            document.getElementById('deleteStep1').classList.remove('hidden');
            document.getElementById('deleteStep2').classList.add('hidden');
            document.getElementById('deleteLoading').classList.add('hidden');
            document.getElementById('deleteConfirmInput').value = '';
            document.getElementById('deleteConfirmError').classList.add('hidden');
            document.getElementById('deleteConfirmBtn').disabled = true;
            document.getElementById('deleteConfirmBtn').classList.add('opacity-50', 'cursor-not-allowed');

            document.getElementById('deleteDivisiName').textContent = name;
            document.getElementById('deleteDivisiName2').textContent = name;

            if (rolesCount > 0) {
                document.getElementById('deleteWarningActive').classList.remove('hidden');
                document.getElementById('deleteSafeBlock').classList.add('hidden');
                document.getElementById('btnNextDelete').classList.add('hidden');
                document.getElementById('deleteRolesCount').textContent = rolesCount;
            } else {
                document.getElementById('deleteWarningActive').classList.add('hidden');
                document.getElementById('deleteSafeBlock').classList.remove('hidden');
                document.getElementById('btnNextDelete').classList.remove('hidden');
            }

            document.getElementById('modalDelete').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('modalDelete').classList.add('hidden');
            deleteId = null;
        }

        function goToDeleteStep2() {
            document.getElementById('deleteStep1').classList.add('hidden');
            document.getElementById('deleteStep2').classList.remove('hidden');
            document.getElementById('deleteConfirmInput').focus();
        }

        function goToDeleteStep1() {
            document.getElementById('deleteStep2').classList.add('hidden');
            document.getElementById('deleteStep1').classList.remove('hidden');
        }

        function checkDeleteConfirm() {
            const input = document.getElementById('deleteConfirmInput').value.trim();
            const btn = document.getElementById('deleteConfirmBtn');
            const error = document.getElementById('deleteConfirmError');

            if (input === 'HAPUS') {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                error.classList.add('hidden');
            } else {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                if (input.length > 0) {
                    error.classList.remove('hidden');
                } else {
                    error.classList.add('hidden');
                }
            }
        }

        function executeDelete() {
            if (!deleteId) return;

            document.getElementById('deleteStep2').classList.add('hidden');
            document.getElementById('deleteLoading').classList.remove('hidden');

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/divisions/${deleteId}`;
            form.style.display = 'none';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Close when clicking outside
        ['modalTambah', 'modalEdit', 'modalDelete'].forEach(modalId => {
            document.getElementById(modalId).addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        });
    </script>
</x-app-layout>
