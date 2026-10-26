@props([
    'page' => 'currentPage',
    'pages' => 'totalPages',
    'prev' => 'prevPage()',
    'next' => 'nextPage()',
    'summary' => true,
])

@php
    $buttonPadding = $summary ? 'px-3 sm:px-4' : 'px-3';
@endphp

{{-- Pager untuk daftar yang dipaginasi Alpine; tampilannya sama dengan resources/views/vendor/pagination/tailwind.blade.php. --}}
<nav aria-label="Navigasi halaman" {{ $attributes->class(['flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between']) }}>
    @if ($summary)
        <p class="text-sm text-gray-600">
            Menampilkan <span class="font-semibold text-gray-800" x-text="showingStart"></span>
            hingga <span class="font-semibold text-gray-800" x-text="showingEnd"></span>
            dari <span class="font-semibold text-gray-800" x-text="totalItems"></span> hasil
        </p>
    @endif

    <div @class(['flex items-center gap-2', 'w-full justify-between' => ! $summary])>
        <button type="button" @click="{{ $prev }}" :disabled="{{ $page }} <= 1"
            class="inline-flex h-10 items-center rounded-lg border border-gray-300 bg-white {{ $buttonPadding }} text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:border-gray-200 disabled:bg-gray-50 disabled:text-gray-400">Sebelumnya</button>

        <span class="whitespace-nowrap px-1 text-sm text-gray-600 sm:px-2">{{ $summary ? 'Halaman ' : '' }}<span class="font-semibold text-gray-800" x-text="{{ $page }}"></span> dari <span x-text="{{ $pages }}"></span></span>

        <button type="button" @click="{{ $next }}" :disabled="{{ $page }} >= {{ $pages }}"
            class="inline-flex h-10 items-center rounded-lg border border-gray-300 bg-white {{ $buttonPadding }} text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:border-gray-200 disabled:bg-gray-50 disabled:text-gray-400">Selanjutnya</button>
    </div>
</nav>
