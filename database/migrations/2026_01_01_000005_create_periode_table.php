<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel periode akademik — Semester Gasal/Genap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['aktif', 'selesai', 'draft'])->default('draft');
            $table->foreignId('ditutup_oleh')
                ->nullable()
                ->constrained('pengguna')
                ->nullOnDelete();
            $table->timestamp('ditutup_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode');
    }
};
