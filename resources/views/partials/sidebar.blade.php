@php
    /** @var \App\Models\Pengguna $user */
    $user = auth()->user();

    $ikon = [
        'grid' => '<rect x="3" y="3" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="2"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="2"/>',
        'tambah' => '<circle cx="12" cy="12" r="9"/><path d="M12 8.5v7M8.5 12h7"/>',
        'daftar' => '<path d="M8 6h13M8 12h13M8 18h13"/><circle cx="3.6" cy="6" r="1.1" fill="currentColor" stroke="none"/><circle cx="3.6" cy="12" r="1.1" fill="currentColor" stroke="none"/><circle cx="3.6" cy="18" r="1.1" fill="currentColor" stroke="none"/>',
        'antrian' => '<path d="M3.5 13.5h4l1.5 3h6l1.5-3h4"/><path d="M5.6 5.4h12.8a2 2 0 0 1 1.9 1.4l1.2 6.7v3a2 2 0 0 1-2 2H4.5a2 2 0 0 1-2-2v-3l1.2-6.7a2 2 0 0 1 1.9-1.4Z"/>',
        'rekap' => '<path d="M18 20V10M12 20V4M6 20v-4"/>',
        'kategori' => '<path d="M20.6 13.4 13 21l-9-9V4h8l8.6 8.6a2 2 0 0 1 0 2.8Z"/><circle cx="7.6" cy="7.6" r="1.3"/>',
        'unit' => '<rect x="3.2" y="7.2" width="17.6" height="12.6" rx="2"/><path d="M8.6 7.2V5.6A1.6 1.6 0 0 1 10.2 4h3.6a1.6 1.6 0 0 1 1.6 1.6v1.6"/><path d="M3.2 12.4h17.6"/>',
        'pengguna' => '<circle cx="9.2" cy="8.4" r="3.4"/><path d="M3 20c0-3.2 2.8-5.4 6.2-5.4S15.4 16.8 15.4 20"/><path d="M16.4 5.3a3.3 3.3 0 0 1 0 6.2M18 14.9c2.1.5 3.5 2.1 3.5 4.4"/>',
        'keluar' => '<path d="M14.5 8.2V5.6A1.6 1.6 0 0 0 12.9 4H5.6A1.6 1.6 0 0 0 4 5.6v12.8A1.6 1.6 0 0 0 5.6 20h7.3a1.6 1.6 0 0 0 1.6-1.6v-2.6"/><path d="M10.4 12h9.2M16.4 8.6 19.8 12l-3.4 3.4"/>',
    ];

    $menu = match (true) {
        $user->isMahasiswa() => [
            ['label' => 'Menu utama'],
            ['nama' => 'Dashboard', 'rute' => 'dashboard', 'ikon' => 'grid'],
            ['nama' => 'Ajukan Aspirasi', 'rute' => 'aspirasi.create', 'ikon' => 'tambah'],
            ['nama' => 'Aspirasi Saya', 'rute' => 'aspirasi.index', 'ikon' => 'daftar', 'badge' => $navAspirasi],
            ['label' => 'Akun'],
            ['nama' => 'Keluar', 'rute' => 'logout', 'ikon' => 'keluar', 'post' => true],
        ],
        $user->isPetugas() => [
            ['label' => 'Menu utama'],
            ['nama' => 'Antrian Tugas', 'rute' => 'petugas.antrian', 'ikon' => 'antrian', 'badge' => $navAntrian, 'hot' => true],
            ['nama' => 'Semua Aspirasi', 'rute' => 'aspirasi.semua', 'ikon' => 'daftar'],
            ['label' => 'Akun'],
            ['nama' => 'Keluar', 'rute' => 'logout', 'ikon' => 'keluar', 'post' => true],
        ],
        default => [
            ['label' => 'Menu utama'],
            ['nama' => 'Rekapitulasi', 'rute' => 'admin.rekap', 'ikon' => 'rekap'],
            ['nama' => 'Semua Aspirasi', 'rute' => 'aspirasi.semua', 'ikon' => 'daftar'],
            ['label' => 'Master data'],
            ['nama' => 'Kategori', 'rute' => 'admin.kategori', 'ikon' => 'kategori'],
            ['nama' => 'Unit', 'rute' => 'admin.unit', 'ikon' => 'unit'],
            ['nama' => 'Pengguna', 'rute' => 'admin.pengguna', 'ikon' => 'pengguna'],
            ['label' => 'Akun'],
            ['nama' => 'Keluar', 'rute' => 'logout', 'ikon' => 'keluar', 'post' => true],
        ],
    };
@endphp

<aside class="sidebar">
    <div class="brand">
        <div class="brand-mark">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 6.5h16M4 12h10M4 17.5h13"/>
            </svg>
        </div>
        <div>
            <div class="brand-name">SIAF</div>
            <div class="brand-sub">FILKOM UB</div>
        </div>
    </div>

    @foreach ($menu as $item)
        @if (isset($item['label']))
            <div class="nav-label">{{ $item['label'] }}</div>
            @continue
        @endif

        @php
            $aktif = request()->routeIs($item['rute']);
            $kelas = 'nav-item'.($aktif ? ' active' : '');
        @endphp

        @if (! empty($item['post']))
            <form method="POST" action="{{ route($item['rute']) }}">
                @csrf
                <button type="submit" class="{{ $kelas }}" style="width:100%;background:none;border:0;cursor:pointer;text-align:left;font-family:inherit">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $ikon[$item['ikon']] !!}</svg>
                    <span>{{ $item['nama'] }}</span>
                </button>
            </form>
        @else
            <a href="{{ route($item['rute']) }}" class="{{ $kelas }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $ikon[$item['ikon']] !!}</svg>
                <span>{{ $item['nama'] }}</span>
                @if (! empty($item['badge']))
                    <span class="nav-badge {{ ! empty($item['hot']) ? 'hot' : '' }}">{{ $item['badge'] }}</span>
                @endif
            </a>
        @endif
    @endforeach

    <div class="side-foot">
        <div class="avatar">{{ $user->inisial }}</div>
        <div style="min-width:0">
            <div class="side-name">{{ $user->nama_pendek }}</div>
            <div class="side-role">{{ $user->role->value }}{{ $user->unit ? ' · '.$user->unit->nama : '' }}</div>
        </div>
    </div>
</aside>
