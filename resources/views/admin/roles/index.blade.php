<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
                {{ __('Manajemen Role') }}
            </h2>
            <p class="text-sm text-slate-500">Kelola role/jabatan, role category, dan permission yang tersedia di sistem.</p>
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
                    <h3 class="text-lg font-bold">Daftar Role / Jabatan</h3>
                    <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm shadow">
                        + Tambah Role Baru
                    </button>
                </div>

                <x-admin-table :headers="['Nama Role', 'Divisi', 'Kode Role', 'Role Category', 'Permissions', 'Jumlah User', 'Aksi']">
                    @foreach($roles as $role)
                    <tr class="hover:bg-slate-50 transition duration-150 group/row">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-900 uppercase">{{ str_replace('_', ' ', $role->name) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm text-slate-600 font-medium">
                                {{ $role->division->nama_divisi ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 bg-slate-100 text-slate-800 rounded text-[10px] font-bold uppercase">
                                {{ $role->kode_role ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <x-badge :type="strtolower($role->role_category ?? 'default')" class="uppercase tracking-widest">
                                {{ $role->role_category ?? '-' }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                @forelse($role->permissions as $perm)
                                <span class="px-1.5 py-0.5 bg-blue-50 border border-blue-200 text-blue-700 rounded text-[10px] font-medium">{{ $perm->name }}</span>
                                @empty
                                <span class="text-xs text-slate-400 italic">Tidak ada permission</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-slate-600">
                            {{ $role->users_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-3 transition duration-200">
                            <button onclick="openEditModal({{ $role->id }}, '{{ $role->name }}', '{{ $role->role_category }}', '{{ $role->division_id }}', '{{ $role->kode_role }}', {{ json_encode($role->permissions->pluck('name')) }})" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-xs font-bold uppercase tracking-widest transition">
                                Edit
                            </button>
                            <button onclick="openDeleteModal({{ $role->id }}, '{{ $role->name }}')" class="inline-flex items-center gap-1.5 text-rose-500 hover:text-rose-700 text-xs font-bold uppercase tracking-widest transition">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </x-admin-table>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH ROLE --}}
    <div id="modalTambah" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900 uppercase">Tambah Role Baru</h3>
                <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
            </div>
            <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Role</label>
                    <input type="text" name="name" required placeholder="contoh: kabag_akunting" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-400">Gunakan format snake_case. Contoh: kabag_akunting, auditor_internal</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role Category</label>
                    <select name="role_category" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                        <option value="maker">Maker</option>
                        <option value="checker">Checker</option>
                        <option value="viewer">Viewer</option>
                        <option value="admin">Admin</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Kategori ini menentukan akses user yang memegang role ini.</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Divisi <span class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                        <select name="division_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode Role <span class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="text" name="kode_role" placeholder="contoh: TL" maxlength="5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                    <div class="grid grid-cols-2 gap-2 max-h-60 overflow-y-auto border border-gray-200 rounded-md p-3">
                        @foreach($permissions as $perm)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span>{{ $perm->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm w-full">Simpan Role Baru</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT ROLE --}}
    <div id="modalEdit" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-lg shadow-lg rounded-md bg-white border-t-4 border-t-yellow-500">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900 uppercase">Edit Role</h3>
                <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
            </div>
            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Role</label>
                    <input type="text" name="name" id="edit_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role Category</label>
                    <select name="role_category" id="edit_role_category" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                        <option value="maker">Maker</option>
                        <option value="checker">Checker</option>
                        <option value="viewer">Viewer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Divisi <span class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                        <select name="division_id" id="edit_division_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode Role <span class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="text" name="kode_role" id="edit_kode_role" placeholder="contoh: TL" maxlength="5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                    <div class="grid grid-cols-2 gap-2 max-h-60 overflow-y-auto border border-gray-200 rounded-md p-3" id="edit_permissions_container">
                        @foreach($permissions as $perm)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="perm-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span>{{ $perm->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded text-sm w-full shadow">Update Role</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DELETE ROLE (2-STEP VERIFICATION) --}}
    <div id="modalDelete" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-md shadow-lg rounded-md bg-white border-t-4 border-t-red-500">

            <!-- STEP 1: Konfirmasi Awal -->
            <div id="deleteStep1">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 uppercase">⚠️ Hapus Role</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                </div>
                <div class="space-y-4">
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                        <p class="text-sm text-red-800 font-semibold">STEP 1/2 — Konfirmasi Awal</p>
                    </div>
                    <p class="text-sm text-gray-700">
                        Apakah Anda yakin ingin menghapus role berikut:
                    </p>
                    <div class="bg-gray-50 p-3 rounded border text-center">
                        <p id="deleteRoleName" class="font-bold text-gray-900 text-lg uppercase"></p>
                        <p id="deleteRoleInfo" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                    <div class="bg-red-50 border border-red-200 p-3 rounded text-xs text-red-800 space-y-1">
                        <p>🔴 <strong>Dampak menghapus role:</strong></p>
                        <p>• Role tidak bisa digunakan lagi oleh user manapun</p>
                        <p>• Role harus TIDAK dipakai oleh user aktif</p>
                        <p>• Aksi ini <strong>TIDAK</strong> bisa dibatalkan</p>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-bold">Batal</button>
                        <button onclick="goToDeleteStep2()" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded text-sm font-bold">Lanjut →</button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Konfirmasi Akhir (Ketik "HAPUS") -->
            <div id="deleteStep2" class="hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 uppercase">🔴 Hapus Role (Final)</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                </div>
                <div class="space-y-4">
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                        <p class="text-sm text-red-800 font-semibold">STEP 2/2 — Konfirmasi Akhir</p>
                    </div>
                    <div class="bg-red-50 border border-red-200 p-3 rounded text-xs text-red-800 space-y-1">
                        <p>🔴 Role <strong id="deleteRoleName2" class="text-red-900"></strong> akan dihapus secara permanen!</p>
                        <p>• Semua permission yang terkait akan dilepas</p>
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
                            🔴 Hapus Role
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
                    <p class="text-gray-700 font-semibold">Menghapus role...</p>
                </div>
            </div>

        </div>
    </div>

    <script>
        // ================================================================
        // DELETE ROLE — 2-STEP VERIFICATION
        // ================================================================
        let deleteRoleId = null;
        let deleteRoleName = '';

        function openDeleteModal(id, name) {
            deleteRoleId = id;
            deleteRoleName = name;

            // Reset state
            document.getElementById('deleteStep1').classList.remove('hidden');
            document.getElementById('deleteStep2').classList.add('hidden');
            document.getElementById('deleteLoading').classList.add('hidden');
            document.getElementById('deleteConfirmInput').value = '';
            document.getElementById('deleteConfirmError').classList.add('hidden');
            document.getElementById('deleteConfirmBtn').disabled = true;
            document.getElementById('deleteConfirmBtn').classList.add('opacity-50', 'cursor-not-allowed');

            // Isi data role
            document.getElementById('deleteRoleName').textContent = name;
            document.getElementById('deleteRoleInfo').textContent = 'Role ID: ' + id;
            document.getElementById('deleteRoleName2').textContent = name;

            // Tampilkan modal
            document.getElementById('modalDelete').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('modalDelete').classList.add('hidden');
            deleteRoleId = null;
            deleteRoleName = '';
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
            if (!deleteRoleId) return;

            // Sembunyikan step 2, tampilkan loading
            document.getElementById('deleteStep2').classList.add('hidden');
            document.getElementById('deleteLoading').classList.remove('hidden');

            // Kirim POST request via form submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/roles/${deleteRoleId}`;
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

        // Tutup modal delete jika klik di luar
        document.getElementById('modalDelete').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // ================================================================
        // EDIT ROLE — OPEN MODAL
        // ================================================================
        function openEditModal(id, name, roleCategory, divisionId, kodeRole, permissions) {
            document.getElementById('modalEdit').classList.remove('hidden');
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_role_category').value = roleCategory;
            document.getElementById('edit_division_id').value = divisionId || '';
            document.getElementById('edit_kode_role').value = kodeRole || '';
            document.getElementById('editForm').action = `/admin/roles/${id}`;

            // Reset semua checkbox
            document.querySelectorAll('#edit_permissions_container .perm-checkbox').forEach(cb => cb.checked = false);

            // Check permissions yang dimiliki role ini
            permissions.forEach(permName => {
                document.querySelectorAll('#edit_permissions_container .perm-checkbox').forEach(cb => {
                    if (cb.value === permName) cb.checked = true;
                });
            });
        }
    </script>
</x-app-layout>
