<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel pengguna — mendukung 7 peran sesuai dokumen rancangan SIAF.
 * Kolom `nama` dipakai seluruh codebase (bukan `name`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', [
                'mahasiswa', 'petugas', 'koordinator',
                'admin_prodi', 'super_admin', 'ultksp', 'pimpinan',
            ])->default('mahasiswa');
            // Unit hanya relevan untuk petugas/koordinator
            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('unit')
                ->nullOnDelete();
            // Prodi hanya relevan untuk mahasiswa / admin_prodi
            $table->foreignId('prodi_id')
                ->nullable()
                ->constrained('prodi')
                ->nullOnDelete();
            // NIM / NIP
            $table->string('npm_nip', 30)->nullable();
            $table->boolean('is_anonim_ok')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
