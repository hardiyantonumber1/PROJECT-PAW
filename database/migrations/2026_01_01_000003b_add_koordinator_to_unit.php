<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tambahkan FK koordinator_id ke unit setelah tabel pengguna ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit', function (Blueprint $table) {
            $table->foreignId('koordinator_id')
                ->nullable()
                ->after('nama')
                ->constrained('pengguna')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('unit', function (Blueprint $table) {
            $table->dropForeign(['koordinator_id']);
            $table->dropColumn('koordinator_id');
        });
    }
};
