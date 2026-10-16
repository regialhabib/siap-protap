<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tahunan {{ $tahun }}</title>
    <style>
        @page { margin: 25px 24px; }
        body { font-family: sans-serif; font-size: 8px; margin: 0; padding: 0; }
        .header { text-align: center; margin: 8px 0 18px; }
        .header h3 { margin: 0; font-size: 14px; font-weight: bold; }
        .meta-table { width: 100%; margin: 0 auto 14px; border: none; font-size: 10px; }
        .meta-table td { border: none; padding: 2px; }
        .data-table { width: 100%; margin: 0 auto; border-collapse: collapse; table-layout: fixed; }
        .data-table th,
        .data-table td { border: 1px solid #000; padding: 4px 3px; text-align: center; vertical-align: middle; }
        .data-table th { background-color: #f2f2f2; font-weight: bold; }
        .data-table td.left { text-align: left; }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="header">
        <h3>LAPORAN TAHUNAN SERANGAN OPT TANAMAN PERKEBUNAN</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Provinsi</strong></td>
            <td width="2%">:</td>
            <td width="83%">Jambi</td>
        </tr>
        <tr>
            <td><strong>Tahun Laporan</strong></td>
            <td>:</td>
            <td>{{ $tahun }}</td>
        </tr>
    </table>

    <table class="data-table">
        <colgroup>
            <col style="width: 3%;">
            <col style="width: 9%;">
            <col style="width: 8%;">
            <col style="width: 6%;">
            <col style="width: 11%;">
            <col style="width: 6%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 10%;">
            <col style="width: 7%;">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Kabupaten/Kota</th>
                <th rowspan="2">Jenis Komoditas</th>
                <th rowspan="2">Luas Komoditas (Ha)</th>
                <th rowspan="2">Jenis OPT</th>
                <th rowspan="2">Luas Pengendalian (Ha)</th>
                <th colspan="3">Luas Serangan (Ha)</th>
                <th colspan="5">Luas Pengendalian (Ha)</th>
                <th rowspan="2">Cara Pengendalian</th>
                <th rowspan="2">Kerugian Hasil (Rp 000)</th>
            </tr>
            <tr>
                <th>Ringan</th>
                <th>Berat</th>
                <th>Jumlah</th>
                <th>APBD I</th>
                <th>APBD II</th>
                <th>Masyarakat</th>
                <th>APBN</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="left">{{ $row->kabupaten }}</td>
                    <td class="left">{{ $row->komoditas }}</td>
                    <td>{{ $row->luas_komoditi_ha }}</td>
                    <td class="left">{{ $row->opt }}{{ $row->jenis_opt !== '' ? ' — '.$row->jenis_opt : '' }}</td>
                    <td>{{ $row->kendali_jumlah }}</td>
                    <td>{{ $row->serangan_ringan }}</td>
                    <td>{{ $row->serangan_berat }}</td>
                    <td><strong>{{ $row->serangan_jumlah }}</strong></td>
                    <td>{{ $row->kendali_apbd_prov }}</td>
                    <td>{{ $row->kendali_apbd_kab }}</td>
                    <td>{{ $row->kendali_masyarakat }}</td>
                    <td>{{ $row->kendali_apbn }}</td>
                    <td><strong>{{ $row->kendali_jumlah }}</strong></td>
                    <td class="left">{{ $row->cara_pengendalian }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="16">Tidak ada data pada tahun {{ $tahun }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
