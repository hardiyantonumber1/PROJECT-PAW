<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'SIAF') · SIAF</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
</head>
<body>
<div class="frame">
    @include('partials.sidebar')

    <div class="main">
        @include('partials.topbar')

        <div class="content">
            @include('partials.flash')

            @yield('isi')
        </div>
    </div>
</div>

{{-- Dialog konfirmasi hapus dipakai ulang di beberapa halaman. --}}
<dialog class="modal" id="dialog-hapus">
    <form method="POST" id="form-hapus">
        @csrf
        @method('DELETE')
        <div class="modal-h">
            <div class="card-t">Hapus data?</div>
        </div>
        <div class="modal-b">
            <div class="alert alert-danger">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 4.2 21 19.4H3L12 4.2Z"/><path d="M12 9.6v4.2"/>
                    <circle cx="12" cy="16.6" r="1" fill="currentColor" stroke="none"/>
                </svg>
                <div id="dialog-pesan">Tindakan ini tidak dapat dibatalkan.</div>
            </div>
        </div>
        <div class="modal-f">
            <button type="button" class="btn btn-s" onclick="document.getElementById('dialog-hapus').close()">Batal</button>
            <button type="submit" class="btn btn-d">Hapus</button>
        </div>
    </form>
</dialog>

<script>
    // Pembungkus agar tombol inline cukup memanggil konfirmasiHapus(url, pesan).
    window.konfirmasiHapus = function (url, pesan) {
        document.getElementById('form-hapus').action = url;
        document.getElementById('dialog-pesan').textContent = pesan || 'Tindakan ini tidak dapat dibatalkan.';
        document.getElementById('dialog-hapus').showModal();
    };

    // Buka dialog hapus dengan aksi & pesan sesuai tombol yang diklik.
    document.addEventListener('click', function (e) {
        const pemicu = e.target.closest('[data-hapus]');
        if (!pemicu) return;
        e.preventDefault();
        document.getElementById('form-hapus').action = pemicu.dataset.hapus;
        document.getElementById('dialog-pesan').textContent = pemicu.dataset.pesan || 'Tindakan ini tidak dapat dibatalkan.';
        document.getElementById('dialog-hapus').showModal();
    });
</script>
</body>
</html>
