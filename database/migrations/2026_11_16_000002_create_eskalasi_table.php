<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel eskalasi — buku besar setiap kali sebuah aspirasi naik tingkat.
 *
 * UNIQUE (aspirasi_id, level) adalah jaminan tingkat basis data bahwa satu
 * aspirasi tidak mungkin tercatat dua kali pada level yang sama, walaupun
 * scheduler berjalan bersamaan atau dijalankan manual berkali-kali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eskalasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aspirasi_id')->constrained('aspirasi')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->unsignedTinyInteger('level_sebelum')->default(0);
            $table->string('alasan', 120);
            $table->text('rincian')->nullable();
            $table->string('penerima_peran', 60)->comment('Peran yang menerima notifikasi eskalasi');
            $table->integer('keterlambatan_jam')->default(0);
            $table->timestamp('terjadi_pada')->useCurrent();
            $table->timestamps();

            $table->unique(['aspirasi_id', 'level'], 'ux_eskalasi_aspirasi_level');
            $table->index(['level', 'terjadi_pada'], 'ix_eskalasi_level_waktu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eskalasi');
    }
};
