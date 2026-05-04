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
        <div class="flex items-center gap-3 px-5 h-16 border-b border-slate-100 shrink-0">
            <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-extrabold text-sm shadow-sm">
                {{ Str::substr(config('app.name', 'BPR'), 0, 2) }}
            </div>
            <div class="leading-tight">
                <span class="block text-sm font-bold text-slate-900">{{ config('app.name', 'BPR') }}</span>
                <span class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider">Risk Management</span>
            </div>
        </div>

        {{-- User Info --}}
        <div class="px-4 py-4 border-b border-slate-100 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ Str::substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[11px] text-slate-400 font-medium truncate uppercase">
                        {{ Auth::user()->primaryRoleName() ?? '—' }}
                        &middot;
                        {{ Auth::user()->branch->nama_cabang ?? 'Pusat' }}
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
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>

            @hasanyrole('teller|ca|csr|security|kacab')
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
            @endhasanyrole

            @hasanyrole('kacab|korwil')
            <a href="{{ route('review.laporan') }}"
               class="{{ request()->routeIs('review.laporan') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Review & Tindak Lanjut</span>
                @if(auth()->user()?->hasRole('kacab'))
                @php
                    $pendingReviewCount = \App\Models\RiskReport::where('branch_id', auth()->user()->branch_id)
                        ->whereIn('approval_status', ['pending_kacab', 'need_revision'])
                        ->count();
                @endphp
                @if($pendingReviewCount > 0)
                <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm">
                    {{ $pendingReviewCount > 99 ? '99+' : $pendingReviewCount }}
                </span>
                @endif
                @endif
            </a>
            @endhasanyrole

            @hasanyrole('korwil|manrisk')
            <a href="{{ route('risk.history') }}"
               class="{{ request()->routeIs('risk.history') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Monitoring</span>
            </a>
            @endhasanyrole

            @hasrole('manrisk')
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

            <a href="{{ route('branches.index') }}"
               class="{{ request()->routeIs('branches.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                <svg class="sidebar-link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Manajemen Cabang</span>
            </a>
            @endhasrole
        </nav>

        {{-- Logout --}}
        <div class="px-3 py-3 border-t border-slate-100 shrink-0">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
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
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-lg border-b border-slate-200 shadow-xs">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">

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
                    {{-- Bell Icon --}}
                    <a href="{{ route('notifications.index') }}" 
                       class="relative p-2 rounded-lg hover:bg-slate-100 transition group"
                       x-data="{ unread: 0 }"
                       x-init="
                           fetch('{{ route('notifications.unread_count') }}')
                               .then(r => r.json())
                               .then(d => { unread = d.count; });
                           setInterval(() => {
                               fetch('{{ route('notifications.unread_count') }}')
                                   .then(r => r.json())
                                   .then(d => { unread = d.count; });
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

                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition group">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-semibold text-slate-800 group-hover:text-indigo-700 transition">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">{{ Auth::user()->primaryRoleName() ?? '—' }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ Str::substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </a>
                </div>
            </div>
        </header>

        {{-- PAGE HEADER --}}
        @isset($header)
        <div class="bg-white border-b border-slate-100">
            <div class="page-shell py-5 sm:py-6">
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
                <p class="flex items-center gap-1">
                    <span>Built with</span>
                    <svg class="w-3.5 h-3.5 text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                    <span>by BPR Dev Team</span>
                </p>
            </div>
        </footer>
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

    @stack('scripts')
</body>
</html>
