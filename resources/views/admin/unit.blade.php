@extends('layouts.app')

@section('judul', 'Unit Penanganan')
@section('crumb', 'Master / Unit')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Unit penanganan</div>
            <div class="sub">Unit adalah pihak yang berwenang menindaklanjuti aspirasi. Satu unit menaungi beberapa petugas.</div>
        </div>
        <div class="head-actions">
            <button type="button" class="btn btn-p" onclick="bukaUnit()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><path d="M12 8.5v7M8.5 12h7"/>
                </svg>Tambah unit
            </button>
        </div>
    </div>

    <div class="grid-4">
        <div class="stat">
            <div class="stat-k">Total unit</div>
            <div class="stat-v">{{ $unit->count() }}</div>
            <div class="stat-f">{{ $unit->where('petugas_count', '>', 0)->count() }} unit punya petugas</div>
        </div>
        <div class="stat">
            <div class="stat-k">Petugas terdaftar</div>
            <div class="stat-v">{{ $totalPetugas }}</div>
            <div class="stat-f">Tersebar di {{ $unit->where('petugas_count', '>', 0)->count() }} unit</div>
        </div>
        <div class="stat">
            <div class="stat-k">Aspirasi ditangani</div>
            <div class="stat-v">{{ $totalAspirasi }}</div>
            <div class="stat-f">Seluruh aspirasi masuk</div>
        </div>
        <div class="stat">
            <div class="stat-k">Beban tertinggi</div>
            <div class="stat-v" style="color:#B45309">{{ $terbanyak?->aspirasi_aktif ?? 0 }}</div>
            <div class="stat-f">{{ $terbanyak?->nama ?? 'Belum ada' }} · aspirasi aktif</div>
        </div>
    </div>

    <div class="card">
        <div class="card-h">
            <div class="card-t">Daftar unit</div>
            <form method="GET" action="{{ route('admin.unit') }}" style="margin-left:auto;display:flex;gap:8px;align-items:center">
                <div class="input" style="height:32px;font-size:11.5px;width:190px">
                    <input type="search" name="q" value="{{ $q }}" placeholder="Cari unit…">
                    <span class="suffix" style="color:var(--ink-3)">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="6.4"/><path d="m16 16 4.2 4.2"/>
                        </svg>
                    </span>
                </div>
            </form>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Nama unit</th>
                        <th>Petugas</th>
                        <th>Aspirasi aktif</th>
                        <th>Rata-rata selesai</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unit as $u)
                        <tr>
                            <td>
                                <span class="ttl">{{ $u->nama }}</span>
                                <span class="meta">{{ $u->petugas_count }} petugas · {{ $u->aspirasi_count }} aspirasi</span>
                            </td>
                            <td>
                                @if ($u->petugas->isEmpty())
                                    <span style="font-size:11.5px;color:var(--ink-3)">Belum ada petugas</span>
                                @else
                                    <div style="display:flex;align-items:center">
                                        @foreach ($u->petugas->take(2) as $p)
                                            <div class="avatar" style="width:26px;height:26px;font-size:9.5px;flex:0 0 26px;{{ $loop->index > 0 ? 'margin-left:-7px;border:2px solid #fff;' : '' }}">{{ $p->inisial }}</div>
                                        @endforeach
                                        @if ($u->petugas->count() > 2)
                                            <span style="margin-left:8px;font-size:11.5px;color:var(--ink-3)">+{{ $u->petugas->count() - 2 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                <b>{{ $u->aspirasi_aktif }}</b>
                                <span style="color:var(--ink-3);font-size:11px">aspirasi</span>
                            </td>
                            <td style="color:{{ $u->rata_selesai === null ? 'var(--ink-3)' : 'var(--ink-2)' }}">
                                {{ $u->rata_selesai === null ? '—' : str_replace('.', ',', (string) $u->rata_selesai).' hari' }}
                            </td>
                            <td style="text-align:right">
                                <span style="display:inline-flex;gap:5px">
                                    <button type="button" class="btn btn-s btn-sm" style="width:30px;padding:0" title="Ubah {{ $u->nama }}"
                                            onclick="bukaUnit(@js(['id' => $u->id, 'nama' => $u->nama]))">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 20h4l10.5-10.5a2.1 2.1 0 0 0 0-3L17 5a2.1 2.1 0 0 0-3 0L3.5 15.5 3 20h1Z"/>
                                            <path d="m13.6 6.4 4 4"/>
                                        </svg>
                                    </button>
                                    @if ($u->aspirasi_count === 0 && $u->petugas_count === 0)
                                        <button type="button" class="btn btn-d btn-sm" style="width:30px;padding:0" title="Hapus {{ $u->nama }}"
                                                onclick="konfirmasiHapus('{{ route('admin.unit.destroy', $u) }}', 'Hapus unit {{ $u->nama }}? Unit ini belum memiliki aspirasi maupun petugas.')">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4.5 6.8h15"/>
                                                <path d="M9.5 6.8V4.9A1.4 1.4 0 0 1 10.9 3.5h2.2a1.4 1.4 0 0 1 1.4 1.4v1.9"/>
                                                <path d="M6.4 6.8 7.3 19a1.6 1.6 0 0 0 1.6 1.5h6.2a1.6 1.6 0 0 0 1.6-1.5l.9-12.2"/>
                                            </svg>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-s btn-sm" style="width:30px;padding:0;opacity:.4;cursor:not-allowed"
                                                title="Tidak dapat dihapus — masih dipakai petugas atau aspirasi">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4.5 6.8h15"/>
                                                <path d="M9.5 6.8V4.9A1.4 1.4 0 0 1 10.9 3.5h2.2a1.4 1.4 0 0 1 1.4 1.4v1.9"/>
                                                <path d="M6.4 6.8 7.3 19a1.6 1.6 0 0 0 1.6 1.5h6.2a1.6 1.6 0 0 0 1.6-1.5l.9-12.2"/>
                                            </svg>
                                        </button>
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty"><b>Tidak ada unit</b>Ubah kata kunci pencarian atau tambahkan unit baru.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-info">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 4.2 21 19.4H3L12 4.2Z"/><path d="M12 9.6v4.2"/>
            <circle cx="12" cy="16.6" r="1" fill="currentColor" stroke="none"/>
        </svg>
        <div>
            <b>Relasi pengguna–unit bersifat opsional.</b> Akun mahasiswa memiliki <code style="font-size:11px">unit_id</code> kosong (NULL),
            sedangkan petugas wajib terhubung ke satu unit agar antrian tugasnya terfilter otomatis.
        </div>
    </div>

    <div class="overlay" id="overlay-unit">
        <form class="modal" method="POST" action="{{ route('admin.unit.store') }}" id="form-unit" style="width:470px">
            @csrf
            <input type="hidden" name="_method" value="POST" id="metode-unit">
            <div class="modal-h">
                <div style="width:32px;height:32px;border-radius:9px;background:#EFF4FF;display:grid;place-items:center;color:#1D4ED8">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20h16M6 20V9.5L12 4l6 5.5V20"/>
                        <path d="M10 20v-4.5h4V20"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:13.5px;font-weight:700" id="judul-unit">Tambah unit</div>
                    <div style="font-size:11px;color:var(--ink-3)">Data master · tabel unit</div>
                </div>
                <button type="button" onclick="tutupUnit()" style="margin-left:auto;color:var(--ink-3);background:none;border:0;font-size:15px;cursor:pointer">✕</button>
            </div>
            <div class="modal-b">
                <div class="field">
                    <label class="label" for="nama-unit">Nama unit <span>*</span></label>
                    <div class="input">
                        <input id="nama-unit" name="nama" type="text" maxlength="100" required value="{{ old('nama') }}" placeholder="BAAK">
                    </div>
                    <div class="hint">Maksimal 100 karakter dan harus unik.</div>
                </div>
            </div>
            <div class="modal-f">
                <button type="button" class="btn btn-s" onclick="tutupUnit()">Batal</button>
                <button type="submit" class="btn btn-p">Simpan unit</button>
            </div>
        </form>
    </div>

    <script>
        const overlayUnit = document.getElementById('overlay-unit');
        const formUnit = document.getElementById('form-unit');

        function bukaUnit(data) {
            const ubah = !!data;
            formUnit.action = ubah ? '{{ url('admin/unit') }}/' + data.id : '{{ route('admin.unit.store') }}';
            document.getElementById('metode-unit').value = ubah ? 'PUT' : 'POST';
            document.getElementById('judul-unit').textContent = ubah ? 'Ubah unit' : 'Tambah unit';
            document.getElementById('nama-unit').value = ubah ? data.nama : '';
            overlayUnit.classList.add('buka');
            document.getElementById('nama-unit').focus();
        }

        function tutupUnit() { overlayUnit.classList.remove('buka'); }
        overlayUnit.addEventListener('click', e => { if (e.target === overlayUnit) tutupUnit(); });
    </script>
@endsection
