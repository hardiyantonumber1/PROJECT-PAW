@extends('layouts.app')

@section('judul', 'Eskalasi & SLA')
@section('crumb', 'Pengawasan / Eskalasi')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Eskalasi &amp; SLA</div>
            <div class="sub">Aspirasi yang melewati SLA respons, tenggat, atau masa tenggang — beserta alasannya.</div>
        </div>
        <div class="head-actions">
            @if (auth()->user()->isSuperAdmin())
                <form method="POST" action="{{ route('admin.eskalasi.jalankan') }}">
                    @csrf
                    <button type="submit" class="btn btn-p">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3.5 17.5 9 12l3.5 3.5L20.5 7"/><path d="M15.5 7h5v5"/>
                        </svg>Jalankan pemindaian
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid-4">
        <div class="stat">
            <div class="stat-k">Total eskalasi</div>
            <div class="stat-v">{{ $ringkas['total'] }}</div>
            <div class="stat-f">Seluruh riwayat kenaikan level</div>
        </div>
        <div class="stat">
            <div class="stat-k">L1 · belum ada respons</div>
            <div class="stat-v" style="color:#6D28D9">{{ $ringkas['l1'] }}</div>
            <div class="stat-f">Melewati SLA respons kategori</div>
        </div>
        <div class="stat">
            <div class="stat-k">L2 · tenggat lewat</div>
            <div class="stat-v" style="color:#B45309">{{ $ringkas['l2'] }}</div>
            <div class="stat-f">Melewati kolom tenggat</div>
        </div>
        <div class="stat">
            <div class="stat-k">L3 · pembiaran</div>
            <div class="stat-v" style="color:#B91C1C">{{ $ringkas['l3'] }}</div>
            <div class="stat-f">{{ $ringkas['berjalan'] }} masih berjalan</div>
        </div>
    </div>

    <div class="card">
        <div class="card-h">
            <div class="card-t">Riwayat eskalasi</div>
            <span class="badge b-netral" style="margin-left:auto">{{ $daftar->total() }} baris</span>
        </div>

        <div style="padding:12px 16px;border-bottom:1px solid var(--line-2)">
            <div class="chips">
                <a class="chip {{ $level === 0 ? 'on' : '' }}" href="{{ route('admin.eskalasi') }}">Semua level</a>
                @foreach ([1 => 'L1 · belum respons', 2 => 'L2 · tenggat lewat', 3 => 'L3 · pembiaran'] as $l => $label)
                    <a class="chip {{ $level === $l ? 'on' : '' }}" href="{{ route('admin.eskalasi', ['level' => $l]) }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Aspirasi</th>
                        <th>Level</th>
                        <th>Alasan</th>
                        <th>Unit</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $e)
                        <tr>
                            <td>
                                @if ($e->aspirasi)
                                    <a class="ttl" href="{{ route('aspirasi.show', $e->aspirasi) }}">{{ $e->aspirasi->judul }}</a>
                                    <span class="meta">{{ $e->aspirasi->kode }} · {{ $e->aspirasi->kategori?->nama }}</span>
                                @else
                                    <span class="ttl" style="color:var(--ink-3)">Data aspirasi tidak tersedia</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $e->badge() }}"><i></i>L{{ $e->level }}</span></td>
                            <td style="color:var(--ink-2);max-width:380px">
                                <span style="font-weight:600">{{ $e->label() }}</span>
                                <span class="meta" style="display:block">{{ $e->rincian }}</span>
                            </td>
                            <td style="color:var(--ink-2)">{{ $e->aspirasi?->unit?->nama ?? '—' }}</td>
                            <td style="color:var(--ink-3);white-space:nowrap">
                                {{ $e->terjadi_pada?->translatedFormat('j M Y H.i') }}
                                <span class="meta" style="display:block">lewat {{ $e->keterlambatan_jam }} jam</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty">
                                    <b>Belum ada eskalasi</b>
                                    Semua aspirasi masih dalam batas SLA, atau pemindaian belum pernah dijalankan.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.paginasi', ['paginator' => $daftar, 'keterangan' => 'eskalasi'])

        <div style="padding:12px 16px;border-top:1px solid var(--line-2);font-size:11px;color:var(--ink-3);line-height:1.7">
            Pemindaian otomatis berjalan tiap jam lewat <b>php artisan schedule:run</b>.
            Level hanya boleh naik, jeda minimal 60 menit antar eskalasi, dan setiap tindak lanjut petugas
            menahan eskalasi berikutnya selama masa tenggang respons.
            @if ($ringkas['terakhir'])
                Eskalasi terakhir: <b>{{ \Illuminate\Support\Carbon::parse($ringkas['terakhir'])->translatedFormat('j M Y H.i') }}</b>.
            @endif
        </div>
    </div>
@endsection
