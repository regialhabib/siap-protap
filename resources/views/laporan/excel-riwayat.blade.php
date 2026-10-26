@php
    $headerStyle = 'font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;';
    $cellStyle = 'vertical-align: middle; border: 1px solid #000000;';
    $numberStyle = 'text-align: center; vertical-align: middle; border: 1px solid #000000;';
    $totalStyle = 'font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;';
    $controlFields = ['kendali_apbd_prov', 'kendali_apbd_kab', 'kendali_masyarakat', 'kendali_apbn'];
    $perubahan = function (float $awal, float $akhir): string {
        $selisih = round($akhir - $awal, 2);
        $hektare = \App\Services\PengamatanHistoryService::hektare(abs($selisih));

        return match (true) {
            $selisih > 0 => "Bertambah {$hektare} Ha",
            $selisih < 0 => "Berkurang {$hektare} Ha",
            default => 'Tetap',
        };
    };
@endphp

<table>
    <tr>
        <td colspan="12" style="font-weight: bold; text-align: center; font-size: 14px;">RIWAYAT PENGAMATAN SERANGAN OPT</td>
    </tr>
    <tr>
        <td colspan="12" style="text-align: center;">Periode {{ $tahun }} (Februari {{ $tahun }} – Januari {{ $tahun + 1 }})</td>
    </tr>
    <tr><td colspan="12"></td></tr>
    <tr>
        <td>Kabupaten/Kota</td><td colspan="5">: {{ $chain->kabupaten }}</td>
        <td colspan="2">Komoditas</td><td colspan="4">: {{ $chain->komoditas }}</td>
    </tr>
    <tr>
        <td>UPPT</td><td colspan="5">: {{ $chain->uppt }}</td>
        <td colspan="2">OPT</td><td colspan="4">: {{ $chain->opt }}</td>
    </tr>
    <tr>
        <td>Kecamatan</td><td colspan="5">: {{ $chain->kecamatan }}</td>
        <td colspan="2">Jenis OPT</td><td colspan="4">: {{ $chain->jenis_opt ?? '-' }}</td>
    </tr>
    <tr>
        <td>Luas Komoditi (Ha)</td><td colspan="11" style="text-align: left;">: {{ $chain->luas_komoditi_ha }}</td>
    </tr>
    <tr><td colspan="12"></td></tr>
    <tr>
        <td rowspan="2" style="{{ $headerStyle }}">Bulan</td>
        <td rowspan="2" style="{{ $headerStyle }}">Kondisi Awal (Ha)</td>
        <td colspan="3" style="{{ $headerStyle }}">+ Serangan Baru (Ha)</td>
        <td colspan="5" style="{{ $headerStyle }}">− Pengendalian (Ha)</td>
        <td rowspan="2" style="{{ $headerStyle }}">= Kondisi Akhir (Ha)</td>
        <td rowspan="2" style="{{ $headerStyle }}">Perubahan</td>
    </tr>
    <tr>
        <td style="{{ $headerStyle }}">R</td>
        <td style="{{ $headerStyle }}">B</td>
        <td style="{{ $headerStyle }}">JML</td>
        <td style="{{ $headerStyle }}">APBD I</td>
        <td style="{{ $headerStyle }}">APBD II</td>
        <td style="{{ $headerStyle }}">Masy</td>
        <td style="{{ $headerStyle }}">APBN</td>
        <td style="{{ $headerStyle }}">JML</td>
    </tr>
    @foreach($data as $month)
        <tr>
            <td style="{{ $cellStyle }}">{{ $month->bulan_label }}</td>
            <td style="{{ $numberStyle }}">{{ $month->kondisi_awal }}</td>
            <td style="{{ $numberStyle }}">{{ $month->values['tambah_ringan'] }}</td>
            <td style="{{ $numberStyle }}">{{ $month->values['tambah_berat'] }}</td>
            <td style="font-weight: bold; {{ $numberStyle }}">{{ $month->values['tambah_jumlah'] }}</td>
            @foreach($controlFields as $field)
                <td style="{{ $numberStyle }}">{{ $month->values[$field] }}</td>
            @endforeach
            <td style="font-weight: bold; {{ $numberStyle }}">{{ $month->values['kendali_jumlah'] }}</td>
            <td style="font-weight: bold; {{ $numberStyle }}">{{ $month->kondisi_akhir }}</td>
            <td style="{{ $numberStyle }}">{{ $perubahan($month->kondisi_awal, $month->kondisi_akhir) }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="{{ $totalStyle }}">Total Periode</td>
        <td style="{{ $totalStyle }}">{{ $chain->total['kondisi_awal'] }}</td>
        <td style="{{ $totalStyle }}">{{ $chain->total['tambah_ringan'] }}</td>
        <td style="{{ $totalStyle }}">{{ $chain->total['tambah_berat'] }}</td>
        <td style="{{ $totalStyle }}">{{ $chain->total['tambah_jumlah'] }}</td>
        @foreach($controlFields as $field)
            <td style="{{ $totalStyle }}">{{ $chain->total[$field] }}</td>
        @endforeach
        <td style="{{ $totalStyle }}">{{ $chain->total['kendali_jumlah'] }}</td>
        <td style="{{ $totalStyle }}">{{ $chain->total['kondisi_akhir'] }}</td>
        <td style="{{ $totalStyle }}">{{ $perubahan($chain->total['kondisi_awal'], $chain->total['kondisi_akhir']) }}</td>
    </tr>
    <tr><td colspan="12"></td></tr>
    <tr>
        <td colspan="12">Hanya bulan yang ada pengamatan. Kondisi akhir terbawa sampai pengamatan berikutnya. R = Ringan, B = Berat, Masy = Swadaya Masyarakat.</td>
    </tr>
    <tr>
        <td colspan="12">Dicetak {{ now()->translatedFormat('d F Y') }}.</td>
    </tr>
</table>
