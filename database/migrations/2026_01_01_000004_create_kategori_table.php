<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel kategori — mendukung SLA (Service Level Agreement).
 * `batas_hari` = kolom asli yang dipakai controller untuk hitung tenggat.
 * Kolom SLA ditambahkan untuk dokumen rancangan SIAF.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->unsignedTinyInteger('batas_hari')->default(7);
            // SLA lebih granular (jam) — sesuai ERD SIAF
            $table->unsignedSmallInteger('sla_respons_jam')->default(24);
            $table->unsignedSmallInteger('sla_selesai_jam')->default(168);
            $table->enum('tingkat_keparahan', ['kritis', 'tinggi', 'sedang', 'rendah'])
                ->default('sedang');
            $table->foreignId('unit_default_id')
                ->nullable()
                ->constrained('unit')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori');
    }
};
