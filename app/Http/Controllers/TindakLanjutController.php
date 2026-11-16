<?php

namespace App\Http\Controllers;

use App\Enums\StatusAspirasi;
use App\Http\Requests\TindakLanjutRequest;
use App\Models\Aspirasi;
use App\Models\TindakLanjut;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TindakLanjutController extends Controller
{
    public function form(Request $request, Aspirasi $aspirasi): View
    {
        Gate::authorize('tindakLanjut', $aspirasi);

        $aspirasi->load(['pengguna.unit', 'kategori', 'unit', 'tindakLanjut.pengguna.unit']);

        // RB-01: hanya status berikutnya yang ditawarkan.
        $pilihan = collect($aspirasi->status->berikutnya())
            ->map(fn (StatusAspirasi $s) => ['status' => $s, 'ket' => $s->keterangan()])
            ->all();

        // FR-16: petugas boleh menambah tanggapan tanpa mengubah status.
        if (! $aspirasi->status->final()) {
            array_unshift($pilihan, [
                'status' => $aspirasi->status,
                'ket' => 'Tambahkan tanggapan tanpa mengubah status.',
            ]);
        }

        return view('petugas.tindak-lanjut', compact('aspirasi', 'pilihan'));
    }

    public function store(TindakLanjutRequest $request, Aspirasi $aspirasi): RedirectResponse
    {
        Gate::authorize('tindakLanjut', $aspirasi);

        $data = $request->validated();
        $tujuan = StatusAspirasi::from($data['status_akhir']);
        $tetap = $tujuan === $aspirasi->status; // FR-16: tanggapan tanpa ubah status.

        if (! $tetap && ! $aspirasi->status->bolehBerubahKe($tujuan)) {
            return back()
                ->withInput()
                ->withErrors(['status_akhir' => 'Status '.$aspirasi->status->value.' tidak dapat langsung diubah menjadi '.$tujuan->value.'.']);
        }

        DB::transaction(function () use ($request, $aspirasi, $tujuan, $data) {
            TindakLanjut::create([
                'aspirasi_id' => $aspirasi->id,
                'pengguna_id' => $request->user()->id,
                'status_awal' => $aspirasi->status,
                'status_akhir' => $tujuan,
                'tanggapan' => $data['tanggapan'] ?? null,
                'tanggal' => now(),
            ]);

            /*
             * Setiap tindak lanjut dihitung sebagai "sudah ditangani":
             * EskalasiService memakai kolom ini untuk menahan eskalasi selama
             * masa tenggang respons, dan penanggung jawab dicatat sekali
             * (petugas pertama yang menangani).
             */
            $aspirasi->forceFill([
                'ditangani_pada' => now(),
                'penanggung_jawab_id' => $aspirasi->penanggung_jawab_id ?? $request->user()->id,
            ]);

            if ($tujuan === $aspirasi->status) {
                $aspirasi->save(); // status tidak berubah, cukup catat tanggapan.

                return;
            }

            $aspirasi->status = $tujuan;

            if ($tujuan === StatusAspirasi::Selesai) {
                $aspirasi->selesai_pada = now();
            }

            if ($tujuan === StatusAspirasi::Ditolak) {
                $aspirasi->alasan_penolakan = $data['alasan_penolakan'] ?? null;
            }

            $aspirasi->save();
        });

        return redirect()
            ->route('petugas.antrian')
            ->with('sukses', $tetap
                ? 'Tanggapan untuk '.$aspirasi->kode.' ditambahkan tanpa mengubah status.'
                : 'Status '.$aspirasi->kode.' diperbarui menjadi '.$tujuan->value.'.');
    }
}
