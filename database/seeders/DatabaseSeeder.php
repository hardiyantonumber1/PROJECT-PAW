<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProdiSeeder::class,
            UnitSeeder::class,
            KategoriSeeder::class,
            PeriodeSeeder::class,
            UserSeeder::class,
        ]);
    }
}

/*
 * LEGACY DatabaseSeeder (lama, referensi saja — tidak dijalankan)
 */
class _LegacyDatabaseSeeder_ref
{
    public function run_legacy(): void
    {
        $hariIni = Carbon::now()->startOfDay();

        /* ------------------------------------------------------------- unit */
        $unit = collect([
            'BAAK' => 'BAAK',
            'BAU' => 'BAU',
            'Prodi IF' => 'Program Studi Informatika',
        ])->map(fn (string $nama) => Unit::updateOrCreate(['nama' => $nama]));

        /* --------------------------------------------------------- kategori */
        $kategori = collect([
            'Akademik' => 7,
            'Fasilitas' => 14,
            'Pelayanan' => 5,
            'Keuangan' => 10,
        ])->map(fn (int $batas, string $nama) => Kategori::updateOrCreate(
            ['nama' => $nama],
            ['batas_hari' => $batas],
        ));

        /* --------------------------------------------------------- pengguna */
        $akun = fn (string $nama, string $email, Role $role, ?Unit $unit = null) => Pengguna::updateOrCreate(
            ['email' => $email],
            [
                'nama' => $nama,
                'password' => Hash::make('password'),
                'role' => $role,
                'unit_id' => $unit?->id,
                'created_at' => Carbon::now()->subMonths(2),
            ],
        );

        $akun('Admin Sistem', 'admin@kampus.ac.id', Role::Admin);
        $siti = $akun('Siti Aminah', 'siti@kampus.ac.id', Role::Petugas, $unit['BAAK']);
        $budi = $akun('Budi Santoso', 'budi@kampus.ac.id', Role::Petugas, $unit['BAU']);

        $hardiyanto = $akun('Hardiyanto Jaya Pranata', 'hardiyanto@kampus.ac.id', Role::Mahasiswa);
        $rina = $akun('Rina Wulandari', 'rina@kampus.ac.id', Role::Mahasiswa);
        $fajar = $akun('Fajar Nugroho', 'fajar@kampus.ac.id', Role::Mahasiswa);

        /* --------------------------------------------------------- aspirasi */
        $buat = function (
            Pengguna $pelapor,
            string $judul,
            string $isi,
            string $namaKategori,
            string $namaUnit,
            StatusAspirasi $status,
            int $masukHariLalu,
            ?string $alasan = null,
            ?int $selesaiHariLalu = null,
        ) use ($kategori, $unit, $hariIni): Aspirasi {
            $k = $kategori[$namaKategori];
            $masuk = $hariIni->copy()->subDays($masukHariLalu)->setTime(9, 41);

            return Aspirasi::updateOrCreate(
                ['judul' => $judul],
                [
                    'pengguna_id' => $pelapor->id,
                    'kategori_id' => $k->id,
                    'unit_id' => $unit[$namaUnit]->id,
                    'isi' => $isi,
                    'status' => $status,
                    'alasan_penolakan' => $alasan,
                    'tanggal_masuk' => $masuk,
                    // RB-02: tenggat disimpan sebagai salinan saat pengajuan dibuat.
                    'tenggat' => $masuk->copy()->addDays($k->batas_hari),
                    'selesai_pada' => $selesaiHariLalu === null
                        ? null
                        : $hariIni->copy()->subDays($selesaiHariLalu)->setTime(14, 20),
                ],
            );
        };

        $a1 = $buat(
            $hardiyanto,
            'AC ruang Lab 3 Informatika tidak berfungsi',
            'AC di ruang Lab 3 Informatika sudah tiga minggu tidak dingin sama sekali, padahal ruangan dipakai praktikum setiap hari. Suhu ruangan sangat panas sehingga mahasiswa sulit berkonsentrasi. Mohon dilakukan pengecekan dan perbaikan unit AC.',
            'Fasilitas',
            'BAU',
            StatusAspirasi::Diproses,
            2,
        );

        $a2 = $buat(
            $rina,
            'Wi-Fi Gedung C sering terputus saat praktikum',
            'Wi-Fi di Gedung C lantai 3 sering terputus, terutama saat praktikum siang. Mahasiswa harus login ulang berkali-kali sehingga pekerjaan praktikum tertunda. Mohon dicek perangkat access point-nya.',
            'Fasilitas',
            'BAU',
            StatusAspirasi::Diproses,
            20,
        );

        $a3 = $buat(
            $hardiyanto,
            'Jadwal UTS bentrok dengan kegiatan MBKM',
            'Jadwal UTS mata kuliah Pemrograman Web bentrok dengan jadwal kegiatan MBKM yang wajib diikuti. Saya sudah mencoba menghubungi dosen pengampu namun belum ada solusi. Mohon diatur ulang jadwal ujian susulan.',
            'Akademik',
            'BAAK',
            StatusAspirasi::Diverifikasi,
            1,
        );

        $a4 = $buat(
            $fajar,
            'Keterlambatan penerbitan KTM angkatan 2025',
            'KTM angkatan 2025 belum diterbitkan hingga awal semester, sehingga mahasiswa tidak dapat meminjam buku perpustakaan maupun mengakses fasilitas laboratorium. Mohon dipercepat proses pencetakannya.',
            'Pelayanan',
            'BAAK',
            StatusAspirasi::Selesai,
            10,
            null,
            6,
        );

        $a5 = $buat(
            $hardiyanto,
            'Nilai mata kuliah Pemrograman Web belum keluar',
            'Nilai mata kuliah Pemrograman Web semester lalu belum muncul di portal akademik padahal seluruh tugas dan ujian sudah dikumpulkan. Hal ini menghambat pengisian KRS semester berikutnya.',
            'Akademik',
            'BAAK',
            StatusAspirasi::Ditolak,
            12,
            'Nilai belum dapat dipublikasikan karena dosen pengampu masih melakukan verifikasi ulang berkas ujian. Silakan mengajukan kembali setelah pengumuman resmi dari program studi.',
        );

        $a6 = $buat(
            $rina,
            'Rincian potongan UKT semester ganjil belum jelas',
            'Tagihan UKT semester ganjil memuat potongan yang tidak dijelaskan rinciannya pada portal keuangan. Saya membutuhkan rincian per komponen agar dapat melakukan pembayaran dengan benar.',
            'Keuangan',
            'BAU',
            StatusAspirasi::Diajukan,
            3,
        );

        /* --------------------------------------------------- tindak lanjut */
        $catat = fn (Aspirasi $a, Pengguna $petugas, ?StatusAspirasi $awal, StatusAspirasi $akhir, ?string $tanggapan, int $hariLalu, int $jam = 10) => TindakLanjut::updateOrCreate(
            [
                'aspirasi_id' => $a->id,
                'status_akhir' => $akhir,
            ],
            [
                'pengguna_id' => $petugas->id,
                'status_awal' => $awal,
                'tanggapan' => $tanggapan,
                'tanggal' => $hariIni->copy()->subDays($hariLalu)->setTime($jam, 14),
            ],
        );

        $catat($a1, $budi, StatusAspirasi::Diajukan, StatusAspirasi::Diverifikasi, 'Laporan valid, diteruskan ke bagian sarana dan prasarana.', 1, 9);
        $catat($a1, $budi, StatusAspirasi::Diverifikasi, StatusAspirasi::Diproses, 'Teknisi sudah dijadwalkan memeriksa unit AC pada hari kerja berikutnya.', 1, 14);

        $catat($a2, $budi, StatusAspirasi::Diajukan, StatusAspirasi::Diverifikasi, 'Laporan valid, access point akan diperiksa tim jaringan.', 19, 9);
        $catat($a2, $budi, StatusAspirasi::Diverifikasi, StatusAspirasi::Diproses, 'Penggantian access point lantai 3 sedang menunggu pengadaan perangkat.', 15, 13);

        $catat($a3, $siti, StatusAspirasi::Diajukan, StatusAspirasi::Diverifikasi, 'Sudah diteruskan ke bagian akademik untuk penjadwalan ulang.', 1, 11);

        $catat($a4, $siti, StatusAspirasi::Diajukan, StatusAspirasi::Diverifikasi, 'Berkas mahasiswa sudah lengkap, KTM masuk antrean cetak.', 9, 10);
        $catat($a4, $siti, StatusAspirasi::Diverifikasi, StatusAspirasi::Diproses, 'Pencetakan KTM dilakukan bertahap per program studi.', 8, 9);
        $catat($a4, $siti, StatusAspirasi::Diproses, StatusAspirasi::Selesai, 'KTM sudah dicetak dan dapat diambil di loket BAAK mulai pekan ini.', 6, 14);

        $catat($a5, $siti, StatusAspirasi::Diajukan, StatusAspirasi::Ditolak, 'Nilai belum dapat dipublikasikan karena dosen pengampu masih melakukan verifikasi ulang berkas ujian.', 11, 15);

        // $a6 sengaja tanpa tindak lanjut: contoh aspirasi berstatus Diajukan.
        unset($a6);
    }
}
