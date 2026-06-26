<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Import User (CSV)
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        @if (session('error'))
            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Panduan Import Data</h3>
                    <p class="text-gray-600 mb-4">Pastikan file CSV Anda memiliki header baris pertama dengan format persis seperti berikut:</p>
                    <code class="block bg-gray-100 p-3 rounded text-sm text-blue-800 font-mono mb-4">
                        nik,nama,kode_cabang,jabatan,divisi
                    </code>
                    <p class="text-gray-600 mb-2">Contoh isi data:</p>
                    <code class="block bg-gray-100 p-3 rounded text-sm text-gray-800 font-mono">
                        1902001,Budi Santoso,001,BRANCH MANAGER,REGIONAL<br>
                        1902002,Siti Aminah,001,TELLER,REGIONAL
                    </code>
                </div>

                <form action="{{ route('admin.users.import.process') }}" method="POST" enctype="multipart/form-data" class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">Pilih File CSV</label>
                        <input type="file" name="csv_file" id="csv_file" accept=".csv" required
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        @error('csv_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center mt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition duration-150 ease-in-out flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Upload & Import
                        </button>
                        <span class="ml-4 text-sm text-gray-500">Maksimal ukuran file: 2MB</span>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
</x-app-layout>
