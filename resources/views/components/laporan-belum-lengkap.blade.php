@props(['message'])

<div class="flex flex-col items-center gap-2 px-6 py-12 text-center">
    <svg class="h-10 w-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <p class="max-w-xl text-base font-semibold text-gray-900">{{ $message }}</p>
    <p class="max-w-xl text-sm text-gray-600">Laporan dibuat setelah setiap bulannya memiliki data pengamatan.</p>
</div>
