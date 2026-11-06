<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Aspirasi;
use App\Models\Pengguna;
use App\Models\Unit;
use App\Support\Ringkasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $unit = Unit::query()
            ->withCount(['aspirasi', 'petugas'])
            ->when($q !== '', fn ($query) => $query->where('nama', 'like', '%'.$q.'%'))
            ->orderBy('nama')
            ->get()
            ->each(function (Unit $u) {
                $u->aspirasi_aktif = $u->aspirasi()->berjalan()->count();
                $u->rata_selesai = Ringkasan::rataHari(
                    $u->aspirasi()->whereNotNull('selesai_pada')->get(['tanggal_masuk', 'selesai_pada'])
                );
            });

        $unit->load('petugas');

        $totalPetugas = Pengguna::petugas()->count();
        $totalAspirasi = Aspirasi::count();
        $terbanyak = $unit->sortByDesc('aspirasi_aktif')->first();

        return view('admin.unit', compact('unit', 'q', 'totalPetugas', 'totalAspirasi', 'terbanyak'));
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        $unit = Unit::create($request->validated());

        return redirect()
            ->route('admin.unit')
            ->with('sukses', 'Unit '.$unit->nama.' ditambahkan.');
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()
            ->route('admin.unit')
            ->with('sukses', 'Unit '.$unit->nama.' diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->dipakai()) {
            return redirect()
                ->route('admin.unit')
                ->with('galat', 'Unit '.$unit->nama.' masih dipakai petugas atau aspirasi sehingga tidak dapat dihapus (RB-04).');
        }

        $nama = $unit->nama;
        $unit->delete();

        return redirect()
            ->route('admin.unit')
            ->with('sukses', 'Unit '.$nama.' dihapus.');
    }
}
