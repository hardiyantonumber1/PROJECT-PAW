<?php

namespace App\Http\Controllers;

use App\Models\Aspirasi;
use App\Models\Eskalasi;
use App\Services\EskalasiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman pengawasan eskalasi: daftar aspirasi yang sudah naik tingkat
 * beserta alasannya, plus tombol menjalankan pemindaian secara manual.
 */
class EskalasiController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $lingkupUnit = ! $user->isSuperAdmin() && ! $user->isPimpinan() && ! $user->isUltksp();

        $dasar = Eskalasi::query()
            ->when($lingkupUnit, fn (Builder $q) => $q->whereHas(
                'aspirasi',
                fn (Builder $w) => $w->where('unit_id', $user->unit_id)
            ));

        $level = (int) $request->query('level', 0);

        $daftar = (clone $dasar)
            ->with(['aspirasi.kategori', 'aspirasi.unit', 'aspirasi.pengguna'])
            ->when($level >= 1 && $level <= 3, fn (Builder $q) => $q->where('level', $level))
            ->orderByDesc('terjadi_pada')
            ->paginate(15)
            ->withQueryString();

        $ringkas = [
            'total'   => (clone $dasar)->count(),
            'l1'      => (clone $dasar)->where('level', 1)->count(),
            'l2'      => (clone $dasar)->where('level', 2)->count(),
            'l3'      => (clone $dasar)->where('level', 3)->count(),
            'berjalan' => Aspirasi::query()
                ->when($lingkupUnit, fn (Builder $q) => $q->where('unit_id', $user->unit_id))
                ->where('level_eskalasi', '>', 0)
                ->berjalan()
                ->count(),
            'terakhir' => (clone $dasar)->max('terjadi_pada'),
        ];

        return view('admin.eskalasi', compact('daftar', 'ringkas', 'level'));
    }

    /** Jalankan pemindaian sekarang (tanpa menunggu scheduler). */
    public function jalankan(Request $request, EskalasiService $service): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang boleh memicu eskalasi manual.');

        $hasil = $service->jalankan();

        return back()->with(
            'sukses',
            'Pemindaian selesai: '.$hasil['dipindai'].' aspirasi diperiksa, '
            .$hasil['naik'].' naik level, '.$hasil['dilewati'].' dilewati.'
        );
    }
}
