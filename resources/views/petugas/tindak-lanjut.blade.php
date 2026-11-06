@extends('layouts.app')

@section('judul', 'Tindak Lanjut')
@section('crumb', 'Antrian / Tindak lanjut')

@section('isi')
    <a href="{{ route('petugas.antrian') }}"
       style="display:inline-flex;align-items:center;gap:8px;font-size:11.5px;color:var(--ink-3);margin-bottom:-4px">
        <span style="transform:rotate(180deg);display:inline-flex">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h13M13 6.5 18.5 12 13 17.5"/>
            </svg>
        </span>Kembali ke antrian
    </a>

    <div class="page-head">
        <div>
            <div class="h1">{{ $aspirasi->judul }}</div>
            <div style="display:flex;align-items:center;gap:7px;margin-top:9px;flex-wrap:wrap">
                <span class="badge {{ $aspirasi->status->badge() }}"><i></i>{{ $aspirasi->status->value }}</span>
                <span class="badge b-netral">{{ $aspirasi->kode }}</span>
                <span class="badge b-netral">{{ $aspirasi->kategori->nama }}</span>
                <span class="badge b-netral">{{ $aspirasi->unit->nama }}</span>
                @if ($aspirasi->terlambat)
                    <span class="badge b-late"><i></i>Lewat tenggat {{ abs($aspirasi->hari_tersisa) }} hari</span>
                @endif
            </div>
        </div>
        <div class="head-actions">
            <span style="font-size:11px;color:var(--ink-3)">
                Tenggat {{ $aspirasi->tenggat->translatedFormat('j M Y') }}
            </span>
        </div>
    </div>

    <div class="grid-2e">
        <div style="display:flex;flex-direction:column;gap:16px">
            @if ($aspirasi->terlambat)
                <div class="alert alert-danger">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 4.2 21 19.4H3L12 4.2Z"/><path d="M12 9.6v4.2"/>
                        <circle cx="12" cy="16.6" r="1" fill="currentColor" stroke="none"/>
                    </svg>
                    <div>
                        <b>Aspirasi ini sudah melewati tenggat {{ abs($aspirasi->hari_tersisa) }} hari.</b>
                        Tenggat dihitung dari tanggal masuk ({{ $aspirasi->tanggal_masuk->translatedFormat('j M') }})
                        ditambah batas hari kategori {{ $aspirasi->kategori->nama }} ({{ $aspirasi->kategori->batas_hari }} hari).
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-h">
                    <div class="card-t">Uraian aspirasi</div>
                    <div class="card-link">Masuk {{ $aspirasi->tanggal_masuk->translatedFormat('j M Y H.i') }}</div>
                </div>
                <div class="card-b" style="font-size:12.5px;line-height:1.8;color:var(--ink-2)">
                    {!! nl2br(e($aspirasi->isi)) !!}
                </div>
            </div>

            <div class="card">
                <div class="card-h">
                    <div class="card-t">Data pelapor</div>
                </div>
                <div class="card-b" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="kv">
                        <div class="kv-row">
                            <div class="kv-k" style="width:78px;flex:0 0 78px">Nama</div>
                            <div class="kv-v">{{ $aspirasi->pengguna->nama }}</div>
                        </div>
                        <div class="kv-row">
                            <div class="kv-k" style="width:78px;flex:0 0 78px">Peran</div>
                            <div class="kv-v">{{ $aspirasi->pengguna->role->value }}</div>
                        </div>
                        <div class="kv-row">
                            <div class="kv-k" style="width:78px;flex:0 0 78px">Unit</div>
                            <div class="kv-v">{{ $aspirasi->pengguna->unit?->nama ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="kv">
                        <div class="kv-row">
                            <div class="kv-k" style="width:78px;flex:0 0 78px">Email</div>
                            <div class="kv-v" style="font-weight:500;font-size:11.5px">{{ $aspirasi->pengguna->email }}</div>
                        </div>
                        <div class="kv-row">
                            <div class="kv-k" style="width:78px;flex:0 0 78px">Kategori</div>
                            <div class="kv-v">{{ $aspirasi->kategori->nama }} · {{ $aspirasi->kategori->batas_hari }} hari</div>
                        </div>
                        <div class="kv-row">
                            <div class="kv-k" style="width:78px;flex:0 0 78px">Unit tujuan</div>
                            <div class="kv-v">{{ $aspirasi->unit->nama }}</div>
                        </div>
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
                        <div class="tl">
                            <div class="tl-dot"></div>
                            <div class="tl-h">Aspirasi diajukan <span class="badge b-netral">status awal</span></div>
                            <div class="tl-t">
                                {{ $aspirasi->pengguna->nama }} · {{ $aspirasi->tanggal_masuk->translatedFormat('j M Y H.i') }}
                            </div>
                        </div>

                        @forelse ($aspirasi->tindakLanjut as $tl)
                            <div class="tl">
                                <div class="tl-dot ok"></div>
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
                                    {{ $tl->pengguna->nama }} · {{ $tl->tanggal->translatedFormat('j M Y H.i') }} · {{ $tl->kode }}
                                </div>
                                @if ($tl->tanggapan)
                                    <div class="tl-b">{{ $tl->tanggapan }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="tl">
                                <div class="tl-dot now"></div>
                                <div class="tl-h" style="color:var(--ink-3);font-weight:600">Belum ada tindak lanjut dari petugas</div>
                                <div class="tl-t">Catatan pertama akan tersimpan saat Anda menyimpan formulir di samping.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="align-self:start;position:sticky;top:16px">
            <div class="card-h">
                <div class="card-t">Form tindak lanjut</div>
                <span class="badge {{ $aspirasi->status->badge() }}" style="margin-left:auto"><i></i>{{ $aspirasi->status->value }}</span>
            </div>

            @if (empty($pilihan))
                <div class="card-b">
                    <div class="alert alert-info" style="margin:0">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 4.2 21 19.4H3L12 4.2Z"/><path d="M12 9.6v4.2"/>
                            <circle cx="12" cy="16.6" r="1" fill="currentColor" stroke="none"/>
                        </svg>
                        <div>Status <b>{{ $aspirasi->status->value }}</b> sudah final, tidak ada perubahan status lagi.</div>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('petugas.tindak-lanjut.store', $aspirasi) }}" id="form-tl">
                    @csrf
                    <div class="card-b" style="display:flex;flex-direction:column;gap:15px">
                        <div class="field">
                            <div class="label">Status baru <span>*</span></div>
                            <div style="display:flex;flex-direction:column;gap:8px">
                                @foreach ($pilihan as $p)
                                    <label class="input {{ $errors->has('status_akhir') ? 'err' : '' }}"
                                           style="height:auto;padding:11px 12px;align-items:flex-start;gap:10px;cursor:pointer">
                                        <input type="radio" name="status_akhir" value="{{ $p['status']->value }}" required
                                               data-tolak="{{ $p['status'] === \App\Enums\StatusAspirasi::Ditolak ? '1' : '0' }}"
                                               {{ old('status_akhir') === $p['status']->value ? 'checked' : '' }}
                                               style="width:15px;height:15px;flex:0 0 15px;margin-top:2px">
                                        <div>
                                            <div style="font-size:12px;font-weight:700">{{ $p['status']->value }}</div>
                                            <div style="font-size:10.5px;color:var(--ink-3);margin-top:1px">{{ $p['ket'] }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('status_akhir')
                                <div class="hint" style="color:#B91C1C">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field" id="field-alasan" style="display:none">
                            <label class="label" for="alasan_penolakan">Alasan penolakan <span>*</span></label>
                            <div class="input textarea {{ $errors->has('alasan_penolakan') ? 'err' : '' }}" style="min-height:96px">
                                <textarea id="alasan_penolakan" name="alasan_penolakan" maxlength="500"
                                          placeholder="Jelaskan alasan aspirasi tidak dapat ditindaklanjuti">{{ old('alasan_penolakan') }}</textarea>
                            </div>
                            @error('alasan_penolakan')
                                <div class="hint" style="color:#B91C1C">{{ $message }}</div>
                            @else
                                <div class="hint">Wajib diisi saat status diubah menjadi Ditolak. Alasan akan terlihat oleh pelapor.</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label" for="tanggapan">Tanggapan petugas</label>
                            <div class="input textarea {{ $errors->has('tanggapan') ? 'err' : '' }}" style="min-height:70px">
                                <textarea id="tanggapan" name="tanggapan" maxlength="1000"
                                          placeholder="Tuliskan tindakan yang sudah atau akan dilakukan…">{{ old('tanggapan') }}</textarea>
                            </div>
                            @error('tanggapan')
                                <div class="hint" style="color:#B91C1C">{{ $message }}</div>
                            @enderror
                        </div>

                        <div style="background:#FAFBFD;border:1px solid var(--line-2);border-radius:10px;padding:11px 12px">
                            <div style="font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3);margin-bottom:7px">
                                Akan dicatat sebagai riwayat
                            </div>
                            <div style="display:flex;align-items:center;gap:7px;font-size:11px;flex-wrap:wrap">
                                <span class="badge {{ $aspirasi->status->badge() }}"><i></i>{{ $aspirasi->status->value }}</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h13M13 6.5 18.5 12 13 17.5"/>
                                </svg>
                                <span class="badge b-netral" id="pratinjau-status">pilih status</span>
                            </div>
                            <div style="font-size:10.5px;color:var(--ink-3);margin-top:7px">
                                {{ auth()->user()->nama }} · {{ now()->translatedFormat('j M Y H.i') }} · tindak_lanjut baru
                            </div>
                        </div>

                        <div style="display:flex;gap:9px">
                            <a class="btn btn-s" style="flex:1" href="{{ route('petugas.antrian') }}">Batal</a>
                            <button type="submit" class="btn btn-p" style="flex:1.3" id="tombol-simpan">Simpan tindak lanjut</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('form-tl');
            if (!form) return;
            const fieldAlasan = document.getElementById('field-alasan');
            const alasan = document.getElementById('alasan_penolakan');
            const tombol = document.getElementById('tombol-simpan');
            const pratinjau = document.getElementById('pratinjau-status');

            function segarkan() {
                const dipilih = form.querySelector('input[name="status_akhir"]:checked');
                const tolak = dipilih && dipilih.dataset.tolak === '1';
                fieldAlasan.style.display = tolak ? '' : 'none';
                alasan.required = !!tolak;
                tombol.textContent = tolak ? 'Simpan penolakan' : 'Simpan tindak lanjut';
                tombol.className = 'btn ' + (tolak ? 'btn-d' : 'btn-p');
                pratinjau.textContent = dipilih ? dipilih.value : 'pilih status';
            }

            form.querySelectorAll('input[name="status_akhir"]').forEach(r => r.addEventListener('change', segarkan));
            segarkan();
        })();
    </script>
@endsection
