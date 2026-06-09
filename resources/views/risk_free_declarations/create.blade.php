@php
    $periodeLabel = $periode === '1' ? '1 - 14' : '15 - ' . now()->daysInMonth;
    $bulanNama = now()->setMonth($bulan)->translatedFormat('F');
@endphp

<x-app-layout>
    @section('page_title', 'Deklarasi')
    <x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
            {{ __('Deklarasi Nihil Risiko') }}
        </h2>
    </div>
</x-slot>

    <div class="pt-4 pb-8 sm:pb-12">
        <div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Info Periode --}}
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h3 class="font-semibold text-blue-800 text-lg">Periode {{ $periode }}: Tanggal {{ $periodeLabel }} {{ $bulanNama }} {{ $tahun }}</h3>
                        <p class="text-sm text-blue-600 mt-1">
                            Dengan ini menyatakan bahwa pada periode tersebut, tidak terdapat kejadian risiko operasional (risk event / loss event) pada jabatan-jabatan di bawah ini.
                        </p>
                    </div>

                    {{-- Peringatan jika ada laporan --}}
                    @if ($adaLaporan)
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">Perhatian!</p>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        Terdapat laporan risiko yang sudah dibuat di periode ini. Jika Anda tetap melakukan deklarasi nihil, 
                                        maka deklarasi ini berpotensi ditandai sebagai <strong>Rejected</strong> oleh ManRisk jika laporan tersebut terbukti valid.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('risk_free_declarations.store') }}" x-data="{ 
                        allClean: true,
                        agreed: false,
                        toggleAll() {
                            this.allClean = !this.allClean;
                            document.querySelectorAll('.jabatan-checkbox').forEach(cb => cb.checked = this.allClean);
                        }
                    }">
                        @csrf

                        {{-- Hidden Input untuk memenuhi validasi backend --}}
                        <input type="hidden" name="statement_text" value="Saya yang bertanda tangan di bawah ini, selaku Kepala Cabang, dengan ini menyatakan dengan sesungguhnya bahwa pada periode ini tidak terdapat kejadian risiko operasional pada seluruh jabatan di cabang saya. Apabila di kemudian hari terbukti terdapat kejadian risiko yang tidak dilaporkan, saya bersedia mempertanggungjawabkan sesuai dengan ketentuan yang berlaku.">

                        {{-- Tabel Checklist Per Jabatan --}}
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-medium text-gray-700">Checklist Jabatan</h4>
                                <button type="button" @click="toggleAll()" class="text-sm text-blue-600 hover:text-blue-800 underline">
                                    Toggle Semua
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 border rounded-lg">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Nihil Risiko</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($jabatanList as $jabatan)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $jabatan }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="hidden" name="jabatan[{{ $jabatan }}][is_clean]" value="0">
                                                <input type="checkbox" 
                                                    name="jabatan[{{ $jabatan }}][is_clean]" 
                                                    value="1"
                                                    checked
                                                    class="jabatan-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text" 
                                                    name="jabatan[{{ $jabatan }}][keterangan]" 
                                                    placeholder="Opsional"
                                                    class="w-full text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @error('jabatan')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Statement Tanggung Jawab --}}
                        <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Pernyataan Tanggung Jawab <span class="text-red-500">*</span>
                            </label>
                            <div class="text-sm text-gray-700 leading-relaxed space-y-2">
                                <p>
                                    Saya yang bertanda tangan di bawah ini, selaku <strong>Kepala Cabang</strong>, dengan ini menyatakan dengan sesungguhnya bahwa pada periode ini <strong>tidak terdapat kejadian risiko operasional (risk event / loss event)</strong> pada seluruh jabatan di cabang saya.
                                </p>
                                <p>
                                    Apabila di kemudian hari terbukti terdapat <strong>risk event / loss event yang tidak dilaporkan</strong> pada periode ini, <strong>saya bersedia mempertanggungjawabkan</strong> sesuai dengan ketentuan dan peraturan yang berlaku di perusahaan.
                                </p>
                            </div>
                        </div>

                        {{-- Checkbox Persetujuan --}}
                        <div class="mb-6">
                            <label class="inline-flex items-start">
                                <input type="checkbox" x-model="agreed" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5 mt-0.5">
                                <span class="ml-2 text-sm text-gray-700">
                                    Saya menyetujui dan menyatakan kebenaran pernyataan tanggung jawab di atas. <span class="text-red-500">*</span>
                                </span>
                            </label>
                        </div>

                        {{-- Tombol Submit --}}
                        <div class="flex items-center justify-between">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800">
                                ← Kembali ke Dashboard
                            </a>
                            <button type="submit" 
                                :disabled="!agreed"
                                :class="agreed ? 'bg-blue-600 hover:bg-blue-700 cursor-pointer' : 'bg-gray-400 cursor-not-allowed'"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                Simpan Deklarasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
