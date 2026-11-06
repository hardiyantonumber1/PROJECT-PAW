@extends('layouts.app')

@section('judul', 'Rekapitulasi')
@section('crumb', 'Rekapitulasi')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Rekapitulasi</div>
            <div class="sub">
                Periode {{ $dari->translatedFormat('j M') }} – {{ $sampai->translatedFormat('j M Y') }} ·
                {{ $total }} aspirasi masuk dari {{ $perUnit->where('jumlah', '>', 0)->count() }} unit.
            </div>
        </div>
        <div class="head-actions">
            <form method="GET" action="{{ route('admin.rekap') }}" style="display:flex;gap:8px;align-items:center">
                <div class="input" style="height:37px;font-size:12px;width:150px">
                    <input type="date" name="dari" value="{{ $dari->toDateString() }}" onchange="this.form.submit()">
                </div>
                <div class="input" style="height:37px;font-size:12px;width:150px">
                    <input type="date" name="sampai" value="{{ $sampai->toDateString() }}" onchange="this.form.submit()">
                </div>
                <a class="btn btn-p" href="{{ route('admin.rekap.ekspor', ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()]) }}">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M13.6 3.5H7.2a1.8 1.8 0 0 0-1.8 1.8v13.4a1.8 1.8 0 0 0 1.8 1.8h9.6a1.8 1.8 0 0 0 1.8-1.8V8.4l-5-4.9Z"/>
                        <path d="M13.4 3.7v4.6h4.8"/>
                    </svg>Unduh laporan
                </a>
            </form>
        </div>
    </div>

    <div class="grid-4">
        <div class="stat">
            <div class="stat-k">Total aspirasi</div>
            <div class="stat-v">{{ $total }}</div>
            <div class="stat-f">Pada periode terpilih</div>
        </div>
        <div class="stat">
            <div class="stat-k">Tingkat penyelesaian</div>
            <div class="stat-v" style="color:#0B7A55">{{ $persenSelesai }}%</div>
            <div class="stat-f">{{ $selesai }} dari {{ $total }} selesai</div>
        </div>
        <div class="stat">
            <div class="stat-k">Rata-rata penanganan</div>
            <div class="stat-v">
                {{ $rataHari === null ? '—' : str_replace('.', ',', (string) $rataHari) }}
                @if ($rataHari !== null)
                    <span style="font-size:13px;font-weight:600;color:var(--ink-3)">hari</span>
                @endif
            </div>
            <div class="stat-f">Dari {{ $selesai }} aspirasi selesai</div>
        </div>
        <div class="stat">
            <div class="stat-k">Melewati tenggat</div>
            <div class="stat-v" style="color:#B91C1C">{{ $terlambat }}</div>
            <div class="stat-f" style="color:{{ $terlambat > 0 ? '#B91C1C' : 'var(--ink-3)' }}">{{ $persenTerlambat }}% dari total</div>
        </div>
    </div>

    <div class="grid-2" style="grid-template-columns:1fr 1fr">
        <div class="card">
            <div class="card-h">
                <div class="card-t">Aspirasi per kategori</div>
                <span class="badge b-netral" style="margin-left:auto">{{ $total }}</span>
            </div>
            <div class="card-b bars">
                @forelse ($perKategori as $baris)
                    <div class="bar-row">
                        <div style="color:var(--ink-2);font-weight:600">{{ $baris['nama'] }}</div>
                        <div class="bar-track">
                            <div class="bar-fill {{ $baris['kelas'] }}" style="width:{{ $baris['persen'] }}%"></div>
                        </div>
                        <div style="text-align:right;font-weight:700">{{ $baris['jumlah'] }}</div>
                    </div>
                @empty
                    <div class="empty"><b>Belum ada data</b>Tidak ada aspirasi pada periode ini.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-h">
                <div class="card-t">Aspirasi per unit</div>
                <span class="badge b-netral" style="margin-left:auto">{{ $total }}</span>
            </div>
            <div class="card-b bars">
                @forelse ($perUnit as $baris)
                    <div class="bar-row">
                        <div style="color:var(--ink-2);font-weight:600">{{ $baris['nama'] }}</div>
                        <div class="bar-track">
                            <div class="bar-fill {{ $baris['kelas'] }}" style="width:{{ $baris['persen'] }}%"></div>
                        </div>
                        <div style="text-align:right;font-weight:700">{{ $baris['jumlah'] }}</div>
                    </div>
                @empty
                    <div class="empty"><b>Belum ada data</b>Tidak ada aspirasi pada periode ini.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid-2" style="grid-template-columns:1.62fr 1fr">
        <div class="card">
            <div class="card-h">
                <div class="card-t">Aspirasi yang melewati tenggat</div>
                <span class="badge b-late" style="margin-left:auto"><i></i>{{ $terlambat }} aspirasi</span>
            </div>

            @if ($daftarTerlambat->isEmpty())
                <div class="empty">
                    <b>Tidak ada keterlambatan</b>
                    Semua aspirasi pada periode ini ditangani sebelum tenggat.
                </div>
            @else
                <div class="tbl-wrap">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Aspirasi</th>
                                <th>Unit</th>
                                <th>Batas hari</th>
                                <th style="text-align:right">Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftarTerlambat as $a)
                                <tr class="row-late">
                                    <td>
                                        <a class="ttl" href="{{ route('aspirasi.show', $a) }}">{{ \Illuminate\Support\Str::limit($a->judul, 44) }}</a>
                                        <span class="meta">{{ $a->kategori->nama }} · {{ $a->kode }}</span>
                                    </td>
                                    <td>{{ $a->unit->nama }}</td>
                                    <td>{{ $a->kategori->batas_hari }} hari</td>
                                    <td style="text-align:right">
                                        <span class="badge b-late"><i></i>{{ abs($a->hari_tersisa) }} hari</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:12px 16px;border-top:1px solid var(--line-2);font-size:11px;color:var(--ink-3)">
                    Keterlambatan dihitung saat laporan ditampilkan: <b>now() &gt; tenggat</b> dan status belum Selesai.
                </div>
            @endif
        </div>

        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="card">
                <div class="card-h">
                    <div class="card-t">Komposisi status</div>
                </div>
                <div class="card-b" style="display:flex;gap:18px;align-items:center">
                    <div class="donut" style="background:{{ $donut }}">
                        <div class="donut-c">
                            <div style="text-align:center">
                                <div style="font-size:21px;font-weight:800;letter-spacing:-.03em;line-height:1">{{ $total }}</div>
                                <div style="font-size:9.5px;color:var(--ink-3)">total</div>
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
                    <div class="card-t">Kepatuhan tenggat</div>
                </div>
                <div class="card-b" style="display:flex;flex-direction:column;gap:13px">
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:11.5px;margin-bottom:6px">
                            <span style="color:var(--ink-2)">Tepat waktu</span>
                            <b style="color:#0B7A55">{{ 100 - $persenTerlambat }}%</b>
                        </div>
                        <div class="progress"><span class="ok" style="width:{{ 100 - $persenTerlambat }}%"></span></div>
                    </div>
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:11.5px;margin-bottom:6px">
                            <span style="color:var(--ink-2)">Melewati tenggat</span>
                            <b style="color:#B91C1C">{{ $persenTerlambat }}%</b>
                        </div>
                        <div class="progress"><span class="bad" style="width:{{ $persenTerlambat }}%"></span></div>
                    </div>
                    <div style="border-top:1px solid var(--line-2);padding-top:11px;font-size:11px;color:var(--ink-3);line-height:1.7">
                        Rata-rata keterlambatan <b style="color:var(--ink)">{{ str_replace('.', ',', (string) $rataTerlambat) }} hari</b>.
                        @if ($unitDominan)
                            Unit {{ $unitDominan['nama'] }} menyumbang {{ $unitDominan['jumlah'] }} dari {{ $terlambat }} keterlambatan.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
