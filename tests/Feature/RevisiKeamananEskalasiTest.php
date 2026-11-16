<?php

namespace Tests\Feature;

use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use App\Models\Eskalasi;
use App\Models\Kategori;
use App\Models\LogKeamanan;
use App\Models\Pengguna;
use App\Models\RiwayatPerubahan;
use App\Models\Unit;
use App\Services\EskalasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Revisi 2026-11-16: keamanan, privasi, eskalasi berlapis, dan jejak audit.
 */
class RevisiKeamananEskalasiTest extends TestCase
{
    use RefreshDatabase;

    private function mahasiswa(): Pengguna
    {
        return Pengguna::factory()->mahasiswa()->create();
    }

    private function petugas(?Unit $unit = null): Pengguna
    {
        return Pengguna::factory()->petugas($unit ?? Unit::factory()->create())->create();
    }

    private function admin(): Pengguna
    {
        return Pengguna::factory()->admin()->create();
    }

    private function layanan(): EskalasiService
    {
        return app(EskalasiService::class);
    }

    /** Aspirasi dengan parameter waktu & kategori yang bisa dikendalikan. */
    private function aspirasi(array $atribut = []): Aspirasi
    {
        $kategori = Kategori::factory()->create([
            'batas_hari' => 10,
            'sla_respons_jam' => 24,
        ]);

        return Aspirasi::factory()->create(array_merge([
            'kategori_id' => $kategori->id,
            'unit_id' => Unit::factory()->create()->id,
            'status' => StatusAspirasi::Diajukan,
            'tanggal_masuk' => now()->subDays(2),
            'tenggat' => now()->addDays(8),
        ], $atribut));
    }

    /* ------------------------------------------------------------ eskalasi */

    public function test_level_1_ketika_belum_ada_respons_melewati_sla(): void
    {
        $aspirasi = $this->aspirasi([
            'tanggal_masuk' => now()->subHours(30),
            'tenggat' => now()->addDays(6),
        ]);

        $hasil = $this->layanan()->jalankan();

        $this->assertSame(1, $hasil['naik']);

        $aspirasi->refresh();
        $this->assertSame(1, $aspirasi->level_eskalasi);
        $this->assertDatabaseHas('eskalasi', [
            'aspirasi_id' => $aspirasi->id,
            'level' => 1,
        ]);
    }

    public function test_eskalasi_idempoten_walau_dijalankan_berulang(): void
    {
        $aspirasi = $this->aspirasi([
            'tanggal_masuk' => now()->subHours(30),
            'tenggat' => now()->addDays(6),
        ]);

        $this->layanan()->jalankan();
        $kedua = $this->layanan()->jalankan();
        $ketiga = $this->layanan()->jalankan();

        $this->assertSame(0, $kedua['naik']);
        $this->assertSame(0, $ketiga['naik']);
        $this->assertSame(1, Eskalasi::where('aspirasi_id', $aspirasi->id)->count());
        $this->assertSame(1, $aspirasi->fresh()->level_eskalasi);
    }

    public function test_level_2_ketika_tenggat_terlewati(): void
    {
        $aspirasi = $this->aspirasi([
            'tanggal_masuk' => now()->subDays(12),
            'tenggat' => now()->subDays(2),
            'status' => StatusAspirasi::Diverifikasi,
        ]);

        $this->layanan()->jalankan();

        $this->assertSame(2, $aspirasi->fresh()->level_eskalasi);
    }

    public function test_level_3_ketika_pembiaran_melewati_masa_tenggang(): void
    {
        $aspirasi = $this->aspirasi([
            'tanggal_masuk' => now()->subDays(20),
            'tenggat' => now()->subDays(8),
            'status' => StatusAspirasi::Diproses,
        ]);

        $this->layanan()->jalankan();

        $this->assertSame(3, $aspirasi->fresh()->level_eskalasi);
    }

    public function test_aspirasi_final_tidak_pernah_dieskalasi(): void
    {
        $this->aspirasi([
            'tanggal_masuk' => now()->subDays(30),
            'tenggat' => now()->subDays(20),
            'status' => StatusAspirasi::Selesai,
        ]);

        $hasil = $this->layanan()->jalankan();

        $this->assertSame(0, $hasil['naik']);
        $this->assertSame(0, Eskalasi::count());
    }

