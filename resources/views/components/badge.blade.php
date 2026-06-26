@props(['type' => 'default'])

@php
    $baseClass = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border";
    
    $colors = [
        // Role Categories
        'maker' => 'bg-blue-50 text-blue-700 border-blue-200',
        'checker' => 'bg-amber-50 text-amber-700 border-amber-200',
        'viewer' => 'bg-slate-100 text-slate-700 border-slate-200',
        'admin' => 'bg-slate-100 text-slate-700 border-slate-200',
        
        // Risk Categories
        'finansial' => 'bg-rose-100 text-rose-700 border-rose-200',
        'non-finansial' => 'bg-orange-100 text-orange-700 border-orange-200',
        
        // Risk Sources
        'manusia' => 'bg-purple-100 text-purple-700 border-purple-200',
        'proses_internal' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
        'sistem_teknologi' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'faktor_eksternal' => 'bg-slate-100 text-slate-700 border-slate-200',

        // Generic states
        'success' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'warning' => 'bg-amber-100 text-amber-700 border-amber-200',
        'danger' => 'bg-rose-100 text-rose-700 border-rose-200',
        'default' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];

    $colorClass = $colors[$type] ?? $colors['default'];
@endphp

<span {{ $attributes->merge(['class' => $baseClass . ' ' . $colorClass]) }}>
    {{ $slot }}
</span>
