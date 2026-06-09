<x-app-layout>
    @section('page_title', 'Riwayat Deklarasi')
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
                {{ __('Status Deklarasi Nihil Cabang') }}
            </h2>
        </div>
    </x-slot>

    <div class="pt-4 pb-8 sm:pb-12">
        <div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('risk_free_declarations.history') }}" class="mb-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
                        <div class="flex flex-col sm:flex-row items-end gap-4">
                            <div class="w-full sm:w-64">
                                <label for="filter_bulan" class="block text-xs font-semibold text-slate-600 mb-1">Filter Bulan & Tahun</label>
                                <select id="filter_bulan" name="bulan" onchange="this.form.submit()" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @foreach($availableMonths as $m)
                                        <option value="{{ $m['bulan'] }}" {{ $bulan == $m['bulan'] && $tahun == $m['tahun'] ? 'selected' : '' }}>
                                            {{ $m['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="tahun" value="{{ $tahun }}">
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th rowspan="2" class="px-4 py-3 text-left font-medium text-gray-500 border-r border-b border-gray-200">Kode</th>
                                    <th rowspan="2" class="px-4 py-3 text-left font-medium text-gray-500 border-r border-b border-gray-200">Nama Cabang</th>
                                    <th colspan="3" class="px-4 py-2 text-center font-medium text-gray-700 bg-indigo-50 border-r border-b border-gray-200">Periode 1 (Tgl 1-14)</th>
                                    <th colspan="3" class="px-4 py-2 text-center font-medium text-gray-700 bg-emerald-50 border-b border-gray-200">Periode 2 (Tgl 15-Akhir)</th>
                                </tr>
                                <tr>
                                    <!-- Kolom Periode 1 -->
                                    <th class="px-4 py-2 text-center font-medium text-gray-500 border-r border-b border-gray-200">Status</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 border-r border-b border-gray-200">Jabatan (Nihil)</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 border-r border-b border-gray-200">Keterangan</th>
                                    <!-- Kolom Periode 2 -->
                                    <th class="px-4 py-2 text-center font-medium text-gray-500 border-r border-b border-gray-200">Status</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 border-r border-b border-gray-200">Jabatan (Nihil)</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 border-b border-gray-200">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($groupedData as $data)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 text-gray-900 border-r border-gray-200">{{ $data['kode_cabang'] }}</td>
                                        <td class="px-4 py-3 text-gray-900 border-r border-gray-200 font-medium whitespace-nowrap">{{ $data['nama_cabang'] }}</td>
                                        
                                        <!-- Data Periode 1 -->
                                        <td class="px-4 py-3 text-center border-r border-gray-200">
                                            @if($data['periode1']['status'] === 'sudah')
                                                <span class="text-green-500" title="Sudah Lapor">✅</span>
                                            @else
                                                <span class="text-red-500" title="Belum Lapor">❌</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 border-r border-gray-200 max-w-xs truncate" title="{{ $data['periode1']['jabatan_nihil'] }}">
                                            {{ $data['periode1']['jabatan_nihil'] }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 border-r border-gray-200 italic text-xs max-w-xs truncate" title="{{ $data['periode1']['keterangan'] }}">
                                            {{ $data['periode1']['keterangan'] }}
                                        </td>

                                        <!-- Data Periode 2 -->
                                        <td class="px-4 py-3 text-center border-r border-gray-200">
                                            @if($data['periode2']['status'] === 'sudah')
                                                <span class="text-green-500" title="Sudah Lapor">✅</span>
                                            @else
                                                <span class="text-red-500" title="Belum Lapor">❌</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 border-r border-gray-200 max-w-xs truncate" title="{{ $data['periode2']['jabatan_nihil'] }}">
                                            {{ $data['periode2']['jabatan_nihil'] }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 italic text-xs max-w-xs truncate" title="{{ $data['periode2']['keterangan'] }}">
                                            {{ $data['periode2']['keterangan'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                            Tidak ada data cabang.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <a href="{{ route('dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                            &larr; Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
