<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel tindak_lanjut — riwayat penanganan aspirasi.
 * Kolom asli: aspirasi_id, pengguna_id, status_awal, status_akhir, tanggapan, tanggal.
 * Kolom baru: bukti (lampiran file tindak lanjut).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aspirasi_id')->constrained('aspirasi')->cascadeOnDelete();
            $table->foreignId('pengguna_id')->constrained('pengguna')->restrictOnDelete();
            // status_awal nullable → tangkapan pertama (Diajukan → Diverifikasi)
            $table->string('status_awal', 30)->nullable();
            $table->string('status_akhir', 30);
            $table->text('tanggapan')->nullable();
            $table->string('bukti')->nullable(); // path file bukti
            $table->timestamp('tanggal')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut');
    }
};
