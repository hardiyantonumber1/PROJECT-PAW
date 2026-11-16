@extends('layouts.app')

@section('judul', 'Log Keamanan')
@section('crumb', 'Pengawasan / Log Keamanan')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Log keamanan</div>
            <div class="sub">Percobaan masuk, pemblokiran, akses ditolak, dan pembukaan identitas pelapor anonim.</div>
        </div>
    </div>

    <div class="grid-4">
        <div class="stat">
            <div class="stat-k">Total peristiwa</div>
            <div class="stat-v">{{ $ringkas['total'] }}</div>
            <div class="stat-f">Seluruh catatan tersimpan</div>
        </div>
        <div class="stat">
            <div class="stat-k">Masuk gagal</div>
            <div class="stat-v" style="color:#B45309">{{ $ringkas['gagal'] }}</div>
            <div class="stat-f">Kredensial tidak cocok</div>
        </div>
        <div class="stat">
            <div class="stat-k">Diblokir rate limit</div>
            <div class="stat-v" style="color:#B91C1C">{{ $ringkas['diblokir'] }}</div>
            <div class="stat-f">Lebih dari 5 percobaan/menit</div>
        </div>
        <div class="stat">
            <div class="stat-k">Akses ditolak</div>
            <div class="stat-v" style="color:#6D28D9">{{ $ringkas['ditolak'] }}</div>
            <div class="stat-f">{{ $ringkas['anonim'] }} pembukaan identitas anonim</div>
        </div>
    </div>

    <div class="card">
        <div class="card-h">
            <div class="card-t">Peristiwa keamanan</div>
            <span class="badge b-netral" style="margin-left:auto">{{ $daftar->total() }} baris</span>
        </div>

        <div style="padding:12px 16px;border-bottom:1px solid var(--line-2)">
            <div class="chips">
                <a class="chip {{ $aksi === '' ? 'on' : '' }}" href="{{ route('admin.keamanan') }}">Semua</a>
                @foreach (['login_berhasil' => 'Masuk', 'login_gagal' => 'Gagal', 'login_diblokir' => 'Diblokir', 'akses_ditolak' => 'Ditolak', 'akses_anonim' => 'Anonim', 'logout' => 'Keluar'] as $k => $label)
                    <a class="chip {{ $aksi === $k ? 'on' : '' }}" href="{{ route('admin.keamanan', ['aksi' => $k]) }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Peristiwa</th>
                        <th>Akun</th>
                        <th>Alamat IP</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $log)
                        <tr>
                            <td style="color:var(--ink-3);white-space:nowrap">
                                {{ $log->terjadi_pada?->translatedFormat('j M Y') }}
                                <span class="meta" style="display:block">{{ $log->terjadi_pada?->format('H.i') }}</span>
                            </td>
                            <td><span class="badge {{ $log->badge() }}"><i></i>{{ $log->label() }}</span></td>
                            <td style="color:var(--ink-2)">
                                {{ $log->pengguna?->nama ?? '—' }}
                                <span class="meta" style="display:block">{{ $log->email ?? '—' }}</span>
                            </td>
                            <td style="color:var(--ink-3);font-variant-numeric:tabular-nums">{{ $log->ip ?? '—' }}</td>
                            <td style="color:var(--ink-2);max-width:360px">{{ $log->keterangan ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty">
                                    <b>Belum ada peristiwa</b>
                                    Catatan muncul setelah ada aktivitas masuk, keluar, atau akses yang ditolak.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.paginasi', ['paginator' => $daftar, 'keterangan' => 'peristiwa'])

        <div style="padding:12px 16px;border-top:1px solid var(--line-2);font-size:11px;color:var(--ink-3);line-height:1.7">
            Email disimpan tersamar (mis. <b>ma***@student.ub.ac.id</b>) agar tabel log tidak menjadi sumber kebocoran data pribadi.
            Pembatasan laju: 5 percobaan masuk per menit per kombinasi email + IP.
        </div>
    </div>
@endsection
