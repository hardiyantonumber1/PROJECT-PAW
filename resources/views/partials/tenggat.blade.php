@php
    /** @var \App\Models\Aspirasi $a */
    $warna = match (true) {
        $a->status === \App\Enums\StatusAspirasi::Selesai => '#0B7A55',
        $a->status === \App\Enums\StatusAspirasi::Ditolak => 'var(--ink-3)',
        $a->terlambat => '#B91C1C',
        default => 'var(--ink-2)',
    };
@endphp

@if ($a->status === \App\Enums\StatusAspirasi::Selesai)
    <span style="font-size:11.5px;color:{{ $warna }};font-weight:600;white-space:nowrap">
        Selesai {{ $a->selesai_pada?->translatedFormat('j M') }}
    </span>
@elseif ($a->status === \App\Enums\StatusAspirasi::Ditolak)
    <span style="font-size:11.5px;color:{{ $warna }};font-weight:600">—</span>
@elseif ($a->terlambat)
    <span class="badge b-late"><i></i>Terlambat {{ abs($a->hari_tersisa) }} hari</span>
@else
    <span style="font-size:11.5px;color:{{ $warna }};font-weight:600;white-space:nowrap">
        {{ $a->tenggat->translatedFormat('j M') }} · {{ $a->label_tenggat }}
    </span>
@endif
