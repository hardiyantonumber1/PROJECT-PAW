@php
    $user = auth()->user();
@endphp

<div class="topbar">
    <div>
        <div class="crumb">Beranda <b>/</b> @yield('crumb', 'Dashboard')</div>
        <div class="page-title">@yield('judul', 'Dashboard')</div>
    </div>

    <form class="search" method="GET" action="{{ route('aspirasi.cari') }}" role="search">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="6.4"/><path d="m16 16 4.2 4.2"/>
        </svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari aspirasi, kategori, unit…"
               style="border:0;background:transparent;outline:none;font:inherit;color:inherit;width:100%">
    </form>

    <div class="icon-btn" title="Notifikasi">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 15.4V10a6 6 0 1 0-12 0v5.4L4.4 18h15.2L18 15.4Z"/>
            <path d="M9.8 20.6a2.4 2.4 0 0 0 4.4 0"/>
        </svg>
    </div>

    <div class="avatar" title="{{ $user->nama }}">{{ $user->inisial }}</div>
</div>