    public function test_tindak_lanjut_menahan_eskalasi_berikutnya(): void
    {
        $aspirasi = $this->aspirasi([
            'tanggal_masuk' => now()->subDays(12),
            'tenggat' => now()->subDays(2),
            'status' => StatusAspirasi::Diproses,
        ]);

        // Petugas baru saja merespons satu jam lalu.
        $aspirasi->ditangani_pada = now()->subHour();
        $aspirasi->save();

        $hasil = $this->layanan()->jalankan();

        $this->assertSame(0, $hasil['naik']);
        $this->assertSame(0, $aspirasi->fresh()->level_eskalasi);
    }

    public function test_perintah_eskalasi_dapat_dijalankan(): void
    {
        $this->aspirasi([
            'tanggal_masuk' => now()->subHours(30),
            'tenggat' => now()->addDays(6),
        ]);

        $this->artisan('aspirasi:eskalasi')->assertSuccessful();
        $this->assertSame(1, Eskalasi::count());
    }

    /* ---------------------------------------------------------- jejak audit */

    public function test_perubahan_kolom_tercatat_lengkap_dengan_nilai_lama_dan_baru(): void
    {
        $mahasiswa = $this->mahasiswa();
        $this->actingAs($mahasiswa);

        $aspirasi = $this->aspirasi(['pengguna_id' => $mahasiswa->id]);

        $this->assertDatabaseHas('riwayat_perubahan', [
            'aspirasi_id' => $aspirasi->id,
            'aksi' => 'dibuat',
        ]);

        $aspirasi->update(['judul' => 'Judul baru setelah revisi pemohon']);

        $baris = RiwayatPerubahan::where('aspirasi_id', $aspirasi->id)
            ->where('field', 'judul')
            ->first();

        $this->assertNotNull($baris);
        $this->assertSame($mahasiswa->nama, $baris->aktor);
        $this->assertNotSame($baris->nilai_lama, $baris->nilai_baru);
        $this->assertSame('Judul baru setelah revisi pemohon', $baris->nilai_baru);
    }

    /**
     * Regresi: dashboard & detail mahasiswa pernah 500 karena
     * tindak_lanjut.status_akhir terbaca string, bukan enum.
     */
    public function test_dashboard_mahasiswa_tetap_terbuka_saat_ada_tindak_lanjut(): void
    {
        $unit = Unit::factory()->create();
        $petugas = $this->petugas($unit);
        $mahasiswa = $this->mahasiswa();

        $aspirasi = $this->aspirasi([
            'pengguna_id' => $mahasiswa->id,
            'unit_id' => $unit->id,
        ]);

        $this->actingAs($petugas)->post(route('petugas.tindak-lanjut.store', $aspirasi), [
            'status_akhir' => StatusAspirasi::Diverifikasi->value,
            'tanggapan' => 'Sudah diverifikasi, diteruskan ke unit sarana.',
        ])->assertRedirect(route('petugas.antrian'));

        $this->actingAs($mahasiswa)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Diverifikasi');

        $this->actingAs($mahasiswa)->get(route('aspirasi.show', $aspirasi))
            ->assertOk()
            ->assertSee('Diverifikasi');
    }

    public function test_perubahan_status_dan_eskalasi_masuk_jejak_audit(): void
    {
        $unit = Unit::factory()->create();
        $petugas = $this->petugas($unit);
        $aspirasi = $this->aspirasi([
            'unit_id' => $unit->id,
            'tanggal_masuk' => now()->subHours(30),
            'tenggat' => now()->addDays(6),
        ]);

        $this->actingAs($petugas)->post(route('petugas.tindak-lanjut.store', $aspirasi), [
            'status_akhir' => StatusAspirasi::Diverifikasi->value,
            'tanggapan' => 'Sudah kami verifikasi dan teruskan ke unit sarana.',
        ])->assertRedirect(route('petugas.antrian'));

        $this->assertDatabaseHas('riwayat_perubahan', [
            'aspirasi_id' => $aspirasi->id,
            'aksi' => 'status',
            'field' => 'status',
        ]);
        $this->assertDatabaseHas('riwayat_perubahan', [
            'aspirasi_id' => $aspirasi->id,
            'aksi' => 'tindak_lanjut',
        ]);

        // Eskalasi berikutnya menulis jejaknya sendiri. Tenggat digeser ke masa
        // lalu agar aturan L2 benar-benar terpicu pada pemindaian berikutnya.
        $aspirasi->forceFill([
            'ditangani_pada' => null,
            'tanggal_masuk' => now()->subDays(12),
            'tenggat' => now()->subDays(2),
        ])->save();

        $this->layanan()->jalankan();

        $this->assertDatabaseHas('riwayat_perubahan', [
            'aspirasi_id' => $aspirasi->id,
            'aksi' => 'eskalasi',
        ]);
    }

