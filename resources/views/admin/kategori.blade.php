@extends('layouts.app')

@section('judul', 'Kategori Aspirasi')
@section('crumb', 'Master / Kategori')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Kategori aspirasi</div>
            <div class="sub">Kategori menentukan batas hari penanganan dan tenggat aspirasi yang masuk.</div>
        </div>
        <div class="head-actions">
            <a class="btn btn-s" href="{{ route('admin.rekap.ekspor') }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13.6 3.5H7.2a1.8 1.8 0 0 0-1.8 1.8v13.4a1.8 1.8 0 0 0 1.8 1.8h9.6a1.8 1.8 0 0 0 1.8-1.8V8.4l-5-4.9Z"/>
                    <path d="M13.4 3.7v4.6h4.8"/>
                </svg>Ekspor
            </a>
            <button type="button" class="btn btn-p" onclick="bukaKategori()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><path d="M12 8.5v7M8.5 12h7"/>
                </svg>Tambah kategori
            </button>
        </div>
    </div>

    <div class="grid-4">
        <div class="stat">
            <div class="stat-k">Total kategori</div>
            <div class="stat-v">{{ $kategori->count() }}</div>
            <div class="stat-f">Semua dapat dipakai</div>
        </div>
        <div class="stat">
            <div class="stat-k">Batas hari tercepat</div>
            <div class="stat-v">{{ $tercepat?->batas_hari ?? '—' }}</div>
            <div class="stat-f">{{ $tercepat?->nama ?? 'Belum ada' }} · {{ $tercepat?->aspirasi_count ?? 0 }} aspirasi</div>
        </div>
        <div class="stat">
            <div class="stat-k">Batas hari terlama</div>
            <div class="stat-v">{{ $terlama?->batas_hari ?? '—' }}</div>
            <div class="stat-f">{{ $terlama?->nama ?? 'Belum ada' }} · {{ $terlama?->aspirasi_count ?? 0 }} aspirasi</div>
        </div>
        <div class="stat">
            <div class="stat-k">Tidak dapat dihapus</div>
            <div class="stat-v" style="color:#B45309">{{ $terpakai }}</div>
            <div class="stat-f">Masih dipakai aspirasi</div>
        </div>
    </div>

    <div class="card">
        <div class="card-h">
            <div class="card-t">Daftar kategori</div>
            <span class="badge b-netral" style="margin-left:auto">{{ $kategori->count() }} baris</span>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Nama kategori</th>
                        <th>Batas hari</th>
                        <th>Dipakai</th>
                        <th>Dibuat</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kategori as $k)
                        <tr>
                            <td>
                                <span class="ttl">{{ $k->nama }}</span>
                                <span class="meta">Tenggat = tanggal masuk + {{ $k->batas_hari }} hari</span>
                            </td>
                            <td><span class="badge b-netral">{{ $k->batas_hari }} hari</span></td>
                            <td>{{ $k->aspirasi_count }} aspirasi</td>
                            <td style="color:var(--ink-3)">{{ $k->created_at?->translatedFormat('j M Y') }}</td>
                            <td style="text-align:right">
                                <span style="display:inline-flex;gap:5px">
                                    <button type="button" class="btn btn-s btn-sm" style="width:30px;padding:0"
                                            title="Ubah {{ $k->nama }}"
                                            onclick="bukaKategori(@js(['id' => $k->id, 'nama' => $k->nama, 'batas' => $k->batas_hari]))">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 20h4l10.5-10.5a2.1 2.1 0 0 0 0-3L17 5a2.1 2.1 0 0 0-3 0L3.5 15.5 3 20h1Z"/>
                                            <path d="m13.6 6.4 4 4"/>
                                        </svg>
                                    </button>
                                    @if ($k->aspirasi_count === 0)
                                        <button type="button" class="btn btn-d btn-sm" style="width:30px;padding:0" title="Hapus {{ $k->nama }}"
                                                onclick="konfirmasiHapus('{{ route('admin.kategori.destroy', $k) }}', 'Hapus kategori {{ $k->nama }}? Kategori ini belum dipakai aspirasi.')">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4.5 6.8h15"/>
                                                <path d="M9.5 6.8V4.9A1.4 1.4 0 0 1 10.9 3.5h2.2a1.4 1.4 0 0 1 1.4 1.4v1.9"/>
                                                <path d="M6.4 6.8 7.3 19a1.6 1.6 0 0 0 1.6 1.5h6.2a1.6 1.6 0 0 0 1.6-1.5l.9-12.2"/>
                                            </svg>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-s btn-sm" style="width:30px;padding:0;opacity:.4;cursor:not-allowed"
                                                title="Tidak dapat dihapus — masih dipakai {{ $k->aspirasi_count }} aspirasi">
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
                                <div class="empty"><b>Belum ada kategori</b>Tambahkan kategori pertama lewat tombol “Tambah kategori”.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:12px 16px;border-top:1px solid var(--line-2);font-size:11px;color:var(--ink-3)">
            Tombol hapus dinonaktifkan bila kategori masih dipakai aspirasi (<b>ON DELETE RESTRICT</b>).
        </div>
    </div>

    <div class="overlay" id="overlay-kategori">
        <form class="modal" method="POST" action="{{ route('admin.kategori.store') }}" id="form-kategori">
            @csrf
            <input type="hidden" name="_method" value="POST" id="metode-kategori">
            <div class="modal-h">
                <div style="width:32px;height:32px;border-radius:9px;background:#EFF4FF;display:grid;place-items:center;color:#1D4ED8">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11.2 3.5H19a1.5 1.5 0 0 1 1.5 1.5v7.8a2 2 0 0 1-.6 1.4l-6.4 6.4a2 2 0 0 1-2.8 0l-6.1-6.1a2 2 0 0 1 0-2.8l6.4-6.4a2 2 0 0 1 1.2-.4Z"/>
                        <circle cx="16.2" cy="7.8" r="1.5"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:13.5px;font-weight:700" id="judul-kategori">Tambah kategori</div>
                    <div style="font-size:11px;color:var(--ink-3)">Data master · tabel kategori</div>
                </div>
                <button type="button" onclick="tutupKategori()" style="margin-left:auto;color:var(--ink-3);background:none;border:0;font-size:15px;cursor:pointer">✕</button>
            </div>
            <div class="modal-b">
                <div class="field">
                    <label class="label" for="nama-kategori">Nama kategori <span>*</span></label>
                    <div class="input">
                        <input id="nama-kategori" name="nama" type="text" maxlength="100" required value="{{ old('nama') }}" placeholder="Kemahasiswaan">
                    </div>
                    <div class="hint">Maksimal 100 karakter dan harus unik.</div>
                </div>
                <div class="field">
                    <label class="label" for="batas-kategori">Batas hari penanganan <span>*</span></label>
                    <div class="input">
                        <input id="batas-kategori" name="batas_hari" type="number" min="1" max="365" required value="{{ old('batas_hari') }}" placeholder="7">
                        <span class="suffix">hari</span>
                    </div>
                    <div class="hint">Nilai 1–365 hari. Dipakai untuk menghitung <b>tenggat</b> = tanggal masuk + batas hari.</div>
                </div>
                <div class="alert alert-warn">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 4.2 21 19.4H3L12 4.2Z"/><path d="M12 9.6v4.2"/>
                        <circle cx="12" cy="16.6" r="1" fill="currentColor" stroke="none"/>
                    </svg>
                    <div>Perubahan batas hari <b>tidak berlaku surut</b> — aspirasi yang sudah masuk tetap memakai tenggat yang tersimpan.</div>
                </div>
            </div>
            <div class="modal-f">
                <button type="button" class="btn btn-s" onclick="tutupKategori()">Batal</button>
                <button type="submit" class="btn btn-p">Simpan kategori</button>
            </div>
        </form>
    </div>

    <script>
        const overlayKategori = document.getElementById('overlay-kategori');
        const formKategori = document.getElementById('form-kategori');

        function bukaKategori(data) {
            const ubah = !!data;
            formKategori.action = ubah
                ? '{{ url('admin/kategori') }}/' + data.id
                : '{{ route('admin.kategori.store') }}';
            document.getElementById('metode-kategori').value = ubah ? 'PUT' : 'POST';
            document.getElementById('judul-kategori').textContent = ubah ? 'Ubah kategori' : 'Tambah kategori';
            document.getElementById('nama-kategori').value = ubah ? data.nama : '';
            document.getElementById('batas-kategori').value = ubah ? data.batas : '';
            overlayKategori.classList.add('buka');
            document.getElementById('nama-kategori').focus();
        }

        function tutupKategori() {
            overlayKategori.classList.remove('buka');
        }

        overlayKategori.addEventListener('click', function (e) {
            if (e.target === overlayKategori) tutupKategori();
        });
    </script>
@endsection
