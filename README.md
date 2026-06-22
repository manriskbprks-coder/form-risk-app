# 🛡️ Form Risk App (Sistem Pelaporan Risiko Terintegrasi)

**Form Risk App** adalah aplikasi berbasis web yang dirancang khusus untuk memfasilitasi pencatatan, pemantauan, dan penyelesaian insiden risiko (Risk Event) di lingkungan operasional perbankan / BPR. 

Aplikasi ini mengusung sistem persetujuan berjenjang (**Maker-Checker-Signer**) untuk memastikan setiap kejadian risiko ditangani secara transparan dan akuntabel oleh pihak yang berwenang.

---

## 🌟 Fitur Utama

- **Role-Based Access Control (RBAC):** Membagi akses secara presisi menjadi 4 peran utama (Maker, Checker, Signer/ManRisk, dan Viewer).
- **Alur Persetujuan Fleksibel:** Dukungan untuk persetujuan berjenjang (Approval), penolakan dengan catatan revisi (Reject), dan update progress penyelesaian insiden.
- **Deklarasi Risiko Nihil (Zero Risk):** Cabang dapat melakukan deklarasi secara berkala jika tidak ada kejadian risiko (nihil).
- **Analisa SKMR:** Modul khusus bagi Manajer Risiko (ManRisk) untuk menganalisis dan memberi rekomendasi terkait kelemahan sistem / SOP.
- **Export & Pelaporan:** Ekspor data laporan risiko ke format PDF dan CSV untuk kebutuhan audit.
- **Notifikasi Email:** Pemberitahuan otomatis (via Email) setiap kali ada perubahan status laporan atau permintaan revisi.

---

## 👥 Alur Bisnis (Workflow)

Aplikasi ini menggunakan alur *bottom-up*:
1. **Maker (Staff Cabang)**: Mengidentifikasi dan melaporkan kejadian risiko (Finansial / Non-Finansial) melalui Form Pelaporan Risiko.
2. **Checker (Kepala Cabang)**: Melakukan tinjauan (*review*). Kacab bisa menyetujui laporan atau menolaknya jika data tidak sesuai. Kacab juga bertanggung jawab meng-update status penyelesaian.
3. **Signer / ManRisk (Pusat)**: Mengawasi seluruh risiko cabang, melakukan analisa SKMR, dan bisa mengembalikan laporan ke cabang (Minta Revisi) jika penyelesaian dianggap kurang memadai.
4. **Viewer (Korwil / Direksi)**: Memantau dasbor secara makro tanpa hak untuk mengubah data.

---

## 📚 Buku Panduan Pengguna (User Manuals)

Untuk memahami cara menggunakan aplikasi sesuai dengan jabatan Anda, silakan baca buku panduan berikut:

- 📖 **[Manual Book: Admin / ManRisk](ManualBook/USER_MANUAL_ADMIN.md)**
- 📖 **[Manual Book: Checker (Kacab)](ManualBook/USER_MANUAL_CHECKER.md)**
- 📖 **[Manual Book: Maker (Staff)](ManualBook/USER_MANUAL_MAKER.md)**
- 📖 **[Manual Book: Viewer (Korwil / Direksi)](ManualBook/USER_MANUAL_VIEWER.md)**

*(Catatan: Anda dapat membuka file Markdown di atas langsung di dalam repository GitHub).*

---

## 🛠️ Teknologi yang Digunakan

Aplikasi ini dibangun menggunakan arsitektur modern untuk memastikan kecepatan, keamanan, dan skalabilitas:
- **Backend**: Laravel 11.x (PHP 8.2+)
- **Frontend**: Blade Templating, Tailwind CSS 3.x, Alpine.js
- **Database**: MySQL 8.0
- **Environment**: Docker & Docker Compose (untuk standarisasi pengembangan)
- **Testing**: PHPUnit & Laravel Dusk

---

## 🚀 Cara Instalasi & Menjalankan Aplikasi (Lokal)

Aplikasi ini sangat mudah dijalankan menggunakan **Docker**. Pastikan Anda sudah menginstal [Docker Desktop](https://www.docker.com/products/docker-desktop/).

1. **Clone Repository**
   ```bash
   git clone https://github.com/manriskbprks-coder/form-risk-app.git
   cd form-risk-app
   ```

2. **Setup File Environment**
   ```bash
   cp .env.example .env
   ```
   *(Sesuaikan kredensial database dan SMTP Email jika diperlukan).*

3. **Jalankan Docker Sail**
   ```bash
   ./vendor/bin/sail up -d
   ```

4. **Install Dependensi & Generate Key**
   ```bash
   ./vendor/bin/sail composer install
   ./vendor/bin/sail npm install
   ./vendor/bin/sail artisan key:generate
   ```

5. **Migrasi Database & Seeding (Data Awal)**
   ```bash
   ./vendor/bin/sail artisan migrate:fresh --seed
   ```
   *(Perintah ini akan membuat akun-akun default berdasarkan data di UserSeeder).*

6. **Kompilasi Frontend (Tailwind)**
   ```bash
   ./vendor/bin/sail npm run build
   ```

Aplikasi kini dapat diakses melalui browser di alamat: **`http://localhost`**

---

## 📝 Lisensi & Hak Cipta
Aplikasi ini merupakan sistem tertutup (Proprietary) yang dikembangkan khusus untuk keperluan internal pelaporan risiko. Dilarang mendistribusikan kode sumber tanpa izin yang berwenang.
