@if (session('sukses'))
    <div class="alert alert-info">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="9"/><path d="m8.4 12.2 2.5 2.5 4.7-4.9"/>
        </svg>
        <div>{{ session('sukses') }}</div>
    </div>
@endif

@if (session('galat'))
    <div class="alert alert-danger">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="8.6"/><path d="M12 7.8v4.6"/>
            <circle cx="12" cy="15.9" r="1" fill="currentColor" stroke="none"/>
        </svg>
        <div>{{ session('galat') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 4.2 21 19.4H3L12 4.2Z"/><path d="M12 9.6v4.2"/>
            <circle cx="12" cy="16.6" r="1" fill="currentColor" stroke="none"/>
        </svg>
        <div>
            <b>{{ $errors->count() }} data belum sesuai.</b>
            <ul style="margin:4px 0 0 16px">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
