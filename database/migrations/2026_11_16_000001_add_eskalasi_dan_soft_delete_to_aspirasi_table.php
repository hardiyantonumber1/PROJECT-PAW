<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Revisi keamanan & eskalasi (2026-11-16)
 * ------------------------------------------------------------------
 * 1. Kolom status eskalasi pada aspirasi — dipakai EskalasiService supaya
 *    proses eskalasi berkala bersifat idempoten (tidak pernah dobel).
 * 2. Soft delete — data yang "dihapus" tidak hilang dari basis data,
 *    sehingga jejak audit (riwayat_perubahan) tetap utuh dan dapat
 *    dipertanggungjawabkan saat pemeriksaan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aspirasi', function (Blueprint $table) {
            $table->unsignedTinyInteger('level_eskalasi')
                ->default(0)
                ->after('status')
                ->comment('0 = normal, 1 = belum ada respons, 2 = tenggat lewat, 3 = pembiaran');

            $table->timestamp('eskalasi_terakhir_pada')
                ->nullable()
                ->after('level_eskalasi')
                ->comment('Kapan eskalasi terakhir dijalankan — dasar cooldown');

            $table->timestamp('ditangani_pada')
                ->nullable()
                ->after('eskalasi_terakhir_pada')
                ->comment('Sentuhan manusia terakhir (tindak lanjut) — menghentikan eskalasi');

            $table->softDeletes();

            // Mempercepat pemindaian scheduler: hanya baris yang masih berjalan.
            $table->index(['status', 'tenggat'], 'ix_aspirasi_status_tenggat');
            $table->index(['level_eskalasi', 'tenggat'], 'ix_aspirasi_level_tenggat');
        });
    }

    public function down(): void
    {
        Schema::table('aspirasi', function (Blueprint $table) {
            $table->dropIndex('ix_aspirasi_status_tenggat');
            $table->dropIndex('ix_aspirasi_level_tenggat');
            $table->dropSoftDeletes();
            $table->dropColumn(['level_eskalasi', 'eskalasi_terakhir_pada', 'ditangani_pada']);
        });
    }
};
