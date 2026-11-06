@if ($paginator->hasPages() || $paginator->total() > 0)
    <div class="pager">
        @if ($paginator->onFirstPage())
            <span class="btn btn-s btn-sm" style="opacity:.45">Sebelumnya</span>
        @else
            <a class="btn btn-s btn-sm" href="{{ $paginator->previousPageUrl() }}">Sebelumnya</a>
        @endif

        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $halaman => $url)
            @if ($halaman === $paginator->currentPage())
                <span class="btn btn-s btn-sm on">{{ $halaman }}</span>
            @else
                <a class="btn btn-s btn-sm" href="{{ $url }}">{{ $halaman }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="btn btn-s btn-sm" href="{{ $paginator->nextPageUrl() }}">Berikutnya</a>
        @else
            <span class="btn btn-s btn-sm" style="opacity:.45">Berikutnya</span>
        @endif

        <span class="info">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </span>
    </div>
@endif
