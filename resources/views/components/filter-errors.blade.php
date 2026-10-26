@props(['title' => null])

@if ($errors->any())
    <div class="mb-6 flex items-start gap-2 rounded-lg border border-red-300 bg-red-100 px-4 py-3 text-sm font-medium text-red-800 shadow-sm" role="alert">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
            @if ($title)
                <p class="font-bold">{{ $title }}</p>
            @endif
            <ul class="space-y-1">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
