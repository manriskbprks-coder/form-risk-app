<x-guest-layout>
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="text-center max-w-md mx-auto px-4">
            <div class="text-6xl font-extrabold text-orange-500 mb-4">419</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Sesi Habis</h1>
            <p class="text-gray-500 mb-6">Sesi Anda telah berakhir. Silakan refresh halaman dan coba lagi.</p>
            <a href="{{ route('login') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition">
                Login Ulang
            </a>
        </div>
    </div>
</x-guest-layout>
