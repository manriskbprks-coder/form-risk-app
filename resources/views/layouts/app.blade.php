<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="appState()" x-init="init()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'BPR Reporting')) — {{ config('app.name') }}</title>

    <!-- Fonts: Inter (premium modern sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Driver.js CDN (Onboarding Tour) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">

    @stack('styles')
</head>
<body class="font-sans antialiased overflow-x-hidden text-slate-800 bg-slate-50">

    {{-- =============================================================
         SIDEBAR — Overlay on mobile, fixed on desktop
         ============================================================= --}}
    <aside
        id="sidebar"
        class="fixed top-0 left-0 z-30 h-full w-64 lg:w-60 bg-white border-r border-slate-200 shadow-sm transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out flex flex-col overflow-y-auto"
        :class="{ 'translate-x-0': sidebarOpen }"
    >
        {{-- Brand / Logo --}}
        <div class="flex items-center gap-3 px-10 h-16 border-b border-slate-200 shrink-0">
            <img src="{{ asset('images/bpr-tulisan-landscape.png') }}" 
                alt="{{ config('app.name') }}" 
                class="h-40 w-auto">
        </div>

        {{-- User Info --}}
        <div class="px-4 border-b border-slate-200 shrink-0 h-20 flex items-center">
            <div class="flex items-center gap-3 w-full">
                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ Str::substr(Auth::user()?->name ?? 'U', 0, 1) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate">{{ Auth::user()?->name ?? 'Guest' }}</p>
                    <p class="text-[11px] text-slate-400 font-medium truncate uppercase">
                        {{ Auth::user()?->primaryRoleName() ?? '—' }}
                        &middot;
                        {{ Auth::user()?->branch->nama_cabang ?? 'Pusat' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

            <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.14em] mb-2 mt-1">Menu Utama</p>

            {{-- Dashboard — semua role --}}
            <a href="{{ route('dashboard') }}"
               class="{{ request()->routeIs('dashboard') ? 'sidebar-link-active' : 'sidebar-link' }}">
                @if(Auth::check() && Auth::user()->isAdmin())
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
                @else
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
                <span>Home</span>
                @endif
            </a>

            @if(Auth::check() && Auth::check() && Auth::user()->canCreateReport())
            <a href="{{ route('risk.history') }}"
               class="{{ request()->routeIs('risk.history') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Riwayat Saya</span>
            </a>

            <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.14em] mb-2 mt-6">Form</p>

            <a href="{{ route('form.risiko', 'finansial') }}"
               class="{{ request()->routeIs('form.risiko') && request()->route('kategori') == 'finansial' ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Risiko Finansial</span>
            </a>

            <a href="{{ route('form.risiko', 'non-finansial') }}"
               class="{{ request()->routeIs('form.risiko') && request()->route('kategori') == 'non-finansial' ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Risiko Non-Finansial</span>
            </a>
            @endif

            @if(Auth::check() && Auth::user()->isChecker())
            <a href="{{ route('review.laporan') }}"
               class="{{ request()->routeIs('review.laporan') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Review & Tindak Lanjut</span>
                @php
                    $pendingReviewCount = \App\Models\RiskReport::where('branch_id', auth()->user()->branch_id)
                        ->whereIn('status', ['pending_atasan', 'pending_revision', 'approved_in_progress'])
                        ->count();
                @endphp
                @if($pendingReviewCount > 0)
                <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm">
                    {{ $pendingReviewCount > 99 ? '99+' : $pendingReviewCount }}
                </span>
                @endif
            </a>

            @php
                $now = now();
                $day = $now->day;
                $bulan = $now->month;
                $tahun = $now->year;
                $periode = $day <= 14 ? '1' : '2';
                $sudahDeklarasi = \App\Models\RiskFreeDeclaration::where('branch_id', auth()->user()->branch_id)
                    ->where('periode', $periode)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->exists();
            @endphp
            <a href="{{ $sudahDeklarasi ? route('risk_free_declarations.history') : route('risk_free_declarations.create') }}"
               class="{{ request()->routeIs('risk_free_declarations.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="flex-1">Deklarasi Nihil Risiko</span>
                @if ($sudahDeklarasi)
                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold text-emerald-700 bg-emerald-100 rounded-full">Sudah</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold text-blue-700 bg-blue-100 rounded-full">Periode {{ $periode }}</span>
                @endif
            </a>
            @endif

            @if(Auth::check() && (Auth::check() && Auth::user()->isViewer() || Auth::check() && Auth::user()->isAdmin()))
            <a href="{{ route('risk.history') }}"
               class="{{ request()->routeIs('risk.history') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Monitoring</span>
            </a>
            @endif


            @if(Auth::check() && Auth::check() && Auth::user()->isAdmin())
            <a href="{{ route('risk_free_declarations.history') }}"
               class="{{ request()->routeIs('risk_free_declarations.history') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Riwayat Deklarasi</span>
            </a>

            <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.14em] mb-2 mt-6">Administrasi</p>

            <a href="{{ route('admin.risk_master.index') }}"
               class="{{ request()->routeIs('admin.risk_master.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Master Data Risiko</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="{{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Manajemen Pengguna</span>
            </a>

            <a href="{{ route('admin.roles.index') }}"
               class="{{ request()->routeIs('admin.roles.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Manajemen Role</span>
            </a>

            <a href="{{ route('admin.divisions.index') }}"
               class="{{ request()->routeIs('admin.divisions.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Manajemen Divisi</span>
            </a>

            <a href="{{ route('branches.index') }}"
               class="{{ request()->routeIs('branches.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Manajemen Cabang</span>
            </a>
            @endif

            <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.14em] mb-2 mt-6">Bantuan</p>

            <a href="{{ route('glosarium') }}"
               class="{{ request()->routeIs('glosarium') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Glosarium</span>
            </a>

            <button type="button" onclick="startOnboardingTour()" class="sidebar-link w-full text-left">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                </svg>
                <span>Panduan Aplikasi</span>
            </button>
        </nav>

    </aside>

    {{-- =============================================================
         SIDEBAR OVERLAY (mobile only)
         ============================================================= --}}
    <div
        x-show="sidebarOpen"
        class="fixed inset-0 z-20 bg-slate-900/40 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
        x-transition.opacity
        style="display: none;"
    ></div>

    {{-- =============================================================
         MAIN CONTENT AREA
         ============================================================= --}}
    <div id="main-content" class="lg:pl-60 min-h-screen flex flex-col">

        {{-- TOP NAVBAR --}}
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-lg border-b border-slate-200 shadow-xs h-16">
            <div class="flex items-center justify-between h-full px-4 sm:px-6 lg:px-8">

                {{-- Left: Hamburger + Page Title --}}
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden -ml-1 p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 truncate tracking-tight">
                        @yield('page_title', 'Dashboard')
                    </h1>
                </div>

                {{-- Right: Notifications + Profile --}}
                <div class="flex items-center gap-3">
                    {{-- Tour Button (Header) --}}
                    <button onclick="startOnboardingTour()" class="p-2 rounded-lg hover:bg-slate-100 transition group" title="Panduan Aplikasi">
                        <svg class="w-5 h-5 text-slate-500 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                        </svg>
                    </button>

                    {{-- Bell Icon --}}
                    <a href="{{ route('notifications.index') }}" 
                       class="relative p-2 rounded-lg hover:bg-slate-100 transition group"
                       x-data="{ unread: 0 }"
                       x-init="
                           fetch('{{ route('notifications.unread_count') }}', {
                               headers: {
                                   'Accept': 'application/json',
                                   'X-Requested-With': 'XMLHttpRequest'
                               }
                           })
                               .then(r => r.json())
                               .then(d => { unread = d.count; })
                               .catch(e => console.log('Notif polling error:', e));
                           setInterval(() => {
                               fetch('{{ route('notifications.unread_count') }}', {
                                   headers: {
                                       'Accept': 'application/json',
                                       'X-Requested-With': 'XMLHttpRequest'
                                   }
                               })
                                   .then(r => r.json())
                                   .then(d => { unread = d.count; })
                                   .catch(e => console.log('Notif polling error:', e));
                           }, 30000);
                       ">
                        <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="unread > 0" 
                              x-text="unread > 99 ? '99+' : unread"
                              class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm border-2 border-white"
                              style="display: none;">
                        </span>
                    </a>

                    {{-- Profile Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition group">
                            <div class="hidden sm:block text-right">
                                <p class="text-sm font-semibold text-slate-800 group-hover:text-indigo-700 transition">{{ Auth::user()?->name ?? 'Guest' }}</p>
                                <p class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">{{ Auth::user()?->primaryRoleName() ?? '—' }}</p>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ Str::substr(Auth::user()?->name ?? 'U', 0, 1) }}
                            </div>
                            <svg class="w-4 h-4 text-slate-400 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1.5 z-50"
                             style="display: none;">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-indigo-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Profile</span>
                            </a>
                            <hr class="mx-3 border-slate-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-rose-50 hover:text-rose-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- PAGE HEADER --}}
        @isset($header)
        <div class="bg-white border-b border-slate-200 min-h-[5rem] py-4 sm:py-0 flex items-center">
            <div class="page-shell w-full">
                {{ $header }}
            </div>
        </div>
        @endisset

        {{-- PAGE CONTENT --}}
        <main class="flex-1">
            <div class="page-shell py-6 sm:py-8 lg:py-10">
                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="mb-6 alert-success flex items-center gap-3" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="flex-1">{{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 font-bold">&times;</button>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 alert-error flex items-center gap-3" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <svg class="w-5 h-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="flex-1">{{ session('error') }}</span>
                    <button @click="show = false" class="text-rose-600 hover:text-rose-800 font-bold">&times;</button>
                </div>
                @endif

                {{ $slot }}
            </div>
        </main>

        {{-- FOOTER --}}
        <footer class="border-t border-slate-100 bg-white">
            <div class="page-shell py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'BPR') }} — Risk Management System</p>
            <!--
                <p class="flex items-center gap-1">
                    <span>Built with</span>
                    <svg class="w-3.5 h-3.5 text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                    <span>by Ray Amadisky</span>
                </p>
            -->
            </div>
        </footer>
    </div>

    {{-- MODAL KONFIRMASI LEWATI PANDUAN --}}
    <div id="tourConfirmModal" class="fixed inset-0 z-[999999] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/75 backdrop-blur-sm" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-amber-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-bold leading-6 text-slate-900" id="modal-title">
                            Lewati Panduan?
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500">
                                Apakah Anda yakin ingin melewati panduan aplikasi ini? Anda bisa membukanya kembali kapan saja melalui tombol "Panduan Aplikasi" di menu samping.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <button type="button" onclick="confirmSkipTour()" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-xl shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:w-auto sm:text-sm transition-colors">
                        Ya, Lewati
                    </button>
                    <button type="button" onclick="cancelSkipTour()" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-slate-700 bg-white border border-slate-300 rounded-xl shadow-sm hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- =============================================================
         ALPINE.JS APP STATE
         ============================================================= --}}
    <script>
        function appState() {
            return {
                sidebarOpen: false,
                init() {
                    // Close sidebar on route change (mobile)
                    if (window.innerWidth < 1024) {
                        this.sidebarOpen = false;
                    }
                }
            }
        }
    </script>

    {{-- Driver.js Library --}}
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>

    {{-- Onboarding Tour Script --}}
    <script>
        function getTourSteps() {
            const role = @js(auth()->user()?->roleCategory() ?? 'maker');
            const roleName = @js(auth()->user()?->primaryRoleName() ?? 'Pengguna');

            const commonSteps = [
                {
                    element: '#sidebar',
                    popover: {
                        title: '📌 Menu Navigasi',
                        description: 'Ini adalah menu utama aplikasi. Semua halaman penting bisa diakses dari sini.',
                        side: 'right',
                        align: 'start'
                    }
                }
            ];

            if (role === 'maker') {
                return [
                    ...commonSteps,
                    {
                        popover: {
                            title: '👋 Selamat Datang, ' + roleName + '!',
                            description: 'Anda berperan sebagai ' + roleName + '. Tugas utama Anda adalah melaporkan setiap kejadian risiko yang terjadi di cabang, baik yang berdampak uang (Finansial) maupun tidak (Non-Finansial).',
                        }
                    },
                    {
                        element: 'a[href*="form-risiko/finansial"]',
                        popover: {
                            title: '💸 Lapor Risiko Finansial',
                            description: 'Klik menu ini jika terjadi kejadian yang merugikan bank secara uang. Contoh: uang palsu masuk, selisih kas, kredit macet.',
                            side: 'right'
                        }
                    },
                    {
                        element: 'a[href*="form-risiko/non-finansial"]',
                        popover: {
                            title: '⚠️ Lapor Risiko Non-Finansial',
                            description: 'Klik menu ini jika terjadi kejadian yang tidak merugikan uang tapi mempengaruhi operasional. Contoh: mesin rusak, komplain nasabah.',
                            side: 'right'
                        }
                    },
                    {
                        element: 'a[href*="riwayat-risiko"]',
                        popover: {
                            title: '📋 Riwayat Laporan Saya',
                            description: 'Pantau status semua laporan yang pernah Anda buat di sini. Anda bisa melihat apakah laporan sudah di-approve, butuh revisi, atau sudah selesai.',
                            side: 'right'
                        }
                    },
                    {
                        popover: {
                            title: '🚨 Perhatikan Alert Revisi!',
                            description: 'Jika atasan Anda menolak laporan, akan muncul kotak merah di Dashboard bertuliskan "BUTUH REVISI". Segera klik untuk memperbaiki laporan Anda.',
                        }
                    },
                    {
                        popover: {
                            title: '✅ Panduan Selesai!',
                            description: 'Anda siap menggunakan aplikasi ini. Jika butuh bantuan, klik tombol "Panduan Aplikasi" di sidebar atau ikon (?) di kanan atas kapan saja. Selamat bekerja!',
                        }
                    }
                ];
            }

            if (role === 'checker') {
                return [
                    ...commonSteps,
                    {
                        popover: {
                            title: '👋 Selamat Datang, ' + roleName + '!',
                            description: 'Anda berperan sebagai ' + roleName + '. Selain me-review laporan dari Staff, Anda juga bisa langsung melaporkan kejadian risiko sendiri.',
                        }
                    },
                    {
                        element: 'a[href*="form-risiko/finansial"]',
                        popover: {
                            title: '💸 Lapor Risiko Finansial',
                            description: 'Anda juga bisa langsung membuat laporan jika terjadi kejadian yang merugikan bank secara uang. Contoh: uang palsu, selisih kas.',
                            side: 'right'
                        }
                    },
                    {
                        element: 'a[href*="form-risiko/non-finansial"]',
                        popover: {
                            title: '⚠️ Lapor Risiko Non-Finansial',
                            description: 'Atau buat laporan kejadian yang tidak merugikan uang tapi mempengaruhi operasional. Contoh: mesin rusak, komplain nasabah.',
                            side: 'right'
                        }
                    },
                    {
                        element: 'a[href*="review-laporan"]',
                        popover: {
                            title: '✅ Review & Tindak Lanjut',
                            description: 'Di sini Anda bisa melihat daftar laporan yang menunggu review. Klik "Review Cepat" untuk membaca detail dan melakukan Approve atau Reject.',
                            side: 'right'
                        }
                    },
                    {
                        element: 'a[href*="deklarasi-nihil"]',
                        popover: {
                            title: '🛡️ Deklarasi Nihil Risiko (WAJIB!)',
                            description: 'Jika tidak ada insiden dalam 2 minggu, Anda WAJIB menekan tombol ini untuk mendeklarasikan bahwa cabang Anda aman. Dilakukan 2x sebulan (Periode 1: tgl 1-14, Periode 2: tgl 15-akhir bulan).',
                            side: 'right'
                        }
                    },
                    {
                        popover: {
                            title: '⚠️ Jangan Lupa Close Laporan!',
                            description: 'Setelah masalah di lapangan selesai, jangan lupa update status laporan menjadi "Closed". Laporan yang menggantung (In Progress) akan terus muncul di Dashboard Anda.',
                        }
                    },
                    {
                        popover: {
                            title: '✅ Panduan Selesai!',
                            description: 'Anda siap menggunakan aplikasi ini. Jika butuh bantuan, klik tombol "Panduan Aplikasi" di sidebar atau ikon (?) di kanan atas kapan saja. Selamat bekerja!',
                        }
                    }
                ];
            }

            if (role === 'viewer') {
                return [
                    ...commonSteps,
                    {
                        popover: {
                            title: '👋 Selamat Datang, ' + roleName + '!',
                            description: 'Anda berperan sebagai ' + roleName + '. Tugas Anda adalah memantau tren risiko dan insiden kritis dari cabang-cabang di bawah wilayah Anda.',
                        }
                    },
                    {
                        element: 'a[href*="riwayat-risiko"]',
                        popover: {
                            title: '📊 Monitoring Wilayah',
                            description: 'Klik menu ini untuk melihat seluruh daftar laporan risiko dari cabang-cabang Anda dalam bentuk tabel lengkap. Anda bisa filter berdasarkan status, cabang, dan waktu.',
                            side: 'right'
                        }
                    },
                    {
                        popover: {
                            title: '🚨 Perhatikan Red Alert!',
                            description: 'Di sisi kanan Dashboard, akan muncul kotak merah jika ada insiden kritis (kerugian > Rp 100 Juta atau skala dampak Tinggi/Sangat Tinggi) dari cabang Anda. Segera koordinasi dengan Kacab terkait.',
                        }
                    },
                    {
                        popover: {
                            title: '✅ Panduan Selesai!',
                            description: 'Anda siap menggunakan aplikasi ini. Jika butuh bantuan, klik tombol "Panduan Aplikasi" di sidebar atau ikon (?) di kanan atas kapan saja. Selamat bekerja!',
                        }
                    }
                ];
            }

            // admin
            return [
                ...commonSteps,
                {
                    popover: {
                        title: '👋 Selamat Datang, ' + roleName + '!',
                        description: 'Anda berperan sebagai ' + roleName + '. Anda memiliki akses penuh ke seluruh fitur: analisis data, kelola Master Data, User Management, dan pengawasan kepatuhan Deklarasi Nihil.',
                    }
                },
                {
                    element: 'a[href*="risk-master"]',
                    popover: {
                        title: '⚙️ Master Data Risiko',
                        description: 'Kelola Bank Soal Risiko di sini: tambah Item Risiko, Penyebab, dan Mitigasi. Data ini akan menjadi pilihan dropdown saat Staff mengisi form laporan.',
                        side: 'right'
                    }
                },
                {
                    element: 'a[href*="admin/users"]',
                    popover: {
                        title: '👥 Manajemen Pengguna',
                        description: 'Buat akun baru, nonaktifkan akun user yang resign, atau reset password user yang lupa sandi.',
                        side: 'right'
                    }
                },
                {
                    element: 'a[href*="riwayat-risiko"]',
                    popover: {
                        title: '📊 Monitoring Seluruh Cabang',
                        description: 'Lihat daftar semua laporan risiko dari seluruh cabang dalam bentuk tabel. Anda juga bisa mengekspor data ke CSV/Excel untuk bahan rapat Direksi.',
                        side: 'right'
                    }
                },
                {
                    popover: {
                        title: '✅ Panduan Selesai!',
                        description: 'Anda siap menggunakan aplikasi ini. Jika butuh bantuan, klik tombol "Panduan Aplikasi" di sidebar atau ikon (?) di kanan atas kapan saja. Selamat bekerja!',
                    }
                }
            ];
        }

        let driverObjInstance = null;
        let pausedTourStepIndex = 0;

        function startOnboardingTour(startIndex = 0) {
            driverObjInstance = window.driver.js.driver({
                showProgress: true,
                showButtons: ['next', 'previous', 'close'],
                nextBtnText: 'Selanjutnya →',
                prevBtnText: '← Sebelumnya',
                doneBtnText: 'Selesai ✅',
                progressText: 'Langkah @{{current}} dari @{{total}}',
                popoverClass: 'driverjs-theme-custom',
                steps: getTourSteps(),
                onHighlightStarted: (element) => {
                    if (window.innerWidth < 1024) {
                        const isSidebarEl = element && element.closest && element.closest('#sidebar');
                        const htmlData = window.Alpine ? window.Alpine.$data(document.documentElement) : null;
                        if (htmlData) {
                            htmlData.sidebarOpen = !!isSidebarEl;
                        }
                    }
                },
                onDestroyStarted: () => {
                    if (window.innerWidth < 1024) {
                        const htmlData = window.Alpine ? window.Alpine.$data(document.documentElement) : null;
                        if (htmlData) htmlData.sidebarOpen = false;
                    }
                    if (!driverObjInstance.hasNextStep()) {
                        driverObjInstance.destroy();
                        markTourAsCompleted();
                    } else {
                        // Simpan step aktif, matikan driver sementara supaya modal bebas dari overlay
                        pausedTourStepIndex = driverObjInstance.getActiveIndex();
                        driverObjInstance.destroy();
                        document.getElementById('tourConfirmModal').classList.remove('hidden');
                    }
                }
            });
            driverObjInstance.drive(startIndex);
        }

        function confirmSkipTour() {
            document.getElementById('tourConfirmModal').classList.add('hidden');
            // Tour sudah dimatikan di onDestroyStarted, cukup tandai selesai
            markTourAsCompleted();
        }

        function cancelSkipTour() {
            document.getElementById('tourConfirmModal').classList.add('hidden');
            // Mulai lagi tour-nya dari step yang disimpan tadi
            startOnboardingTour(pausedTourStepIndex);
        }

        function markTourAsCompleted() {
            fetch('{{ route("user.finish_tour") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).catch(err => console.log('Tour status update error:', err));
        }

        // Auto-trigger tour for new users
        document.addEventListener('DOMContentLoaded', function() {
            const hasSeenTour = @js(auth()->user()?->has_seen_tour ?? false);
            const isOnDashboard = window.location.pathname === '/dashboard';
            if (!hasSeenTour && isOnDashboard) {
                setTimeout(() => startOnboardingTour(), 800);
            }
        });
    </script>

    {{-- Custom Driver.js Styling --}}
    <style>
        .driver-popover.driverjs-theme-custom {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
            max-width: 380px;
        }
        .driver-popover.driverjs-theme-custom .driver-popover-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
        }
        .driver-popover.driverjs-theme-custom .driver-popover-description {
            font-size: 13px;
            color: #475569;
            line-height: 1.6;
        }
        .driver-popover.driverjs-theme-custom .driver-popover-progress-text {
            font-size: 11px;
            color: #94a3b8;
        }
        .driver-popover.driverjs-theme-custom button {
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 14px;
        }
        .driver-popover.driverjs-theme-custom .driver-popover-navigation-btns .driver-popover-next-btn {
            background-color: #4f46e5;
            color: #fff;
            border: none;
            text-shadow: none;
        }
        .driver-popover.driverjs-theme-custom .driver-popover-navigation-btns .driver-popover-next-btn:hover {
            background-color: #4338ca;
        }
        .driver-popover.driverjs-theme-custom .driver-popover-navigation-btns .driver-popover-prev-btn {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
    </style>

    @stack('scripts')
</body>
</html>
