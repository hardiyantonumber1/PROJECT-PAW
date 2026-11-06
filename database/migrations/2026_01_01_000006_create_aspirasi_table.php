<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel aspirasi — inti sistem SIAF.
 * Kolom asli: pengguna_id, judul, isi, kategori_id, unit_id, status,
 *             tanggal_masuk, tenggat, selesai_pada, kode, alasan_penolakan.
 * Kolom baru:  prodi_id, periode_id, kode_tiket, is_anonim, penanggung_jawab_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspirasi', function (Blueprint $table) {
            $table->id();

            // ── Kolom asli (dipakai controller lama) ──
            $table->foreignId('pengguna_id')->constrained('pengguna')->restrictOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('unit')->restrictOnDelete();
            $table->string('judul', 150);
            $table->text('isi');
            $table->enum('status', [
                'Diajukan', 'Diverifikasi', 'Diproses', 'Selesai', 'Ditolak',
            ])->default('Diajukan');
            $table->timestamp('tanggal_masuk')->useCurrent();
            $table->timestamp('tenggat')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            // Kode tiket format ASP-YYYYMMDD-XXXX
            $table->string('kode', 30)->unique()->nullable();
            $table->text('alasan_penolakan')->nullable();

            // ── Kolom tambahan SIAF ──
            $table->foreignId('prodi_id')
                ->nullable()
                ->constrained('prodi')
                ->nullOnDelete();
            $table->foreignId('periode_id')
                ->nullable()
                ->constrained('periode')
                ->nullOnDelete();
            $table->boolean('is_anonim')->default(false);
            $table->foreignId('penanggung_jawab_id')
                ->nullable()
                ->constrained('pengguna')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aspirasi');
    }
};
