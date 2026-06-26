<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Import Selesai
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-8 bg-white border-b border-gray-200 text-center">
                
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h3 class="text-2xl font-bold text-gray-900 mb-2">Proses Import Berhasil!</h3>
                <p class="text-gray-600 mb-6 text-lg">{{ session('success') }}</p>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 max-w-2xl mx-auto mb-8">
                    <h4 class="font-semibold text-blue-900 mb-2">Langkah Selanjutnya:</h4>
                    <p class="text-blue-800 mb-4">Sistem telah membuatkan password acak untuk setiap user yang diimpor. Silakan download file CSV di bawah ini untuk mendapatkan daftar lengkap Username dan Password mentah mereka.</p>
                    
                    <a href="{{ route('admin.users.import.download', ['filename' => $filename]) }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download File Kredensial (CSV)
                    </a>
                </div>

                <a href="{{ route('admin.users.import') }}" class="text-gray-500 hover:text-gray-700 font-medium underline">
                    Kembali ke Halaman Import
                </a>

            </div>
        </div>
    </div>
</div>

<!-- Auto trigger download on load -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            window.location.href = "{{ route('admin.users.import.download', ['filename' => $filename]) }}";
        }, 1500);
    });
</script>
</x-app-layout>
