<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>429 - Terlalu Banyak Permintaan</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-slate-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center max-w-md mx-auto px-4">
            <div class="text-8xl font-extrabold text-red-500 mb-4">429</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ __($message ?? 'Terlalu Banyak Permintaan') }}</h1>
            <p class="text-gray-500 mb-6">Silakan coba lagi dalam {{ $retry_after ?? 60 }} detik.</p>
            <a href="{{ route('login') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition">
                Kembali ke Halaman Login
            </a>
        </div>
    </div>
</body>
</html>
