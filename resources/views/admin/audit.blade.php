@extends('layouts.app')

@section('judul', 'Jejak Audit')
@section('crumb', 'Pengawasan / Jejak Audit')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Jejak audit perubahan</div>
            <div class="sub">Catatan tetap setiap perubahan data: siapa, kapan, kolom apa, dari nilai apa menjadi apa.</div>
        </div>
    </div>

    <div class="grid-4">
        <div class="stat">
            <div class="stat-k">Total catatan</div>
            <div class="stat-v">{{ $ringkas['total'] }}</div>
            <div class="stat-f">{{ $ringkas['hari_ini'] }} hari ini</div>
        </div>
        <div class="stat">
            <div class="stat-k">Perubahan status</div>
            <div class="stat-v" style="color:#6D28D9">{{ $ringkas['status'] }}</div>
            <div class="stat-f">Alur penanganan aspirasi</div>
        </div>
        <div class="stat">
            <div class="stat-k">Eskalasi</div>
            <div class="stat-v" style="color:#B45309">{{ $ringkas['eskalasi'] }}</div>
            <div class="stat-f">Kenaikan level otomatis</div>
        </div>
        <div class="stat">
            <div class="stat-k">Penghapusan</div>
            <div class="stat-v" style="color:#B91C1C">{{ $ringkas['hapus'] }}</div>
            <div class="stat-f">Soft delete, data tetap tersimpan</div>
        </div>
    </div>

    <div class="card">
        <div class="card-h">
            <div class="card-t">Riwayat perubahan</div>
            <span class="badge b-netral" style="margin-left:auto">{{ $daftar->total() }} baris</span>
        </div>

        <div style="padding:12px 16px;border-bottom:1px solid var(--line-2);display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="chips">
                <a class="chip {{ $aksi === '' ? 'on' : '' }}" href="{{ route('admin.audit', array_filter(['q' => $cari])) }}">Semua</a>
                @foreach (['dibuat', 'diubah', 'status', 'tindak_lanjut', 'eskalasi', 'dihapus'] as $a)
                    <a class="chip {{ $aksi === $a ? 'on' : '' }}" href="{{ route('admin.audit', array_filter(['aksi' => $a, 'q' => $cari])) }}">{{ ucfirst(str_replace('_', ' ', $a)) }}</a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.audit') }}" style="margin-left:auto;display:flex;gap:8px">
                @if ($aksi !== '')
                    <input type="hidden" name="aksi" value="{{ $aksi }}">
                @endif
                <div class="input" style="height:34px;width:230px">
                    <input name="q" value="{{ $cari }}" placeholder="Cari kode tiket, aktor, keterangan">
                </div>
                <button type="submit" class="btn btn-s btn-sm" style="height:34px">Cari</button>
            </form>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Aksi</th>
                        <th>Oleh</th>
                        <th>Objek</th>
                        <th>Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $r)
                        <tr>
                            <td style="color:var(--ink-3);white-space:nowrap">
                                {{ $r->terjadi_pada?->translatedFormat('j M Y') }}
                                <span class="meta" style="display:block">{{ $r->terjadi_pada?->format('H.i') }}</span>
                            </td>
                            <td><span class="badge {{ $r->badge() }}"><i></i>{{ $r->labelAksi() }}</span></td>
                            <td style="color:var(--ink-2)">
                                {{ $r->aktor ?? 'Sistem' }}
                                <span class="meta" style="display:block">{{ $r->aktor_peran ?? 'sistem' }}</span>
                            </td>
                            <td>
                                @if ($r->aspirasi)
                                    <a class="ttl" href="{{ route('aspirasi.show', $r->aspirasi) }}">{{ $r->aspirasi->kode }}</a>
                                    <span class="meta">{{ \Illuminate\Support\Str::limit($r->aspirasi->judul, 40) }}</span>
                                @else
                                    <span class="ttl">{{ $r->entitas }}</span>
                                    <span class="meta">#{{ $r->entitas_id ?? '—' }}</span>
                                @endif
                            </td>
                            <td style="color:var(--ink-2);max-width:340px">
                                @if ($r->field)
                                    <span style="font-weight:600">{{ $r->keterangan ?? $r->field }}</span>
                                    <span class="meta" style="display:block">
                                        {{ \Illuminate\Support\Str::limit($r->nilai_lama ?? '—', 60) }}
                                        →
                                        {{ \Illuminate\Support\Str::limit($r->nilai_baru ?? '—', 60) }}
                                    </span>
                                @else
                                    {{ \Illuminate\Support\Str::limit($r->nilai_baru ?? $r->keterangan ?? '—', 90) }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty">
                                    <b>Belum ada catatan</b>
                                    Jejak audit akan terisi otomatis begitu ada data yang dibuat atau diubah.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.paginasi', ['paginator' => $daftar, 'keterangan' => 'catatan'])

        <div style="padding:12px 16px;border-top:1px solid var(--line-2);font-size:11px;color:var(--ink-3)">
            Tabel ini hanya bertambah — aplikasi tidak menyediakan jalur untuk mengubah atau menghapus catatan audit.
        </div>
    </div>
@endsection
