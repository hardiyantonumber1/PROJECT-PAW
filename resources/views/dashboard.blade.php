@extends('layouts.app')

@section('judul', 'Dashboard')
@section('crumb', 'Dashboard')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Selamat {{ $sapaan }}, {{ $namaDepan }}</div>
            <div class="sub">
                {{ $tanggalPanjang }} · {{ $berjalan }} aspirasi sedang ditangani{{ $terlambat > 0 ? ', '.$terlambat.' di antaranya melewati tenggat' : '' }}.
            </div>
        </div>
        <div class="head-actions">
            <a class="btn btn-s" href="{{ route('aspirasi.ekspor') }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13.6 3.5H7.2a1.8 1.8 0 0 0-1.8 1.8v13.4a1.8 1.8 0 0 0 1.8 1.8h9.6a1.8 1.8 0 0 0 1.8-1.8V8.4l-5-4.9Z"/>
                    <path d="M13.4 3.7v4.6h4.8"/>
                </svg>Ekspor rekap
            </a>
            <a class="btn btn-p" href="{{ route('aspirasi.create') }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><path d="M12 8.5v7M8.5 12h7"/>
                </svg>Ajukan aspirasi
            </a>
        </div>
    </div>

    <div class="grid-4">
        <div class="stat">
            <div class="stat-k">Total aspirasi</div>
            <div class="stat-v">{{ $total }}</div>
            <div class="stat-f">Sepanjang semester ganjil</div>
            <div class="stat-ic" style="background:#EFF4FF;color:#1D4ED8">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3.5 13.5h4l1.5 3h6l1.5-3h4"/>
                    <path d="M5.6 5.4h12.8a2 2 0 0 1 1.9 1.4l1.2 6.7v3a2 2 0 0 1-2 2H4.5a2 2 0 0 1-2-2v-3l1.2-6.7a2 2 0 0 1 1.9-1.4Z"/>
                </svg>
            </div>
        </div>
        <div class="stat">
            <div class="stat-k">Sedang diproses</div>
            <div class="stat-v" style="color:#B45309">{{ $berjalan }}</div>
            <div class="stat-f">{{ $mendekati }} mendekati tenggat</div>
            <div class="stat-ic" style="background:#FEF6E7;color:#B45309">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="8.6"/><path d="M12 7.4V12l3.2 2"/>
                </svg>
            </div>
        </div>
        <div class="stat">
            <div class="stat-k">Selesai</div>
            <div class="stat-v" style="color:#0B7A55">{{ $selesai }}</div>
            <div class="stat-f">
                @if ($rataHari !== null)
                    Rata-rata {{ $rataHari }} hari penanganan
                @else
                    Belum ada aspirasi selesai
                @endif
            </div>
            <div class="stat-ic" style="background:#E8F8F1;color:#0B7A55">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><path d="m8.4 12.2 2.5 2.5 4.7-4.9"/>
                </svg>
            </div>
        </div>
        <div class="stat">
            <div class="stat-k">Terlambat</div>
            <div class="stat-v" style="color:#B91C1C">{{ $terlambat }}</div>
            <div class="stat-f" style="color:{{ $terlambat > 0 ? '#B91C1C' : 'var(--ink-3)' }}">
                @if ($terlambat > 0)
                    Melewati tenggat hingga {{ $lewatMax }} hari
                @else
                    Semua masih dalam tenggat
                @endif
            </div>
            <div class="stat-ic" style="background:#FDECEC;color:#B91C1C">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 4.2 21 19.4H3L12 4.2Z"/><path d="M12 9.6v4.2"/>
                    <circle cx="12" cy="16.6" r="1" fill="currentColor" stroke="none"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-h">
                <div class="card-t">Aspirasi terbaru</div>
                <a class="card-link" href="{{ route('aspirasi.index') }}">Lihat semua
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h13M13 6.5 18.5 12 13 17.5"/>
                    </svg>
                </a>
            </div>

            @if ($terbaru->isEmpty())
                <div class="empty">
                    <b>Belum ada aspirasi</b>
                    Mulai dengan menekan tombol “Ajukan aspirasi”.
                </div>
            @else
                <div class="tbl-wrap">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Judul &amp; kategori</th>
                                <th>Status</th>
                                <th style="text-align:right">Tenggat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($terbaru as $a)
                                <tr class="{{ $a->terlambat ? 'row-late' : '' }}">
                                    <td>
                                        <a class="ttl" href="{{ route('aspirasi.show', $a) }}">{{ $a->judul }}</a>
                                        <span class="meta">{{ $a->kategori->nama }} · {{ $a->unit->nama }}</span>
                                    </td>
                                    <td><span class="badge {{ $a->status->badge() }}"><i></i>{{ $a->status->value }}</span></td>
                                    <td style="text-align:right;white-space:nowrap">@include('partials.tenggat', ['a' => $a])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="card">
                <div class="card-h">
                    <div class="card-t">Ringkasan status</div>
                    <span class="badge b-netral" style="margin-left:auto">{{ $total }} aspirasi</span>
                </div>
                <div class="card-b" style="display:flex;gap:20px;align-items:center">
                    <div class="donut" style="background:{{ $donut }}">
                        <div class="donut-c">
                            <div style="text-align:center">
                                <div style="font-size:22px;font-weight:800;letter-spacing:-.03em;line-height:1">{{ $total }}</div>
                                <div style="font-size:9.5px;color:var(--ink-3);margin-top:2px">total</div>
                            </div>
                        </div>
                    </div>
                    <div class="legend">
                        @foreach ($hitung as $status => $jumlah)
                            <div class="lg">
                                <i style="background:{{ \App\Enums\StatusAspirasi::from($status)->warna() }}"></i>
                                {{ $status }}<b>{{ $jumlah }}</b>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-h">
                    <div class="card-t">Aktivitas terakhir</div>
                </div>
                <div class="card-b">
                    @if ($aktivitas->isEmpty())
                        <div class="empty" style="padding:12px 0">
                            <b>Belum ada aktivitas</b>
                            Riwayat penanganan akan muncul di sini.
                        </div>
                    @else
                        <div class="timeline">
                            @foreach ($aktivitas as $tl)
                                <div class="tl">
                                    <div class="tl-dot {{ $loop->first ? 'now' : '' }}"></div>
                                    <div class="tl-h">
                                        Status menjadi {{ $tl->status_akhir->value }}
                                        <span class="badge {{ $tl->status_akhir->badge() }}"><i></i>{{ $tl->status_akhir->value }}</span>
                                    </div>
                                    <div class="tl-t">
                                        {{ \Illuminate\Support\Str::limit($tl->aspirasi->judul, 26) }} ·
                                        {{ $tl->pengguna->nama }} ({{ $tl->pengguna->unit?->nama ?? '-' }}) ·
                                        {{ $tl->tanggal->translatedFormat('j M H.i') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
