@props(['bordered' => true])

<div {{ $attributes->class(['px-6 py-3 text-sm text-gray-600', 'border-b border-gray-100' => $bordered]) }}>
    <p>
        <span class="font-semibold text-gray-800">Keterangan:</span>
        R = Ringan · B = Berat · JML = Jumlah · APBD I = APBD Provinsi · APBD II = APBD Kabupaten/Kota · Masy = Masyarakat (swadaya)
    </p>
    <p class="mt-1 flex items-center gap-1.5 text-emerald-800">
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
        Geser tabel ke kanan untuk melihat semua kolom.
    </p>
</div>
