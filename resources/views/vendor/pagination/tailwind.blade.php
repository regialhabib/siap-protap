@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-600">
            Menampilkan <span class="font-semibold text-gray-800">{{ $paginator->firstItem() }}</span>
            hingga <span class="font-semibold text-gray-800">{{ $paginator->lastItem() }}</span>
            dari <span class="font-semibold text-gray-800">{{ $paginator->total() }}</span> hasil
        </p>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-10 cursor-not-allowed items-center rounded-lg border border-gray-200 bg-gray-50 px-3 sm:px-4 text-sm font-semibold text-gray-400">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-10 items-center rounded-lg border border-gray-300 bg-white px-3 text-sm sm:px-4 font-semibold text-gray-700 hover:bg-gray-50">Sebelumnya</a>
            @endif

            <span class="whitespace-nowrap px-1 text-sm text-gray-600 sm:px-2">Halaman <span class="font-semibold text-gray-800">{{ $paginator->currentPage() }}</span> dari {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-10 items-center rounded-lg border border-gray-300 bg-white px-3 text-sm sm:px-4 font-semibold text-gray-700 hover:bg-gray-50">Selanjutnya</a>
            @else
                <span class="inline-flex h-10 cursor-not-allowed items-center rounded-lg border border-gray-200 bg-gray-50 px-3 sm:px-4 text-sm font-semibold text-gray-400">Selanjutnya</span>
            @endif
        </div>
    </nav>
@endif
