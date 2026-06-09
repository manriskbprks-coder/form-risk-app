@props(['skala'])

@php
    $skalaColors = [
        'Sangat Tinggi' => 'bg-red-500 text-red-100 border-red-300',
        'Tinggi'        => 'bg-red-100 text-red-800 border-red-400',
        'Sedang'        => 'bg-yellow-100 text-yellow-800 border-yellow-400',
        'Rendah'        => 'bg-green-100 text-green-700 border-green-400',
        'Sangat Rendah' => 'bg-green-500 text-green-100 border-green-300',
    ];
    $colorClass = $skalaColors[$skala] ?? 'bg-gray-100 text-gray-800 border-gray-400';
@endphp

<span {{ $attributes->merge(['class' => "px-2 py-0.5 text-[10px] font-bold uppercase rounded border {$colorClass}"]) }}>
    {{ $skala ?: 'Tidak ada skala' }}
</span>
