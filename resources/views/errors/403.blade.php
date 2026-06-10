<x-guest-layout>
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="text-center max-w-md mx-auto px-4">
            <div class="text-6xl font-extrabold text-red-500 mb-4">403</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ __($exception->getMessage() ?: 'Akses Ditolak') }}</h1>
            <p class="text-gray-500 mb-6">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
            <a href="{{ route('dashboard') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</x-guest-layout>
