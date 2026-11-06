<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\PenggunaRequest;
use App\Models\Pengguna;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        $peran = (string) $request->query('peran', '');
        $unitId = $request->query('unit');
        $q = trim((string) $request->query('q', ''));

        $pengguna = Pengguna::query()
            ->with('unit')
            ->when(Role::tryFrom($peran) !== null, fn ($query) => $query->where('role', $peran))
            ->when($unitId, fn ($query) => $query->where('unit_id', $unitId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%');
                });
            })
            ->orderByRaw("case role
                when 'super_admin' then 1
                when 'admin_prodi' then 2
                when 'ultksp' then 3
                when 'pimpinan' then 4
                when 'koordinator' then 5
                when 'petugas' then 6
                else 7 end")
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $jumlahPeran = Pengguna::query()
            ->selectRaw('role, count(*) as jumlah')
            ->groupBy('role')
            ->pluck('jumlah', 'role');

        return view('admin.pengguna', [
            'pengguna' => $pengguna,
            'peran' => $peran,
            'peranTersedia' => Role::cases(),
            'jumlahPeran' => $jumlahPeran,
            'unit' => Unit::orderBy('nama')->get(),
            'unitId' => $unitId,
            'q' => $q,
        ]);
    }

    public function store(PenggunaRequest $request): RedirectResponse
    {
        $pengguna = Pengguna::create($request->validated());

        return redirect()
            ->route('admin.pengguna')
            ->with('sukses', 'Akun '.$pengguna->nama.' berhasil dibuat.');
    }

    public function update(PenggunaRequest $request, Pengguna $pengguna): RedirectResponse
    {
        $data = $request->validated();

        // Kata sandi hanya diganti bila diisi.
        if (blank($data['password'] ?? null)) {
            $data = Arr::except($data, ['password']);
        }

        $pengguna->update($data);

        return redirect()
            ->route('admin.pengguna')
            ->with('sukses', 'Data akun '.$pengguna->nama.' diperbarui.');
    }
}
