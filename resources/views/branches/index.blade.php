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
                                     <form action="{{ route('branches.update', $branch->id) }}" method="POST" class="flex items-center justify-between gap-4 w-full">
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
                                            <button type="submit" onclick="document.getElementById('status_{{ $branch->id }}').value = '0'" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1.5 px-3 rounded text-xs transition">
                                                Non-Aktifkan
                                            </button>
                                            @else
                                            <button type="submit" onclick="document.getElementById('status_{{ $branch->id }}').value = '1'" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1.5 px-3 rounded text-xs transition">
                                                Aktifkan
                                            </button>
                                            @endif

                                            <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-1.5 px-4 rounded text-xs transition">
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
</x-app-layout>