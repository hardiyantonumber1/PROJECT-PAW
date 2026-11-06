<?php

use App\Http\Controllers\AntrianController;
use App\Http\Controllers\AspirasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Aplikasi SIAF (Sistem Informasi Aspirasi Fakultas)
|--------------------------------------------------------------------------
| Tiga peran dengan beranda berbeda (Role::beranda()):
|   mahasiswa → dashboard          petugas → petugas.antrian
|   admin     → admin.rekap
| Pembatasan peran memakai middleware alias "role" (PastikanPeran).
*/

Route::get('/', function () {
    $user = auth()->user();

    return $user
        ? redirect()->route($user->role->beranda())
        : redirect()->route('login');
})->name('beranda');

/* ------------------------------------------------------------ autentikasi */

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->name('login');
    Route::post('/login', [AuthController::class, 'masuk'])->name('login.proses');
});

Route::post('/logout', [AuthController::class, 'keluar'])
    ->middleware('auth')
    ->name('logout');

/* ------------------------------------------------------------ area masuk */

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:mahasiswa')
        ->name('dashboard');

    /* ------------------------------------------------------- aspirasi */

    Route::get('/aspirasi', [AspirasiController::class, 'index'])
        ->middleware('role:mahasiswa')
        ->name('aspirasi.index');

    Route::get('/aspirasi/semua', [AspirasiController::class, 'semua'])
        ->middleware('role:petugas,koordinator,super_admin,admin_prodi,ultksp,pimpinan')
        ->name('aspirasi.semua');

    Route::get('/aspirasi/cari', [AspirasiController::class, 'cari'])->name('aspirasi.cari');
    Route::get('/aspirasi/ekspor', [AspirasiController::class, 'ekspor'])->name('aspirasi.ekspor');

    Route::get('/aspirasi/ajukan', [AspirasiController::class, 'create'])
        ->middleware('role:mahasiswa')
        ->name('aspirasi.create');

    Route::post('/aspirasi', [AspirasiController::class, 'store'])
        ->middleware('role:mahasiswa')
        ->name('aspirasi.store');

    Route::get('/aspirasi/{aspirasi}', [AspirasiController::class, 'show'])->name('aspirasi.show');

    Route::get('/aspirasi/{aspirasi}/ubah', [AspirasiController::class, 'edit'])
        ->middleware('role:mahasiswa')
        ->name('aspirasi.edit');

    Route::put('/aspirasi/{aspirasi}', [AspirasiController::class, 'update'])
        ->middleware('role:mahasiswa')
        ->name('aspirasi.update');

    Route::delete('/aspirasi/{aspirasi}', [AspirasiController::class, 'destroy'])
        ->middleware('role:mahasiswa')
        ->name('aspirasi.destroy');

    /* -------------------------------------------- antrian & tindak lanjut */

    Route::middleware('role:petugas,koordinator,super_admin,admin_prodi,ultksp,pimpinan')->group(function () {
        Route::get('/antrian', [AntrianController::class, 'index'])->name('petugas.antrian');

        Route::get('/antrian/{aspirasi}', [TindakLanjutController::class, 'form'])
            ->name('petugas.tindak-lanjut');

        Route::post('/antrian/{aspirasi}', [TindakLanjutController::class, 'store'])
            ->name('petugas.tindak-lanjut.store');
    });

    /* ------------------------------------------------------ area admin */

    Route::middleware('role:super_admin,admin_prodi,ultksp,pimpinan')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap');
        Route::get('/rekap/ekspor', [RekapController::class, 'ekspor'])->name('rekap.ekspor');

        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

        Route::get('/unit', [UnitController::class, 'index'])->name('unit');
        Route::post('/unit', [UnitController::class, 'store'])->name('unit.store');
        Route::put('/unit/{unit}', [UnitController::class, 'update'])->name('unit.update');
        Route::delete('/unit/{unit}', [UnitController::class, 'destroy'])->name('unit.destroy');

        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna');
        Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
    });
});
