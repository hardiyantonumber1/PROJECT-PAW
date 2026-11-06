@extends('layouts.app')

@section('judul', 'Hasil Pencarian')
@section('crumb', 'Pencarian')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Hasil pencarian</div>
            <div class="sub">
                @if ($q === '')
                    Masukkan kata kunci pada kolom pencarian di bilah atas.
                @else
                    {{ $hasil->total() }} aspirasi cocok dengan “{{ $q }}”.
                @endif
            </div>
        </div>
    </div>

    @if ($q === '')
        <div class="card">
            <div class="empty">
                <b>Belum ada kata kunci</b>
                Pencarian mencakup judul dan uraian aspirasi yang boleh Anda lihat.
            </div>
        </div>
    @elseif ($hasil->isEmpty())
        <div class="card">
            <div class="empty">
                <b>Tidak ada hasil untuk “{{ $q }}”</b>
                Coba kata kunci lain, atau periksa ejaan.
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-h">
                <div class="card-t">Aspirasi ditemukan</div>
                <span class="badge b-netral" style="margin-left:auto">{{ $hasil->total() }} baris</span>
            </div>

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
                        @foreach ($hasil as $a)
                            <tr class="{{ $a->terlambat ? 'row-late' : '' }}">
                                <td>
                                    <a class="ttl" href="{{ route('aspirasi.show', $a) }}">{{ $a->judul }}</a>
                                    <span class="meta">{{ $a->kode }} · {{ $a->pengguna->nama }}</span>
                                </td>
                                <td><span class="badge b-netral">{{ $a->kategori->nama }}</span></td>
                                <td style="color:var(--ink-2)">{{ $a->unit->nama }}</td>
                                <td><span class="badge {{ $a->status->badge() }}"><i></i>{{ $a->status->value }}</span></td>
                                <td>@include('partials.tenggat', ['a' => $a])</td>
                                <td style="text-align:right">
                                    <a class="btn btn-s btn-sm" href="{{ route('aspirasi.show', $a) }}">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('partials.paginasi', ['paginator' => $hasil, 'keterangan' => 'hasil'])
        </div>
    @endif
@endsection
