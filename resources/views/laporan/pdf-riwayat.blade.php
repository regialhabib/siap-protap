<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Riwayat Pengamatan</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 14px; }
        .header h3 { margin: 0; padding: 0; font-size: 14px; font-weight: bold; }
        .header p { margin: 4px 0 0; font-size: 11px; }
        .meta-table { width: 100%; margin-bottom: 10px; border: none; font-size: 11px; }
        .meta-table td { padding: 2px; }

        table.summary-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 11px; }
        table.summary-table td { border: 1px solid #000; padding: 6px; text-align: center; }
        table.summary-table strong { display: block; margin-top: 2px; font-size: 13px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        table.data-table th { background-color: #f2f2f2; font-weight: bold; }
        table.data-table td.left { text-align: left; }
        table.data-table tfoot td { background-color: #f2f2f2; font-weight: bold; }

        .note { font-size: 10px; color: #444; }
    </style>
</head>
<body>
    @php
        $hektare = fn ($value) => \App\Services\PengamatanHistoryService::hektare((float) $value);
        $controlFields = ['kendali_apbd_prov', 'kendali_apbd_kab', 'kendali_masyarakat', 'kendali_apbn'];
        $perubahan = function (float $awal, float $akhir) use ($hektare): string {
            $selisih = round($akhir - $awal, 2);

            return match (true) {
                $selisih > 0 => 'Bertambah '.$hektare($selisih).' Ha',
                $selisih < 0 => 'Berkurang '.$hektare(abs($selisih)).' Ha',
                default => 'Tetap',
            };
        };
    @endphp

    <div class="header">
        <h3>RIWAYAT PENGAMATAN SERANGAN OPT</h3>
        <p>Periode {{ $tahun }} (Februari {{ $tahun }} – Januari {{ $tahun + 1 }})</p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%">Kabupaten/Kota</td><td width="2%">:</td><td width="33%">{{ $chain->kabupaten }}</td>
            <td width="15%">Komoditas</td><td width="2%">:</td><td width="33%">{{ $chain->komoditas }}</td>
        </tr>
        <tr>
            <td>UPPT</td><td>:</td><td>{{ $chain->uppt }}</td>
            <td>OPT</td><td>:</td><td>{{ $chain->opt }}</td>
        </tr>
        <tr>
            <td>Kecamatan</td><td>:</td><td>{{ $chain->kecamatan }}</td>
            <td>Jenis OPT</td><td>:</td><td>{{ $chain->jenis_opt ?? '-' }}</td>
        </tr>
        <tr>
            <td>Luas Komoditi</td><td>:</td><td>{{ $hektare($chain->luas_komoditi_ha) }} Ha</td>
            <td></td><td></td><td></td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            <td>Kondisi awal periode<strong>{{ $hektare($chain->total['kondisi_awal']) }} Ha</strong></td>
            <td>+ Serangan baru<strong>{{ $hektare($chain->total['tambah_jumlah']) }} Ha</strong></td>
            <td>- Pengendalian<strong>{{ $hektare($chain->total['kendali_jumlah']) }} Ha</strong></td>
            <td>= Kondisi akhir<strong>{{ $hektare($chain->total['kondisi_akhir']) }} Ha</strong>{{ $perubahan($chain->total['kondisi_awal'], $chain->total['kondisi_akhir']) }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2">Bulan</th>
                <th rowspan="2">Kondisi Awal (Ha)</th>
                <th colspan="3">+ Serangan Baru (Ha)</th>
                <th colspan="5">- Pengendalian (Ha)</th>
                <th rowspan="2">= Kondisi Akhir (Ha)</th>
                <th rowspan="2">Perubahan</th>
            </tr>
            <tr>
                <th>R</th>
                <th>B</th>
                <th>JML</th>
                <th>APBD I</th>
                <th>APBD II</th>
                <th>Masy</th>
                <th>APBN</th>
                <th>JML</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $month)
                <tr>
                    <td class="left">{{ $month->bulan_label }}</td>
                    <td>{{ $hektare($month->kondisi_awal) }}</td>
                    <td>{{ $hektare($month->values['tambah_ringan']) }}</td>
                    <td>{{ $hektare($month->values['tambah_berat']) }}</td>
                    <td><strong>{{ $hektare($month->values['tambah_jumlah']) }}</strong></td>
                    @foreach($controlFields as $field)
                        <td>{{ $hektare($month->values[$field]) }}</td>
                    @endforeach
                    <td><strong>{{ $hektare($month->values['kendali_jumlah']) }}</strong></td>
                    <td><strong>{{ $hektare($month->kondisi_akhir) }}</strong></td>
                    <td>{{ $perubahan($month->kondisi_awal, $month->kondisi_akhir) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="left">Total Periode</td>
                <td>{{ $hektare($chain->total['kondisi_awal']) }}</td>
                <td>{{ $hektare($chain->total['tambah_ringan']) }}</td>
                <td>{{ $hektare($chain->total['tambah_berat']) }}</td>
                <td>{{ $hektare($chain->total['tambah_jumlah']) }}</td>
                @foreach($controlFields as $field)
                    <td>{{ $hektare($chain->total[$field]) }}</td>
                @endforeach
                <td>{{ $hektare($chain->total['kendali_jumlah']) }}</td>
                <td>{{ $hektare($chain->total['kondisi_akhir']) }}</td>
                <td>{{ $perubahan($chain->total['kondisi_awal'], $chain->total['kondisi_akhir']) }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="note">Hanya bulan yang ada pengamatan. Kondisi akhir terbawa sampai pengamatan berikutnya. R = Ringan, B = Berat, Masy = Swadaya Masyarakat.<br>Dicetak {{ now()->translatedFormat('d F Y') }}.</p>
</body>
</html>
