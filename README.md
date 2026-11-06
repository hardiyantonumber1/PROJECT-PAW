# SIAF (Sistem Informasi Aspirasi Fakultas)
**Proyek Akhir Pemrograman Web - Kelompok 13**
Fakultas Ilmu Komputer, Universitas Brawijaya (FILKOM UB)

---

## Tentang Proyek
SIAF (Sistem Informasi Aspirasi Fakultas) adalah platform berbasis web yang dikembangkan untuk mengelola, memproses, dan memantau aspirasi mahasiswa di lingkungan Fakultas Ilmu Komputer Universitas Brawijaya. Sistem ini mendukung 5 Program Studi, mengakomodasi ribuan mahasiswa, serta menerapkan 7 peran hak akses (Role-Based Access Control / RBAC) untuk memastikan alur penanganan aspirasi yang transparan, akuntabel, dan sesuai standar SLA (Service Level Agreement).

## Tim Pengembang (Kelompok 13)
* **Hardi** (Pengembang Utama / Lead Developer)
* **Radya Harbani** (Kontributor & Pengembang Modul Database & UI/UX)

## Tech Stack
* **Framework Backend:** Laravel 11 (PHP)
* **Database:** MySQL / MariaDB
* **Frontend:** Blade Templating, Tailwind CSS / Bootstrap, Custom Responsive Design sesuai Desain Figma
* **Kontrol Versi:** Git & GitHub

## Fitur Utama & Peran Pengguna (7 Peran RBAC)
1. **Mahasiswa:** Mengajukan aspirasi, melacak status tiket, memberikan tanggapan/lampiran.
2. **Admin Prodi:** Mengelola dan memfilter aspirasi tingkat program studi.
3. **Petugas Kemahasiswaan / ULTKSP:** Menindaklanjuti dan memproses eskalasi aspirasi fakultas.
4. **Pimpinan / Dekanat:** Memantau rekapitulasi dan laporan statistik aspirasi.
5. **Unit Terkait:** Menangani teknis penanganan aspirasi sesuai bidang.
6. **Super Admin:** Manajemen pengguna, kategori, periode, dan konfigurasi sistem secara menyeluruh.

## Cara Menjalankan Proyek Secara Lokal

1. **Clone Repository**
   ```bash
   git clone https://github.com/hardiyantonumber1/PROJECT-PAW.git
   cd PROJECT-PAW
   ```

2. **Install Dependensi PHP & Node.js**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Konfigurasi Environment**
   Salin file `.env.example` menjadi `.env`, lalu sesuaikan koneksi database Anda:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Jalankan Migrasi & Seeder Database**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Jalankan Server Lokal**
   ```bash
   php artisan serve
   ```
   Akses aplikasi melalui browser di `http://127.0.0.1:8000`.

---
© 2026 Kelompok 13 - Pemrograman Web FILKOM UB
