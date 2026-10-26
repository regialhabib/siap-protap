@php
    $headerStyle = 'font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;';
    $cellStyle = 'vertical-align: middle; border: 1px solid #000000;';
    $numberStyle = 'text-align: center; vertical-align: middle; border: 1px solid #000000;';
    $totalStyle = 'font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;';
    // Warna mengikuti tabel di layar; latar dibuat muda agar cetak hitam-putih tetap terbaca.
    $seranganHeaderStyle = $headerStyle.' background-color: #fee2e2; color: #991b1b;';
    $pengendalianHeaderStyle = $headerStyle.' background-color: #dbeafe; color: #1e40af;';
    $akhirHeaderStyle = $headerStyle.' background-color: #d1fae5; color: #065f46;';
    $seranganStyle = $numberStyle.' background-color: #fef2f2;';
    $pengendalianStyle = $numberStyle.' background-color: #eff6ff;';
    $akhirStyle = $numberStyle.' background-color: #ecfdf5; font-weight: bold;';
    $perubahanStyle = fn (object $perubahan): string => $numberStyle.' font-weight: bold; color: '.$perubahan->warna.';';
    $controlFields = ['kendali_apbd_prov', 'kendali_apbd_kab', 'kendali_masyarakat', 'kendali_apbn'];
    $perubahan = function (float $awal, float $akhir): object {
        $selisih = round($akhir - $awal, 2);
        $hektare = \App\Services\PengamatanHistoryService::hektare(abs($selisih));

        return match (true) {
            $selisih > 0 => (object) ['teks' => "Bertambah {$hektare} Ha", 'warna' => '#b91c1c'],
            $selisih < 0 => (object) ['teks' => "Berkurang {$hektare} Ha", 'warna' => '#047857'],
            default => (object) ['teks' => 'Tetap', 'warna' => '#4b5563'],
        };
    };
    $totalPerubahan = $perubahan($chain->total['kondisi_awal'], $chain->total['kondisi_akhir']);
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
        <td colspan="3" style="{{ $seranganHeaderStyle }}">+ Serangan Baru (Ha)</td>
        <td colspan="5" style="{{ $pengendalianHeaderStyle }}">− Pengendalian (Ha)</td>
        <td rowspan="2" style="{{ $akhirHeaderStyle }}">= Kondisi Akhir (Ha)</td>
        <td rowspan="2" style="{{ $headerStyle }}">Perubahan</td>
    </tr>
    <tr>
        <td style="{{ $seranganHeaderStyle }}">R</td>
        <td style="{{ $seranganHeaderStyle }}">B</td>
        <td style="{{ $seranganHeaderStyle }}">JML</td>
        <td style="{{ $pengendalianHeaderStyle }}">APBD I</td>
        <td style="{{ $pengendalianHeaderStyle }}">APBD II</td>
        <td style="{{ $pengendalianHeaderStyle }}">Masy</td>
        <td style="{{ $pengendalianHeaderStyle }}">APBN</td>
        <td style="{{ $pengendalianHeaderStyle }}">JML</td>
    </tr>
    @foreach($data as $month)
        @php $bulanPerubahan = $perubahan($month->kondisi_awal, $month->kondisi_akhir); @endphp
        <tr>
            <td style="{{ $cellStyle }}">{{ $month->bulan_label }}</td>
            <td style="{{ $numberStyle }}">{{ $month->kondisi_awal }}</td>
            <td style="{{ $seranganStyle }}">{{ $month->values['tambah_ringan'] }}</td>
            <td style="{{ $seranganStyle }}">{{ $month->values['tambah_berat'] }}</td>
            <td style="{{ $seranganStyle }} font-weight: bold; color: #b91c1c;">{{ $month->values['tambah_jumlah'] }}</td>
            @foreach($controlFields as $field)
                <td style="{{ $pengendalianStyle }}">{{ $month->values[$field] }}</td>
            @endforeach
            <td style="{{ $pengendalianStyle }} font-weight: bold; color: #1d4ed8;">{{ $month->values['kendali_jumlah'] }}</td>
            <td style="{{ $akhirStyle }}">{{ $month->kondisi_akhir }}</td>
            <td style="{{ $perubahanStyle($bulanPerubahan) }}">{{ $bulanPerubahan->teks }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="{{ $totalStyle }}">Total Periode</td>
        <td style="{{ $totalStyle }}">{{ $chain->total['kondisi_awal'] }}</td>
        <td style="{{ $totalStyle }}">{{ $chain->total['tambah_ringan'] }}</td>
        <td style="{{ $totalStyle }}">{{ $chain->total['tambah_berat'] }}</td>
        <td style="{{ $totalStyle }} color: #b91c1c;">{{ $chain->total['tambah_jumlah'] }}</td>
        @foreach($controlFields as $field)
            <td style="{{ $totalStyle }}">{{ $chain->total[$field] }}</td>
        @endforeach
        <td style="{{ $totalStyle }} color: #1d4ed8;">{{ $chain->total['kendali_jumlah'] }}</td>
        <td style="{{ $totalStyle }}">{{ $chain->total['kondisi_akhir'] }}</td>
        <td style="{{ $totalStyle }} color: {{ $totalPerubahan->warna }};">{{ $totalPerubahan->teks }}</td>
    </tr>
    <tr><td colspan="12"></td></tr>
    <tr>
        <td colspan="12">Hanya bulan yang ada pengamatan. Kondisi akhir terbawa sampai pengamatan berikutnya. R = Ringan, B = Berat, Masy = Swadaya Masyarakat.</td>
    </tr>
    <tr>
        <td colspan="12">Dicetak {{ now()->translatedFormat('d F Y') }}.</td>
    </tr>
</table>
