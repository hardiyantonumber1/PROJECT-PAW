@extends('layouts.app')

@section('judul', 'Ajukan Aspirasi')
@section('crumb', 'Aspirasi / Ajukan')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Ajukan aspirasi</div>
            <div class="sub">Sampaikan permasalahan yang Anda temui. Tenggat penanganan dihitung otomatis dari kategori.</div>
        </div>
        <div class="head-actions">
            <a class="btn btn-s" href="{{ route('aspirasi.index') }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 6h13M8 12h13M8 18h13"/>
                    <circle cx="3.6" cy="6" r="1.1" fill="currentColor" stroke="none"/>
                    <circle cx="3.6" cy="12" r="1.1" fill="currentColor" stroke="none"/>
                    <circle cx="3.6" cy="18" r="1.1" fill="currentColor" stroke="none"/>
                </svg>Daftar aspirasi
            </a>
        </div>
    </div>

    @include('aspirasi._form', [
        'aksi' => route('aspirasi.store'),
        'aspirasi' => null,
        'tombol' => 'Kirim aspirasi',
        'batal' => route('dashboard'),
    ])
@endsection
