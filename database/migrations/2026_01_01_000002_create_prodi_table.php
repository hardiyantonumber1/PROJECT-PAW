<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel prodi — 5 program studi FILKOM UB.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prodi', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama', 100);
            $table->string('jenjang', 10)->default('S1'); // S1, S2
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prodi');
    }
};
