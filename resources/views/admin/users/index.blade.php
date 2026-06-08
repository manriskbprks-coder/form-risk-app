<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
                {{ __('Manajemen Karyawan (Super Admin)') }}
            </h2>
            <p class="text-sm text-slate-500">Kelola akun, status aktif, dan mutasi user dengan tata letak yang lebih bersih dan efisien.</p>
        </div>
    </x-slot>

    <div class="pt-4 pb-8 sm:pb-12">
        <div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto page-stack">

            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
            @endif

            <div class="surface-card overflow-hidden">
                <div class="p-4 sm:p-6 text-gray-900 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                    <h3 class="text-lg font-bold">Daftar Karyawan BPR</h3>
                </div>

                <div class="pt-4 pb-8 sm:pb-12">
                    <div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto">

                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-800 uppercase tracking-wider">Manajemen Pengguna BPR</h3>
                            <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm shadow">
                                + Tambah User Baru
                            </button>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200"
                             x-data="{
                                search: '',
                                sortField: 'name',
                                sortDir: 'asc',
                                get sortedUsers() {
                                    let items = {{ Js::from($users->map(function($u) {
                                        return [
                                            'id' => $u->id,
                                            'name' => $u->name,
                                            'username' => $u->username,
                                            'email' => $u->email,
                                            'branch_name' => $u->branch->nama_cabang ?? 'Pusat',
                                            'role_name' => $u->roles->first()->name ?? '-',
                                            'is_active' => $u->is_active,
                                        ];
                                    })->values()->toArray()) }};

                                    // Filter
                                    if (this.search.trim() !== '') {
                                        let q = this.search.toLowerCase().trim();
                                        items = items.filter(u =>
                                            u.name.toLowerCase().includes(q) ||
                                            u.username.toLowerCase().includes(q) ||
                                            u.email.toLowerCase().includes(q)
                                        );
                                    }

                                    // Sort
                                    items.sort((a, b) => {
                                        let valA, valB;
                                        if (this.sortField === 'name') {
                                            valA = a.name.toLowerCase();
                                            valB = b.name.toLowerCase();
                                        } else if (this.sortField === 'branch') {
                                            valA = a.branch_name.toLowerCase();
                                            valB = b.branch_name.toLowerCase();
                                        } else if (this.sortField === 'role') {
                                            valA = a.role_name.toLowerCase();
                                            valB = b.role_name.toLowerCase();
                                        } else if (this.sortField === 'status') {
                                            valA = a.is_active ? 1 : 0;
                                            valB = b.is_active ? 1 : 0;
                                        }
                                        if (valA < valB) return this.sortDir === 'asc' ? -1 : 1;
                                        if (valA > valB) return this.sortDir === 'asc' ? 1 : -1;
                                        return 0;
                                    });

                                    return items;
                                },
                                sortBy(field) {
                                    if (this.sortField === field) {
                                        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                                    } else {
                                        this.sortField = field;
                                        this.sortDir = 'asc';
                                    }
                                },
                                sortIcon(field) {
                                    if (this.sortField !== field) return '↕';
                                    return this.sortDir === 'asc' ? '▲' : '▼';
                                }
                             }">
                            <div class="p-4 border-b border-gray-200 bg-gray-50/50">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="relative w-full sm:w-72">
                                        <input type="text"
                                               x-model="search"
                                               placeholder="Cari nama, username, atau email..."
                                               class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <span x-text="sortedUsers.length"></span> dari <span>{{ $users->count() }}</span> karyawan
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto -mx-4 sm:mx-0">
                            <table class="min-w-[850px] w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th @click="sortBy('name')" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase cursor-pointer select-none hover:text-gray-700">
                                            <span class="inline-flex items-center gap-1">Nama & Username <span x-text="sortIcon('name')" class="text-[10px]"></span></span>
                                        </th>
                                        <th @click="sortBy('branch')" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase cursor-pointer select-none hover:text-gray-700">
                                            <span class="inline-flex items-center gap-1">Cabang <span x-text="sortIcon('branch')" class="text-[10px]"></span></span>
                                        </th>
                                        <th @click="sortBy('role')" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase cursor-pointer select-none hover:text-gray-700">
                                            <span class="inline-flex items-center gap-1">Jabatan <span x-text="sortIcon('role')" class="text-[10px]"></span></span>
                                        </th>
                                        <th @click="sortBy('status')" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase cursor-pointer select-none hover:text-gray-700">
                                            <span class="inline-flex items-center gap-1">Status <span x-text="sortIcon('status')" class="text-[10px]"></span></span>
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="user in sortedUsers" :key="user.id">
                                    <tr :class="!user.is_active ? 'bg-gray-50' : ''">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900" x-text="user.name"></div>
                                            <div class="text-xs text-gray-500" x-text="user.username + ' | ' + user.email"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 uppercase" x-text="user.branch_name"></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-[10px] font-bold uppercase" x-text="user.role_name"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase"
                                                  :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  x-text="user.is_active ? 'Aktif' : 'Non-Aktif'"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button @click="openEditModalFromAlpine(user)" class="inline-flex items-center justify-center min-w-[84px] text-blue-600 hover:text-white hover:bg-blue-500 border border-blue-300 px-3.5 py-2 rounded-xl text-[11px] font-bold uppercase tracking-[0.14em] transition bg-white">Edit</button>
                                            <button @click="openResetModal(user)" class="inline-flex items-center justify-center min-w-[84px] text-amber-600 hover:text-white hover:bg-amber-500 border border-amber-300 px-3.5 py-2 rounded-xl text-[11px] font-bold uppercase tracking-[0.14em] transition bg-white">Reset</button>
                                            <button @click="openToggleConfirm(user, `/admin/users/${user.id}/toggle-status`)"
                                                    class="inline-flex items-center justify-center min-w-[84px] px-3.5 py-2 rounded-xl text-[11px] font-bold uppercase tracking-[0.14em] transition bg-white"
                                                    :class="user.is_active ? 'text-rose-600 hover:text-white hover:bg-rose-500 border border-rose-300' : 'text-emerald-600 hover:text-white hover:bg-emerald-500 border border-emerald-300'"
                                                    x-text="user.is_active ? 'Non-Aktifkan' : 'Aktifkan'"></button>
                                        </td>
                                    </tr>
                                    </template>
                                </tbody>
                            </table>
                            </div>
                            <div x-show="sortedUsers.length === 0" class="p-8 text-center text-gray-500 text-sm">
                                Tidak ada karyawan yang cocok dengan pencarian "<span x-text="search" class="font-semibold"></span>".
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="modalTambah" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 uppercase">Tambah User Baru</h3>
                    <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" name="username" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cabang Penempatan</label>
                            <select name="branch_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jabatan (Role)</label>
                            <select name="role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                                @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm w-full">Simpan Akun Baru</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modalEdit" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-md shadow-lg rounded-md bg-white border-t-4 border-t-yellow-500">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 uppercase">Edit / Mutasi User</h3>
                    <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                </div>
                <form id="editForm" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="name" id="edit_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm bg-gray-50 focus:ring-indigo-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mutasi Cabang</label>
                            <select name="branch_id" id="edit_branch" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ubah Jabatan</label>
                            <select name="role" id="edit_role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm uppercase focus:ring-indigo-500">
                                @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded text-sm w-full shadow">Update Data User</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================================================================ -->
        <!-- MODAL TOGGLE STATUS (KONFIRMASI NON-AKTIFKAN / AKTIFKAN)         -->
        <!-- ================================================================ -->
        <div id="modalToggle" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
            <div id="toggleModalContainer" class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-md shadow-lg rounded-md bg-white border-t-4 border-t-red-500">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="toggleModalTitle" class="text-lg font-bold text-gray-900 uppercase">⚠️ Non-Aktifkan User</h3>
                    <button onclick="closeToggleModal()" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                </div>
                <div class="space-y-4">
                    <div class="bg-gray-50 border-l-4 border-gray-400 p-4 rounded">
                        <p class="text-sm text-gray-800 font-semibold">Konfirmasi Aksi</p>
                    </div>
                    <p class="text-sm text-gray-700">
                        Apakah Anda yakin ingin <strong id="toggleActionText">menonaktifkan</strong> user berikut:
                    </p>
                    <div class="bg-gray-50 p-3 rounded border text-center">
                        <p id="toggleUserName" class="font-bold text-gray-900 text-lg"></p>
                        <p id="toggleUserInfo" class="text-xs text-gray-500 mt-1"></p>
                    </div>

                    <!-- Warning khusus untuk Non-Aktifkan -->
                    <div id="toggleWarningBlock" class="bg-red-50 border border-red-200 p-3 rounded text-xs text-red-800 space-y-1">
                        <p>🔴 <strong>Dampak menonaktifkan user:</strong></p>
                        <p>• User <strong>TIDAK</strong> dapat login ke sistem</p>
                        <p>• Semua sesi aktif user akan berakhir</p>
                        <p>• Data laporan yang sudah dibuat tetap tersimpan</p>
                    </div>

                    <!-- Info untuk Aktifkan -->
                    <div id="toggleInfoBlock" class="bg-green-50 border border-green-200 p-3 rounded text-xs text-green-800 space-y-1 hidden">
                        <p>🟢 <strong>Dampak mengaktifkan user:</strong></p>
                        <p>• User dapat login kembali ke sistem</p>
                        <p>• Semua akses dan peran akan kembali normal</p>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <button onclick="closeToggleModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-bold">Batal</button>
                        <button id="toggleConfirmBtn"
                                onclick="executeToggle()"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-bold shadow">🔴 Non-Aktifkan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================ -->
        <!-- MODAL RESET PASSWORD (2-STEP VERIFICATION)                       -->
        <!-- ================================================================ -->
        <div id="modalReset" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-6 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-md shadow-lg rounded-md bg-white border-t-4 border-t-amber-500">

                <!-- STEP 1: Konfirmasi Awal -->
                <div id="resetStep1">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900 uppercase">🔒 Reset Password</h3>
                        <button onclick="closeResetModal()" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded">
                            <p class="text-sm text-amber-800 font-semibold">STEP 1/2 — Konfirmasi Awal</p>
                        </div>
                        <p class="text-sm text-gray-700">
                            Apakah Anda yakin ingin mereset password untuk:
                        </p>
                        <div class="bg-gray-50 p-3 rounded border text-center">
                            <p id="resetUserName" class="font-bold text-gray-900 text-lg"></p>
                            <p id="resetUserInfo" class="text-xs text-gray-500 mt-1"></p>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 p-3 rounded text-xs text-yellow-800">
                            ⚠️ Password saat ini akan diganti dengan <strong>password sementara</strong> yang baru.
                            User akan diminta mengganti password saat login pertama kali.
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button onclick="closeResetModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-bold">Batal</button>
                            <button onclick="goToStep2()" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded text-sm font-bold">Lanjut →</button>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Konfirmasi Akhir (Ketik "RESET") -->
                <div id="resetStep2" class="hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900 uppercase">⚠️ Konfirmasi Terakhir</h3>
                        <button onclick="closeResetModal()" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                            <p class="text-sm text-red-800 font-semibold">STEP 2/2 — Konfirmasi Terakhir</p>
                        </div>
                        <div class="bg-red-50 border border-red-200 p-3 rounded text-xs text-red-800 space-y-1">
                            <p>🔴 Reset password akan:</p>
                            <p>• Mengganti password <strong id="resetUserName2" class="text-red-900"></strong> saat ini</p>
                            <p>• Membuat user harus ganti password saat login pertama kali</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Ketik <strong>"RESET"</strong> untuk konfirmasi:
                            </label>
                            <input type="text" id="resetConfirmInput"
                                   oninput="checkResetConfirm()"
                                   placeholder="Ketik RESET di sini..."
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-red-500 focus:border-red-500 uppercase">
                            <p id="resetConfirmError" class="text-xs text-red-600 mt-1 hidden">Anda harus mengetik "RESET" untuk melanjutkan.</p>
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button onclick="goToStep1()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-bold">Kembali</button>
                            <button id="resetConfirmBtn" disabled
                                    onclick="executeReset()"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-bold opacity-50 cursor-not-allowed">
                                🔄 Reset Sekarang
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Loading -->
                <div id="resetLoading" class="hidden">
                    <div class="text-center py-8">
                        <svg class="animate-spin h-10 w-10 text-amber-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-700 font-semibold">Memproses reset password...</p>
                    </div>
                </div>

                <!-- STEP 4: Hasil (Password Sementara) -->
                <div id="resetResult" class="hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-green-700 uppercase">✅ Berhasil!</h3>
                        <button onclick="closeResetModal()" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-green-50 border border-green-200 p-4 rounded text-center">
                            <p class="text-sm text-green-800 font-semibold mb-2">Password sementara untuk:</p>
                            <p id="resultUserName" class="font-bold text-gray-900 text-lg"></p>
                        </div>
                        <div class="bg-gray-900 p-4 rounded text-center">
                            <p class="text-xs text-gray-400 mb-1">Password Sementara:</p>
                            <p id="resultTempPassword" class="font-mono text-2xl text-green-400 font-bold tracking-wider select-all"></p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 p-3 rounded text-xs text-blue-800 space-y-1">
                            <p>📋 <strong>Password ini hanya muncul SEKARANG!</strong></p>
                            <p>Silakan salin dan kirimkan ke user via email/chat internal.</p>
                            <p>🔑 User akan diminta mengganti password saat login pertama kali.</p>
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button onclick="copyTempPassword()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-bold">📋 Salin Password</button>
                            <button onclick="closeResetModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-bold">Tutup</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script>
            // ================================================================
            // EDIT USER — OPEN MODAL FROM ALPINE
            // ================================================================
            function openEditModalFromAlpine(user) {
                // Isi field form
                document.getElementById('edit_name').value = user.name;

                // Set branch_id select
                const branchSelect = document.getElementById('edit_branch');
                for (let i = 0; i < branchSelect.options.length; i++) {
                    if (branchSelect.options[i].value == user.branch_id) {
                        branchSelect.selectedIndex = i;
                        break;
                    }
                }

                // Set role select
                const roleSelect = document.getElementById('edit_role');
                for (let i = 0; i < roleSelect.options.length; i++) {
                    if (roleSelect.options[i].value === user.role_name) {
                        roleSelect.selectedIndex = i;
                        break;
                    }
                }

                // Set form action URL
                document.getElementById('editForm').action = `/admin/users/${user.id}`;

                // Tampilkan modal
                document.getElementById('modalEdit').classList.remove('hidden');
            }

            // ================================================================
            // RESET PASSWORD — 2-STEP VERIFICATION
            // ================================================================
            let resetUserId = null;
            let resetUserName = '';
            let resetUserUsername = '';
            let resetUserEmail = '';

            function openResetModal(user) {
                resetUserId = user.id;
                resetUserName = user.name;
                resetUserUsername = user.username;
                resetUserEmail = user.email;

                // Reset state
                document.getElementById('resetStep1').classList.remove('hidden');
                document.getElementById('resetStep2').classList.add('hidden');
                document.getElementById('resetLoading').classList.add('hidden');
                document.getElementById('resetResult').classList.add('hidden');
                document.getElementById('resetConfirmInput').value = '';
                document.getElementById('resetConfirmError').classList.add('hidden');
                document.getElementById('resetConfirmBtn').disabled = true;
                document.getElementById('resetConfirmBtn').classList.add('opacity-50', 'cursor-not-allowed');

                // Isi data user
                document.getElementById('resetUserName').textContent = user.name;
                document.getElementById('resetUserInfo').textContent = user.username + ' | ' + user.email;
                document.getElementById('resetUserName2').textContent = user.name;

                // Tampilkan modal
                document.getElementById('modalReset').classList.remove('hidden');
            }

            function closeResetModal() {
                document.getElementById('modalReset').classList.add('hidden');
                resetUserId = null;
            }

            function goToStep2() {
                document.getElementById('resetStep1').classList.add('hidden');
                document.getElementById('resetStep2').classList.remove('hidden');
                document.getElementById('resetConfirmInput').focus();
            }

            function goToStep1() {
                document.getElementById('resetStep2').classList.add('hidden');
                document.getElementById('resetStep1').classList.remove('hidden');
            }

            function checkResetConfirm() {
                const input = document.getElementById('resetConfirmInput').value.trim();
                const btn = document.getElementById('resetConfirmBtn');
                const error = document.getElementById('resetConfirmError');

                if (input === 'RESET') {
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

            function executeReset() {
                if (!resetUserId) return;

                // Sembunyikan step 2, tampilkan loading
                document.getElementById('resetStep2').classList.add('hidden');
                document.getElementById('resetLoading').classList.remove('hidden');

                // Kirim request AJAX
                fetch(`/admin/users/${resetUserId}/reset-password`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('resetLoading').classList.add('hidden');

                    if (data.error) {
                        alert('Error: ' + data.error);
                        closeResetModal();
                        return;
                    }

                    if (data.success) {
                        // Tampilkan hasil
                        document.getElementById('resultUserName').textContent = data.user_name;
                        document.getElementById('resultTempPassword').textContent = data.temp_password;
                        document.getElementById('resetResult').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    document.getElementById('resetLoading').classList.add('hidden');
                    alert('Terjadi kesalahan saat mereset password. Silakan coba lagi.');
                    closeResetModal();
                });
            }

            function copyTempPassword() {
                const password = document.getElementById('resultTempPassword').textContent;
                navigator.clipboard.writeText(password).then(() => {
                    alert('Password sementara berhasil disalin!');
                }).catch(() => {
                    // Fallback: select text
                    const range = document.createRange();
                    const el = document.getElementById('resultTempPassword');
                    range.selectNode(el);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                    document.execCommand('copy');
                    alert('Password sementara berhasil disalin!');
                });
            }

            // ================================================================
            // TOGGLE STATUS — KONFIRMASI NON-AKTIFKAN / AKTIFKAN
            // ================================================================
            let toggleActionUrl = '';

            function openToggleConfirm(user, actionUrl) {
                toggleActionUrl = actionUrl;

                const isActive = user.is_active;

                // Isi data user di modal
                document.getElementById('toggleUserName').textContent = user.name;
                document.getElementById('toggleUserInfo').textContent = user.username + ' | ' + user.email + ' | ' + user.role_name;

                // Set border color
                const container = document.getElementById('toggleModalContainer');
                container.className = container.className.replace(/border-t-(red|green)-500/g, '');
                container.classList.add(isActive ? 'border-t-red-500' : 'border-t-green-500');

                // Set title
                document.getElementById('toggleModalTitle').textContent = isActive ? '⚠️ Non-Aktifkan User' : '✅ Aktifkan User';

                // Set action text (menonaktifkan / mengaktifkan)
                document.getElementById('toggleActionText').textContent = isActive ? 'menonaktifkan' : 'mengaktifkan';

                // Toggle warning/info blocks
                document.getElementById('toggleWarningBlock').classList.toggle('hidden', !isActive);
                document.getElementById('toggleInfoBlock').classList.toggle('hidden', isActive);

                // Set confirm button
                const confirmBtn = document.getElementById('toggleConfirmBtn');
                confirmBtn.className = 'px-4 py-2 text-white rounded text-sm font-bold shadow ' +
                    (isActive ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700');
                confirmBtn.textContent = isActive ? '🔴 Non-Aktifkan' : '🟢 Aktifkan';

                // Tampilkan modal
                document.getElementById('modalToggle').classList.remove('hidden');
            }

            function closeToggleModal() {
                document.getElementById('modalToggle').classList.add('hidden');
                toggleActionUrl = '';
            }

            function executeToggle() {
                if (!toggleActionUrl) return;

                // Disable button biar ga double-click
                const btn = document.getElementById('toggleConfirmBtn');
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');

                // Kirim POST request via form submit (biar sesuai dengan route POST)
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = toggleActionUrl;
                form.style.display = 'none';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            }

            // Tutup modal toggle jika klik di luar
            document.getElementById('modalToggle').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeToggleModal();
                }
            });

            // Tutup modal reset jika klik di luar
            document.getElementById('modalReset').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeResetModal();
                }
            });
        </script>
    </div>
</x-app-layout>
