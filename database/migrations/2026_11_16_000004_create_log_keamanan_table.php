<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel log_keamanan — catatan peristiwa autentikasi & akses.
 *
 * Dipakai untuk mendeteksi percobaan masuk berulang (brute force),
 * memantau pembukaan data anonim, dan menyediakan bukti saat audit.
 * Email disimpan dalam bentuk tersamar (mis. ma***@student.ub.ac.id)
 * agar tabel ini sendiri tidak menjadi sumber kebocoran data pribadi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_keamanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('email', 100)->nullable()->comment('Selalu tersamar');
            $table->string('aksi', 40)->comment('login_berhasil|login_gagal|login_diblokir|logout|akses_ditolak|akses_anonim');
            $table->string('ip', 45)->nullable();
            $table->string('perangkat', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamp('terjadi_pada')->useCurrent();
            $table->timestamps();

            $table->index(['aksi', 'terjadi_pada'], 'ix_log_keamanan_aksi_waktu');
            $table->index(['ip', 'terjadi_pada'], 'ix_log_keamanan_ip_waktu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_keamanan');
    }
};
