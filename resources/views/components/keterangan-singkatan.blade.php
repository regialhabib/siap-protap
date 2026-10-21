@props(['bordered' => true])

<p {{ $attributes->class(['px-6 py-2.5 text-xs text-gray-500', 'border-b border-gray-100' => $bordered]) }}>
    <span class="font-semibold text-gray-700">Keterangan:</span>
    R = Ringan · B = Berat · JML = Jumlah · APBD I = APBD Provinsi · APBD II = APBD Kabupaten/Kota · Masy = Masyarakat (swadaya)
</p>
