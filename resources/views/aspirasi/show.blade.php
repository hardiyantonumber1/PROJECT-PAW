@extends('layouts.app')

@section('judul', $aspirasi->judul)
@section('crumb', 'Aspirasi / Detail')

@section('isi')
    <a href="{{ auth()->user()->role === \App\Enums\Role::Mahasiswa ? route('aspirasi.index') : url()->previous() }}"
       style="display:inline-flex;align-items:center;gap:8px;font-size:11.5px;color:var(--ink-3);margin-bottom:-4px">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h13M13 6.5 18.5 12 13 17.5"/>
        </svg>
        <span style="transform:rotate(180deg);display:inline-flex">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h13M13 6.5 18.5 12 13 17.5"/>
            </svg>
        </span>Kembali
    </a>

    <div class="page-head">
        <div>
            <div class="h1">{{ $aspirasi->judul }}</div>
            <div style="display:flex;align-items:center;gap:7px;margin-top:9px;flex-wrap:wrap">
                <span class="badge {{ $aspirasi->status->badge() }}"><i></i>{{ $aspirasi->status->value }}</span>
                <span class="badge b-netral">{{ $aspirasi->kode }}</span>
                <span class="badge b-netral">{{ $aspirasi->kategori->nama }}</span>
                <span class="badge b-netral">{{ $aspirasi->unit->nama }}</span>
                @if ($aspirasi->status === \App\Enums\StatusAspirasi::Selesai)
                    <span class="badge b-selesai"><i></i>Selesai {{ $aspirasi->selesai_pada?->translatedFormat('j M') }}</span>
                @elseif ($aspirasi->status === \App\Enums\StatusAspirasi::Ditolak)
                    <span class="badge b-tolak"><i></i>Ditolak</span>
                @elseif ($aspirasi->terlambat)
                    <span class="badge b-late"><i></i>Lewat {{ abs($aspirasi->hari_tersisa) }} hari</span>
                @else
                    <span class="badge b-netral">Tenggat {{ $aspirasi->tenggat->translatedFormat('j M') }}</span>
                @endif
            </div>
        </div>
        <div class="head-actions">
            <button type="button" class="btn btn-s" onclick="window.print()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13.6 3.5H7.2a1.8 1.8 0 0 0-1.8 1.8v13.4a1.8 1.8 0 0 0 1.8 1.8h9.6a1.8 1.8 0 0 0 1.8-1.8V8.4l-5-4.9Z"/>
                    <path d="M13.4 3.7v4.6h4.8"/>
                </svg>Cetak
            </button>
            @can('delete', $aspirasi)
                <button type="button" class="btn btn-d btn-sm" style="height:37px"
                        onclick="konfirmasiHapus('{{ route('aspirasi.destroy', $aspirasi) }}', 'Hapus aspirasi ini? Tindakan tidak dapat dibatalkan.')">
                    Hapus
                </button>
            @endcan
            @can('update', $aspirasi)
                <a class="btn btn-p" href="{{ route('aspirasi.edit', $aspirasi) }}">Ubah aspirasi</a>
            @endcan
        </div>
    </div>

    @if ($aspirasi->status === \App\Enums\StatusAspirasi::Ditolak)
        <div class="card">
            <div class="card-b" style="display:flex;gap:11px;align-items:flex-start">
                <span style="width:30px;height:30px;flex:0 0 30px;border-radius:9px;background:#FDECEC;color:#B91C1C;display:grid;place-items:center">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/>
                    </svg>
                </span>
                <div>
                    <div style="font-size:12.5px;font-weight:700">Aspirasi ditolak</div>
                    <div style="font-size:11.5px;color:var(--ink-2);line-height:1.7;margin-top:3px">
                        {{ $aspirasi->alasan_penolakan ?: 'Tidak ada alasan yang dicatat.' }}
                    </div>
                </div>
            </div>
        </div>
    @else
        @php
            $langkah = match ($aspirasi->status) {
                \App\Enums\StatusAspirasi::Diajukan => 1,
                \App\Enums\StatusAspirasi::Diverifikasi => 2,
                \App\Enums\StatusAspirasi::Diproses => 3,
                \App\Enums\StatusAspirasi::Selesai => 4,
                default => 1,
            };
            $namaLangkah = ['Diajukan', 'Diverifikasi', 'Diproses', 'Selesai'];
        @endphp
        <div class="card">
            <div class="card-b" style="padding:16px 20px">
                <div class="stepper">
                    @foreach ($namaLangkah as $i => $label)
                        @php $no = $i + 1; @endphp
                        <div class="step {{ $no < $langkah ? 'done' : ($no === $langkah ? 'now' : '') }}">
                            <div class="step-dot">{{ $no < $langkah ? '✓' : $no }}</div>{{ $label }}
                        </div>
                        @if (! $loop->last)
                            <div class="step-bar {{ $no < $langkah ? 'done' : '' }}"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="grid-2e">
        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="card">
                <div class="card-h">
                    <div class="card-t">Uraian aspirasi</div>
                    <div class="card-link">Diajukan {{ $aspirasi->tanggal_masuk->translatedFormat('j M Y H.i') }}</div>
                </div>
                <div class="card-b" style="font-size:12.5px;line-height:1.8;color:var(--ink-2)">
                    {!! nl2br(e($aspirasi->isi)) !!}
                    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--line-2);font-size:11px;color:var(--ink-3)">
                        Terakhir diperbarui {{ $aspirasi->updated_at->translatedFormat('j M Y H.i') }}
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-h">
                    <div class="card-t">Riwayat tindak lanjut</div>
                    <span class="badge b-netral" style="margin-left:auto">{{ $aspirasi->tindakLanjut->count() }} catatan</span>
                </div>
                <div class="card-b">
                    <div class="timeline">
                        @foreach ($aspirasi->tindakLanjut as $tl)
                            <div class="tl">
                                <div class="tl-dot {{ $loop->first ? 'now' : 'ok' }}"></div>
                                <div class="tl-h">
                                    @if ($tl->status_awal && $tl->status_awal !== $tl->status_akhir)
                                        <span class="badge {{ $tl->status_awal->badge() }}"><i></i>{{ $tl->status_awal->value }}</span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12h13M13 6.5 18.5 12 13 17.5"/>
                                        </svg>
                                    @endif
                                    <span class="badge {{ $tl->status_akhir->badge() }}"><i></i>{{ $tl->status_akhir->value }}</span>
                                </div>
                                <div class="tl-t">
                                    {{ $tl->pengguna->nama }} · {{ $tl->pengguna->unit?->nama ?? 'Administrator' }} ·
                                    {{ $tl->tanggal->translatedFormat('j M Y H.i') }}
                                </div>
                                @if ($tl->tanggapan)
                                    <div class="tl-b">{{ $tl->tanggapan }}</div>
                                @endif
                            </div>
                        @endforeach

                        <div class="tl">
                            <div class="tl-dot"></div>
                            <div class="tl-h">Aspirasi diajukan <span class="badge b-netral">status awal</span></div>
                            <div class="tl-t">
                                {{ $aspirasi->pengguna->nama }} · {{ $aspirasi->tanggal_masuk->translatedFormat('j M Y H.i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="card">
                <div class="card-h">
                    <div class="card-t">Informasi</div>
                </div>
                <div class="card-b" style="display:flex;flex-direction:column;gap:12px">
                    <div class="kv">
                        <div class="kv-row">
                            <div class="kv-k">Pelapor</div>
                            <div class="kv-v">{{ $aspirasi->pengguna->nama }}</div>
                        </div>
                        <div class="kv-row">
                            <div class="kv-k">Kategori</div>
                            <div class="kv-v">{{ $aspirasi->kategori->nama }}</div>
                        </div>
                        <div class="kv-row">
                            <div class="kv-k">Unit tujuan</div>
                            <div class="kv-v">{{ $aspirasi->unit->nama }}</div>
                        </div>
                        <div class="kv-row">
                            <div class="kv-k">Tanggal masuk</div>
                            <div class="kv-v">{{ $aspirasi->tanggal_masuk->translatedFormat('j M Y H.i') }}</div>
                        </div>
                        <div class="kv-row">
                            <div class="kv-k">Tenggat</div>
                            <div class="kv-v">{{ $aspirasi->tenggat->translatedFormat('j M Y H.i') }}</div>
                        </div>
                        <div class="kv-row">
                            <div class="kv-k">Selesai pada</div>
                            <div class="kv-v" style="color:var(--ink-3);font-weight:500">
                                {{ $aspirasi->selesai_pada?->translatedFormat('j M Y H.i') ?? 'Belum selesai' }}
                            </div>
                        </div>
                    </div>

                    @if ($aspirasi->status !== \App\Enums\StatusAspirasi::Selesai && $aspirasi->status !== \App\Enums\StatusAspirasi::Ditolak)
                        @php
                            $totalHari = max(1, $aspirasi->tanggal_masuk->diffInDays($aspirasi->tenggat));
                            $sisaHari = max(0, (int) now()->diffInDays($aspirasi->tenggat, false));
                        @endphp
                        <div style="border-top:1px solid var(--line-2);padding-top:12px">
                            <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--ink-3);margin-bottom:6px">
                                <span>Sisa waktu</span>
                                <span style="color:{{ $aspirasi->terlambat ? '#B91C1C' : '#B45309' }};font-weight:700">
                                    {{ $sisaHari }} dari {{ $totalHari }} hari
                                </span>
                            </div>
                            <div class="progress">
                                <span class="{{ $aspirasi->terlambat ? 'late' : ($aspirasi->progres > 70 ? 'warn' : '') }}"
                                      style="width:{{ min(100, $aspirasi->progres) }}%"></span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($petugas)
                <div class="card">
                    <div class="card-h">
                        <div class="card-t">Petugas penanggung jawab</div>
                    </div>
                    <div class="card-b" style="display:flex;align-items:center;gap:11px">
                        <div class="avatar" style="width:38px;height:38px;flex:0 0 38px">{{ $petugas->inisial }}</div>
                        <div>
                            <div style="font-size:12.5px;font-weight:700">{{ $petugas->nama }}</div>
                            <div style="font-size:10.5px;color:var(--ink-3)">
                                {{ $petugas->role->label() }} {{ $petugas->unit?->nama }} · menangani sejak
                                {{ $aspirasi->tindakLanjut->first()->tanggal->translatedFormat('j M') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="alert alert-info">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 4.2 21 19.4H3L12 4.2Z"/>
                    <path d="M12 9.6v4.2"/>
                    <circle cx="12" cy="16.6" r="1" fill="currentColor" stroke="none"/>
                </svg>
                <div>Status hanya dapat diubah oleh petugas sesuai alur <b>Diajukan → Diverifikasi → Diproses → Selesai</b>.</div>
            </div>
        </div>
    </div>
@endsection
