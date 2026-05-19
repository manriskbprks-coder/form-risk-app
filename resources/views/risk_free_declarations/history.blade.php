@php
    use App\Models\RiskFreeDeclaration;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Deklarasi Nihil Risiko') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if ($declarations->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-2 text-gray-500">Belum ada deklarasi nihil risiko.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cabang</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bulan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deklarator</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detail</th>
                                        @role('manrisk')
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                        @endrole
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($declarations as $dec)
                                    @php
                                        $bulanNama = now()->setMonth($dec->bulan)->translatedFormat('F');
                                        $periodeLabel = $dec->periode === '1' ? '1-14' : '15-' . now()->setMonth($dec->bulan)->daysInMonth;
                                        $statusBadge = match($dec->status) {
                                            'active' => ['bg-green-100 text-green-800', 'Aktif'],
                                            'rejected' => ['bg-red-100 text-red-800', 'Rejected'],
                                            'cancelled' => ['bg-gray-100 text-gray-800', 'Dibatalkan'],
                                            default => ['bg-gray-100 text-gray-800', $dec->status],
                                        };
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $dec->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $dec->branch->nama_cabang ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $periodeLabel }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $bulanNama }} {{ $dec->tahun }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $dec->user->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[0] }}">
                                                {{ $statusBadge[1] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            <button type="button" 
                                                onclick="document.getElementById('detail-{{ $dec->id }}').classList.toggle('hidden')"
                                                class="text-blue-600 hover:text-blue-800 underline text-xs">
                                                Lihat Detail
                                            </button>
                                        </td>
                                        @role('manrisk')
                                        <td class="px-4 py-3 text-center">
                                            @if ($dec->status === 'active')
                                            <form method="POST" action="{{ route('risk_free_declarations.reject', $dec->id) }}" 
                                                onsubmit="return confirm('Yakin ingin menolak deklarasi ini?')">
                                                @csrf
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-800 underline">
                                                    Tolak (Reject)
                                                </button>
                                            </form>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                        @endrole
                                    </tr>
                                    <tr id="detail-{{ $dec->id }}" class="hidden">
                                        <td colspan="{{ auth()->user()->hasRole('manrisk') ? 8 : 7 }}" class="px-4 py-3 bg-gray-50">
                                            <div class="text-sm">
                                                <p class="font-medium text-gray-700 mb-2">Pernyataan:</p>
                                                <p class="text-gray-600 mb-3 italic">"{{ $dec->statement_text }}"</p>
                                                
                                                <p class="font-medium text-gray-700 mb-2">Checklist Jabatan:</p>
                                                <table class="min-w-full text-xs">
                                                    <thead>
                                                        <tr class="border-b">
                                                            <th class="text-left py-1 pr-4">Jabatan</th>
                                                            <th class="text-left py-1 pr-4">Status</th>
                                                            <th class="text-left py-1">Keterangan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($dec->details as $detail)
                                                        <tr class="border-b border-gray-200">
                                                            <td class="py-1 pr-4 font-medium">{{ $detail->jabatan }}</td>
                                                            <td class="py-1 pr-4">
                                                                @if ($detail->is_clean)
                                                                    <span class="text-green-600">Nihil</span>
                                                                @else
                                                                    <span class="text-red-600">Ada Risiko</span>
                                                                @endif
                                                            </td>
                                                            <td class="py-1">{{ $detail->keterangan ?? '-' }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>

                                                @if ($dec->rejected_at)
                                                <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-xs">
                                                    <span class="font-medium text-red-700">Ditolak pada:</span> 
                                                    {{ $dec->rejected_at->format('d/m/Y H:i') }} 
                                                    oleh {{ $dec->rejecter->name ?? 'Unknown' }}
                                                </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $declarations->links() }}
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800">
                            ← Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
