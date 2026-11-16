<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel riwayat_perubahan — jejak audit tingkat kolom (append-only).
 *
 * Setiap perubahan pada aspirasi, tindak_lanjut, kategori, unit, dan pengguna
 * dicatat sebagai satu baris: siapa, kapan, dari mana, kolom apa, nilai lama,
 * nilai baru. Baris di sini TIDAK pernah diubah atau dihapus aplikasi.
 *
 * aspirasi_id memakai nullOnDelete agar catatan audit tentang sebuah data
 * tetap dapat dibaca walau data induknya hilang secara fisik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_perubahan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('aspirasi_id')->nullable()->constrained('aspirasi')->nullOnDelete();
            $table->foreignId('pengguna_id')->nullable()->constrained('pengguna')->nullOnDelete();

            // Snapshot pelaku — tetap terbaca walaupun akunnya kelak dihapus.
            $table->string('aktor', 100)->nullable();
            $table->string('aktor_peran', 30)->nullable();

            $table->string('entitas', 40)->comment('aspirasi|tindak_lanjut|kategori|unit|pengguna|eskalasi');
            $table->unsignedBigInteger('entitas_id')->nullable();
            $table->string('aksi', 30)->comment('dibuat|diubah|dihapus|status|eskalasi|tindak_lanjut|akses_anonim');

            $table->string('field', 60)->nullable();
            $table->text('nilai_lama')->nullable();
            $table->text('nilai_baru')->nullable();
            $table->text('keterangan')->nullable();

            $table->string('ip', 45)->nullable();
            $table->string('perangkat', 255)->nullable();

            $table->timestamp('terjadi_pada')->useCurrent();
            $table->timestamps();

            $table->index(['aspirasi_id', 'terjadi_pada'], 'ix_riwayat_aspirasi_waktu');
            $table->index(['entitas', 'entitas_id'], 'ix_riwayat_entitas');
            $table->index(['aksi', 'terjadi_pada'], 'ix_riwayat_aksi_waktu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_perubahan');
    }
};
