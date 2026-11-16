<?php

namespace App\Http\Controllers;

use App\Models\LogKeamanan;
use App\Models\RiwayatPerubahan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dua halaman pengawasan:
 *   audit    — jejak perubahan data (riwayat_perubahan)
 *   keamanan — peristiwa autentikasi & akses (log_keamanan)
 */
class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $aksi = (string) $request->query('aksi', '');
        $cari = trim((string) $request->query('q', ''));

        $daftar = RiwayatPerubahan::query()
            ->with(['aspirasi', 'pengguna'])
            ->when($aksi !== '', fn (Builder $q) => $q->where('aksi', $aksi))
            ->when($cari !== '', fn (Builder $q) => $q->where(function (Builder $w) use ($cari) {
                $w->where('keterangan', 'like', '%'.$cari.'%')
                    ->orWhere('aktor', 'like', '%'.$cari.'%')
                    ->orWhereHas('aspirasi', fn (Builder $a) => $a->where('kode', 'like', '%'.$cari.'%'));
            }))
            ->orderByDesc('terjadi_pada')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $ringkas = [
            'total'    => RiwayatPerubahan::count(),
            'hari_ini' => RiwayatPerubahan::whereDate('terjadi_pada', today())->count(),
            'status'   => RiwayatPerubahan::where('aksi', 'status')->count(),
            'eskalasi' => RiwayatPerubahan::where('aksi', 'eskalasi')->count(),
            'hapus'    => RiwayatPerubahan::where('aksi', 'dihapus')->count(),
        ];

        return view('admin.audit', compact('daftar', 'ringkas', 'aksi', 'cari'));
    }

    public function keamanan(Request $request): View
    {
        $aksi = (string) $request->query('aksi', '');

        $daftar = LogKeamanan::query()
            ->with('pengguna')
            ->when($aksi !== '', fn (Builder $q) => $q->where('aksi', $aksi))
            ->orderByDesc('terjadi_pada')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $ringkas = [
            'total'    => LogKeamanan::count(),
            'gagal'    => LogKeamanan::where('aksi', 'login_gagal')->count(),
            'diblokir' => LogKeamanan::where('aksi', 'login_diblokir')->count(),
            'ditolak'  => LogKeamanan::where('aksi', 'akses_ditolak')->count(),
            'anonim'   => LogKeamanan::where('aksi', 'akses_anonim')->count(),
        ];

        return view('admin.keamanan', compact('daftar', 'ringkas', 'aksi'));
    }
}
