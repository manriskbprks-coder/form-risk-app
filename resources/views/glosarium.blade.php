<x-app-layout>
    @section('title', 'Glosarium')
    @section('page_title', 'Glosarium')

    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                📖 Glosarium Istilah Manajemen Risiko
            </h2>
            <p class="text-sm text-slate-500">
                Kamus istilah penting yang digunakan di dalam aplikasi ini.
            </p>
        </div>
    </x-slot>

    <div x-data="glossaryManager()" class="space-y-6">

        {{-- SEARCH BAR --}}
        <div class="surface-card section-pad">
            <div class="relative max-w-xl">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="search" placeholder="Cari istilah... (misal: LED, RCSA, Mitigasi)"
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
            </div>

            {{-- CATEGORY FILTER --}}
            <div class="flex flex-wrap gap-2 mt-4">
                <template x-for="cat in ['semua','umum','proses','status','analisis']" :key="cat">
                    <button @click="activeCategory = cat" 
                            :class="activeCategory === cat ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold capitalize transition" x-text="cat">
                    </button>
                </template>
            </div>
        </div>

        {{-- GLOSSARY ENTRIES --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <template x-for="item in filteredItems" :key="item.term">
                <div class="surface-card p-5 hover:shadow-md transition-shadow border-l-4"
                     :class="{
                         'border-l-indigo-500': item.category === 'umum',
                         'border-l-amber-500': item.category === 'proses',
                         'border-l-sky-500': item.category === 'status',
                         'border-l-rose-500': item.category === 'analisis'
                     }">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-900" x-text="item.term"></h3>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                  :class="{
                                      'bg-indigo-100 text-indigo-700': item.category === 'umum',
                                      'bg-amber-100 text-amber-700': item.category === 'proses',
                                      'bg-sky-100 text-sky-700': item.category === 'status',
                                      'bg-rose-100 text-rose-700': item.category === 'analisis'
                                  }" x-text="item.category"></span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 mt-3 leading-relaxed" x-text="item.definition"></p>
                    <p x-show="item.example" class="text-xs text-slate-400 mt-2 italic" x-text="'💡 Contoh: ' + item.example"></p>
                </div>
            </template>
        </div>

        {{-- EMPTY STATE --}}
        <div x-show="filteredItems.length === 0" class="surface-card p-12 text-center" style="display: none;">
            <p class="text-slate-400 text-sm">Tidak ada istilah yang cocok dengan pencarian Anda.</p>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('glossaryManager', () => ({
                search: '',
                activeCategory: 'semua',
                items: [
                    // === UMUM ===
                    { term: 'Risiko Operasional', category: 'umum', definition: 'Risiko kerugian yang diakibatkan oleh ketidakmemadaian atau kegagalan proses internal, manusia, sistem teknologi, atau kejadian eksternal.', example: 'Kesalahan input data oleh teller, sistem ATM offline, bencana alam.' },
                    { term: 'LED (Loss Event Database)', category: 'umum', definition: 'Database pencatatan seluruh kejadian kerugian (loss event) yang terjadi di suatu organisasi. Ini adalah inti dari aplikasi Form Risiko ini.', example: 'Seluruh laporan risiko yang di-submit oleh Staff melalui aplikasi ini tersimpan sebagai LED.' },
                    { term: 'RCSA (Risk & Control Self-Assessment)', category: 'umum', definition: 'Proses penilaian mandiri oleh unit kerja terhadap risiko dan kontrol internal yang ada. Digunakan untuk mengidentifikasi risiko secara proaktif sebelum terjadi.', example: 'Di awal tahun, Kacab diminta mengisi daftar potensi risiko di cabangnya beserta langkah pencegahan.' },
                    { term: 'KRI (Key Risk Indicator)', category: 'umum', definition: 'Indikator kunci yang digunakan untuk mendeteksi perubahan profil risiko secara dini. Bisa berupa angka, tren, atau ambang batas tertentu.', example: 'Jika jumlah selisih kas di suatu cabang meningkat 50% dalam sebulan, itu adalah sinyal KRI.' },
                    { term: 'SKMR (Satuan Kerja Manajemen Risiko)', category: 'umum', definition: 'Unit kerja di kantor pusat yang bertugas mengevaluasi laporan kejadian risiko, menentukan akar masalah, dan memberikan rekomendasi mitigasi.', example: 'Setelah laporan di-close, SKMR (ManRisk) mengisi kolom Analisa SKMR di detail laporan.' },
                    { term: 'BPR (Bank Perekonomian Rakyat)', category: 'umum', definition: 'Jenis bank yang melaksanakan kegiatan usaha secara konvensional atau berdasarkan prinsip syariah yang dalam kegiatannya tidak memberikan jasa dalam lalu lintas pembayaran.', example: '' },
                    { term: 'OJK (Otoritas Jasa Keuangan)', category: 'umum', definition: 'Lembaga negara yang mengatur dan mengawasi seluruh kegiatan jasa keuangan di Indonesia, termasuk perbankan.', example: '' },
                    { term: 'Risiko Finansial', category: 'umum', definition: 'Kategori risiko yang menimbulkan kerugian berupa uang secara langsung bagi bank. Wajib mencantumkan nominal dampak finansial.', example: 'Uang palsu masuk ke kas teller sebesar Rp 500.000.' },
                    { term: 'Risiko Non-Finansial', category: 'umum', definition: 'Kategori risiko yang tidak menimbulkan kerugian uang secara langsung, namun berdampak pada operasional, reputasi, atau kepatuhan.', example: 'Mesin EDC rusak sehingga nasabah tidak bisa bertransaksi.' },
                    
                    // === PROSES ===
                    { term: 'Deklarasi Nihil Risiko', category: 'proses', definition: 'Pernyataan resmi dari Kepala Cabang bahwa selama periode tertentu (2 minggu) tidak terjadi insiden/kejadian risiko apapun di cabangnya. Wajib dilakukan 2x sebulan.', example: 'Kacab menekan tombol Deklarasi Nihil pada tanggal 14 karena tidak ada kejadian.' },
                    { term: 'Approval Flow', category: 'proses', definition: 'Alur persetujuan berjenjang dalam sistem. Laporan harus melewati beberapa tahap persetujuan sebelum bisa ditindaklanjuti atau ditutup.', example: 'Staff submit → Kacab review → Approve → In Progress → Closed.' },
                    { term: 'Bank Soal Risiko (Master Data)', category: 'proses', definition: 'Daftar induk kejadian risiko, penyebab, dan mitigasi yang telah didefinisikan oleh ManRisk. Digunakan sebagai pilihan dropdown saat Staff mengisi form laporan.', example: 'Item: "Selisih Kas", Penyebab: "Kelalaian Teller", Mitigasi: "Verifikasi ganda sebelum tutup kas".' },
                    { term: 'Sumber Risiko', category: 'proses', definition: 'Asal/akar penyebab terjadinya sebuah kejadian risiko operasional. Dikategorikan menjadi: Internal Fraud, External Fraud, Praktik Ketenagakerjaan, Nasabah/Produk/Praktik Bisnis, Kerusakan Aset, Gangguan Sistem, dan Eksekusi/Manajemen Proses.', example: 'Karyawan menggelapkan dana nasabah → Sumber: Internal Fraud.' },
                    
                    // === STATUS ===
                    { term: 'Pending Atasan', category: 'status', definition: 'Status laporan yang menunggu persetujuan dari Kepala Cabang (Checker). Laporan baru saja di-submit oleh Staff.', example: '' },
                    { term: 'Need Revision', category: 'status', definition: 'Status laporan yang ditolak/dikembalikan oleh Kepala Cabang karena ada data yang perlu diperbaiki oleh Staff (Maker).', example: 'Kacab menolak laporan karena kronologi tidak jelas.' },
                    { term: 'Approved In Progress', category: 'status', definition: 'Status laporan yang sudah disetujui oleh atasan dan sedang dalam proses penanganan/tindak lanjut di lapangan.', example: 'Laporan mesin ATM rusak sudah di-approve, menunggu teknisi vendor datang.' },
                    { term: 'Closed', category: 'status', definition: 'Status laporan yang sudah selesai ditangani secara tuntas. Insiden sudah di-resolve dan tidak ada tindakan lanjutan yang diperlukan.', example: 'Mesin ATM sudah diperbaiki, Kacab mengubah status menjadi Closed.' },
                    { term: 'Dampak Finansial', category: 'status', definition: 'Nominal kerugian uang (dalam Rupiah) yang diakibatkan oleh suatu kejadian risiko. Diisi pada laporan kategori Finansial.', example: 'Rp 500.000 (uang palsu yang masuk).' },
                    { term: 'Skala Dampak', category: 'status', definition: 'Tingkat keparahan dampak dari suatu kejadian risiko, dikategorikan dari Sangat Rendah hingga Sangat Tinggi.', example: 'Kerugian di atas Rp 100 Juta masuk kategori "Sangat Tinggi".' },
                    
                    // === ANALISIS ===
                    { term: 'Inherent Risk (Risiko Inheren)', category: 'analisis', definition: 'Risiko yang melekat pada suatu aktivitas bisnis sebelum dilakukan pengendalian atau mitigasi apapun. Ini adalah risiko "mentah" yang ada secara alami.', example: 'Setiap transaksi kas selalu memiliki risiko inheren selisih, terlepas dari apakah ada kontrol double-counting atau tidak.' },
                    { term: 'Residual Risk (Risiko Residual)', category: 'analisis', definition: 'Risiko yang tersisa setelah dilakukan upaya mitigasi dan pengendalian. Risiko ini tidak mungkin nol, tapi harus berada di level yang bisa diterima (acceptable).', example: 'Setelah memasang CCTV dan double-check, risiko pencurian masih ada tapi berkurang drastis.' },
                    { term: 'Mitigasi Risiko', category: 'analisis', definition: 'Langkah-langkah atau tindakan yang diambil untuk mengurangi kemungkinan terjadinya risiko atau mengurangi dampak jika risiko tersebut terjadi.', example: 'Menerapkan verifikasi ganda (dual control) untuk setiap transaksi di atas Rp 10 Juta.' },
                    { term: 'Risk Appetite', category: 'analisis', definition: 'Tingkat risiko yang bersedia ditanggung oleh organisasi dalam menjalankan aktivitas bisnisnya untuk mencapai tujuan.', example: 'BPR menetapkan batas toleransi kerugian operasional maksimal 2% dari total aset per tahun.' },
                    
                ],
                get filteredItems() {
                    const q = this.search.toLowerCase();
                    return this.items.filter(item => {
                        const matchSearch = q === '' || item.term.toLowerCase().includes(q) || item.definition.toLowerCase().includes(q);
                        const matchCategory = this.activeCategory === 'semua' || item.category === this.activeCategory;
                        return matchSearch && matchCategory;
                    });
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
