<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel unit dibuat lebih dulu karena pengguna.unit_id mereferensikannya.
 * Kolom koordinator_id ditambahkan via ALTER setelah pengguna dibuat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            // koordinator_id ditambah via migration 000003b setelah tabel pengguna ada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit');
    }
};
