@php
    /** @var \App\Models\Aspirasi|null $aspirasi */
    $ubah = isset($aspirasi) && $aspirasi !== null;
    $nilai = fn (string $kolom, $bawaan = null) => old($kolom, $ubah ? $aspirasi->{$kolom} : $bawaan);
@endphp

<form method="POST" action="{{ $aksi }}">
    @csrf
    @if ($ubah)
        @method('PUT')
    @endif

    <div class="grid-2e">
        <div class="card">
            <div class="card-h">
                <div class="card-t">{{ $ubah ? 'Ubah data aspirasi' : 'Formulir aspirasi' }}</div>
                @if ($ubah)
                    <span class="badge {{ $aspirasi->status->badge() }}" style="margin-left:auto"><i></i>{{ $aspirasi->status->value }}</span>
                @else
                    <span class="badge b-diajukan" style="margin-left:auto"><i></i>Akan tersimpan sebagai Diajukan</span>
                @endif
            </div>
            <div class="card-b" style="display:flex;flex-direction:column;gap:16px">
                <div class="field">
                    <label class="label" for="judul">Judul aspirasi <span>*</span></label>
                    <div class="input {{ $errors->has('judul') ? 'err' : '' }}">
                        <input id="judul" name="judul" type="text" maxlength="150" required
                               value="{{ $nilai('judul') }}" placeholder="Ringkas inti permasalahan"
                               data-hitung="hitung-judul">
                        <span class="suffix"><span id="hitung-judul">{{ strlen((string) $nilai('judul')) }}</span> / 150</span>
                    </div>
                    <div class="hint">Minimal 10 karakter, maksimal 150 karakter. Tuliskan inti permasalahan.</div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="field">
                        <label class="label" for="kategori_id">Kategori <span>*</span></label>
                        <div class="input {{ $errors->has('kategori_id') ? 'err' : '' }}">
                            <select id="kategori_id" name="kategori_id" required data-kategori>
                                <option value="" disabled {{ $nilai('kategori_id') ? '' : 'selected' }}>Pilih kategori</option>
                                @foreach ($kategori as $k)
                                    <option value="{{ $k->id }}" data-batas="{{ $k->batas_hari }}"
                                        {{ (int) $nilai('kategori_id') === $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }} ({{ $k->batas_hari }} hari)
                                    </option>
                                @endforeach
                            </select>
                            <span class="chev">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m6 9.5 6 6 6-6"/>
                                </svg>
                            </span>
                        </div>
                        <div class="hint">
                            @if ($ubah)
                                Mengubah kategori menghitung ulang tenggat dari tanggal masuk.
                            @else
                                Menentukan batas hari penanganan.
                            @endif
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="unit_id">Unit tujuan <span>*</span></label>
                        <div class="input {{ $errors->has('unit_id') ? 'err' : '' }}">
                            <select id="unit_id" name="unit_id" required>
                                <option value="" disabled {{ $nilai('unit_id') ? '' : 'selected' }}>Pilih unit</option>
                                @foreach ($unit as $u)
                                    <option value="{{ $u->id }}" {{ (int) $nilai('unit_id') === $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                                @endforeach
                            </select>
                            <span class="chev">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m6 9.5 6 6 6-6"/>
                                </svg>
                            </span>
                        </div>
                        <div class="hint">Unit yang berwenang menindaklanjuti.</div>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="isi">Uraian aspirasi <span>*</span></label>
                    <div class="input textarea {{ $errors->has('isi') ? 'err' : '' }}">
                        <textarea id="isi" name="isi" maxlength="2000" required data-hitung="hitung-isi"
                                  placeholder="Jelaskan kronologi, lokasi, waktu, dan dampak permasalahan">{{ $nilai('isi') }}</textarea>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <div class="hint">Minimal 20 karakter. Jelaskan waktu, lokasi, dan dampaknya.</div>
                        <div class="hint" style="font-weight:600"><span id="hitung-isi">{{ strlen((string) $nilai('isi')) }}</span> / 2.000</div>
                    </div>
                </div>

                @unless ($ubah)
                    <label style="display:flex;align-items:flex-start;gap:9px;padding:12px 13px;background:#FAFBFD;border:1px solid var(--line-2);border-radius:10px;cursor:pointer">
                        <input type="checkbox" name="pernyataan" value="1" required {{ old('pernyataan') ? 'checked' : '' }}
                               style="width:15px;height:15px;margin-top:2px;flex:0 0 15px">
                        <span style="font-size:11.5px;color:var(--ink-2);line-height:1.6">
                            Saya menyatakan informasi ini benar dan belum pernah diajukan sebelumnya.
                        </span>
                    </label>
                @endunless

                <div style="display:flex;gap:9px;justify-content:flex-end;border-top:1px solid var(--line-2);padding-top:15px">
                    <a class="btn btn-s" href="{{ $batal }}">Batal</a>
                    <button type="submit" class="btn btn-p">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.5 3.8 3.6 10.4l6.1 2.4 2.4 6.1 8.4-15.1Z"/>
                            <path d="m9.7 12.8 4.2-4.2"/>
                        </svg>{{ $tombol }}
                    </button>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="card">
                <div class="card-h">
                    <div class="card-t">Pratinjau tenggat</div>
                </div>
                <div class="card-b" style="display:flex;flex-direction:column;gap:12px">
                    <div class="kv">
                        <div class="kv-row">
                            <div class="kv-k">Tanggal masuk</div>
                            <div class="kv-v">{{ $ubah ? $aspirasi->tanggal_masuk->translatedFormat('j M Y · H.i') : now()->translatedFormat('j M Y · H.i') }}</div>
                        </div>
                        <div class="kv-row">
                            <div class="kv-k">Kategori</div>
                            <div class="kv-v" id="pratinjau-kategori">—</div>
                        </div>
                        <div class="kv-row">
                            <div class="kv-k">Batas hari</div>
                            <div class="kv-v" id="pratinjau-batas">—</div>
                        </div>
                    </div>
                    <div style="background:#EFF4FF;border:1px solid #D3E1FF;border-radius:11px;padding:13px 14px">
                        <div style="font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#4B72C7">Tenggat penanganan</div>
                        <div style="font-size:19px;font-weight:800;letter-spacing:-.02em;color:#1E40AF;margin-top:4px" id="pratinjau-tenggat">—</div>
                        <div style="font-size:11px;color:#4B72C7;margin-top:3px" id="pratinjau-ket">Pilih kategori untuk menghitung tenggat</div>
                    </div>
                    <div style="font-size:11px;color:var(--ink-3);line-height:1.7">
                        Nilai tenggat disimpan pada baris aspirasi saat pengajuan, sehingga perubahan
                        <b>batas_hari</b> kategori tidak mengubah aspirasi yang sudah masuk.
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-h">
                    <div class="card-t">Ketentuan pengajuan</div>
                </div>
                <div class="card-b" style="display:flex;flex-direction:column;gap:10px">
                    <div style="display:flex;gap:9px;font-size:11.5px;color:var(--ink-2);line-height:1.6">
                        <span style="color:#2563EB;font-weight:800">1</span>Status awal selalu <b>Diajukan</b> dan hanya dapat diubah oleh petugas.
                    </div>
                    <div style="display:flex;gap:9px;font-size:11.5px;color:var(--ink-2);line-height:1.6">
                        <span style="color:#2563EB;font-weight:800">2</span>Aspirasi hanya bisa diubah atau dihapus selama status masih <b>Diajukan</b>.
                    </div>
                    <div style="display:flex;gap:9px;font-size:11.5px;color:var(--ink-2);line-height:1.6">
                        <span style="color:#2563EB;font-weight:800">3</span>Penolakan wajib disertai alasan yang dapat dibaca pelapor.
                    </div>
                    <div style="display:flex;gap:9px;font-size:11.5px;color:var(--ink-2);line-height:1.6">
                        <span style="color:#2563EB;font-weight:800">4</span>Setiap tindak lanjut tercatat lengkap dengan nama petugas dan waktu.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    // Penghitung karakter sederhana untuk judul & uraian.
    document.querySelectorAll('[data-hitung]').forEach(function (el) {
        const target = document.getElementById(el.dataset.hitung);
        const segarkan = () => target.textContent = el.value.length.toLocaleString('id-ID');
        el.addEventListener('input', segarkan);
        segarkan();
    });

    // Pratinjau tenggat: tanggal masuk + batas hari kategori.
    (function () {
        const pilih = document.querySelector('[data-kategori]');
        const elKategori = document.getElementById('pratinjau-kategori');
        const elBatas = document.getElementById('pratinjau-batas');
        const elTenggat = document.getElementById('pratinjau-tenggat');
        const elKet = document.getElementById('pratinjau-ket');
        if (!pilih) return;

        const mulai = new Date('{{ $ubah ? $aspirasi->tanggal_masuk->toDateString() : now()->toDateString() }}T00:00:00');

        const segarkan = function () {
            const opsi = pilih.options[pilih.selectedIndex];
            const batas = parseInt(opsi?.dataset?.batas || '0', 10);
            if (!batas) {
                elKategori.textContent = '—';
                elBatas.textContent = '—';
                elTenggat.textContent = '—';
                elKet.textContent = 'Pilih kategori untuk menghitung tenggat';
                return;
            }
            const tenggat = new Date(mulai.getTime() + batas * 86400000);
            const fmt = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            elKategori.textContent = opsi.textContent.replace(/\s*\(\d+ hari\)\s*$/, '').trim();
            elBatas.textContent = batas + ' hari';
            elTenggat.textContent = fmt.format(tenggat);
            elKet.textContent = batas + ' hari setelah tanggal masuk';
        };

        pilih.addEventListener('change', segarkan);
        segarkan();
    })();
</script>
