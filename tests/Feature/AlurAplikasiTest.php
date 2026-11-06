<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use App\Models\Kategori;
use App\Models\Pengguna;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurAplikasiTest extends TestCase
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

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Masuk', false);
    }

    public function test_masuk_dengan_kata_sandi_salah_ditolak(): void
    {
        $pengguna = $this->mahasiswa();

        $this->post('/login', ['email' => $pengguna->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_setiap_peran_mendarat_di_beranda_sendiri(): void
    {
        $mahasiswa = $this->mahasiswa();
        $petugas = $this->petugas();
        $admin = $this->admin();

        $this->actingAs($mahasiswa)->get('/')->assertRedirect(route('dashboard'));
        $this->actingAs($petugas)->get('/')->assertRedirect(route('petugas.antrian'));
        $this->actingAs($admin)->get('/')->assertRedirect(route('admin.rekap'));
    }

    public function test_mahasiswa_dapat_membuka_semua_halaman_utamanya(): void
    {
        $mahasiswa = $this->mahasiswa();
        $aspirasi = Aspirasi::factory()->create(['pengguna_id' => $mahasiswa->id]);

        $this->actingAs($mahasiswa)->get(route('dashboard'))->assertOk();
        $this->actingAs($mahasiswa)->get(route('aspirasi.index'))->assertOk();
        $this->actingAs($mahasiswa)->get(route('aspirasi.create'))->assertOk();
        $this->actingAs($mahasiswa)->get(route('aspirasi.show', $aspirasi))->assertOk();
        $this->actingAs($mahasiswa)->get(route('aspirasi.edit', $aspirasi))->assertOk();
        $this->actingAs($mahasiswa)->get(route('aspirasi.cari', ['q' => 'jalan']))->assertOk();
        $this->actingAs($mahasiswa)->get(route('aspirasi.ekspor'))->assertOk();
    }

    public function test_mahasiswa_tidak_boleh_masuk_area_petugas_dan_admin(): void
    {
        $mahasiswa = $this->mahasiswa();

        $this->actingAs($mahasiswa)->get(route('petugas.antrian'))->assertForbidden();
        $this->actingAs($mahasiswa)->get(route('admin.rekap'))->assertForbidden();
        $this->actingAs($mahasiswa)->get(route('admin.kategori'))->assertForbidden();
    }

    public function test_ajukan_aspirasi_menyimpan_tenggat_sesuai_batas_hari_kategori(): void
    {
        $mahasiswa = $this->mahasiswa();
        $kategori = Kategori::factory()->create(['batas_hari' => 7]);
        $unit = Unit::factory()->create();

        $this->actingAs($mahasiswa)->post(route('aspirasi.store'), [
            'judul' => 'Lampu koridor gedung A mati sejak sepekan',
            'kategori_id' => $kategori->id,
            'unit_id' => $unit->id,
            'isi' => 'Lampu koridor lantai 2 gedung A mati sejak pekan lalu sehingga gelap saat kelas malam.',
            'pernyataan' => '1',
        ])->assertRedirect();

        $aspirasi = Aspirasi::first();

        $this->assertNotNull($aspirasi);
        $this->assertSame(StatusAspirasi::Diajukan, $aspirasi->status);
        $this->assertSame(7, (int) $aspirasi->tanggal_masuk->diffInDays($aspirasi->tenggat));
    }

    public function test_petugas_melihat_antrian_unitnya_dan_dapat_menindaklanjuti(): void
    {
        $unit = Unit::factory()->create();
        $petugas = $this->petugas($unit);

        $aspirasi = Aspirasi::factory()->create([
            'unit_id' => $unit->id,
            'status' => StatusAspirasi::Diajukan,
        ]);

        $this->actingAs($petugas)->get(route('petugas.antrian'))->assertOk();
        $this->actingAs($petugas)->get(route('petugas.tindak-lanjut', $aspirasi))->assertOk();

        $this->actingAs($petugas)->post(route('petugas.tindak-lanjut.store', $aspirasi), [
            'status_akhir' => StatusAspirasi::Diverifikasi->value,
            'tanggapan' => 'Sudah kami terima dan sedang dicek ke bagian sarana.',
        ])->assertRedirect(route('petugas.antrian'));

        $aspirasi->refresh();

        $this->assertSame(StatusAspirasi::Diverifikasi, $aspirasi->status);
        $this->assertDatabaseHas('tindak_lanjut', [
            'aspirasi_id' => $aspirasi->id,
            'pengguna_id' => $petugas->id,
            'status_akhir' => StatusAspirasi::Diverifikasi->value,
        ]);
    }

    public function test_petugas_tidak_boleh_menangani_aspirasi_unit_lain(): void
    {
        $petugas = $this->petugas(Unit::factory()->create());
        $aspirasi = Aspirasi::factory()->create(['unit_id' => Unit::factory()->create()->id]);

        $this->actingAs($petugas)->get(route('petugas.tindak-lanjut', $aspirasi))->assertForbidden();
    }

    public function test_admin_dapat_membuka_rekap_dan_kelola_data_master(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.rekap'))->assertOk();
        $this->actingAs($admin)->get(route('admin.rekap', ['dari' => now()->subMonth()->toDateString(), 'sampai' => now()->toDateString()]))->assertOk();
        $this->actingAs($admin)->get(route('admin.rekap.ekspor'))->assertOk();
        $this->actingAs($admin)->get(route('admin.kategori'))->assertOk();
        $this->actingAs($admin)->get(route('admin.unit'))->assertOk();
        $this->actingAs($admin)->get(route('admin.pengguna'))->assertOk();
        $this->actingAs($admin)->get(route('aspirasi.semua'))->assertOk();
    }

    public function test_admin_dapat_menambah_dan_menghapus_kategori(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama' => 'Kebersihan',
            'batas_hari' => 5,
        ])->assertRedirect(route('admin.kategori'));

        $kategori = Kategori::where('nama', 'Kebersihan')->firstOrFail();

        $this->actingAs($admin)->delete(route('admin.kategori.destroy', $kategori))
            ->assertRedirect(route('admin.kategori'));

        $this->assertDatabaseMissing('kategori', ['id' => $kategori->id]);
    }

    public function test_kategori_yang_dipakai_tidak_dapat_dihapus(): void
    {
        $admin = $this->admin();
        $aspirasi = Aspirasi::factory()->create();

        $this->actingAs($admin)->delete(route('admin.kategori.destroy', $aspirasi->kategori_id))
            ->assertRedirect(route('admin.kategori'))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('kategori', ['id' => $aspirasi->kategori_id]);
    }

    public function test_mahasiswa_tidak_dapat_mengubah_aspirasi_yang_sudah_diverifikasi(): void
    {
        $mahasiswa = $this->mahasiswa();
        $aspirasi = Aspirasi::factory()->create([
            'pengguna_id' => $mahasiswa->id,
            'status' => StatusAspirasi::Diproses,
        ]);

        $this->actingAs($mahasiswa)->get(route('aspirasi.edit', $aspirasi))->assertForbidden();
    }

    public function test_admin_dapat_mengelola_unit(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.unit.store'), ['nama' => 'Perpustakaan'])
            ->assertRedirect(route('admin.unit'));

        $unit = Unit::where('nama', 'Perpustakaan')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.unit.update', $unit), ['nama' => 'Perpustakaan Pusat'])
            ->assertRedirect(route('admin.unit'));

        $this->assertSame('Perpustakaan Pusat', $unit->fresh()->nama);

        $this->actingAs($admin)->delete(route('admin.unit.destroy', $unit))
            ->assertRedirect(route('admin.unit'));

        $this->assertDatabaseMissing('unit', ['id' => $unit->id]);
    }

    public function test_unit_yang_dipakai_tidak_dapat_dihapus(): void
    {
        $admin = $this->admin();
        $aspirasi = Aspirasi::factory()->create();

        $this->actingAs($admin)->delete(route('admin.unit.destroy', $aspirasi->unit_id))
            ->assertRedirect(route('admin.unit'))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('unit', ['id' => $aspirasi->unit_id]);
    }

    public function test_admin_dapat_membuat_dan_mengubah_pengguna(): void
    {
        $admin = $this->admin();
        $unit = Unit::factory()->create();

        $this->actingAs($admin)->post(route('admin.pengguna.store'), [
            'nama' => 'Rani Puspita',
            'email' => 'rani@kampus.ac.id',
            'role' => Role::Petugas->value,
            'unit_id' => $unit->id,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('admin.pengguna'));

        $baru = Pengguna::where('email', 'rani@kampus.ac.id')->firstOrFail();

        $this->assertSame(Role::Petugas, $baru->role);
        $this->assertSame($unit->id, $baru->unit_id);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('rahasia123', $baru->password));

        // Ubah tanpa mengisi kata sandi: sandi lama harus tetap berlaku.
        $sandiLama = $baru->password;

        $this->actingAs($admin)->put(route('admin.pengguna.update', $baru), [
            'nama' => 'Rani Puspitasari',
            'email' => 'rani@kampus.ac.id',
            'role' => Role::Petugas->value,
            'unit_id' => $unit->id,
        ])->assertRedirect(route('admin.pengguna'));

        $baru->refresh();

        $this->assertSame('Rani Puspitasari', $baru->nama);
        $this->assertSame($sandiLama, $baru->password);
    }

    public function test_mahasiswa_dapat_mengubah_dan_menghapus_aspirasinya_sendiri(): void
    {
        $mahasiswa = $this->mahasiswa();
        $aspirasi = Aspirasi::factory()->create([
            'pengguna_id' => $mahasiswa->id,
            'status' => StatusAspirasi::Diajukan,
        ]);

        $this->actingAs($mahasiswa)->put(route('aspirasi.update', $aspirasi), [
            'judul' => 'Judul yang sudah diperbaiki',
            'kategori_id' => $aspirasi->kategori_id,
            'unit_id' => $aspirasi->unit_id,
            'isi' => 'Uraian aspirasi yang sudah diperbaiki agar lebih jelas dan lengkap.',
            'pernyataan' => '1',
        ])->assertRedirect(route('aspirasi.show', $aspirasi));

        $this->assertSame('Judul yang sudah diperbaiki', $aspirasi->fresh()->judul);

        $this->actingAs($mahasiswa)->delete(route('aspirasi.destroy', $aspirasi))
            ->assertRedirect(route('aspirasi.index'));

        $this->assertDatabaseMissing('aspirasi', ['id' => $aspirasi->id]);
    }

    public function test_mahasiswa_tidak_dapat_menghapus_aspirasi_orang_lain(): void
    {
        $mahasiswa = $this->mahasiswa();
        $aspirasi = Aspirasi::factory()->create();

        $this->actingAs($mahasiswa)->delete(route('aspirasi.destroy', $aspirasi))->assertForbidden();
        $this->assertDatabaseHas('aspirasi', ['id' => $aspirasi->id]);
    }

    public function test_penolakan_aspirasi_wajib_menyertakan_alasan(): void
    {
        $unit = Unit::factory()->create();
        $petugas = $this->petugas($unit);
        $aspirasi = Aspirasi::factory()->create([
            'unit_id' => $unit->id,
            'status' => StatusAspirasi::Diajukan,
        ]);

        $this->actingAs($petugas)->post(route('petugas.tindak-lanjut.store', $aspirasi), [
            'status_akhir' => StatusAspirasi::Ditolak->value,
            'tanggapan' => 'Tidak sesuai kewenangan unit.',
        ])->assertSessionHasErrors('alasan_penolakan');

        $this->assertSame(StatusAspirasi::Diajukan, $aspirasi->fresh()->status);
    }

    public function test_rekap_hanya_menghitung_aspirasi_pada_periode_terpilih(): void
    {
        $admin = $this->admin();

        Aspirasi::factory()->create(['tanggal_masuk' => now()->subMonths(3)]);
        Aspirasi::factory()->create(['tanggal_masuk' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.rekap', ['dari' => now()->startOfMonth()->toDateString(), 'sampai' => now()->toDateString()]))
            ->assertOk()
            ->assertViewHas('total', 1);
    }

    public function test_petugas_dapat_menambah_tanggapan_tanpa_mengubah_status(): void
    {
        $unit = Unit::factory()->create();
        $petugas = $this->petugas($unit);
        $aspirasi = Aspirasi::factory()->create([
            'unit_id' => $unit->id,
            'status' => StatusAspirasi::Diproses,
        ]);

        $this->actingAs($petugas)->post(route('petugas.tindak-lanjut.store', $aspirasi), [
            'status_akhir' => StatusAspirasi::Diproses->value,
            'tanggapan' => 'Sudah dikoordinasikan dengan bagian sarana, menunggu jadwal perbaikan.',
        ])->assertRedirect(route('petugas.antrian'));

        $this->assertSame(StatusAspirasi::Diproses, $aspirasi->fresh()->status);
        $this->assertDatabaseHas('tindak_lanjut', [
            'aspirasi_id' => $aspirasi->id,
            'status_awal' => StatusAspirasi::Diproses->value,
            'status_akhir' => StatusAspirasi::Diproses->value,
        ]);
    }

    public function test_tanggapan_tanpa_ubah_status_tetap_wajib_berisi_teks(): void
    {
        $unit = Unit::factory()->create();
        $petugas = $this->petugas($unit);
        $aspirasi = Aspirasi::factory()->create([
            'unit_id' => $unit->id,
            'status' => StatusAspirasi::Diproses,
        ]);

        $this->actingAs($petugas)->post(route('petugas.tindak-lanjut.store', $aspirasi), [
            'status_akhir' => StatusAspirasi::Diproses->value,
        ])->assertSessionHasErrors('tanggapan');
    }

    public function test_keluar_mengakhiri_sesi(): void
    {
        $this->actingAs($this->mahasiswa())->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
