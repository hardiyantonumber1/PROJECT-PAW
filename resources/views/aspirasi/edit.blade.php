@extends('layouts.app')

@section('judul', 'Ubah Aspirasi')
@section('crumb', 'Aspirasi / Ubah')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Ubah aspirasi</div>
            <div class="sub">{{ $aspirasi->kode }} · diajukan {{ $aspirasi->tanggal_masuk->translatedFormat('j M Y') }}</div>
        </div>
        <div class="head-actions">
            <a class="btn btn-s" href="{{ route('aspirasi.show', $aspirasi) }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.4 5.6 4 12l6.4 6.4"/><path d="M4 12h16"/>
                </svg>Kembali ke detail
            </a>
        </div>
    </div>

    <div class="alert alert-warn" style="margin-bottom:16px">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 4.2 21 19.4H3L12 4.2Z"/><path d="M12 9.6v4.2"/>
            <circle cx="12" cy="16.6" r="1" fill="currentColor" stroke="none"/>
        </svg>
        <div>
            Perubahan hanya diizinkan selama status masih <b>Diajukan</b>. Setelah petugas memverifikasi,
            isi aspirasi terkunci agar jejak penanganan tetap konsisten.
        </div>
    </div>

    @include('aspirasi._form', [
        'aksi' => route('aspirasi.update', $aspirasi),
        'aspirasi' => $aspirasi,
        'tombol' => 'Simpan perubahan',
        'batal' => route('aspirasi.show', $aspirasi),
    ])
@endsection