    /* ------------------------------------------------------------- privasi */

    public function test_identitas_pelapor_anonim_disembunyikan_dari_petugas(): void
    {
        $unit = Unit::factory()->create();
        $petugas = $this->petugas($unit);
        $mahasiswa = $this->mahasiswa();

        $aspirasi = $this->aspirasi([
            'unit_id' => $unit->id,
            'pengguna_id' => $mahasiswa->id,
            'is_anonim' => true,
        ]);

        $this->actingAs($petugas)
            ->get(route('aspirasi.show', $aspirasi))
            ->assertOk()
            ->assertSee('Anonim')
            ->assertDontSee($mahasiswa->nama);
    }

    public function test_pelapor_tetap_melihat_namanya_sendiri(): void
    {
        $mahasiswa = $this->mahasiswa();

        $aspirasi = $this->aspirasi([
            'pengguna_id' => $mahasiswa->id,
            'is_anonim' => true,
        ]);

        $this->actingAs($mahasiswa)
            ->get(route('aspirasi.show', $aspirasi))
            ->assertOk()
            ->assertSee($mahasiswa->nama);
    }

    public function test_pembukaan_identitas_anonim_oleh_super_admin_tercatat(): void
    {
        $admin = $this->admin();
        $mahasiswa = $this->mahasiswa();

        $aspirasi = $this->aspirasi([
            'pengguna_id' => $mahasiswa->id,
            'is_anonim' => true,
        ]);

        $this->actingAs($admin)->get(route('aspirasi.show', $aspirasi))->assertOk();

        $this->assertDatabaseHas('log_keamanan', [
            'aksi' => 'akses_anonim',
            'pengguna_id' => $admin->id,
        ]);
    }

    /* ------------------------------------------------------------ keamanan */

    public function test_header_keamanan_terpasang(): void
    {
        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_percobaan_masuk_dibatasi_dan_tercatat(): void
    {
        $mahasiswa = $this->mahasiswa();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $mahasiswa->email, 'password' => 'salah'])
                ->assertSessionHasErrors('email');
        }

        // Percobaan keenam diblokir rate limit, bukan sekadar gagal kredensial.
        $this->post('/login', ['email' => $mahasiswa->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseHas('log_keamanan', ['aksi' => 'login_gagal']);
        $this->assertDatabaseHas('log_keamanan', ['aksi' => 'login_diblokir']);
    }

    public function test_email_pada_log_keamanan_tersamar(): void
    {
        $mahasiswa = $this->mahasiswa();

        $this->post('/login', ['email' => $mahasiswa->email, 'password' => 'salah']);

        $log = LogKeamanan::where('aksi', 'login_gagal')->firstOrFail();

        $this->assertStringContainsString('***@', $log->email);
        $this->assertStringNotContainsString(explode('@', $mahasiswa->email)[0], $log->email);
    }

    public function test_akses_ditolak_tercatat_di_log_keamanan(): void
    {
        $mahasiswa = $this->mahasiswa();

        $this->actingAs($mahasiswa)->get(route('admin.rekap'))->assertForbidden();

        $this->assertDatabaseHas('log_keamanan', [
            'aksi' => 'akses_ditolak',
            'pengguna_id' => $mahasiswa->id,
        ]);
    }

    /* ----------------------------------------------------------- pengawasan */

    public function test_halaman_pengawasan_hanya_untuk_peran_berwenang(): void
    {
        $admin = $this->admin();
        $petugas = $this->petugas();

        $this->actingAs($admin)->get(route('admin.eskalasi'))->assertOk();
        $this->actingAs($admin)->get(route('admin.audit'))->assertOk();
        $this->actingAs($admin)->get(route('admin.keamanan'))->assertOk();

        $this->actingAs($petugas)->get(route('admin.eskalasi'))->assertForbidden();
    }

    public function test_super_admin_dapat_memicu_eskalasi_manual(): void
    {
        $admin = $this->admin();

        $this->aspirasi([
            'tanggal_masuk' => now()->subHours(30),
            'tenggat' => now()->addDays(6),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.eskalasi.jalankan'))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertSame(1, Eskalasi::count());
    }

    public function test_petugas_biasa_tidak_dapat_memicu_eskalasi_manual(): void
    {
        $petugas = $this->petugas();

        // Petugas bahkan tidak lolos gerbang peran pada grup rute admin.
        $this->actingAs($petugas)
            ->post(route('admin.eskalasi.jalankan'))
            ->assertForbidden();
    }
}
