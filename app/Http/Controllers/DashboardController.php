<?php

namespace App\Http\Controllers;

use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use App\Models\TindakLanjut;
use App\Support\Ringkasan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $dasar = Aspirasi::query()->terlihatOleh($user);

        $total = (clone $dasar)->count();
        $berjalan = (clone $dasar)->berjalan()->count();
        $terlambat = (clone $dasar)->terlambat()->count();
        $selesai = (clone $dasar)->where('status', StatusAspirasi::Selesai->value)->count();

        $mendekati = (clone $dasar)->berjalan()
            ->whereBetween('tenggat', [now(), now()->addDays(3)])
            ->count();

        $lewat = (clone $dasar)->terlambat()->pluck('tenggat');
        $lewatMax = $lewat->isEmpty()
            ? 0
            : (int) $lewat->map(fn ($t) => $t->startOfDay()->diffInDays(now()->startOfDay()))->max();

        $hitung = Ringkasan::hitungStatus($dasar);
        $donut = Ringkasan::donut($hitung, $total);

        $rataHari = Ringkasan::rataHari(
            (clone $dasar)->whereNotNull('selesai_pada')->get(['tanggal_masuk', 'selesai_pada'])
        );

        $terbaru = (clone $dasar)->with(['kategori', 'unit'])
            ->orderByDesc('tanggal_masuk')
            ->limit(5)
            ->get();

        $aktivitas = TindakLanjut::query()
            ->whereHas('aspirasi', fn ($q) => $q->terlihatOleh($user))
            ->with(['aspirasi', 'pengguna.unit'])
            ->orderByDesc('tanggal')
            ->limit(4)
            ->get();

        $jam = (int) now()->format('H');
        $sapaan = match (true) {
            $jam < 11 => 'pagi',
            $jam < 15 => 'siang',
            $jam < 18 => 'sore',
            default => 'malam',
        };

        $namaDepan = explode(' ', trim($user->nama))[0];
        $tanggalPanjang = now()->translatedFormat('l, j F Y');

        return view('dashboard', compact(
            'total', 'berjalan', 'terlambat', 'selesai', 'mendekati', 'lewatMax',
            'hitung', 'donut', 'rataHari', 'terbaru', 'aktivitas',
            'sapaan', 'namaDepan', 'tanggalPanjang'
        ));
    }
}
