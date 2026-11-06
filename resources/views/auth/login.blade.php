<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk · SIAF</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
</head>
<body>
<div class="frame">
    <div class="login">
        <div class="login-l">
            <div style="display:flex;align-items:center;gap:11px;position:relative">
                <div class="brand-mark" style="width:38px;height:38px;flex:0 0 38px">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 6.5h16M4 12h10M4 17.5h13"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:700">SIAF</div>
                    <div style="font-size:10px;color:#7386A3;letter-spacing:.08em">FILKOM UB</div>
                </div>
            </div>

            <div class="login-quote" style="margin-top:120px">Setiap aspirasi mahasiswa<br>tercatat, terukur, dan <em>terjawab</em>.</div>

            <div class="login-points">
                <div class="lp">
                    <div class="lp-ic">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="8.6"/>
                            <path d="M12 7.4V12l3.2 2"/>
                        </svg>
                    </div>
                    <div>
                        <b style="color:#fff;font-weight:600">Tenggat otomatis per kategori.</b>
                        <br>Batas hari kategori menentukan tenggat, sehingga keterlambatan bisa dibuktikan.
                    </div>
                </div>
                <div class="lp">
                    <div class="lp-ic">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 6h13M8 12h13M8 18h13"/>
                            <circle cx="3.6" cy="6" r="1.1" fill="currentColor" stroke="none"/>
                            <circle cx="3.6" cy="12" r="1.1" fill="currentColor" stroke="none"/>
                            <circle cx="3.6" cy="18" r="1.1" fill="currentColor" stroke="none"/>
                        </svg>
                    </div>
                    <div>
                        <b style="color:#fff;font-weight:600">Riwayat penanganan terekam.</b>
                        <br>Setiap perubahan status menyimpan petugas, waktu, dan tanggapan.
                    </div>
                </div>
                <div class="lp">
                    <div class="lp-ic">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9.2" cy="8.4" r="3.4"/>
                            <path d="M3 20c0-3.2 2.8-5.4 6.2-5.4S15.4 16.8 15.4 20"/>
                            <path d="M16.4 5.3a3.3 3.3 0 0 1 0 6.2M18 14.9c2.1.5 3.5 2.1 3.5 4.4"/>
                        </svg>
                    </div>
                    <div>
                        <b style="color:#fff;font-weight:600">Tiga peran, satu alur.</b>
                        <br>Mahasiswa mengajukan, petugas menindaklanjuti, admin mengelola master data.
                    </div>
                </div>
            </div>

            <div style="margin-top:auto;padding-top:34px;font-size:10.5px;color:#5E6E8A;position:relative">Proyek Akhir Pemrograman Web · 2026</div>
        </div>

        <div class="login-r">
            <div class="login-card">
                <div style="width:44px;height:44px;border-radius:12px;background:#EFF4FF;display:grid;place-items:center;color:#1D4ED8;margin-bottom:20px">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3.5 13.9 9l5.6 2-5.6 2-1.9 5.5L10.1 13 4.5 11l5.6-2L12 3.5Z"/>
                    </svg>
                </div>
                <div class="login-t">Masuk ke akun Anda</div>
                <div class="login-s">Gunakan email kampus untuk mengakses sistem aspirasi.</div>

                <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:14px;margin-top:26px">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="8.6"/>
                                <path d="M12 7.8v4.6"/>
                                <circle cx="12" cy="15.9" r="1" fill="currentColor" stroke="none"/>
                            </svg>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <div class="field">
                        <label class="label" for="email">Email</label>
                        <div class="input @error('email') err @enderror">
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   placeholder="nama@kampus.ac.id" autocomplete="username" autofocus required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="password">Kata sandi</label>
                        <div class="input @error('password') err @enderror">
                            <input id="password" type="password" name="password" placeholder="••••••••••"
                                   autocomplete="current-password" required>
                        </div>
                    </div>

                    <label style="display:flex;align-items:center;gap:8px;font-size:11.5px;color:var(--ink-2);cursor:pointer">
                        <input type="checkbox" name="remember" value="1" style="width:15px;height:15px">
                        Ingat saya
                        <span style="margin-left:auto;color:var(--ink-3);font-weight:600">Lupa kata sandi? Hubungi admin</span>
                    </label>

                    <button type="submit" class="btn btn-p" style="height:43px;margin-top:4px">Masuk</button>

                    <div class="alert alert-info" style="margin-top:2px">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 4.2 21 19.4H3L12 4.2Z"/>
                            <path d="M12 9.6v4.2"/>
                            <circle cx="12" cy="16.6" r="1" fill="currentColor" stroke="none"/>
                        </svg>
                        <div>
                            <b>Peran ditentukan otomatis</b> dari kolom <code style="font-size:11px">pengguna.role</code>.
                            Mahasiswa diarahkan ke dashboard, petugas ke antrian tugas unitnya, admin ke panel master data.
                        </div>
                    </div>
                </form>

                <div class="demo">
                    <div class="demo-t">Akun demo (seeder)</div>
                    <div class="demo-row">
                        <span class="badge b-netral">super_admin</span>
                        <code>superadmin@filkom.ub.ac.id</code>
                    </div>
                    <div class="demo-row">
                        <span class="badge b-netral">petugas</span>
                        <code>petugas.kemahasiswaan@filkom.ub.ac.id</code>
                    </div>
                    <div class="demo-row">
                        <span class="badge b-netral">mahasiswa</span>
                        <code>mahasiswa@student.ub.ac.id</code>
                    </div>
                    <div class="demo-row" style="border-top:1px dashed var(--line);margin-top:6px;padding-top:8px">
                        <span style="color:var(--ink-3)">Kata sandi semua akun:</span>
                        <code>password</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
