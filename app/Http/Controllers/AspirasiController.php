<?php

namespace App\Http\Controllers;

use App\Enums\StatusAspirasi;
use App\Http\Requests\AspirasiRequest;
use App\Models\Aspirasi;
use App\Models\Kategori;
use App\Models\LogKeamanan;
use App\Models\Unit;
use App\Support\EksporCsv;
use App\Support\Ringkasan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AspirasiController extends Controller
{
    /** Daftar aspirasi milik mahasiswa yang sedang masuk. */
    public function index(Request $request): View
    {
        return $this->daftar($request, 'aspirasi.index', false);
    }

    /** Daftar seluruh aspirasi dalam jangkauan peran (petugas: unitnya, admin: semua). */
    public function semua(Request $request): View
    {
        return $this->daftar($request, 'aspirasi.semua', true);
    }

    public function create(): View
    {
        return view('aspirasi.create', [
            'kategori' => Kategori::orderBy('nama')->get(),
            'unit' => Unit::orderBy('nama')->get(),
        ]);
    }

    public function store(AspirasiRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $kategori = Kategori::findOrFail($data['kategori_id']);
        $masuk = now();

        // RB-02: tenggat disimpan sebagai snapshot, bukan dihitung ulang saat dibaca.
        $aspirasi = Aspirasi::simpanBaru([
            'pengguna_id' => $request->user()->id,
            'kategori_id' => $kategori->id,
            'unit_id' => $data['unit_id'],
            'judul' => $data['judul'],
            'isi' => $data['isi'],
            'status' => StatusAspirasi::Diajukan,
            'tanggal_masuk' => $masuk,
            'tenggat' => $masuk->copy()->addDays($kategori->batas_hari),
            // Privasi: identitas pelapor disembunyikan dari petugas bila dicentang.
            'is_anonim' => $request->boolean('is_anonim'),
        ]);

        return redirect()
            ->route('aspirasi.show', $aspirasi)
            ->with('sukses', 'Aspirasi '.$aspirasi->kode.' berhasil diajukan dan menunggu verifikasi petugas.');
    }

    public function show(Request $request, Aspirasi $aspirasi): View
    {
        Gate::authorize('view', $aspirasi);

        $aspirasi->load([
            'pengguna.unit',
            'kategori',
            'unit',
            'tindakLanjut.pengguna.unit',
            'eskalasi',
            'riwayat.pengguna',
        ]);

        $penanganan = $aspirasi->tindakLanjut->first(fn ($tl) => $tl->pengguna?->isPetugas());
        $petugas = $penanganan?->pengguna;

        /*
         * Privasi: bila identitas pelapor anonim dibuka oleh peran pengawas
         * (Super Admin), peristiwa itu wajib tercatat agar penyalahgunaan
         * wewenang dapat ditelusuri.
         */
        if ($aspirasi->is_anonim
            && $request->user()->isSuperAdmin()
            && $request->user()->id !== $aspirasi->pengguna_id) {
            LogKeamanan::catat(
                'akses_anonim',
                $request->user(),
                null,
                'Membuka identitas pelapor anonim pada '.$aspirasi->kode.'.'
            );
        }

        return view('aspirasi.show', compact('aspirasi', 'petugas'));
    }

    public function edit(Aspirasi $aspirasi): View
    {
        Gate::authorize('update', $aspirasi);

        return view('aspirasi.edit', [
            'aspirasi' => $aspirasi,
            'kategori' => Kategori::orderBy('nama')->get(),
            'unit' => Unit::orderBy('nama')->get(),
        ]);
    }

    public function update(AspirasiRequest $request, Aspirasi $aspirasi): RedirectResponse
    {
        Gate::authorize('update', $aspirasi);

        $data = $request->validated();
        $kategori = Kategori::findOrFail($data['kategori_id']);

        $aspirasi->fill([
            'kategori_id' => $kategori->id,
            'unit_id' => $data['unit_id'],
            'judul' => $data['judul'],
            'isi' => $data['isi'],
            // Tenggat dihitung ulang dari tanggal masuk bila kategori berubah.
            'tenggat' => $aspirasi->tanggal_masuk->copy()->addDays($kategori->batas_hari),
            'is_anonim' => $request->boolean('is_anonim'),
        ])->save();

        return redirect()
            ->route('aspirasi.show', $aspirasi)
            ->with('sukses', 'Perubahan aspirasi '.$aspirasi->kode.' tersimpan.');
    }

    public function destroy(Aspirasi $aspirasi): RedirectResponse
    {
        Gate::authorize('delete', $aspirasi);

        $kode = $aspirasi->kode;

        /*
         * Soft delete: baris tetap tersimpan (deleted_at terisi) sehingga
         * riwayat penanganan dan jejak auditnya tidak ikut hilang. Hapus
         * permanen hanya lewat operasi basis data oleh admin sistem.
         */
        $aspirasi->delete();

        return redirect()
            ->route('aspirasi.index')
            ->with('sukses', 'Aspirasi '.$kode.' telah dihapus dari daftar (jejak audit tetap tersimpan).');
    }

    public function cari(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $hasil = Aspirasi::query()
            ->terlihatOleh($request->user())
            ->cari($q)
            ->with(['kategori', 'unit'])
            ->orderByDesc('tanggal_masuk')
            ->paginate(10)
            ->withQueryString();

        return view('aspirasi.cari', compact('q', 'hasil'));
    }

    public function ekspor(Request $request): StreamedResponse
    {
        $daftar = Aspirasi::query()
            ->terlihatOleh($request->user())
            ->with(['kategori', 'unit', 'pengguna'])
            ->orderBy('tanggal_masuk')
            ->get();

        $baris = $daftar->map(fn (Aspirasi $a) => [
            $a->kode,
            $a->judul,
            $a->kategori->nama,
            $a->unit->nama,
            // Privasi: aspirasi anonim tetap tanpa nama pelapor di berkas ekspor.
            $a->namaPelapor($request->user()),
            $a->status->value,
            $a->tanggal_masuk->translatedFormat('d/m/Y'),
            $a->tenggat->translatedFormat('d/m/Y'),
            $a->selesai_pada?->translatedFormat('d/m/Y') ?? '-',
            $a->terlambat ? 'Ya' : 'Tidak',
        ]);

        $nama = 'aspirasi-'.$request->user()->role->value.'-'.now()->format('Ymd-His').'.csv';

        return EksporCsv::unduh($nama, [
            'Kode', 'Judul', 'Kategori', 'Unit', 'Pelapor',
            'Status', 'Tanggal Masuk', 'Tenggat', 'Selesai Pada', 'Terlambat',
        ], $baris);
    }

    /* ------------------------------------------------------------- bantuan */

    private function daftar(Request $request, string $rute, bool $semua): View
    {
        $user = $request->user();

        $dasar = Aspirasi::query()->terlihatOleh($user);

        $status = (string) $request->query('status', '');
        $kategoriId = $request->query('kategori');
        $urut = (string) $request->query('urut', 'terbaru');

        $total = (clone $dasar)->count();
        $menunggu = (clone $dasar)->where('status', StatusAspirasi::Diajukan->value)->count();
        $hitung = Ringkasan::hitungStatus($dasar);

        $aspirasi = $dasar
            ->with(['kategori', 'unit', 'pengguna'])
            ->when(StatusAspirasi::tryFrom($status) !== null, fn (Builder $q) => $q->where('status', $status))
            ->when($kategoriId, fn (Builder $q) => $q->where('kategori_id', $kategoriId))
            ->when(true, function (Builder $q) use ($urut) {
                return match ($urut) {
                    'tenggat' => $q->orderBy('tenggat'),
                    'judul' => $q->orderBy('judul'),
                    'terlama' => $q->orderBy('tanggal_masuk'),
                    default => $q->orderByDesc('tanggal_masuk'),
                };
            })
            ->paginate(10)
            ->withQueryString();

        return view('aspirasi.index', [
            'aspirasi' => $aspirasi,
            'kategori' => Kategori::orderBy('nama')->get(),
            'total' => $total,
            'menunggu' => $menunggu,
            'hitung' => $hitung,
            'status' => $status,
            'kategoriId' => $kategoriId,
            'urut' => $urut,
            'ruteDaftar' => $rute,
            'semua' => $semua,
        ]);
    }
}
