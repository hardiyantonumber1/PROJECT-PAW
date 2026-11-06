@extends('layouts.app')

@section('judul', 'Pengguna')
@section('crumb', 'Master / Pengguna')

@section('isi')
    <div class="page-head">
        <div>
            <div class="h1">Pengguna</div>
            <div class="sub">Kelola akun mahasiswa, petugas, dan admin. Akun tidak dihapus agar jejak penanganan tetap utuh.</div>
        </div>
        <div class="head-actions">
            <button type="button" class="btn btn-p" onclick="bukaPengguna()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><path d="M12 8.5v7M8.5 12h7"/>
                </svg>Tambah pengguna
            </button>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.pengguna') }}"
              style="display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--line-2);flex-wrap:wrap">
            <div class="chips">
                <a class="chip {{ $peran === '' ? 'on' : '' }}" href="{{ route('admin.pengguna', array_filter(['unit' => $unitId])) }}">
                    Semua <span class="n">{{ $jumlahPeran->sum() }}</span>
                </a>
                @foreach ($jumlahPeran as $nama => $jumlah)
                    <a class="chip {{ $peran === $nama ? 'on' : '' }}"
                       href="{{ route('admin.pengguna', array_filter(['peran' => $nama, 'unit' => $unitId])) }}">
                        {{ ucfirst($nama) }} <span class="n">{{ $jumlah }}</span>
                    </a>
                @endforeach
            </div>
            <div style="margin-left:auto;display:flex;gap:8px">
                @if ($peran !== '')
                    <input type="hidden" name="peran" value="{{ $peran }}">
                @endif
                <div class="input" style="height:33px;font-size:11.5px;width:160px">
                    <select name="unit" onchange="this.form.submit()">
                        <option value="">Semua unit</option>
                        @foreach ($unit as $u)
                            <option value="{{ $u->id }}" {{ (int) $unitId === $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                        @endforeach
                    </select>
                    <span class="chev">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9.5 6 6 6-6"/>
                        </svg>
                    </span>
                </div>
                <div class="input" style="height:33px;font-size:11.5px;width:190px">
                    <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama atau email…">
                </div>
                <button type="submit" class="icon-btn" style="height:33px;width:33px" title="Cari">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="6.4"/><path d="m16 16 4.2 4.2"/>
                    </svg>
                </button>
            </div>
        </form>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Peran</th>
                        <th>Unit</th>
                        <th>Terdaftar</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengguna as $p)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="avatar" style="{{ $p->role === \App\Enums\Role::Admin ? 'background:#0C1424;color:#fff' : ($p->role === \App\Enums\Role::Petugas ? 'background:#E8F8F1;color:#0B7A55' : '') }}">
                                        {{ $p->inisial }}
                                    </div>
                                    <div>
                                        <span class="ttl">{{ $p->nama }}</span>
                                        <span class="meta">{{ $p->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $p->role === \App\Enums\Role::Admin ? 'b-admin' : ($p->role === \App\Enums\Role::Petugas ? 'b-verifikasi' : 'b-netral') }}">
                                    <i></i>{{ $p->role->value }}
                                </span>
                            </td>
                            <td>
                                @if ($p->unit)
                                    <span class="badge b-netral">{{ $p->unit->nama }}</span>
                                @else
                                    <span style="color:var(--ink-3)">—</span>
                                @endif
                            </td>
                            <td style="color:var(--ink-3)">{{ $p->created_at?->translatedFormat('j M Y') }}</td>
                            <td style="text-align:right">
                                <button type="button" class="btn btn-s btn-sm" style="width:30px;padding:0" title="Ubah {{ $p->nama }}"
                                        onclick="bukaPengguna(@js([
                                            'id' => $p->id,
                                            'nama' => $p->nama,
                                            'email' => $p->email,
                                            'role' => $p->role->value,
                                            'unit_id' => $p->unit_id,
                                        ]))">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 20h4l10.5-10.5a2.1 2.1 0 0 0 0-3L17 5a2.1 2.1 0 0 0-3 0L3.5 15.5 3 20h1Z"/>
                                        <path d="m13.6 6.4 4 4"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty"><b>Tidak ada pengguna</b>Ubah filter atau kata kunci pencarian.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.paginasi', ['paginator' => $pengguna, 'keterangan' => 'pengguna'])
        <div style="padding:12px 16px;border-top:1px solid var(--line-2);font-size:11px;color:var(--ink-3)">
            Tidak tersedia aksi hapus — hanya pembaruan data agar riwayat tindak lanjut tetap utuh.
        </div>
    </div>

    <div class="card">
        <div class="card-h">
            <div class="card-t" id="judul-pengguna">Tambah pengguna baru</div>
            <span class="badge b-netral" style="margin-left:auto" id="lencana-pengguna">akun baru</span>
        </div>
        <form method="POST" action="{{ route('admin.pengguna.store') }}" id="form-pengguna">
            @csrf
            <input type="hidden" name="_method" value="POST" id="metode-pengguna">
            <div class="card-b" style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                <div class="field">
                    <label class="label" for="nama-pengguna">Nama lengkap <span>*</span></label>
                    <div class="input {{ $errors->has('nama') ? 'err' : '' }}">
                        <input id="nama-pengguna" name="nama" type="text" maxlength="100" required value="{{ old('nama') }}" placeholder="Dewi Kartika">
                    </div>
                    @error('nama')<div class="hint" style="color:#B91C1C">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label class="label" for="email-pengguna">Email <span>*</span></label>
                    <div class="input {{ $errors->has('email') ? 'err' : '' }}">
                        <input id="email-pengguna" name="email" type="email" maxlength="100" required value="{{ old('email') }}" placeholder="dewi@kampus.ac.id">
                    </div>
                    @error('email')
                        <div class="hint" style="color:#B91C1C">{{ $message }}</div>
                    @else
                        <div class="hint">Harus unik — kolom email diberi indeks UNIQUE.</div>
                    @enderror
                </div>

                <div class="field">
                    <label class="label" for="role-pengguna">Peran <span>*</span></label>
                    <div class="input {{ $errors->has('role') ? 'err' : '' }}">
                        <select id="role-pengguna" name="role" required>
                            @foreach ($peranTersedia as $r)
                                <option value="{{ $r->value }}" {{ old('role') === $r->value ? 'selected' : '' }}>{{ $r->value }}</option>
                            @endforeach
                        </select>
                        <span class="chev">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9.5 6 6 6-6"/>
                            </svg>
                        </span>
                    </div>
                    <div class="hint">Menentukan menu dan hak akses.</div>
                </div>

                <div class="field">
                    <label class="label" for="unit-pengguna">Unit</label>
                    <div class="input {{ $errors->has('unit_id') ? 'err' : '' }}">
                        <select id="unit-pengguna" name="unit_id">
                            <option value="">— tanpa unit —</option>
                            @foreach ($unit as $u)
                                <option value="{{ $u->id }}" {{ (int) old('unit_id') === $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                            @endforeach
                        </select>
                        <span class="chev">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9.5 6 6 6-6"/>
                            </svg>
                        </span>
                    </div>
                    <div class="hint">Wajib untuk petugas, kosong untuk mahasiswa.</div>
                </div>

                <div class="field">
                    <label class="label" for="password-pengguna">Kata sandi <span id="tanda-wajib">*</span></label>
                    <div class="input {{ $errors->has('password') ? 'err' : '' }}">
                        <input id="password-pengguna" name="password" type="password" autocomplete="new-password" placeholder="minimal 8 karakter">
                        <span class="suffix" style="cursor:pointer" onclick="acakSandi()">Acak</span>
                    </div>
                    @error('password')
                        <div class="hint" style="color:#B91C1C">{{ $message }}</div>
                    @else
                        <div class="hint" id="hint-password">Wajib saat membuat akun baru.</div>
                    @enderror
                </div>

                <div class="field">
                    <label class="label" for="password-konfirmasi">Konfirmasi kata sandi</label>
                    <div class="input">
                        <input id="password-konfirmasi" name="password_confirmation" type="password" autocomplete="new-password">
                    </div>
                </div>

                <div style="grid-column:1 / -1;display:flex;gap:9px;justify-content:flex-end;border-top:1px solid var(--line-2);padding-top:14px">
                    <button type="button" class="btn btn-s" onclick="bukaPengguna()">Reset</button>
                    <button type="submit" class="btn btn-p" id="tombol-pengguna">Simpan pengguna</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const formPengguna = document.getElementById('form-pengguna');
        const sandi = document.getElementById('password-pengguna');

        function bukaPengguna(data) {
            const ubah = !!data;
            formPengguna.action = ubah ? '{{ url('admin/pengguna') }}/' + data.id : '{{ route('admin.pengguna.store') }}';
            document.getElementById('metode-pengguna').value = ubah ? 'PUT' : 'POST';
            document.getElementById('judul-pengguna').textContent = ubah ? 'Ubah pengguna' : 'Tambah pengguna baru';
            document.getElementById('lencana-pengguna').textContent = ubah ? 'perbarui data akun' : 'akun baru';
            document.getElementById('nama-pengguna').value = ubah ? data.nama : '';
            document.getElementById('email-pengguna').value = ubah ? data.email : '';
            document.getElementById('role-pengguna').value = ubah ? data.role : 'mahasiswa';
            document.getElementById('unit-pengguna').value = ubah && data.unit_id ? String(data.unit_id) : '';
            sandi.value = '';
            document.getElementById('password-konfirmasi').value = '';
            sandi.required = !ubah;
            document.getElementById('tanda-wajib').style.display = ubah ? 'none' : '';
            document.getElementById('hint-password').textContent = ubah
                ? 'Biarkan kosong bila tidak ingin mengganti kata sandi.'
                : 'Wajib saat membuat akun baru.';
            document.getElementById('tombol-pengguna').textContent = ubah ? 'Simpan perubahan' : 'Simpan pengguna';
            document.getElementById('nama-pengguna').focus();
            formPengguna.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function acakSandi() {
            const huruf = 'abcdefghjkmnpqrstuvwxyz';
            const besar = 'ABCDEFGHJKMNPQRSTUVWXYZ';
            const angka = '23456789';
            const semua = huruf + besar + angka;
            let hasil = besar[Math.floor(Math.random() * besar.length)] + huruf[Math.floor(Math.random() * huruf.length)];
            for (let i = 0; i < 8; i++) hasil += semua[Math.floor(Math.random() * semua.length)];
            sandi.value = hasil;
            document.getElementById('password-konfirmasi').value = hasil;
            sandi.type = 'text';
            setTimeout(() => { sandi.type = 'password'; }, 4000);
        }
    </script>
@endsection
