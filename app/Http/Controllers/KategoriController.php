<?php

namespace App\Http\Controllers;

use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        $kategori = Kategori::withCount('aspirasi')->orderBy('nama')->get();

        $tercepat = $kategori->sortBy('batas_hari')->first();
        $terlama = $kategori->sortByDesc('batas_hari')->first();
        $terpakai = $kategori->where('aspirasi_count', '>', 0)->count();

        return view('admin.kategori', compact('kategori', 'tercepat', 'terlama', 'terpakai'));
    }

    public function store(KategoriRequest $request): RedirectResponse
    {
        $kategori = Kategori::create($request->validated());

        return redirect()
            ->route('admin.kategori')
            ->with('sukses', 'Kategori '.$kategori->nama.' ditambahkan.');
    }

    public function update(KategoriRequest $request, Kategori $kategori): RedirectResponse
    {
        $kategori->update($request->validated());

        return redirect()
            ->route('admin.kategori')
            ->with('sukses', 'Kategori '.$kategori->nama.' diperbarui. Tenggat aspirasi lama tidak berubah (RB-02).');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->dipakai()) {
            return redirect()
                ->route('admin.kategori')
                ->with('galat', 'Kategori '.$kategori->nama.' masih dipakai aspirasi sehingga tidak dapat dihapus (ON DELETE RESTRICT).');
        }

        $nama = $kategori->nama;
        $kategori->delete();

        return redirect()
            ->route('admin.kategori')
            ->with('sukses', 'Kategori '.$nama.' dihapus.');
    }
}
