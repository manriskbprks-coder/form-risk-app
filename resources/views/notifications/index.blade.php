<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
                    {{ __('Notifikasi') }}
                </h2>
                <p class="text-sm text-slate-500">Semua pemberitahuan terkait laporan risiko Anda.</p>
            </div>
            @if($notifications->total() > 0)
            <form action="{{ route('notifications.mark_all_read') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex w-full sm:w-auto justify-center bg-indigo-600 hover:bg-indigo-800 text-white font-bold py-2 px-4 rounded text-sm shadow transition">
                    Tandai Semua Dibaca
                </button>
            </form>
            @endif
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-3">
                @forelse($notifications as $notification)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 {{ $notification->is_read ? 'border-l-gray-300' : 'border-l-indigo-500' }} transition hover:shadow-md">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    @if(!$notification->is_read)
                                    <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                    @endif
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded 
                                        @switch($notification->type)
                                            @case('new_report') bg-blue-100 text-blue-700 @break
                                            @case('approved') bg-green-100 text-green-700 @break
                                            @case('rejected') bg-red-100 text-red-700 @break
                                            @case('closed') bg-gray-800 text-white @break
                                            @default bg-gray-100 text-gray-600 @endswitch
                                    ">
                                        @switch($notification->type)
                                            @case('new_report') Laporan Baru @break
                                            @case('approved') Disetujui @break
                                            @case('rejected') Ditolak @break
                                            @case('closed') Ditutup @break
                                            @default {{ $notification->type }} @endswitch
                                    </span>
                                </div>
                                <p class="text-sm text-gray-800 {{ $notification->is_read ? 'font-normal' : 'font-semibold' }}">
                                    {{ $notification->message }}
                                </p>
                            </div>
                            <div class="shrink-0">
                                <a href="{{ route('notifications.read', $notification->id) }}" 
                                   class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded transition whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Lihat
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-10 text-center">
                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-600">Belum Ada Notifikasi</p>
                        <p class="text-xs text-gray-400 mt-1">Notifikasi akan muncul di sini saat ada aktivitas terkait laporan Anda.</p>
                    </div>
                </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
