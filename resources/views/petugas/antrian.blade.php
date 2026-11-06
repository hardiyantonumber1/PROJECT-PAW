@extends('layouts.app')

@section('judul', 'Antrian Tugas')
@section('crumb', 'Antrian / Tugas')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Antrian tugas</div>
            <div class="sub">
                Unit {{ $unit?->nama ?? 'tanpa unit' }} · {{ $aktif }} aspirasi menunggu tindakan{{ $terlambat > 0 ? ', '.$terlambat.' sudah melewati tenggat' : '' }}.
            </div>
        </div>
        <div class="head-actions">
            <a class="btn btn-s" href="{{ route('petugas.antrian', ['saring' => 'lewat']) }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3.8 5.4h16.4l-6.4 7.4v5.6l-3.6 1.8v-7.4L3.8 5.4Z"/>
                </svg>Hanya lewat tenggat
            </a>
            @if ($berikutnya)
                <a class="btn btn-p" href="{{ route('petugas.tindak-lanjut', $berikutnya) }}">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"/><path d="m8.4 12.2 2.5 2.5 4.7-4.9"/>
                    </svg>Ambil tugas berikutnya
                </a>
            @else
                <span class="btn btn-s" style="opacity:.55;cursor:default">Tidak ada tugas tersisa</span>
            @endif
        </div>
    </div>

    <div class="grid-4">
        <div class="stat">
            <div class="stat-k">Menunggu verifikasi</div>
            <div class="stat-v">{{ $menunggu }}</div>
            <div class="stat-f">Belum disentuh petugas</div>
            <div class="stat-ic" style="background:#EAF1FF;color:#1D4ED8">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3.5 13.5h4l1.5 3h6l1.5-3h4"/>
                    <path d="M5.6 5.4h12.8a2 2 0 0 1 1.9 1.4l1.2 6.7v3a2 2 0 0 1-2 2H4.5a2 2 0 0 1-2-2v-3l1.2-6.7a2 2 0 0 1 1.9-1.4Z"/>
                </svg>
            </div>
        </div>
        <div class="stat">
            <div class="stat-k">Sedang diproses</div>
            <div class="stat-v" style="color:#B45309">{{ $diproses }}</div>
            <div class="stat-f">Ditangani unit {{ $unit?->nama ?? '-' }}</div>
            <div class="stat-ic" style="background:#FEF6E7;color:#B45309">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="8.6"/><path d="M12 7.4V12l3.2 2"/>
                </svg>
            </div>
        </div>
        <div class="stat">
            <div class="stat-k">Melewati tenggat</div>
            <div class="stat-v" style="color:#B91C1C">{{ $terlambat }}</div>
            <div class="stat-f" style="color:{{ $terlambat > 0 ? '#B91C1C' : 'var(--ink-3)' }}">
                {{ $terlambat > 0 ? 'Perlu tindakan hari ini' : 'Tidak ada keterlambatan' }}
            </div>
            <div class="stat-ic" style="background:#FDECEC;color:#B91C1C">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 4.2 21 19.4H3L12 4.2Z"/><path d="M12 9.6v4.2"/>
                    <circle cx="12" cy="16.6" r="1" fill="currentColor" stroke="none"/>
                </svg>
            </div>
        </div>
        <div class="stat">
            <div class="stat-k">Selesai bulan ini</div>
            <div class="stat-v" style="color:#0B7A55">{{ $selesaiBulan }}</div>
            <div class="stat-f">
                {{ $rataHari !== null ? 'Rata-rata '.str_replace('.', ',', (string) $rataHari).' hari' : 'Belum ada yang selesai' }}
            </div>
            <div class="stat-ic" style="background:#E8F8F1;color:#0B7A55">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><path d="m8.4 12.2 2.5 2.5 4.7-4.9"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('petugas.antrian') }}"
              style="display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--line-2);flex-wrap:wrap">
            <div class="chips">
                <a class="chip {{ $saring === 'aktif' ? 'on' : '' }}" href="{{ route('petugas.antrian', ['urut' => $urut]) }}">
                    Unit saya ({{ $unit?->nama ?? '-' }}) <span class="n">{{ $totalUnit }}</span>
                </a>
                <a class="chip {{ $saring === 'lewat' ? 'on' : '' }}" href="{{ route('petugas.antrian', ['saring' => 'lewat', 'urut' => $urut]) }}">
                    Lewat tenggat <span class="n">{{ $terlambat }}</span>
                </a>
                <a class="chip {{ $saring === 'selesai' ? 'on' : '' }}" href="{{ route('petugas.antrian', ['saring' => 'selesai', 'urut' => $urut]) }}">
                    Selesai <span class="n">{{ $selesaiUnit }}</span>
                </a>
            </div>
            <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
                <span style="font-size:11px;color:var(--ink-3)">Urut:</span>
                <div class="input" style="height:33px;font-size:11.5px;width:168px">
                    <select name="urut" onchange="this.form.submit()">
                        <option value="tenggat" {{ $urut === 'tenggat' ? 'selected' : '' }}>Tenggat terdekat</option>
                        <option value="terbaru" {{ $urut === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                        <option value="judul" {{ $urut === 'judul' ? 'selected' : '' }}>Judul A–Z</option>
                    </select>
                    <span class="chev">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9.5 6 6 6-6"/>
                        </svg>
                    </span>
                </div>
                @if ($saring !== 'aktif')
                    <input type="hidden" name="saring" value="{{ $saring }}">
                @endif
            </div>
        </form>

        @if ($daftar->isEmpty())
            <div class="empty">
                <b>Tidak ada tugas pada filter ini</b>
                Semua aspirasi unit Anda sudah ditindaklanjuti.
            </div>
        @else
            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Aspirasi</th>
                            <th>Pelapor</th>
                            <th>Status</th>
                            <th>Tenggat &amp; SLA</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $a)
                            <tr class="{{ $a->terlambat ? 'row-late' : '' }}">
                                <td>
                                    <a class="ttl" href="{{ route('aspirasi.show', $a) }}">{{ $a->judul }}</a>
                                    <span class="meta">{{ $a->kode }} · {{ $a->kategori->nama }}</span>
                                </td>
                                <td style="color:var(--ink-2)">
                                    {{ $a->pengguna->nama }} · {{ $a->pengguna->unit?->nama ?? '-' }}
                                </td>
                                <td><span class="badge {{ $a->status->badge() }}"><i></i>{{ $a->status->value }}</span></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;min-width:150px">
                                        <div class="progress" style="width:74px">
                                            <span class="{{ $a->terlambat ? 'bad' : ($a->progres > 70 ? 'warn' : 'ok') }}"
                                                  style="width:{{ min(100, max(6, $a->progres)) }}%"></span>
                                        </div>
                                        <div>
                                            <div style="font-size:11px;font-weight:600">{{ $a->tenggat->translatedFormat('j M Y') }}</div>
                                            <div style="font-size:10px;font-weight:{{ $a->terlambat ? '700' : '500' }};color:{{ $a->terlambat ? '#B91C1C' : 'var(--ink-3)' }}">
                                                {{ $a->label_tenggat }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align:right;white-space:nowrap">
                                    <a class="btn btn-p btn-sm" href="{{ route('petugas.tindak-lanjut', $a) }}">Tindak lanjut</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('partials.paginasi', ['paginator' => $daftar, 'keterangan' => 'tugas'])
        @endif
    </div>
@endsection
