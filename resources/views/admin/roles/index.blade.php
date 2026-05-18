<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
                {{ __('Manajemen Role') }}
            </h2>
            <p class="text-sm text-slate-500">Kelola role/jabatan, role category, dan permission yang tersedia di sistem.</p>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="page-shell page-stack">

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
                    <h3 class="text-lg font-bold">Daftar Role / Jabatan</h3>
                    <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm shadow">
                        + Tambah Role Baru
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Role</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Role Category</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Permissions</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Jumlah User</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Tipe</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($roles as $role)
                            @php
                                $catColors = ['maker' => 'bg-blue-100 text-blue-800', 'checker' => 'bg-purple-100 text-purple-800', 'viewer' => 'bg-gray-100 text-gray-800', 'admin' => 'bg-amber-100 text-amber-800'];
                                $catColor = $catColors[$role->role_category] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-900 uppercase">{{ $role->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 {{ $catColor }} rounded text-[10px] font-bold uppercase">
                                        {{ $role->role_category ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @forelse($role->permissions as $perm)
                                        <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 rounded text-[10px] font-medium">{{ $perm->name }}</span>
                                        @empty
                                        <span class="text-xs text-gray-400 italic">Tidak ada permission</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    {{ $role->users_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    {{ $role->guard_name ?? 'web' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                    <button onclick="openEditModal({{ $role->id }}, '{{ $role->name }}', '{{ $role->role_category }}', {{ json_encode($role->permissions->pluck('name')) }})" class="inline-block text-indigo-600 hover:text-indigo-900 font-bold uppercase text-[10px]">Edit</button>
                                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus role {{ $role->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-bold uppercase text-[10px]">Hapus</button>
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

    <script>
        function openEditModal(id, name, roleCategory, permissions) {
            document.getElementById('modalEdit').classList.remove('hidden');
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_role_category').value = roleCategory;
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
