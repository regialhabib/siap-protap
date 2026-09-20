<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengamatan</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h3 { margin: 0; padding: 0; font-size: 14px; font-weight: bold; }
        .meta-table { width: 100%; margin-bottom: 10px; border: none; font-size: 11px; }
        .meta-table td { padding: 2px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        table.data-table th { background-color: #f2f2f2; font-weight: bold; }
        table.data-table td.left { text-align: left; }
        
        .footer { width: 100%; margin-top: 30px; font-size: 11px; }
        .footer-table { width: 100%; border: none; }
        .footer-table td { text-align: right; width: 50%; padding-right: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <h3>LAPORAN BULANAN SERANGAN OPT TANAMAN PERKEBUNAN</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%">Wilayah Pengamatan</td>
            <td width="2%">:</td>
            <td width="83%">{{ empty($uppt_id) ? 'Semua Wilayah' : ($data->first()->uppt->nama_uppt ?? '-') }}</td>
        </tr>
        <tr>
            <td>Kabupaten</td>
            <td>:</td>
            <td>Rokan Hilir</td>
        </tr>
        <tr>
            <td>Periode Laporan</td>
            <td>:</td>
            <td>
                @if($jenis == 'bulanan') 
                    {{ \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') }} {{ $tahun }}
                @elseif($jenis == 'triwulan')
                    Kuartal {{ $triwulan }} Tahun {{ $tahun }}
                @else
                    Tahun {{ $tahun }}
                @endif
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Tanggal</th>
                <th rowspan="2">Wilayah / UPPT</th>
                <th rowspan="2">Jenis Komoditas</th>
                <th rowspan="2">Luas Komoditi (Ha)</th>
                <th rowspan="2">Jenis OPT</th>
                <th colspan="4">Luas Serangan (Ha)</th>
                <th colspan="4">Luas Pengendalian (Ha)</th>
                <th rowspan="2">Kondisi Serangan</th>
            </tr>
            <tr>
                <th>Ringan</th>
                <th>Sedang</th>
                <th>Berat</th>
                <th>Jumlah</th>
                <th>APBD Kabupaten</th>
                <th>APBD Provinsi</th>
                <th>Masyarakat</th>
                <th>APBN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $d)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ \Carbon\Carbon::parse($d->tanggal_pengamatan)->format('d/m/Y') }}</td>
                <td class="left">{{ $d->uppt->nama_uppt ?? '-' }}</td>
                <td class="left">{{ $d->komoditas->nama_komoditas ?? '-' }}</td>
                <td>{{ floatval($d->luas_komoditi_ha) }}</td>
                <td class="left">{{ $d->opt->nama_opt ?? '-' }}</td>
                
                <td>{{ floatval($d->serangan_ringan) }}</td>
                <td>{{ floatval($d->serangan_sedang) }}</td>
                <td>{{ floatval($d->serangan_berat) }}</td>
                <td><strong>{{ floatval($d->serangan_jumlah) }}</strong></td>
                
                <td>{{ floatval($d->kendali_apbd_kab) }}</td>
                <td>{{ floatval($d->kendali_apbd_prov) }}</td>
                <td>{{ floatval($d->kendali_masyarakat) }}</td>
                <td>{{ floatval($d->kendali_apbn) }}</td>
                
                <td class="left">{{ $d->kondisi_serangan ?: '-' }}</td>
            </tr>
            @endforeach
            
            @if($data->isEmpty())
            <tr>
                <td colspan="15">Nihil / Tidak ada data pengamatan pada periode ini.</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td></td>
                <td>..................., .................. 20....<br><br>Petugas Pengamat,<br><br><br><br><br>( ................................................. )</td>
            </tr>
        </table>
    </div>
</body>
</html>
