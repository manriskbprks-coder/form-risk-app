<x-app-layout>
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="text-center max-w-md mx-auto px-4">
            <div class="text-6xl font-extrabold text-gray-400 mb-4">404</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Halaman Tidak Ditemukan</h1>
            <p class="text-gray-500 mb-6">Halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>
            <a href="{{ route('dashboard') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
