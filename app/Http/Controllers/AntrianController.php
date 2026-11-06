<?php

namespace App\Http\Controllers;

use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use App\Support\Ringkasan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AntrianController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $unit = $user->unit;

        $dasar = Aspirasi::query()
            ->when($unit, fn (Builder $q) => $q->where('unit_id', $unit->id), fn (Builder $q) => $q->whereRaw('1 = 0'));

        $saring = (string) $request->query('saring', 'aktif');
        $urut = (string) $request->query('urut', 'tenggat');

        $aktif = (clone $dasar)->berjalan()->count();
        $terlambat = (clone $dasar)->terlambat()->count();
        $menunggu = (clone $dasar)->where('status', StatusAspirasi::Diajukan->value)->count();
        $diproses = (clone $dasar)->where('status', StatusAspirasi::Diproses->value)->count();

        $selesaiBulan = (clone $dasar)
            ->where('status', StatusAspirasi::Selesai->value)
            ->whereBetween('selesai_pada', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $selesaiUnit = (clone $dasar)->where('status', StatusAspirasi::Selesai->value)->count();
        $totalUnit = (clone $dasar)->count();

        $rataHari = Ringkasan::rataHari(
            (clone $dasar)->whereNotNull('selesai_pada')->get(['tanggal_masuk', 'selesai_pada'])
        );

        $berikutnya = (clone $dasar)->berjalan()->orderBy('tenggat')->first();

        $daftar = $dasar
            ->with(['kategori', 'unit', 'pengguna.unit'])
            ->when($saring === 'lewat', fn (Builder $q) => $q->terlambat())
            ->when($saring === 'selesai', fn (Builder $q) => $q->where('status', StatusAspirasi::Selesai->value))
            ->when($saring === 'aktif', fn (Builder $q) => $q->berjalan())
            ->when(true, function (Builder $q) use ($urut) {
                return match ($urut) {
                    'terbaru' => $q->orderByDesc('tanggal_masuk'),
                    'judul' => $q->orderBy('judul'),
                    default => $q->orderBy('tenggat'),
                };
            })
            ->paginate(10)
            ->withQueryString();

        return view('petugas.antrian', compact(
            'unit', 'aktif', 'terlambat', 'menunggu', 'diproses',
            'selesaiBulan', 'selesaiUnit', 'totalUnit', 'rataHari',
            'berikutnya', 'daftar', 'saring', 'urut'
        ));
    }
}
