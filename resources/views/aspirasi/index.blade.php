@extends('layouts.app')

@php
    // View ini dipakai dua rute: aspirasi.index (milik sendiri) dan aspirasi.semua (petugas/admin).
    $ruteDaftar = $ruteDaftar ?? 'aspirasi.index';
    $semua = $semua ?? false;
@endphp

@section('judul', $semua ? 'Semua Aspirasi' : 'Aspirasi Saya')
@section('crumb', 'Aspirasi / '.(auth()->user()->isMahasiswa() ? 'Daftar' : 'Semua'))

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">{{ $semua ? 'Semua aspirasi' : 'Aspirasi saya' }}</div>
            <div class="sub">
                {{ $total }} aspirasi tercatat · {{ $menunggu }} masih menunggu verifikasi petugas.
            </div>
        </div>
        <div class="head-actions">
            <a class="btn btn-s" href="{{ route('aspirasi.ekspor') }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13.6 3.5H7.2a1.8 1.8 0 0 0-1.8 1.8v13.4a1.8 1.8 0 0 0 1.8 1.8h9.6a1.8 1.8 0 0 0 1.8-1.8V8.4l-5-4.9Z"/>
                    <path d="M13.4 3.7v4.6h4.8"/>
                </svg>Ekspor
            </a>
            @if (auth()->user()->isMahasiswa())
                <a class="btn btn-p" href="{{ route('aspirasi.create') }}">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"/><path d="M12 8.5v7M8.5 12h7"/>
                    </svg>Ajukan aspirasi
                </a>
            @endif
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route($ruteDaftar) }}"
              style="display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--line-2);flex-wrap:wrap">
            <div class="chips">
                <a class="chip {{ $status === '' ? 'on' : '' }}" href="{{ route($ruteDaftar, array_filter(['kategori' => $kategoriId, 'urut' => $urut])) }}">
                    Semua <span class="n">{{ $total }}</span>
                </a>
                @foreach ($hitung as $nama => $jumlah)
                    <a class="chip {{ $status === $nama ? 'on' : '' }}"
                       href="{{ route($ruteDaftar, array_filter(['status' => $nama, 'kategori' => $kategoriId, 'urut' => $urut])) }}">
                        {{ $nama }} <span class="n">{{ $jumlah }}</span>
                    </a>
                @endforeach
            </div>

            <div style="margin-left:auto;display:flex;gap:8px">
                @if ($status !== '')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif

                <div class="input" style="height:33px;font-size:11.5px;width:150px">
                    <select name="kategori" onchange="this.form.submit()">
                        <option value="">Semua kategori</option>
                        @foreach ($kategori as $k)
                            <option value="{{ $k->id }}" {{ (int) $kategoriId === $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    <span class="chev">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9.5 6 6 6-6"/>
                        </svg>
                    </span>
                </div>

                <div class="input" style="height:33px;font-size:11.5px;width:160px">
                    <select name="urut" onchange="this.form.submit()">
                        <option value="terbaru" {{ $urut === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                        <option value="tenggat" {{ $urut === 'tenggat' ? 'selected' : '' }}>Tenggat terdekat</option>
                        <option value="judul" {{ $urut === 'judul' ? 'selected' : '' }}>Judul A–Z</option>
                    </select>
                    <span class="chev">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9.5 6 6 6-6"/>
                        </svg>
                    </span>
                </div>

                <button type="submit" class="icon-btn" style="height:33px;width:33px" title="Terapkan filter">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3.8 5.4h16.4l-6.4 7.4v5.6l-3.6 1.8v-7.4L3.8 5.4Z"/>
                    </svg>
                </button>
            </div>
        </form>

        @if ($aspirasi->isEmpty())
            <div class="empty">
                <b>Belum ada aspirasi pada filter ini</b>
                Ubah filter atau ajukan aspirasi baru.
            </div>
        @else
            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Aspirasi</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Tenggat</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aspirasi as $a)
                            <tr class="{{ $a->terlambat ? 'row-late' : '' }}">
                                <td>
                                    <a class="ttl" href="{{ route('aspirasi.show', $a) }}">{{ $a->judul }}</a>
                                    <span class="meta">{{ $a->kode }} · diajukan {{ $a->tanggal_masuk->translatedFormat('j M Y') }}</span>
                                </td>
                                <td><span class="badge b-netral">{{ $a->kategori->nama }}</span></td>
                                <td style="color:var(--ink-2)">{{ $a->unit->nama }}</td>
                                <td><span class="badge {{ $a->status->badge() }}"><i></i>{{ $a->status->value }}</span></td>
                                <td>
                                    @if ($a->status === \App\Enums\StatusAspirasi::Selesai)
                                        <div style="font-size:11.5px;font-weight:600">{{ $a->tenggat->translatedFormat('j M Y') }}</div>
                                        <div style="color:#0B7A55;font-size:10.5px;font-weight:600">Selesai</div>
                                    @elseif ($a->status === \App\Enums\StatusAspirasi::Ditolak)
                                        <div style="font-size:11.5px;font-weight:600">{{ $a->tenggat->translatedFormat('j M Y') }}</div>
                                        <div style="color:#B91C1C;font-size:10.5px;font-weight:600">Ditolak</div>
                                    @else
                                        <div style="font-size:11.5px;font-weight:600">{{ $a->tenggat->translatedFormat('j M Y') }}</div>
                                        <div style="font-size:10.5px;font-weight:600;color:{{ $a->terlambat ? '#B91C1C' : 'var(--ink-3)' }}">
                                            {{ $a->label_tenggat }}
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align:right;white-space:nowrap">
                                    <a class="btn btn-s btn-sm" href="{{ route('aspirasi.show', $a) }}">Detail</a>
                                    @can('update', $a)
                                        <a class="btn btn-s btn-sm" href="{{ route('aspirasi.edit', $a) }}">Ubah</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('partials.paginasi', ['paginator' => $aspirasi])
        @endif
    </div>

    <div style="display:flex;gap:12px;align-items:center;padding:0 2px">
        <div style="display:flex;align-items:center;gap:7px;font-size:11px;color:var(--ink-3)">
            <span style="width:6px;height:6px;border-radius:50%;background:#DC2626"></span>
            Baris dengan garis merah berarti sudah melewati tenggat (dihitung saat data ditampilkan).
        </div>
    </div>
@endsection
